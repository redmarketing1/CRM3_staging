<?php

namespace Modules\Estimation\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmartTemplate;
use App\Models\SmartPromptQueue;
use Butschster\Head\Facades\Meta;
use Illuminate\Routing\Controller;
use Modules\Taskly\Entities\Project;
use Illuminate\Contracts\Support\Renderable;
use Modules\Taskly\Entities\EstimationGroup;
use Modules\Taskly\Entities\EstimateQuoteItem;
use Modules\Estimation\Entities\ProjectEstimation;

class EstimationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('estimation::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('estimation::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

    }

    /**
     * Show the specified resource.
     * @param ProjectEstimation $estimation 
     */
    public function show(ProjectEstimation $estimation)
    {
        $ai_description_field = null;
        $user                 = auth()->user();
        $desc_template        = SmartTemplate::where('type', 0)->first();


        Meta::prependTitle($estimation->title)->setTitle('Estiomation Detail');

        if ($user->type != 'company') {
            $estimation = ProjectEstimation::with('userQuote')->first();
        }


        if (isset($desc_template->id) && $user->type == 'company') {
            foreach ($estimation->products as $product) {
                if ($product->ai_description != '') {
                    $ai_description_field = true;
                    break;
                }
            }
        }

        // if (! isset($ai_description_field) && $user->type == 'company') {
        //     $sp_queues = SmartPromptQueue::where('type', 0)->where('estimation_id', $estimation->id)->count();
        //     if ($sp_queues > 0) {
        //         $ai_description_field = true;
        //     }
        // } 

        $project_id                    = $estimation->project_id;
        $total_prices                  = [];
        $allQuotes                     = $estimation->Quote;
        $final_id                      = 0;
        $client_final_quote_id         = 0;
        $sub_contractor_final_quote_id = 0;
        $first_quote_id                = 0;
        $smart_templates               = SmartTemplate::get();
        $quote_items_ids               = [];

        if ($user->type != 'company') {
            $allQuotes = $estimation->user_quotes;
        }


        if (isset($allQuotes) && count($allQuotes) > 0) {
            foreach ($allQuotes as $key => $quote) {
                $contractor["sc" . $quote->id]                              = $quote->user->name ?? $quote->title;
                $gross['gross_sc' . $quote->id]                             = $quote->gross;
                $gross_with_discount['gross_with_discount_sc' . $quote->id] = $quote->gross_with_discount;
                $net['net_sc' . $quote->id]                                 = $quote->net;
                $net_with_discount['net_with_discount_sc' . $quote->id]     = $quote->net_with_discount;
                $vat['tax_sc' . $quote->id]                                 = $quote->tax;
                $discount['discount_sc' . $quote->id]                       = currency_format_with_sym($quote->discount, '', '', false);
                $markup['markup_sc' . $quote->id]
                                              = $quote;
                if ($quote->is_final == 1) {
                    $final_id = $quote->id;
                }

                if ($quote->final_for_client == 1) {
                    $client_final_quote_id = $quote->id;
                }

                if ($quote->final_for_sub_contractor == 1) {
                    $sub_contractor_final_quote_id = $quote->id;
                }
            }

            $total_prices = [
                'contractors'         => $contractor,
                'net_with_discount'   => $net_with_discount,
                'gross_with_discount' => $gross_with_discount,
                'net'                 => $net,
                'gross'               => $gross,
                'tax'                 => $vat,
                'discount'            => $discount,
                'markup'              => $markup,
            ];

            foreach ($estimation->products as $key => $value) {
                $quote_items_ids[] = $value->id;
            }

            $quote_items = [];
            $result      = EstimateQuoteItem::whereIn('product_id', $quote_items_ids)->with('quote')->orderBy('estimate_quote_id')->get();

            foreach ($result as $row) {
                if ($user->type == "company") {
                    if (isset($row->quote->is_display) && $row->quote->is_display == 1) {
                        $quote_items[$row->product_id][] = $row;
                    }
                } else {
                    if (isset($row->quote->user_id) && $row->quote->user_id == $user->id) {
                        $quote_items[$row->product_id][] = $row;
                    }
                }
            }
        }

        return view('estimation::estimation.show.show', compact(
            'estimation',
            'total_prices',
            'allQuotes',
            'final_id',
            'smart_templates',
            'ai_description_field',
            'client_final_quote_id',
            'sub_contractor_final_quote_id',
            'quote_items',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('estimation::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
