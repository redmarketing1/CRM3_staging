<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Lead\Entities\Label;
use Modules\Taskly\Entities\Task;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Taskly\Entities\Stage;
use Modules\Project\Entities\Project;
use Modules\Taskly\Entities\EstimateQuote;
use Modules\Taskly\Entities\ProjectComment;
use Illuminate\Contracts\Support\Renderable;
use Modules\Taskly\Entities\ProjectEstimation;
use Modules\Taskly\Entities\ProjectClientFeedback;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('project::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('project::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id 
     */
    public function show(Project $project)
    {
        // if (auth()->user()->isAbleTo('project show')) {
        //     return redirect()
        //         ->back()
        //         ->with('error', __('Permission Denied.'));
        // }

        $daysleft         = $project->expired_date;
        $estimationStatus = ProjectEstimation::$statues;
        $statuesColor     = ProjectEstimation::$statuesColor;

        $chartData = $this->getProjectChart(
            [
                'workspace_id' => getActiveWorkSpace(),
                'project_id'   => $project->id,
                'duration'     => 'week',
            ],
        );


        $user = auth()->user();
        if ($project) {



            if ($user->type != "company") {
                $project_estimation_ids = EstimateQuote::where('project_id', $project->id)->where('user_id', Auth::user()->id)->pluck('project_estimation_id')->toArray();
                //	$project_estimation_ids2 = SubContractorEstimation::where('project_id', $id)->where('user_id', \Auth::user()->id)->pluck('project_estimation_id')->toArray();

                //	$project_estimation_ids = array_merge($project_estimation_ids1,$project_estimation_ids2);

                $project_estimations = ProjectEstimation::with(['all_quotes_list'])->where('project_id', $project->id)->where('init_status', 1)->whereIn("id", $project_estimation_ids)->get();

            } else {
                $project_estimations = ProjectEstimation::with(['all_quotes_list'])->where('project_id', $project->id)->where('init_status', 1)->get();
            }


            $active_estimation = ProjectEstimation::where('project_id', $project->id)->where('is_active', 1)->first();
            $feedbacks         = ProjectClientFeedback::where('project_id', $project->id)->whereNull('parent')->get();
            $comments          = ProjectComment::where('project_id', $project->id)->whereNull('parent')->get();
        }


        $site_money_format = site_money_format();

        $project_dropdowns = Label::get_project_dropdowns();

        $projectStatus      = isset($project_dropdowns['project_status']) ? $project_dropdowns['project_status'] : array();
        $status_labels      = isset($project_dropdowns['project_label']) ? $project_dropdowns['project_label'] : array();
        $priorities         = isset($project_dropdowns['priority']) ? $project_dropdowns['priority'] : array();
        $construction_types = isset($project_dropdowns['construction_type']) ? $project_dropdowns['construction_type'] : array();
        $properties         = isset($project_dropdowns['property']) ? $project_dropdowns['property'] : array();

        return view('project::project.show', compact(
            'project',
            'daysleft',
            'chartData',
            'project_estimations',
            'estimationStatus',
            'statuesColor',
            'site_money_format',
            'active_estimation',
            'feedbacks',
            'comments',
            'projectStatus',
            'status_labels',
            'priorities',
            'construction_types',
            'properties'),
        );

    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('project::edit');
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

    public function getProjectChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration'] && $arrParam['duration'] == 'week') {
            $previous_week = Project::getFirstSeventhWeekDay(-1);
            foreach ($previous_week['datePeriod'] as $dateObject) {
                $arrDuration[$dateObject->format('Y-m-d')] = $dateObject->format('D');
            }
        }

        $arrTask     = [
            'label' => [],
            'color' => [],
        ];
        $stages      = Stage::where('workspace_id', '=', $arrParam['workspace_id'])->orderBy('order');
        $stagesQuery = $stages->pluck('name', 'id')->toArray();
        foreach ($arrDuration as $date => $label) {
            $objProject = Task::select('status', DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->groupBy('status');
            if (isset($arrParam['project_id'])) {
                $objProject->where('project_id', '=', $arrParam['project_id']);
            }
            if (isset($arrParam['workspace_id'])) {
                $objProject->whereIn('project_id', function ($query) use ($arrParam) {
                    $query->select('id')->from('projects')->where('workspace', '=', $arrParam['workspace_id']);
                });
            }
            $data = $objProject->pluck('total', 'status')->all();
            foreach ($stagesQuery as $id => $stage) {
                $arrTask[$id][] = isset($data[$id]) ? $data[$id] : 0;
            }
            $arrTask['label'][] = __($label);
        }
        $arrTask['stages'] = $stagesQuery;
        $arrTask['color']  = $stages->pluck('color')->toArray();
        return $arrTask;
    }
}