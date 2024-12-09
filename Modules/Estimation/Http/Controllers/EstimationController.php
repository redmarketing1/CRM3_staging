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
        $newItems = collect($request->newItems);

        $new_ids = [
            'items'    => [],
            'comments' => [],
            'groups'   => [],
        ];

        try {
            DB::beginTransaction();

            // Delete previous items
            ProjectEstimationProduct::where('project_estimation_id', $form['id'])->delete();
            EstimationGroup::where('estimation_id', $form['id'])->delete();

            // First create all groups
            $groupMapping = [];  // To store temporary group ID to new group ID mapping
            foreach ($newItems as $item) {
                if ($item['type'] === 'group') {
                    $newGroup = EstimationGroup::create([
                        'estimation_id' => $form['id'],
                        'group_name'    => $item['name'],
                        'group_pos'     => $item['pos'],
                    ]);

                    $groupMapping[$item['id']] = $newGroup->id;

                    if (strlen($item['id']) === 13) {
                        $new_ids['groups'][$item['id']] = $newGroup->id;
                    }
                }
            }

            // Then create all items and comments with correct group IDs
            foreach ($newItems as $item) {
                if ($item['type'] === 'item') {
                    $newItem = ProjectEstimationProduct::create([
                        'project_estimation_id' => $form['id'],
                        'group_id'              => $groupMapping[$item['groupId']] ?? null,
                        'name'                  => $item['name'],
                        'pos'                   => $item['pos'],
                        'type'                  => 'item',
                        'quantity'              => $item['quantity'],
                        'unit'                  => $item['unit'],
                        'is_optional'           => $item['optional'],
                    ]);

                    if (strlen($item['id']) === 13) {
                        $new_ids['items'][$item['id']] = $newItem->id;
                    }

                    // Process prices
                    if (! empty($item['prices'])) {
                        foreach ($item['prices'] as $price) {
                            EstimateQuoteItem::updateOrCreate([
                                'estimate_quote_id' => $price['id'],
                                'product_id'        => $newItem->id,
                            ], [
                                'price'       => $price['singlePrice'],
                                'total_price' => $price['totalPrice'],
                            ]);
                        }
                    }
                } elseif ($item['type'] === 'comment') {
                    $newComment = ProjectEstimationProduct::create([
                        'project_estimation_id' => $form['id'],
                        'group_id'              => $groupMapping[$item['groupId']] ?? null,
                        'comment'               => $item['content'],
                        'pos'                   => $item['pos'],
                        'type'                  => 'comment',
                    ]);

                    if (strlen($item['id']) === 13) {
                        $new_ids['comments'][$item['id']] = $newComment->id;
                    }
                }
            }

            // Process quote settings
            self::estimateQuote($cards);

            // Update estimation details
            ProjectEstimation::find($form['id'])->update([
                "title"                 => $form['title'],
                "issue_date"            => $form['issue_date'],
                "technical_description" => $form['technical_description'],
            ]);

            DB::commit();

            return response()->json([
                'items'    => $new_ids['items'],
                'comments' => $new_ids['comments'],
                'groups'   => $new_ids['groups'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

    private function updateItem($item, $form)
    {
        ProjectEstimationProduct::where('id', $item['id'])->delete();
        ProjectEstimationProduct::create([
            'project_estimation_id' => $form['id'],
            'name'                  => $item['name'],
            'pos'                   => $item['pos'],
            'type'                  => 'item',
            'group_id'              => $item['groupId'],
            'quantity'              => $item['quantity'],
            'unit'                  => $item['unit'],
            'is_optional'           => $item['optional'],
        ]);
    }

    private function updateComment($comments, $form)
    {
        $new_ids = [];

        foreach ($comments ?? [] as $key => $comment) {

            $existingItem = ProjectEstimationProduct::query();
            $tempId       = $comment['id'];

            if ($existingItem->find($comment['id'])) {
                $existingItem->update([
                    'project_estimation_id' => $form['id'],
                    'description'           => $comment['content'],
                    'comment'               => $comment['content'],
                    'pos'                   => $comment['pos'],
                    'group_id'              => $comment['groupId'],
                ]);
            } else {
                $newComment = tap($existingItem->create([
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

            if (strlen($tempId) === 13) { // Timestamp ID check
                $new_ids[$tempId] = $newComment->id;
            }
        }

        return $new_ids;
    }

    private function updateGroupItem($items, $form)
    {
        $new_ids = [];

        foreach ($items ?? [] as $key => $item) {

            $tempId = $item['id'];

            $newItem = EstimationGroup::updateOrCreate(
                [
                    'id' => $item['id'],
                ],
                [
                    'estimation_id' => $form['id'],
                    'group_name'    => $item['name'],
                    'group_pos'     => $item['pos'],
                ],
            );

            if (strlen($tempId) === 13) { // Timestamp ID check
                $new_ids[$tempId] = $newItem->id;
            }
        }

        return $new_ids;
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
