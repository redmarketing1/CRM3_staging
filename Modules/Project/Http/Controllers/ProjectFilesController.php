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
		$project 	= Project::find($project_id);
		$files 		= ProjectFile::where('project_id', $project_id)->get();

        if($request->html=="true"){
            return view('project::project.show.section.all_files', compact('files','project'));
        }
        return $files;
    }

    //upload files
    public function fileUpload(Request $request,$project_id){

        $project = Project::find($project_id);

        $validator = Validator::make(
            $request->all(),
            [
                'files' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        
        foreach ($request->file('files') as $file) {
            $image_size = $file->getSize();
            $request = new Request();
            $request->file = $file;

            $file_name = $file->getClientOriginalName();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            date_default_timezone_set('Europe/Berlin');
            $currentTime = date('His'); // Format: HHMMSS
            $fileName = $originalName . '_' . $project_id . '_' . $currentTime . '.' . $extension;


            $url = '';
            $dir = 'projects';

            $path = upload_file($request, 'file', $fileName, $dir, []);

            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }

            $notes = new ProjectFile();
            $notes->project_id = $project->id;
            $notes->file_name = $fileName;
            $notes->file_path = $path['url'];
            $notes->save();

            ActivityLog::create(
                [
                    'user_id'    => Auth::user()->id,
                    'user_type'  => get_class(Auth::user()),
                    'project_id' => $project->id,
                    'log_type'   => 'Upload File',
                    'remark'     => json_encode(['file_name' => $fileName]),
                ],
            );
        }
        return response()->json([
            'is_success' => true,
            'message'    => __('File successfully created.'),
        ]);
    }

    //file edit
    public function fileEdit(){
        return response()->json([
            'is_success' => false,
            'message'    => __('Under Development.'),
        ]);
    }

    public function set_default_file(){
        return response()->json([
            'is_success' => false,
            'message'    => __('Under Development.'),
        ]);
    }

    public function delete_files(){
        return response()->json([
            'is_success' => false,
            'message'    => __('Under Development.'),
        ]);
    }

}