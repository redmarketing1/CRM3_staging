<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;

class ProjectMapController extends Controller
{
    public function index(Request $request)
    {
        $user        = Auth::user();
        $workspaceID = getActiveWorkSpace();

        if (! $user->isAbleTo('project manage')) {
            return redirect()
                ->back()
                ->with('error', __('Permission Denied.'));
        }

        $query = $user->type == 'company'
            ? Project::ForCompany($user->id)
            : Project::ForClient($user->id, $workspaceID);

        $projects = $query->whereIsArchive(0)
            ->without([
                'priorityData',
                'property',
                'thumbnail',
                'comments',
            ])->get();

        $validProjects = $projects->filter([$this, 'isValidContactDetail']);
        $mapsLocations = $validProjects->map([$this, 'mapProjectToLocation'])->values();


        $projectTabs = $validProjects
            ->sortBy(function ($project) {
                return $project->statusData->order ?? 0;
            })
            ->unique('status');

        $groupedProjectLists = $mapsLocations->groupBy('status');

        return view('project::project.map.index', compact(
            'mapsLocations',
            'projectTabs',
            'groupedProjectLists',
        ));
    }

    /**
     * Check if a project has valid contact detail.
     *
     * @param  Project  $project
     * @return bool
     */
    public function isValidContactDetail($project)
    {
        return isset(
            $project->contactDetail->id,
            $project->contactDetail->lat,
            $project->contactDetail->long
        );
    }

    /**
     * Map project data to location array.
     *
     * @param  Project  $project
     * @return object
     */
    public function mapProjectToLocation($project)
    {
        return (object) [
            'id'             => $project->contactDetail->id,
            'lat'            => (float) $project->contactDetail->lat,
            'lng'            => (float) $project->contactDetail->long,
            'name'           => $project->name,
            'shortName'      => $project->shortName ?? 'N/A',
            'status'         => $project->statusData->name ?? 'N/A',
            'fontColor'      => $project->statusData->font_color ?? '#111111',
            'backgrounColor' => $project->backgroundColor ?? '#111111',
            'url'            => route('project.show', $project->id),
            'content'        => view('project::project.map.construction-content', [
                'detail'  => $project->contactDetail,
                'project' => $project,
            ])->render(),
        ];
    }
}
