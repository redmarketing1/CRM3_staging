<?php

namespace Modules\Project\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Modules\Lead\Entities\Label;
use Butschster\Head\Facades\Meta;
use Modules\Taskly\Entities\Task;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Taskly\Entities\Stage;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;
use Illuminate\Support\Facades\Storage;
use Modules\Taskly\Events\UpdateProject;
use Illuminate\Support\Facades\Validator;
use Modules\Taskly\Entities\EstimateQuote;
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

        return $projects->table($request);
        if (request()->ajax()) {
        }

        Meta::prependTitle(trans('Manage Projects'));

        return view('project::project.index.index');
    }


    /**
     * Show the specified resource.
     * @param int $project 
     */
    public function show(Project $project)
    {
        $project_users = $project->users()->get();
        $authUser      = Auth::user();

        if (! ($authUser->type == 'company') && ! $project_users->contains($authUser)) {
            abort(403, 'Permission Denied');
        }

        if (! Auth::user()->isAbleTo('project show')) {
            abort(403, __('Permission denied.'));
        }

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

        // $workspace_users = User::where('created_by', '=', creatorId())
        //     ->emp()
        //     ->where('workspace_id', getActiveWorkSpace())
        //     ->get();

        $workspace_users = genericGetContacts();

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

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Project $project)
    {
        if (! Auth::user()->isAbleTo('project edit')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $customFields = null;
        if (module_is_active('CustomField')) {
            $project->customField = \Modules\CustomField\Entities\CustomField::getData($project, 'taskly', 'projects');
            $customFields         = \Modules\CustomField\Entities\CustomField::where('workspace_id', getActiveWorkSpace())
                ->where('module', 'taskly')
                ->where('sub_module', 'projects')
                ->get();
        }

        $projectLabel = Label::get_project_dropdowns();

        return view('project::project.show.edit.index', compact('project', 'customFields', 'projectLabel'));
    }

    private function getFirstSeventhWeekDay($week)
    {
        $first_day = $seventh_day = null;

        if (isset($week)) {
            $first_day   = Carbon::now()->addWeeks($week)->startOfWeek();
            $seventh_day = Carbon::now()->addWeeks($week)->endOfWeek();
        }

        $dateCollection['first_day']   = $first_day;
        $dateCollection['seventh_day'] = $seventh_day;

        $period = CarbonPeriod::create($first_day, $seventh_day);

        foreach ($period as $key => $dateobj) {
            $dateCollection['datePeriod'][$key] = $dateobj;
        }

        return $dateCollection;
    }
    public function getProjectChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration'] && $arrParam['duration'] == 'week') {
            $previous_week = $this->getFirstSeventhWeekDay(-1);
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

    public function quickView(Project $project)
    {
        $project_users = $project->users()->get();
        $authUser      = Auth::user();

        if (! ($authUser->type == 'company') && ! $project_users->contains($authUser)) {
            abort(403, 'Permission Denied');
        }

        if (! Auth::user()->isAbleTo('project show')) {
            abort(403, __('Permission denied.'));
        }

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

        // $workspace_users = User::where('created_by', '=', creatorId())
        //     ->emp()
        //     ->where('workspace_id', getActiveWorkSpace())
        //     ->get();

        $workspace_users = genericGetContacts();

        Meta::prependTitle($project->name)->setTitle('Project Detail');


        return view('project::project.quickView.index', compact(
            'project',
            'chartData',
            'project_estimations',
            'estimationStatus',
            'projectLabel',
            'workspace_users',
        ));
    }

    /**
     * Update project by given ids.
     *  
     */
    public function update(Request $request)
    {
        return self::{$request->type}($request->ids);
    }

    /**
     * Update project data by id
     * @param mixed $ids
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function updateAll($ids)
    {
        if (! Auth::user()->isAbleTo('project edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $id = request()->route('project');

        $project = Project::findOrFail($id);

        $project->update(request()->all());

        if (module_is_active('CustomField')) {
            \Modules\CustomField\Entities\CustomField::saveData($project, $request->customField ?? []);
        }

        event(new UpdateProject(request(), $project));

        return redirect()->back()->with('success', __('Project updated successfully!'));
    }

    /**
     * Delete project by id
     * @param mixed $ids
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function delete($ids)
    {
        Project::whereIn('id', $ids)
            ->delete();

        return response()->json(['success' => 'Items has been delete successfully.']);
    }

    /**
     * Move to archive project by id
     * @param mixed $ids
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function archive($ids)
    {
        Project::whereIn('id', $ids)->update([
            'is_archive' => 1,
        ]);

        return response()->json(['success' => 'Items has been archive successfully.']);
    }

    /**
     * Move to unarchive project by id
     * @param mixed $ids
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function unarchive($ids)
    {
        Project::whereIn('id', $ids)->update([
            'is_archive' => 0,
        ]);

        return response()->json(['success' => 'Items has been unarchive successfully.']);
    }

    /**
     * Duplicate project by id
     * @param mixed $ids
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function duplicate($ids)
    {
        $projects = Project::whereIn('id', $ids)->get();

        foreach ($projects as $project) {
            $newProject = $project->replicate();
            $newProject->save();

            // Step 2: Automatically replicate relationships
            /**
             * Pls never remove this code, I'll fix this later
             * @author Fxc Jahid <fxcjahid3@gmail.com>
             */
            // $this->duplicateRelations($project, $newProject);
        }

        return response()->json(['success' => 'Projects have been duplicated successfully.']);
    }

    /**
     * Automatically duplicate all related data for a project.
     *
     * @param Project $originalProject The original project being duplicated.
     * @param Project $newProject The newly created duplicate project.
     * @filesource Modules\Project\Traits\Relationship 
     */
    protected function duplicateRelations($originalProject, $newProject)
    {
        $relationships = [
            'activities',
            'client_feedback',
            'comments',
            'delays',
            'estimation',
            'files',
            'milestones',
            // 'notes',
            'progress',
            // 'progressFiles',
            // 'progressMains',
            // 'smartChats',
            // 'smartChatAttachments',
            // 'stages',
            // 'subContractors',
            'task',
            // 'taskCheckLists',
            // 'taskFiles',
            // 'taskTimers',
        ];

        foreach ($relationships as $relation) {
            if (method_exists($originalProject, $relation)) {
                foreach ($originalProject->$relation as $relatedItem) {
                    $newRelatedItem             = $relatedItem->replicate();
                    $newRelatedItem->project_id = $newProject->id;
                    $newRelatedItem->save();
                }
            }
        }
    }

    /**
     * Change the project status by id
     *
     * @param string|int $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function changeStatus($id)
    {
        if (! Auth::user()->isAbleTo('project setting')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $statusID = request('statusID');

        Project::where('id', $id)->update([
            'status' => $statusID,
        ]);

        return response()->json(['success' => 'Project status has change successfully.']);
    }
}
