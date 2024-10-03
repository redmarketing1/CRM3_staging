<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Taskly\Entities\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectDelayController extends Controller
{

    /**
     * Display a listing of the posts.
     *
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        return view('project::project.show.section.add_delay', compact('id'));
    }


    /**
     * Store a newly created project delay announcement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
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

                return redirect()->route('project.show', $request->id)->with('error', $messages->first());
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
                    $fileName = $request->id . time() . "_" . $file->getClientOriginalName();

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
            $inputs['project_id'] = $request->id;

            $projectDelay = \auth()->user()->projectDelays()->create($inputs);
            if (! empty($projectDelay)) {
                $project           = Project::find($request->id);
                $project->end_date = $inputs['new_deadline'];
                $project->save();
            }
            return redirect()->route('project.show', $request->id)->with('success', __('Project Delay added successfully'));

        } catch (\Throwable $th) {
            dd($th);
        }
    }
}