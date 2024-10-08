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

        $projects = $query->without([
            'priorityData',
            'property',
            'thumbnail',
            'comments',
        ])->get();

        $validProjects = $projects->filter([$this, 'isValidConstructionDetail']);
        $mapsLocations = $validProjects->map([$this, 'mapProjectToLocation'])->values();


        $projectTabs         = $validProjects->unique('status');
        $groupedProjectLists = $mapsLocations->groupBy('status');

        return view('project::project.map.index', compact(
            'mapsLocations',
            'projectTabs',
            'groupedProjectLists',
        ));
    }

    /**
     * Check if a project has valid construction detail.
     *
     * @param  Project  $project
     * @return bool
     */
    public function isValidConstructionDetail($project)
    {
        return isset(
            $project->constructionDetail->id,
            $project->constructionDetail->lat,
            $project->constructionDetail->long
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
            'id'        => $project->constructionDetail->id,
            'lat'       => (float) $project->constructionDetail->lat,
            'lng'       => (float) $project->constructionDetail->long,
            'name'      => $project->name,
            'shortName' => $project->shortName ?? 'N/A',
            'status'    => $project->statusData->name ?? 'N/A',
            'color'     => $project->statusData->background_color ?? '#EEEEEE',
            'url'       => route('project.show', $project->id),
            'content'   => view('project::project.map.construction-content', [
                'detail'  => $project->constructionDetail,
                'project' => $project,
            ])->render(),
        ];
    }
}
