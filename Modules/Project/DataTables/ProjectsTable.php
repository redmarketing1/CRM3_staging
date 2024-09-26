<?php

namespace Modules\Project\DataTables;


use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;
use Modules\Core\UI\DataTables as Tables;

class ProjectsTable extends Tables
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected $rawColumns = [];

    /**
     * Convert hexa color to RGB
     * @param mixed $hex
     * @param mixed $percentage
     * @return string
     */
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

                // $query->limit(50);
    
            })
            ->setRowId(function ($entity) {
                return $entity->id;
            })
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
            ->editColumn('thumbnail', function ($project) {
                return self::thumbnail($project->thumbnail);
            })
            ->editColumn('name', function ($project) {
                return view('project::project.index.partials.table.name', compact('project'));
            })
            ->editColumn('status', function ($project) {
                return view('project::project.index.partials.table.status', compact('project'));
            })
            ->editColumn('priority', function ($project) {
                return view('project::project.index.partials.table.priority', compact('project'));
            })
            ->editColumn('budget', function ($project) {
                return currency_format_with_sym($project->budget);
            })
            ->editColumn('created_at', function ($project) {
                return company_datetime_formate($project->created_at);
            })
            ->addColumn('comments', function ($project) {
                return $this->comments($project);
            })
            ->addColumn('construction', function ($project) {
                return $this->construction($project);
            })
            ->addColumn('action', function ($project) {
                return view('project::project.index.partials.table.action', compact('project'));
            })
            ->with('filterableStatusList', $this->filterableStatusList())
            ->with('filterablePriorityList', $this->filterablePriorityList())
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
        $defaultThumbnail = asset('assets/images/default_thumbnail3.png');
        $thumbnail        = isset($thumbnail->file_path) ? asset($thumbnail->file_path) : $defaultThumbnail;

        return view('project::project.index.partials.table.thumbnail', compact('thumbnail'));
    }

    protected function filterableStatusList()
    {
        /**
         * Cache::remember for 60 * 60 = 1 hours
         * after expired cache it will again fetch new data 
         */
        return Cache::remember('filterableStatusList-' . auth()->id(), 60 * 60, function () {
            $statusLists = $this->source->with('statusData')
                ->where('is_active', 1) // retrieve only actibv project
                ->get()
                ->filter(function ($project) {
                    return ! empty($project->statusData->name);
                })
                ->groupBy('statusData.name')
                ->map(function ($group) {
                    $status = $group->first()->statusData->toArray();
                    return (object) array_merge(['total' => $group->count()], $status);
                });

            $html = view('project::project.index.utility.tabs_filter_button', compact('statusLists'))
                ->render();

            return [
                'html' => $html,
                'data' => $statusLists,
            ];
        });
    }
    protected function filterablePriorityList()
    {
        /**
         * Cache::remember for 60 * 60 = 1 hours
         * after expired cache it will again fetch new data 
         */
        return Cache::remember('filterablePriorityList-' . auth()->id(), 60 * 60, function () {

            return $this->source->with('priorityData')
                ->get()
                ->filter(function ($project) {
                    // Only include priorities that have stars (★) in their name
                    return ! empty($project->priorityData->name) && strpos($project->priorityData->name, '★') !== false;
                })
                ->groupBy('priorityData.name')
                ->map(function ($group) {
                    $status = $group->first()->priorityData->toArray();
                    return (object) array_merge(['total' => $group->count()], $status);
                })
                ->sortBy(function ($item) {
                    // Sort by the number of stars in the priority name
                    return mb_strlen($item->name);
                });
            ;

        });
    }

    protected function comments($project)
    {
        $comments    = $project->comments->take(15);
        $description = $project->description;

        if ($comments->isEmpty() && empty($description)) {
            return 'N/A';
        }

        return view('project::project.index.partials.table.comments', compact('comments', 'description'));
    }

    protected function construction($project)
    {
        return view('project::project.index.partials.table.construction', compact('project'));
    }

}
