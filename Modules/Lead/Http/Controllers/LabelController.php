<?php

namespace Modules\Lead\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lead\Entities\Deal;
use Modules\Lead\Entities\Label;
use Modules\Lead\Entities\Lead;
use Modules\Lead\Entities\Pipeline;
use Modules\Lead\Events\CreateLabel;
use Modules\Lead\Events\DestroyLabel;
use Modules\Lead\Events\UpdateLabel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('labels manage')) {
            $labels    = Label::select('labels.*', 'pipelines.name as pipeline')
                ->join('pipelines', 'pipelines.id', '=', 'labels.pipeline_id')
                ->where('pipelines.created_by', '=', creatorId())
                ->where('labels.created_by', '=', creatorId())
                ->where('labels.workspace_id', '=', getActiveWorkSpace())
                ->orderBy('labels.pipeline_id')->get()->sortBy('order');
            $pipelines = [];

            foreach ($labels as $label) {
                if (! array_key_exists($label->pipeline_id, $pipelines)) {
                    $pipelines[$label->pipeline_id]           = [];
                    $pipelines[$label->pipeline_id]['name']   = $label['pipeline'];
                    $pipelines[$label->pipeline_id]['labels'] = [];
                }
                $pipelines[$label->pipeline_id]['labels'][] = $label;
            }

            return view('lead::labels.index')->with('pipelines', $pipelines);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('labels create')) {
            $pipelines = Pipeline::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('lead::labels.create')->with('pipelines', $pipelines);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('labels create')) {

            $validator = Validator::make(
                $request->all(), [
                    'name'             => 'required',
                    'order'            => 'required',
                    'pipeline_id'      => 'required',
                    'background_color' => 'required',
                    'font_color'       => 'required',
                ],
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $label                   = new Label();
            $label->name             = $request->name;
            $label->order            = $request->order;
            $label->background_color = $request->background_color;
            $label->font_color       = $request->font_color;
            $label->pipeline_id      = $request->pipeline_id;
            $label->created_by       = creatorId();
            $label->workspace_id     = getActiveWorkSpace();
            $label->save();

            event(new CreateLabel($request, $label));

            return redirect()->route('labels.index')->with('success', __('Label successfully created!'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('lead::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Label $label)
    {
        if (Auth::user()->isAbleTo('labels edit')) {
            if ($label->created_by == creatorId() && $label->workspace_id == getActiveWorkSpace()) {
                $pipelines = Pipeline::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');

                return view('lead::labels.edit', compact('label', 'pipelines'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }

    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Label $label)
    {
        if (Auth::user()->isAbleTo('labels edit')) {

            if ($label->created_by == creatorId() && $label->workspace_id == getActiveWorkSpace()) {

                $validator = Validator::make(
                    $request->all(), [
                        'name'             => 'required',
                        'order'            => 'required',
                        'pipeline_id'      => 'required',
                        'background_color' => 'required',
                        'font_color'       => 'required',
                    ],
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('labels.index')->with('error', $messages->first());
                }

                $label->name             = $request->name;
                $label->order            = $request->order;
                $label->background_color = $request->background_color;
                $label->font_color       = $request->font_color;
                $label->pipeline_id      = $request->pipeline_id;
                $label->save();

                event(new UpdateLabel($request, $label));

                return redirect()->back()->with('success', __('Label successfully updated!'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Label $label)
    {
        if (Auth::user()->isAbleTo('labels delete')) {
            if ($label->created_by == creatorId() && $label->workspace_id == getActiveWorkSpace()) {
                $lead = Lead::where('labels', '=', $label->id)->where('created_by', $label->created_by)->count();
                $deal = Deal::where('labels', '=', $label->id)->where('created_by', $label->created_by)->count();
                if ($lead == 0 && $deal == 0) {

                    $label->delete();

                    event(new DestroyLabel($label));

                    return redirect()->route('labels.index')->with('success', __('Label successfully deleted!'));
                } else {
                    return redirect()->back()->with('error', __('There are some Lead and Deal on Label, please remove it first!'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
