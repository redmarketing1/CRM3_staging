<?php

namespace Modules\Project\Http\Controllers;

use App\Models\User;
use Butschster\Head\Facades\Meta;
use Modules\Project\Entities\Project;
use Modules\Taskly\Entities\Task;
use Illuminate\Routing\Controller;
use Modules\Taskly\Entities\Stage;
use Illuminate\Support\Facades\Auth;
use Modules\Taskly\Entities\UserProject;
use Modules\Taskly\Entities\ClientProject;

class ProjectDashboardController extends Controller
{
    public function __construct()
    {
        if (module_is_active('GoogleAuthentication')) {
            $this->middleware('2fa');
        }
    }

    public function index()
    {
        if (! Auth::user()->isAbleTo('taskly dashboard manage')) {
            return redirect()
                ->back()
                ->with('error', __('Permission Denied.'));
        }

        Meta::prependTitle(trans('Projects Dashboard'));

        $user      = Auth::user();
        $workspace = getActiveWorkSpace();

        $data = $this->getDashboardData($user, $workspace);

        return view('project::project.dashboard.index', $data);
    }

    private function getDashboardData($user, $workspace)
    {
        $doneStage = Stage::where('workspace_id', $workspace)->where('complete', 1)->first();
        $isClient  = $user->hasRole('client');

        $totalProject = $this->getProjectCount($user, $workspace);
        $totalBugs    = $this->getBugCount($user, $workspace);
        $totalTasks   = $this->getTaskCount($user, $workspace);
        $totalMembers = $this->getTotalMembersCount($user, $workspace);

        $completeTasks = $doneStage ? $this->getCompletedTaskCount($user, $workspace, $doneStage, $isClient) : 0;
        $tasks         = $this->getRecentTasks($user, $workspace, $isClient);

        $projectProcess = $this->getProjectProcess($user, $workspace, $totalProject, $isClient);
        $processData    = $this->calculateProcessData($projectProcess, $totalProject);

        $chartData = app('Modules\Taskly\Http\Controllers\ProjectController')
            ->getProjectChart(['workspace_id' => $workspace, 'duration' => 'week']);

        // dd($processData);

        return compact(
            'workspace',
            'totalProject',
            'totalBugs',
            'totalTasks',
            'totalMembers',
            'projectProcess',
            'processData',
            'completeTasks',
            'tasks',
            'chartData',
        );
    }

    private function getProjectCount($user, $workspace)
    {
        $query = ($user->type == 'company') ?
            Project::forCompany($user->id) :
            Project::forClient($user->id, $workspace);

        return $query->count();
    }

    private function getTaskCount($user, $workspace)
    {
        return UserProject::join("tasks", "tasks.project_id", "=", "user_projects.project_id")
            ->join("projects", "projects.id", "=", "user_projects.project_id")
            ->where("user_id", "=", $user->id)
            ->where('projects.workspace', '=', $workspace)
            ->where('projects.type', 'project')
            ->count();
    }

    private function getBugCount($user, $workspace)
    {
        return UserProject::join("bug_reports", "bug_reports.project_id", "=", "user_projects.project_id")
            ->join("projects", "projects.id", "=", "user_projects.project_id")
            ->where("user_id", "=", $user->id)
            ->where('projects.type', 'project')
            ->where('projects.workspace', '=', $workspace)
            ->count();
    }

    private function getTotalMembersCount($user, $workspace)
    {
        return User::where('created_by', creatorId())->emp()->count();
    }

    private function getCompletedTaskCount($user, $workspace, $doneStage, $isClient)
    {
        $client = ClientProject::join("tasks", "tasks.project_id", "=", "client_projects.project_id")
            ->join("projects", "projects.id", "=", "client_projects.project_id")
            ->where('projects.workspace', '=', $workspace)
            ->where('projects.type', 'project')
            ->where("client_id", "=", $user->id)
            ->where('tasks.status', '=', $doneStage->id)
            ->count();

        $user = UserProject::join("tasks", "tasks.project_id", "=", "user_projects.project_id")
            ->join("projects", "projects.id", "=", "user_projects.project_id")
            ->where("user_id", "=", $user->id)
            ->where('projects.workspace', '=', $workspace)
            ->where('tasks.status', '=', $doneStage->id)
            ->where('projects.type', 'project')
            ->count();

        return $isClient ? $client : $user;
    }

    private function getRecentTasks($user, $workspace, $isClient)
    {
        $client = Task::select([
            'tasks.*',
            'stages.name as status',
            'stages.complete',
        ])->join("client_projects", "tasks.project_id", "=", "client_projects.project_id")
            ->join("projects", "projects.id", "=", "client_projects.project_id")
            ->join("stages", "stages.id", "=", "tasks.status")
            ->where('projects.workspace', '=', $workspace)
            ->where("client_id", "=", $user->id)
            ->orderBy('tasks.id', 'desc')
            ->where('projects.type', 'project')
            ->limit(5)
            ->with('project')
            ->get();

        $user = Task::select([
            'tasks.*',
            'stages.name as status',
            'stages.complete',
        ])->join("user_projects", "tasks.project_id", "=", "user_projects.project_id")
            ->join("projects", "projects.id", "=", "user_projects.project_id")
            ->join("stages", "stages.id", "=", "tasks.status")
            ->where("user_id", "=", $user->id)
            ->where('projects.workspace', '=', $workspace)
            ->orderBy('tasks.id', 'desc')
            ->where('projects.type', 'project')
            ->limit(5)
            ->with('project')->get();

        return $isClient ? $client : $user;
    }

    private function getProjectProcess($user, $workspace, $totalProjects, $isClient)
    {
        $client = ClientProject::join("projects", "projects.id", "=", "client_projects.project_id")
            ->where('projects.workspace', '=', $workspace)
            ->where('projects.type', 'project')
            ->where("client_id", "=", $user->id)
            ->groupBy('projects.status')
            ->selectRaw('count(projects.id) as count, projects.status')
            ->pluck('count', 'projects.status');

        $user = UserProject::join("projects", "projects.id", "=", "user_projects.project_id")
            ->where("user_id", "=", $user->id)
            ->where('projects.workspace', '=', $workspace)
            ->where('projects.type', 'project')
            ->groupBy('projects.status')
            ->selectRaw('count(projects.id) as count, projects.status')
            ->pluck('count', 'projects.status');

        return $isClient ? $client : $user;
    }

    private function calculateProcessData($projectProcess, $totalProjects)
    {
        $processData = [
            'label'      => [],
            'percentage' => [],
            'class'      => ['text-success', 'text-primary', 'text-danger'],
        ];

        if ($projectProcess->isNotEmpty()) {
            foreach ($projectProcess as $label => $process) {
                $processData['label'][]      = $label;
                $processData['percentage'][] = $totalProjects == 0 ? 0.00 : round(($process * 100) / $totalProjects, 2);
            }
        } else {
            $processData['percentage'][0] = 100;
            $processData['label'][0]      = '';
        }

        return $processData;
    }


}