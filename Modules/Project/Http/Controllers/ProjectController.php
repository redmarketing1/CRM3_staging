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

        if (request()->ajax()) {
            return $projects->table($request);
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

    /**
     * Update project by given ids.
     *  
     */
    public function update(Request $request)
    {
        return self::{$request->type}($request->ids);
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
}
