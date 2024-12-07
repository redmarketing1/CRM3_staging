<?php

namespace Modules\Estimation\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmartTemplate;
use App\Models\SmartPromptQueue;
use Butschster\Head\Facades\Meta;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Taskly\Entities\Project;
use Modules\Taskly\Entities\EstimateQuote;
use Illuminate\Contracts\Support\Renderable;
use Modules\Taskly\Entities\EstimationGroup;
use Modules\Taskly\Entities\EstimateQuoteItem;
use Modules\Estimation\Entities\ProjectEstimation;
use Modules\Taskly\Entities\ProjectEstimationProduct;

class EstimationController extends Controller
{
    /**
     * Show the specified resource.
     * @param ProjectEstimation $estimation 
     */
    public function show(ProjectEstimation $estimation)
    {
        $ai_description_field = null;
        $allQuotes            = $estimation->Quote;
        $user                 = auth()->user();


        Meta::prependTitle($estimation->title)->setTitle('Estiomation Detail');

        if ($user->type != 'company') {
            $estimation = ProjectEstimation::with('userQuote')->first();
            $allQuotes  = $estimation->user_quotes;
        }

        $quoteItems = EstimateQuoteItem::whereIn('product_id', $estimation->products->pluck('id'))
            ->with('quote')
            ->orderBy('estimate_quote_id')
            ->get()
            ->groupBy('product_id');

        return view('estimation::estimation.show.show', compact(
            'estimation',
            'allQuotes',
            'ai_description_field',
            'quoteItems',
        ));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id 
     */
    public function update(Request $request)
    {
        $form     = $request->form;
        $cards    = $request->cards;
        $groups   = $request->groups;
        $items    = $request->items;
        $comments = $request->comments;

        $prices = collect($items)->flatMap(function ($item) {
            return collect($item['prices'])->map(function ($price) use ($item) {
                return array_merge(['productId' => $item['id']], $price);
            });
        });
    
        self::updateItem($items, $form);
        self::updateComment($comments, $form);
        self::updateGroupItem($groups, $form);
        self::updateQuote($prices);
        self::estimateQuote($cards);

        ProjectEstimation::find($form['id'])->update([
            "title"                 => $form['title'],
            "issue_date"            => $form['issue_date'],
            "technical_description" => $form['technical_description'],
        ]);
    }

    private function estimateQuote($quotes)
    {
        foreach ($quotes ?? [] as $key => $quote) {
            EstimateQuote::updateOrCreate(["id" => $key],
                [
                    "gross"               => $quote['totals']['gross'],
                    "gross_with_discount" => $quote['totals']['grossIncludingDiscount'],
                    "net"                 => $quote['totals']['net'],
                    "net_with_discount"   => $quote['totals']['netIncludingDiscount'],

                    "tax"                 => $quote['settings']['vat'],
                    "discount"            => $quote['settings']['cashDiscount'],
                    "markup"              => $quote['settings']['markup'],
                ]);
        }
    }

    private function updateQuote($quotes)
    {
        foreach ($quotes ?? [] as $key => $quote) {
            EstimateQuoteItem::updateOrCreate([
                "estimate_quote_id" => $quote['id'],
                "product_id"        => $quote['productId'],
            ], [
                'price'       => $quote['singlePrice'],
                'total_price' => $quote['totalPrice'],
            ]);
        }
    }

    private function updateItem($items, $form)
    {
        foreach ($items ?? [] as $key => $item) {

            $existingItem = ProjectEstimationProduct::query();

            if ($existingItem->find($item['id'])) {
                $existingItem->update([
                    'project_estimation_id' => $form['id'],
                    'name'                  => $item['name'],
                    'pos'                   => $item['pos'],
                    'type'                  => 'item',
                    'quantity'              => $item['quantity'],
                    'unit'                  => $item['unit'],
                    'is_optional'           => $item['optional'],
                ]);
            } else {
                tap($existingItem->create([
                    'project_estimation_id' => $form['id'],
                    'name'                  => $item['name'],
                    'pos'                   => $item['pos'],
                    'type'                  => 'item',
                    'group_id'              => $item['groupId'],
                    'quantity'              => $item['quantity'],
                    'unit'                  => $item['unit'],
                    'is_optional'           => $item['optional'],
                ]), function ($qoute) use ($item) {
                    $prices = collect($item['prices'])->map(function ($price) use ($qoute) {
                        return array_merge(['productId' => $qoute['id']], $price);
                    });
                    self::updateQuote($prices);
                }
                );
            }
        }
    }

    private function updateComment($comments, $form)
    {
        foreach ($comments ?? [] as $key => $comment) {

            $existingItem = ProjectEstimationProduct::query();

            if ($existingItem->find($comment['id'])) {
                $existingItem->update([
                    'project_estimation_id' => $form['id'],
                    'description'           => $comment['content'],
                    'comment'               => $comment['content'],
                    'pos'                   => $comment['pos'],
                ]);
            } else {
                tap($existingItem->create([
                    'project_estimation_id' => $form['id'],
                    'description'           => $comment['content'],
                    'comment'               => $comment['content'],
                    'pos'                   => $comment['pos'],
                    'type'                  => 'comment',
                    'group_id'              => $comment['groupId'],
                ]), function ($qoute) use ($comment) {
                    $prices = collect($comment['prices'])->map(function ($price) use ($qoute) {
                        return array_merge(['productId' => $qoute['id']], $price);
                    });
                    self::updateQuote($prices);
                }
                );
            }
        }
    }

    private function updateGroupItem($items, $form)
    {
        foreach ($items ?? [] as $key => $item) {
            EstimationGroup::updateOrCreate(
                [
                    'id' => $item['id'],
                ],
                [
                    'estimation_id' => $form['id'],
                    'group_name'    => $item['name'],
                    'group_pos'     => $item['pos'],
                ],
            );
        }
    }

    public function destroy(Request $request)
    {
        if ($request->items) {
            ProjectEstimationProduct::whereIn('id', $request->items)
                ->delete();
        }

        if ($request->groups) {
            EstimationGroup::whereIn('id', $request->groups)
                ->delete();
        }
    }
}
