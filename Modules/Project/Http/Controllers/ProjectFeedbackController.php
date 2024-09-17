<?php

namespace Modules\Project\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Taskly\Entities\Project;
use Modules\Taskly\Entities\ActivityLog;
use Illuminate\Contracts\Support\Renderable;
use Modules\Taskly\Entities\ProjectClientFeedback;

class ProjectFeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index($projectID)
    {
        $project       = Project::find($projectID);
        $templateItems = Content::with(['contentTemplate'])->get();

        if (empty($project)) {
            return self::notfound();
        }

        return view('project::project.show.popup.add_feedback',
            compact('project', 'templateItems'),
        );
    }

    /**
     * Store a newly created post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $project_id)
    {
        $project = Project::findOrFail($project_id);

        $fileName = self::handleFileUpload($request);

        /** For update feedback */
        if ($request->filled('project_feedback_id')) {
            return $this->updateFeedback($request, $project, $fileName);
        }

        return self::createFeedback($request, $project, $fileName);
    }

    protected function handleFileUpload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->store('feedback', 'public');

            return $path;
        }

        return null;
    }

    protected function updateFeedback(Request $request, $project, $fileName)
    {
        $feedback              = ProjectClientFeedback::findOrFail($request->project_feedback_id);
        $feedback->project_id  = $project->id;
        $feedback->file        = $fileName;
        $feedback->feedback    = $request->input('feedback');
        $feedback->feedback_by = auth()->id();
        $feedback->parent      = $request->input('parent', null);
        $feedback->save();

        ActivityLog::create([
            'user_id'    => creatorId(),
            'user_type'  => get_class(auth()->user()),
            'project_id' => $project->id,
            'log_type'   => 'Feedback updated',
            'remark'     => json_encode(['title' => 'Project feedback post updated.', 'feedback_id' => $feedback->id]),
        ]);

        return redirect()
            ->back()
            ->with('success', __('Feedback has updated successfully.'));
    }

    private function createFeedback(Request $request, $project, $fileName)
    {
        if ($request->filled('feedback') || $request->hasFile('file')) {

            $feedback              = new ProjectClientFeedback();
            $feedback->project_id  = $project->id;
            $feedback->file        = $fileName;
            $feedback->feedback    = $request->input('feedback');
            $feedback->feedback_by = auth()->id();
            $feedback->parent      = $request->input('parent', null);
            $feedback->save();

            ActivityLog::create(
                [
                    'user_id'    => creatorId(),
                    'user_type'  => get_class(auth()->user()),
                    'project_id' => $project->id,
                    'log_type'   => 'Feedback Create',
                    'remark'     => json_encode(['title' => 'Project feedback created', 'feedback_id' => $feedback->id]),
                ],
            );

            return redirect()
                ->back()
                ->with('success', __('Feedback has post successfully.'));
        }

        return redirect()
            ->back()
            ->with('error', __('Please fill in the value.'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('taskly::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('taskly::edit');
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

    protected function notfound()
    {
        return view('project::project.show.popup.404');
    }
}