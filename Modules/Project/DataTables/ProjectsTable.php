<?php

namespace Modules\Project\DataTables;

use Modules\Core\UI\DataTables;
use Modules\Project\Sidebar\projectsTabs;

class ProjectsTable extends DataTables
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected $rawColumns = [];

    /**
     * Make table response for the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->editColumn('thumbnail', function ($thumbnail) {
                return self::thumbnail($thumbnail);
            })
            ->editColumn('name', function ($project) {
                return view('project::project.index.partials.table.name', compact('project'));
            })
            ->editColumn('status', function ($project) {
                return view('project::project.index.partials.table.status', compact('project'));
            })
            ->editColumn('budget', function ($project) {
                return currency_format_with_sym($project->budget);
            })
            ->addColumn('comments', 'N/A')
            ->addColumn('construction', 'N/A')
            ->addColumn('action', function ($project) {
                return view('project::project.index.partials.table.action', compact('project'));
            })
            ->addColumn('projectBackgroundColor', function ($project) {
                return $project->status->background_color ?? '#c3c3c3';
            })
            ->addColumn('projectFontColor', function ($project) {
                return $project->status->font_color ?? '#000000';
            })
            ->withQuery('tabsFilterable', function ($filteredQuery) {
                // dd($filteredQuery);
                return $filteredQuery;
            })
        ;
    }


    protected function thumbnail($thumbnail)
    {
        $defaultThubnail = asset('assets/images/default_thumbnail3.png');
        // $thumbnail       = $thumbnail ? $thumbnail : $defaultThubnail;
        $thumbnail = $defaultThubnail;

        return view('project::project.index.partials.table.thumbnail', compact('thumbnail'));
    }

    protected function tabsFilterable($projects)
    {
        return $projects->filter(function ($project) {
            return ! empty($project->status->name);
        })->groupBy('status.name')
            ->map(function ($group) {
                $status = $group->first()->status->toArray();
                return (object) array_merge(['total' => $group->count()], $status);
            });
    }
}
