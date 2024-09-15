<?php

namespace Modules\Project\Http\Controllers;

use App\Models\User;
use App\Models\Country;
use Illuminate\Http\Request;
use Modules\Lead\Entities\Label;
use Butschster\Head\Facades\Meta;
use Modules\Taskly\Entities\Task;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Taskly\Entities\Stage;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;
use Modules\Taskly\Entities\EstimateQuote;
use Illuminate\Contracts\Support\Renderable;
use Modules\Taskly\Entities\ProjectEstimation;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index(Request $request)
    {
        if (! Auth::user()->isAbleTo('project manage')) {
            return redirect()
                ->back()
                ->with('error', __('Permission Denied.'));
        }

        $objUser     = Auth::user();
        $projectUser = array();
        $city        = array();
        $state       = array();

        if (Auth::user()->hasRole('client')) {
            $projects = Project::select('projects.*')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->projectonly()->where('client_projects.client_id', '=', Auth::user()->id)->where('projects.workspace', '=', getActiveWorkSpace());
        } else {
            $projects = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->projectonly()->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', getActiveWorkSpace());
        }
        if ($request->start_date) {
            $projects->where('start_date', $request->start_date);
        }
        if ($request->end_date) {
            $projects->where('end_date', $request->end_date);
        }
        $projects = $projects->get();

        foreach ($projects as $project) {
            if (isset($project->clients)) {
                foreach ($project->clients as $user) {
                    $projectUser[] = $user;
                }
            }

            if (isset($project->construction_detail->city) && ($project->construction_detail->city != '')) {
                $city[] = ucfirst($project->construction_detail->city);
            }
            if (isset($project->construction_detail->state) && ($project->construction_detail->state != '')) {
                $state[] = ucfirst($project->construction_detail->state);
            }

        }

        $filters_request['order_by'] = array('field' => 'projects.created_at', 'order' => 'DESC');
        $project_record              = Project::get_all($filters_request);
        $all_projects                = isset($project_record['records']) ? $project_record['records'] : array();

        $project_dropdown = Label::get_project_dropdowns();
        $projectmaxprice  = EstimateQuote::max('net');
        $countries        = Country::select(['id', 'name', 'iso'])->get();
        $city             = array_unique($city);
        $state            = array_unique($state);

        return view('project::project.index.index', compact(
            'projects',
            'project_dropdown',
            'all_projects',
            'countries',
            'city',
            'state',
            'projectUser',
            'projectmaxprice',
        ));
    }

    /**
     * Show the specified resource.
     * @param int $project 
     */
    public function show(Project $project)
    {
        $chartData = $this->getProjectChart([
            'workspace_id' => getActiveWorkSpace(),
            'project_id'   => $project->id,
            'duration'     => 'week',
        ]);

        $user = auth()->user();

        $project_estimations = ProjectEstimation::with('all_quotes_list')
            ->where('project_id', $project->id)
            ->where('init_status', 1)
            ->when($user->type !== 'company', function ($query) use ($project) {
                $estimationIds = EstimateQuote::where('project_id', $project->id)
                    ->where('user_id', auth()->id())
                    ->pluck('project_estimation_id')
                    ->toArray();

                return $query->whereIn('id', $estimationIds);
            })
            ->get();

        $estimationStatus = ProjectEstimation::$statues;
        $projectLabel     = Label::get_project_dropdowns();

        $workspace_users = User::where('created_by', '=', creatorId())
            ->emp()
            ->where('workspace_id', getActiveWorkSpace())
            ->get();


        Meta::prependTitle($project->name)->setTitle('Project Detail');

        return view('project::project.show.show', compact(
            'project',
            'chartData',
            'project_estimations',
            'estimationStatus',
            'projectLabel',
            'workspace_users',
        ));
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