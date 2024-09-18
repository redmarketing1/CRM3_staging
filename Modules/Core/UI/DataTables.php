<?php

namespace Modules\Core\UI;

use Illuminate\Contracts\Support\Responsable;

class DataTables implements Responsable
{
    /**  
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected $rawColumns = [];

    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected $defaultRawColumns = [
        'created',
    ];

    /**
     * Source of the table.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $source;

    /**
     * Create a new table instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $source
     * @return void
     */
    public function __construct($source = null)
    {
        $this->source = $source;
    }

    /**
     * Make table response for the resource.
     *
     * @param mixed $source 
     */
    public function make()
    {
        return $this->newTable();
    }

    /**
     * Create a new datatable instance;
     *
     * @param mixed $source 
     */
    public function newTable()
    {
        return datatables($this->source)
            // ->addColumn('checkbox', function ($entity) {
            //     return view('core::partials.table.checkbox', compact('entity'));
            // })
            // ->editColumn('status', function ($entity) {
            //     return $entity->is_active
            //         ? '<span class="dot green"></span>'
            //         : '<span class="dot red"></span>';
            // })
            ->editColumn('created_at', function ($entity) {
                return view('core::partials.table.date')->with('date', $entity->created_at);
            })
            ->editColumn('updated_at', function ($entity) {
                return view('core::partials.table.date')->with('date', $entity->updated_at);
            })
            ->rawColumns(array_merge($this->defaultRawColumns, $this->rawColumns));
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return $this->make()->toJson();
    }
}