<?php

namespace Modules\Estimation\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Estimation\Entities\EstimateQuote;
use Modules\Estimation\Entities\ProjectEstimation;

class QuoteCardController extends Controller
{
    /**
     * Handle the clone estimation request
     * @param Request $request
     */
    public function duplicateQuoteCard(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('estimation::estimation.show.section.duplicate-quote-card');
        }

        return $request->isMethod('PUT')
            ? $this->updateQuote($request)
            : $this->cloneQuote($request);
    }

    /**
     * Update existing quote
     */
    private function updateQuote(Request $request)
    {
        try {
            DB::beginTransaction();

            $estimateQuote = EstimateQuote::findOrFail($request->id);

            if ($request->subContractor) {
                $subContractor = User::findOrFail(request('subContractor'));
            }

            // Update only the provided fields
            $estimateQuote->fill(array_filter([
                'title'   => $subContractor->name ?? $request->title,
                'user_id' => $subContractor->id ?? null,
            ]));

            // Check if anything actually changed
            if (! $estimateQuote->isDirty()) {
                return redirect()
                    ->back()
                    ->with('success', __('No changes were made'));
            }

            $estimateQuote->save();
            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('Quote has been updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Clone an existing estimation quote with all its related data
     * @param ProjectEstimation $estimation 
     */
    private function cloneQuote(Request $request)
    {
        try {
            DB::beginTransaction();

            $estimateQuote = EstimateQuote::findOrFail($request->id);

            if (request('subContractor')) {
                $subContractor = User::findOrFail(request('subContractor'));
            }

            // Clone the Quote
            $newEstimateQuote          = $estimateQuote->replicate();
            $newEstimateQuote->title   = $subContractor->name ?? request('title') ?? $newEstimateQuote->title . ' (Copy)';
            $newEstimateQuote->user_id = $subContractor->id ?? $newEstimateQuote->user_id;
            $newEstimateQuote->save();

            // Clone the Quote Item
            foreach ($estimateQuote->quoteItem as $quote) {
                $newQuote                    = $quote->replicate();
                $newQuote->estimate_quote_id = $newEstimateQuote->id;
                $newQuote->save();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('Estimation qoute cloned successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }


    /**
     * Update Quate Type Status 
     */
    public function quateTypesStatus(Request $request)
    {
        $estimateQuote = EstimateQuote::findOrFail($request->id);
        $type          = $request->type;
        $checkbox      = $request->integer('checkbox');

        $estimateQuote->update([
            'is_final'                 => 0,
            'final_for_client'         => 0,
            'final_for_sub_contractor' => 0,
        ]);

        switch ($type) {
            case 'clientQuote':
                $estimateQuote->final_for_client = $checkbox;
                break;
            case 'subcontractor':
                $estimateQuote->final_for_sub_contractor = $checkbox;
                break;
            case 'quote':
                $estimateQuote->is_final = $checkbox;
                break;
        }

        $estimateQuote->save();

        return response()->json([
            'success' => true,
            'message' => 'Quote status updated successfully',
        ]);

    }

    /**
     * Delete Quote and their related items 
     */
    public function deleteQuote(Request $request)
    {
        $estimateQuote = EstimateQuote::findOrFail($request->id);
        foreach ($estimateQuote->quoteItem as $item) {
            $item->delete();
        }
        $estimateQuote->delete();
    }
}
