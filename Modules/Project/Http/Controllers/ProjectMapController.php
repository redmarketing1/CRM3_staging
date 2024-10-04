<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Lead\Entities\Label;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;
use Illuminate\Contracts\Support\Renderable;

class ProjectMapController extends Controller
{

    public function index(Request $request)
    {
        if (! Auth::user()->isAbleTo('project manage')) {
            return redirect()
                ->back()
                ->with('error', __('Permission Denied.'));
        }

        $user        = Auth::user();
        $workspaceID = getActiveWorkSpace();

        $query = ($user->type == 'company') ?
            Project::ForCompany($user->id) :
            Project::ForClient($user->id, $workspaceID);

        $projects = $query->get();


        $locations = $projects
            ->filter(fn ($project) => isset (
                $project->constructionDetail->id,
                $project->constructionDetail->lat,
                $project->constructionDetail->long)
            )
            ->map(function ($project) {
                return [
                    'id'      => $project->constructionDetail->id,
                    'lat'     => floatval($project->constructionDetail->lat),
                    'lng'     => floatval($project->constructionDetail->long),
                    'name'    => $project->name,
                    'color'   => $project->statusData->background_color ?? '#EEEEEE',
                    'url'     => route('projects.show', $project->id),
                    'content' => view('project::project.map.construction-content', ['detail' => $project->constructionDetail, 'name' => $project->name])->render(),
                ];
            })
            ->unique('id')
            ->values()
            ->toArray();

        return view('project::project.map.index', compact('locations'));
    }
}