<?php

namespace Modules\Project\Http\Controllers;


use App\Models\Content;
use App\Models\Country;
use Illuminate\Http\Request;
use Google\Service\Blogger\Post;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Taskly\Entities\Project;
use Illuminate\Support\Facades\Storage;
use Modules\Taskly\Entities\ActivityLog;
use Modules\Taskly\Entities\EstimateQuote;
use Modules\Taskly\Entities\ProjectComment;
use Modules\Taskly\Entities\ProjectEstimation;

class ProjectContactDetails extends Controller
{

    /**
     * Display a listing of the posts.
     *
     * @return \Illuminate\View\View
     */
    public function index($projectID, $field = "")
    {

        $objUser = Auth::user();


        $project    = Project::find($projectID);
        $form_field = "";
        if ($field != "") {
            $form_field = $field;
        }
        $clients = genericGetContacts();

        $project_estimations = ProjectEstimation::where('project_id', $projectID)->where('init_status', 1)->pluck('id');

        $estimation_quotes = array();

        if (count($project_estimations) > 0) {
            $estimation_quotes = EstimateQuote::whereIn('project_estimation_id', $project_estimations)->get();
        }
        $countries = Country::select(['id', 'name', 'iso'])->get();


        return view('project::project.show.edit.contact_details', compact('project', 'form_field', 'clients', 'estimation_quotes', 'countries'));

    }



    /**
     * Store a newly created post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Project $project)
    {
        $project->update([
            'construction_detail_id' => $request->construction_user_id,
            'client'                 => $request->client,
        ]);


        return redirect()
            ->back()
            ->with('success', __('Project contact details has been saved.'));

    }
}