<?php

namespace Modules\Search\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Search\Service\GlobalSearch;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(GlobalSearch $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keywords');
        $results = $this->searchService->search($keyword);

        $html = collect($results)->map(function ($result) {
            return $result['view'];
        })->implode('');

        return response()->json(['html' => $html]);
    }
}
