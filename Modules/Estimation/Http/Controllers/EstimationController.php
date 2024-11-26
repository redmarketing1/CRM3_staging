<?php

namespace Modules\Estimation\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmartTemplate;
use App\Models\SmartPromptQueue;
use Butschster\Head\Facades\Meta;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Taskly\Entities\Project;
use Illuminate\Contracts\Support\Renderable;
use Modules\Taskly\Entities\EstimationGroup;
use Modules\Taskly\Entities\EstimateQuoteItem;
use Modules\Estimation\Entities\ProjectEstimation;
use Modules\Taskly\Entities\ProjectEstimationProduct;

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
     */
    public function update(Request $request)
    {
        $form    = $request->form;
        $request = collect($request->item);

        $items  = $request->where('type', 'item') ?? [];
        $prices = $items->flatMap(function ($item) {
            return collect($item['prices'])->map(function ($price) use ($item) {
                return array_merge(['productId' => $item['id']], $price);
            });
        });

        self::updateItem($items, $form);
        self::updateQuote($prices);
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
            ProjectEstimationProduct::updateOrCreate(
                [
                    'id' => $item['id'],
                    // 'project_estimation_id' => $form['id'],
                ],
                [
                    'project_estimation_id' => $form['id'],
                    'name'                  => $item['name'],
                    'pos'                   => $item['pos'],
                    'quantity'              => $item['quantity'],
                    'unit'                  => $item['unit'],
                    'is_optional'           => $item['optional'],
                ],
            );

        }
    }
}
