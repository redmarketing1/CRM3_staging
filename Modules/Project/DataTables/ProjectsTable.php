<?php

namespace Modules\Project\DataTables;


use Modules\Core\UI\DataTables as Tables;
use Yajra\DataTables\Facades\DataTables;

class ProjectsTable extends Tables
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected $rawColumns = [];

    function hexToRgb($hex, $percentage = 1)
    {
        $hex = ltrim($hex, '#');
        $int = hexdec($hex);

        $r = ($int >> 16) & 0xFF;
        $g = ($int >> 8) & 0xFF;
        $b = $int & 0xFF;

        $percentage = min(1, max(0, $percentage));

        return "rgb($r, $g, $b, $percentage)";
    }

    public function newTable()
    {
        return DataTables::eloquent($this->source)
            ->filter(function ($query) {

                $query->limit(50);

            })
            ->setRowId('project-items')
            ->setRowAttr([
                'data-id' => function ($entity) {
                    return $entity->id;
                },
                'style'   => function ($entity) {
                    return $this->style($entity);
                },
            ])
        ;
    }

    /**
     * Make table response for the resource.
     * 
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
            ->editColumn('description', function ($project) {
                return 'description';
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
            ->with('filterableStatusList', $this->filterableStatusList())
            ->withQuery('tabsFilterable', function ($filteredQuery) {
                // dd($filteredQuery);
                return $filteredQuery;
            })
            // ->only(['id', 'name'])
        ;
    }

    protected function style($entity)
    {
        $backgroundColor = $entity->statusData->background_color ?? '#c3c3c3';
        $fontColor       = $entity->statusData->font_color ?? '#000000';

        return "
            --background-color: {$backgroundColor}; 
            --hover-background-color: {$this->hexToRgb($backgroundColor, 0.05)}; 
            --font-color: {$fontColor};
        ";
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

    protected function filterableStatusList()
    {
        $statusLists = $this->source->get()->filter(function ($project) {
            return ! empty($project->statusData->name);
        })->groupBy('statusData.name')
            ->map(function ($group) {
                $status = $group->first()->statusData->toArray();
                return (object) array_merge(['total' => $group->count()], $status);
            });

        return view('project::project.index.utility.tabs_filter_button', compact('statusLists'))
            ->render();
    }
}
