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
        $allQuotes = $estimation->Quote;
        $user      = auth()->user();

        Meta::prependTitle($estimation->title)->setTitle('Estiomation Detail');

        if ($user->type != 'company') {
            $estimation = ProjectEstimation::with('userQuote')->first();
            $allQuotes  = $estimation->user_quotes;
        }

        return view('estimation::estimation.show.show', compact(
            'estimation',
            'allQuotes',
        ));
    }

    public function update(Request $request)
    {
        $form       = $request->form;
        $cards      = $request->cards;
        $newItems   = collect($request->newItems);
        $idMappings = [];

        try {
            DB::beginTransaction();

            self::beginDeleteAllItems($form['id']);

            // Create groups with their products and prices
            $newItems->where('type', 'group')->each(function ($group) use ($newItems, $form, &$idMappings) {
                $newGroup                 = $this->createEstimationGroup($form, $group);
                $idMappings[$group['id']] = $newGroup->id;

                $newItems->where('groupId', $group['id'])->each(function ($item) use ($newGroup, &$idMappings) {
                    $newProduct              = $this->createEstimationProduct($item, $newGroup);
                    $idMappings[$item['id']] = $newProduct->id;

                    if ($item['type'] === 'item') {
                        $this->createQuoteItems($item, $newProduct);
                    }
                });
            });

            // Process quote settings and update estimation details
            $this->estimateQuote($cards);
            ProjectEstimation::find($form['id'])->update([
                "title"                 => $form['title'],
                "issue_date"            => $form['issue_date'],
                "technical_description" => $form['technical_description'],
            ]);

            DB::commit();

            return response()->json($idMappings);

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function createEstimationStructure($newItems, $form)
    {
        $idMappings = [];

        $newItems->where('type', 'group')->each(function ($group) use ($newItems, $form, &$idMappings) {
            $newGroup                 = $this->createEstimationGroup($form, $group);
            $idMappings[$group['id']] = $newGroup->id;

            $newItems->where('groupId', $group['id'])->each(function ($item) use ($newGroup, &$idMappings) {
                $newProduct              = $this->createEstimationProduct($item, $newGroup);
                $idMappings[$item['id']] = $newProduct->id;

                if ($item['type'] === 'item') {
                    $this->createQuoteItems($item, $newProduct);
                }
            });
        });

        return $idMappings;
    }

    private function createEstimationGroup($form, $group)
    {
        return EstimationGroup::create([
            'estimation_id' => $form['id'],
            'group_name'    => $group['name'],
            'group_pos'     => $group['pos'],
        ]);
    }

    private function createEstimationProduct($item, $group)
    {
        return $group->estimation_products()->create([
            'name'        => $item['name'] ?? null,
            'comment'     => $item['content'] ?? null,
            'pos'         => $item['pos'],
            'type'        => $item['type'],
            'quantity'    => $item['quantity'] ?? 0,
            'unit'        => $item['unit'] ?? null,
            'is_optional' => $item['optional'] ?? false,
        ]);
    }

    private function createQuoteItems($item, $product)
    {
        if ($item['type'] === 'item' && ! empty($item['prices'])) {
            collect($item['prices'])->each(function ($price) use ($product) {
                $product->quoteItems()->create([
                    'estimate_quote_id' => $price['quoteId'],
                    'base_price'        => $price['singlePrice'],
                    'price'             => $price['singlePrice'],
                    'total_price'       => $price['totalPrice'],
                ]);
            });
        }
    }

    private function estimateQuote($quotes)
    {
        collect($quotes)->each(function ($quote, $key) {
            EstimateQuote::updateOrCreate(
                ["id" => $key],
                [
                    "gross"               => $quote['totals']['gross'],
                    "gross_with_discount" => $quote['totals']['grossIncludingDiscount'],
                    "net"                 => $quote['totals']['net'],
                    "net_with_discount"   => $quote['totals']['netIncludingDiscount'],
                    "tax"                 => $quote['settings']['vat'],
                    "discount"            => $quote['settings']['cashDiscount'],
                    "markup"              => $quote['settings']['markup'],
                ],
            );
        });
    }

    /**
     * Delete all estimation items 
     */
    private function beginDeleteAllItems($ids)
    {
        return EstimationGroup::where('estimation_id', $ids)->tap(
            fn ($groups) => $groups->each(fn ($group) =>
                $group->estimation_products()->tap(
                    fn ($products) => $products->each(
                        fn ($product) => $product->quoteItems()->delete()
                    )
                )->delete()
            )
        )->delete();
    }

    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();

            collect($request->items)->map(function ($item) {
                ProjectEstimationProduct::whereId($item)
                    ->tap(
                        fn ($products) => $products->each(
                            fn ($product) => $product->quoteItems()->delete()
                        )
                    )->delete();
            });

            collect($request->groups)->map(function ($group) {
                EstimationGroup::whereId($group)->delete();
            });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
