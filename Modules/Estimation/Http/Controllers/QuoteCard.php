<?php

namespace Modules\Estimation\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Estimation\Entities\EstimateQuote;
use Modules\Estimation\Entities\ProjectEstimation;

class QuoteCard extends Controller
{
    /**
     * Handle the clone estimation request
     * @param Request $request
     */
    public function duplicateQuoteCard(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('estimation::estimation.show.section.duplicate-quote-card');
        } else {
            try {
                $estimateQuote = EstimateQuote::findOrFail($request->id);
                $this->cloneEstimateQuote($estimateQuote);

                return redirect()
                    ->back()
                    ->with('success', __('Estimation qoute cloned successfully'));

            } catch (\Exception $e) {
                return redirect()
                    ->back()
                    ->with('error', $e->getMessage());
            }
        }
    }

    /**
     * Clone an existing estimation quote with all its related data
     * @param ProjectEstimation $estimation 
     */
    public function cloneEstimateQuote(EstimateQuote $estimateQuote)
    {
        try {
            DB::beginTransaction();

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
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
