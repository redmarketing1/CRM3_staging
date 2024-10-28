<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Project\Entities\Project;
use Modules\Project\Entities\ProjectFile;
use Modules\Taskly\Entities\ActivityLog;

class ProjectFilesController extends Controller
{

    //get files
    public function all_files(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        $files   = ProjectFile::where('project_id', $project_id)->get();

        if ($request->html == "true") {
            return view('project::project.show.section.all_files', compact('files', 'project'));
        }
        return $files;
    }

    //upload files
    public function fileUpload(Request $request, $project_id)
    {

        $project = Project::find($project_id);

        $validator = Validator::make(
            $request->all(),
            [
                'files' => 'required',
            ],
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        foreach ($request->file('files') as $file) {
            $image_size    = $file->getSize();
            $request       = new Request();
            $request->file = $file;

            $file_name    = $file->getClientOriginalName();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension    = $file->getClientOriginalExtension();
            date_default_timezone_set('Europe/Berlin');
            $currentTime = date('His'); // Format: HHMMSS
            $fileName    = $originalName . '_' . $project_id . '_' . $currentTime . '.' . $extension;


            $url = '';
            $dir = 'projects';

            $path = upload_file($request, 'file', $fileName, $dir, []);

            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }

            $notes             = new ProjectFile();
            $notes->project_id = $project->id;
            $notes->file_name  = $fileName;
            $notes->file_path  = $path['url'];
            $notes->save();
        }
        return response()->json([
            'is_success' => true,
            'message'    => __('File successfully created.'),
        ]);
    }

    //set default file
    public function set_default_file(Request $request, $project_id)
    {
        $file_id = $request->file;
        if ($file_id != '') {
            ProjectFile::where('project_id', $project_id)->update([
                'is_default' => 0,
            ]);

            ProjectFile::where('id', $file_id)->update([
                'is_default' => 1,
            ]);

            return response()->json([
                'is_success' => true,
                'message'    => __('Default files selected successfully.'),
            ]);
        }
        return response()->json([
            'is_success' => false,
            'message'    => __('Something went wrong.'),
        ]);
    }

    // Bulk Delete Files
    public function delete_files(Request $request)
    {
        $remove_files_ids = isset($request->remove_files_ids) ? json_decode($request->remove_files_ids) : array();

        if (! empty($remove_files_ids)) {
            foreach ($remove_files_ids as $file) {
                $file = ProjectFile::find($file);
                delete_file($file->file_path);
                $file->delete();
            }
            return response()->json([
                'is_success' => true,
                'message'    => __('Files Deleted.'),
            ]);
        } else {
            return response()->json([
                'is_success' => false,
                'message'    => __('Failed to Delete.'),
            ]);
        }
    }

    //Single FIle Delete
    public function fileDelete($file_id)
    {

        if ($file_id) {
            $file = ProjectFile::find($file_id);
            delete_file($file->file_path);
            $file->delete();
            return response()->json([
                'is_success' => true,
                'message'    => __('Files Deleted.'),
            ]);
        } else {
            return response()->json([
                'is_success' => false,
                'message'    => __('Failed to Delete.'),
            ]);
        }
    }

}