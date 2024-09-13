<?php

namespace Modules\Project\Http\Controllers;

use App\Models\User;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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

        return view('project::project.show', compact(
            'project',
            'chartData',
            'project_estimations',
            'estimationStatus',
            'projectLabel',
            'workspace_users'
        ));
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
                    'new_deadline' => 'required',
                    'reason' => 'required',
                    'delay_in_weeks' => 'required',
                    'internal_comment' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show',$id)->with('error', $messages->first());
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
                    $path = 'delays';
                    $fileName         = $id . time() . "_" . $file->getClientOriginalName();
                    
                    $save = Storage::disk()->putFileAs(
                        $path,
                        $file,
                        $fileName
                    );
                    // $upload = upload_file($request, 'media', $fileName, 'delays', []);
                
                    
                    $extra_files[] = 'uploads/' . $save;
                }
            }

            $inputs['media'] = json_encode($extra_files);
            $inputs['project_id'] = $id;

            $projectDelay = \auth()->user()->projectDelays()->create($inputs);
            if (!empty($projectDelay)) {
                $project = Project::find($id);
                $project->end_date = $inputs['new_deadline'];
                $project->save();
            }
            return redirect()->route('project.show', $id)->with('success', __('Project Delay added successfully'));

        } catch (\Throwable $th) {
            dd($th);
        }
    }
}