<?php

namespace Modules\Project\Sidebar;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Project\Entities\Project;

class projectsTabs
{
    /**
     * @var array Menu items for the project sidebar
     */
    protected $menuItems;

    /**
     * @var object get projects item
     */
    protected $projects;

    /**
     * ProjectsTabs constructor.
     * Initializes the menu items.
     */
    public function __construct()
    {
        $this->menuItems = $this->registerProjectSidebarMenu();
        $this->projects  = $this->getProject();
    }

    protected function getProject()
    {
        $user        = Auth::user();
        $workspaceID = getActiveWorkSpace();

        $query = ($user->type == 'company') ?
            Project::forCompany($user->id) :
            Project::forClient($user->id, $workspaceID);

        return $query;
    }

    /**
     * Renders the complete tab structure, including the tab items and the project list.
     *
     * @return string Rendered HTML for tabs and project list.
     */
    public function render()
    {
        /**
         * Cache::remember for 60 * 60 = 1 hours
         * after expired cache it will again fetch new data 
         */
        return Cache::remember('projectSubmenu-' . auth()->id(), 60 * 60, function () {
            return $this->renderTabItems() . $this->renderProjectList() . $this->renderHtmlMenu();
        });
    }

    /**
     * Registers the menu items for the project sidebar.
     *
     * @return array Array of menu items.
     */
    protected function registerProjectSidebarMenu()
    {
        return [
            [
                'title'      => __('All Projects'),
                'route'      => 'project.index',
                'permission' => 'project manage',
            ],
            [
                'title'      => __('Project Report'),
                'route'      => 'project_report.index',
                'permission' => 'project report manage',
            ],
            [
                'title'      => __('System Setup'),
                'route'      => 'pipelines.index',
                'permission' => 'taskly setup manage',
            ],
            // Add more menu items as needed...
        ];
    }

    /**
     * Renders the project's sidebar menu items, including the dynamic sidebar menu.
     *
     * @return string Rendered HTML for sidebar menu items and sidebar menu.
     */
    protected function renderHtmlMenu()
    {
        $menuHtml = '';

        $menuHtml .= '<ul class="project-submenu">';

        foreach ($this->menuItems as $item) {

            $menuHtml .= $this->generateSubmenu($item);

            /**
             * user permission not working that's do it wihtout permission
             * if you look for permission before fix permission issue
             */
            // if (auth()->user()->can($item['permission'])) {
            //     $menuHtml .= $this->generateSubmenu($item);
            // }
        }

        $menuHtml .= '</ul>';

        return $menuHtml;
    }

    /**
     * Renders the tab items, including the dynamic sidebar menu.
     *
     * @return string Rendered HTML for tab items and sidebar menu.
     */
    public function renderTabItems()
    {
        $tabItems = $this->projects->with('statusData')
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
                return (object) array_merge([
                    'shortName' => $group->first()->shortName,
                    'total'     => $group->count(),
                    'tabID'     => preg_replace('/[^a-zA-Z0-9_]/', '', strtolower(str_replace(' ', '_', $status['name']))),
                ], $status);
            });


        $html = view('project::project.sidebar.filter_button_tabslist', compact('tabItems'))->render();

        return $html;
    }

    public function renderProjectList()
    {
        $allProjects = $this->projects->whereIsArchive(0)->get();

        $groupedProjects = $allProjects->groupBy('statusData.name');

        $html = view('project::project.sidebar.filtered_project_lists', compact('groupedProjects', 'allProjects'))->render();

        return $html;
    }

    /**
     * Custom generated projects sidebar menu under "@renderTabItems" section 
     * @param mixed $item
     * @return string
     */
    public function generateSubmenu($item)
    {

        $html  = '';
        $url   = route($item['route']);
        $title = __($item['title']);
        $html  = '<li class="dash-item"><a href="' . $url . '" class="dash-link">' . $title . '</a></li>';

        return $html;

    }
}