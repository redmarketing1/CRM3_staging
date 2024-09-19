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
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Modules\Taskly\Entities\EstimateQuote;
use Illuminate\Contracts\Support\Renderable;
use Modules\Taskly\Entities\ProjectEstimation;

class ProjectController extends Controller
{
    /**
     * Display for projects table 
     */
    public function index(Request $request, Project $projects)
    {
        if (! Auth::user()->isAbleTo('project manage')) {
            return redirect()
                ->back()
                ->with('error', __('Permission Denied.'));
        }

        if ($request->has('table')) {
            return $projects->table($request);
        }


        // $projects = $this->dataTables();

        // $groupedProjects = $this->groupProjectsByStatus($projects);

        return view('project::project.index.index', [
            'groupedProjects' => $groupedProjects ?? [],
        ]);
    }

    public function dataTables($filters = null)
    {
        $user = Auth::user();

        return ($user->type == 'company') ?

            Project::whereCreatedBy($user->id)
                ->orderByDesc('created_at')
                ->get()

            : Project::leftjoin('client_projects', 'client_projects.project_id', 'projects.id')
                ->leftjoin('estimate_quotes', 'estimate_quotes.project_id', 'projects.id')
                ->where(function ($query) use ($user) {
                    $query->where('client_projects.client_id', $user->id)
                        ->orWhere('estimate_quotes.user_id', $user->id);
                })
                ->orderByDesc('created_at')
                ->get();
    }

    /**
     * Group projects by their status and count them.
     *
     * @param \Illuminate\Support\Collection $projects
     * @return \Illuminate\Support\Collection
     */
    protected function groupProjectsByStatus($projects)
    {
        return $projects->filter(function ($project) {
            return ! empty($project->status->name);
        })->groupBy('status.name')
            ->map(function ($group) {
                $status = $group->first()->status->toArray();
                return (object) array_merge(['total' => $group->count()], $status);
            });
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

    //Project Delays
    public function addProjectDelay($id)
    {
        return view('project::project.delayAdd', compact('id'));
    }

    // Project Delay Store
    public function delayAnnouncement(Request $request, $id)
    {

        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'new_deadline'     => 'required',
                    'reason'           => 'required',
                    'delay_in_weeks'   => 'required',
                    'internal_comment' => 'required',
                ],
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show', $id)->with('error', $messages->first());
            }

            $inputs = $request->only([
                'new_deadline',
                'reason',
                'delay_in_weeks',
                'internal_comment',
            ]);

            $extra_files = [];
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $key => $file) {
                    $path     = 'delays';
                    $fileName = $id . time() . "_" . $file->getClientOriginalName();

                    $save = Storage::disk()->putFileAs(
                        $path,
                        $file,
                        $fileName,
                    );
                    // $upload = upload_file($request, 'media', $fileName, 'delays', []);


                    $extra_files[] = 'uploads/' . $save;
                }
            }

            $inputs['media']      = json_encode($extra_files);
            $inputs['project_id'] = $id;

            $projectDelay = \auth()->user()->projectDelays()->create($inputs);
            if (! empty($projectDelay)) {
                $project           = Project::find($id);
                $project->end_date = $inputs['new_deadline'];
                $project->save();
            }
            return redirect()->route('project.show', $id)->with('success', __('Project Delay added successfully'));

        } catch (\Throwable $th) {
            dd($th);
        }
    }
}