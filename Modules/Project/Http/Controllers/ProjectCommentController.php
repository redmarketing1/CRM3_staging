<?php

namespace Modules\Project\Http\Controllers;


use App\Models\Content;
use Illuminate\Http\Request;
use Google\Service\Blogger\Post;
use Illuminate\Routing\Controller;
use Modules\Taskly\Entities\Project;
use Illuminate\Support\Facades\Storage;
use Modules\Taskly\Entities\ActivityLog;
use Modules\Taskly\Entities\ProjectComment;

class ProjectCommentController extends Controller
{

    /**
     * Display a listing of the posts.
     *
     * @return \Illuminate\View\View
     */
    public function index($projectID)
    {

        $project       = Project::find($projectID);
        $templateItems = Content::with(['contentTemplate'])->get();

        if (empty($project)) {
            return self::notfound();
        }

        return view('project::project.popup.add_comment',
            compact('project', 'templateItems'),
        );
    }


    /**
     * Show the form for creating a new post.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('posts.create');
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

        /** For update comment */
        if ($request->filled('project_comment_id')) {
            return $this->updateComment($request, $project, $fileName);
        }

        return self::createComment($request, $project, $fileName);
    }

    protected function handleFileUpload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->store('comments', 'public');

            return $path;
        }

        return null;
    }

    protected function updateComment(Request $request, $project, $fileName)
    {
        $comment             = ProjectComment::findOrFail($request->project_comment_id);
        $comment->project_id = $project->id;
        $comment->file       = $fileName ?: $request->project_comment_old_file;
        $comment->comment    = $request->input('comment');
        $comment->comment_by = auth()->id();
        $comment->parent     = $request->input('parent', null);
        $comment->save();

        ActivityLog::create([
            'user_id'    => creatorId(),
            'user_type'  => get_class(auth()->user()),
            'project_id' => $project->id,
            'log_type'   => 'Comment updated',
            'remark'     => json_encode(['title' => 'Project comment post updated.', 'project_comment_id' => $request->project_comment_id]),
        ]);

        return redirect()->back()->with('success', __('Comment updated successfully.'));
    }

    private function createComment(Request $request, $project, $fileName)
    {
        if ($request->filled('comment') || $request->hasFile('file')) {
            $comment             = new ProjectComment();
            $comment->project_id = $project->id;
            $comment->file       = $fileName;
            $comment->comment    = $request->input('comment');
            $comment->comment_by = auth()->id();
            $comment->parent     = $request->input('parent', null);
            $comment->save();

            ActivityLog::create([
                'user_id'    => creatorId(),
                'user_type'  => get_class(auth()->user()),
                'project_id' => $project->id,
                'log_type'   => 'Comment Create',
                'remark'     => json_encode(['title' => 'Project comment posted.', 'project_comment_id' => $comment->id]),
            ]);

            return redirect()
                ->back()
                ->with('success', __('Comment successfully posted.'));
        }

        return redirect()
            ->back()
            ->with('error', __('Please fill in the value.'));
    }


    /**
     * Display the specified post.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\View\View
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\View\View
     */
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Post $post)
    {
        $post->update($request->all());
        return redirect()->route('posts.index');
    }

    /**
     * Remove the specified post from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index');
    }

    protected function notfound()
    {
        return view('project::project.popup.404');
    }

}