<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Modules\Project\Entities\Project;
use Modules\Project\Entities\ProjectFile;

class createThumb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-thumb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allProjects     = Project::all();
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        foreach ($allProjects as $project) {

            $thumbnail = $project->hasOne(ProjectFile::class, 'project_id', 'id')
                ->where('is_default', 1)
                ->where(function ($query) use ($imageExtensions) {
                    foreach ($imageExtensions as $extension) {
                        $query->orWhere('file_path', 'like', "%.{$extension}");
                    }
                })
                ->orderBy('is_default', 'desc')
                ->first();


            if (! $thumbnail) {
                $thumbnail = $project->hasOne(ProjectFile::class, 'project_id', 'id')
                    ->where('is_default', 0)
                    ->where(function ($query) use ($imageExtensions) {
                        foreach ($imageExtensions as $extension) {
                            $query->orWhere('file_path', 'like', "%.{$extension}");
                        }
                    })
                    ->orderBy('is_default', 'desc')
                    ->first();
            }

            if (isset($thumbnail->file_path) && File::exists(public_path($thumbnail->file_path))) {
                $project->addMedia(public_path($thumbnail->file_path))
                    ->preservingOriginal()
                    ->toMediaCollection('projects', 'projects');
            }

        }
        echo "Done";
    }
}
