<?php

namespace Modules\Project\DataTables;


use Illuminate\Support\Facades\Cache;
use Modules\Project\Entities\Project;
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

    public function newTable()
    {
        return DataTables::eloquent($this->source)
            ->filter(function ($query) {
                // $query->limit(50); 
            })
            ->setRowId(function ($entity) {
                return $entity->id;
            })
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
                return $project->ThumbnailOrDefault;
            })
            ->editColumn('name', function ($project) {
                return view('project::project.index.partials.table.name', compact('project'));
            })
            ->addColumn('comments', function ($project) {
                return $this->comments($project);
            })
            ->addColumn('construction', function ($project) {
                return $this->construction($project);
            })
            ->editColumn('budget', function ($project) {
                return currency_format_with_sym($project->budget);
            })
            ->editColumn('created_at', function ($project) {
                return company_datetime_formate($project->created_at);
            })
            ->addColumn('action', function ($project) {
                $cacheKey = 'projectTableAction-' . auth()->id() . '-' . $project->id;

                return Cache::remember($cacheKey, 60 * 60, function () use ($project) {
                    return view('project::project.index.partials.table.action', compact('project'))->render();
                });
            })
            ->with('filterableStatusList', $this->filterableStatusList())
            ->with('filterablePriorityList', $this->filterablePriorityList())
            ->with('minBudget', $this->minBudget())
            ->with('maxBudget', $this->maxBudget())
        ;
    }

    protected function filterableStatusList()
    {
        /**
         * Cache::remember for 60 * 60 = 1 hours
         * after expired cache it will again fetch new data 
         */
        return Cache::remember('filterableStatusList-' . auth()->id(), 60 * 60, function () {
            $statusLists = $this->source->with('statusData')
                // ->where('projects.is_active', 0) // retrieve only active project
                ->get()
                ->sortBy(function ($project) {
                    return $project->statusData->order ?? 0;
                })
                ->filter(function ($project) {
                    return ! empty($project->statusData->name) && $project->is_archive === 0;
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
            return '-';
        }

        return view('project::project.index.partials.table.comments', compact('comments', 'description'));
    }

    protected function construction($project)
    {
        return view('project::project.index.partials.table.construction', compact('project'));
    }

    protected function maxBudget()
    {
        $maxBudget = Project::max('budget');
        // return currency_format_with_sym($maxBudget);
        return "1000000";
    }
    protected function minBudget()
    {
        $minBudget = Project::min('budget');
        return currency_format_with_sym($minBudget);
    }
}
