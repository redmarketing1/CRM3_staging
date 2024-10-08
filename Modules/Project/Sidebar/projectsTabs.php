<?php

namespace Modules\Project\Sidebar;

use Illuminate\Support\Facades\Auth;
use Modules\Taskly\Entities\Project;
use Illuminate\Support\Facades\Cache;

class projectsTabs
{
    /**
     * @var array Menu items for the project sidebar
     */
    protected $menuItems;

    /**
     * ProjectsTabs constructor.
     * Initializes the menu items.
     */
    public function __construct()
    {
        $this->menuItems = $this->registerProjectSidebarMenu();
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
            return $this->renderTabItems() . $this->renderProjectList();
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
                'route'      => 'projects.index',
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

        $menuHtml .= '<ul class="dash-submenu">';

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
        $user = Auth::user();
        if ($user->type == 'company') {
            $allProjects = Project::with('status_data')->get();
        } else {
            $allProjects = Project::with('status_data')
                ->leftJoin('client_projects', 'client_projects.project_id', 'projects.id')
                ->leftJoin('user_projects', 'user_projects.project_id', 'projects.id')
                ->leftJoin('estimate_quotes', 'estimate_quotes.project_id', 'projects.id')
                ->where(function ($query) use ($user) {
                    $query->where('client_projects.client_id', $user->id)
                        ->orWhere('user_projects.user_id', $user->id)
                        ->orWhere('estimate_quotes.user_id', $user->id);
                })
                ->select('projects.*')
                ->distinct()
                ->get();
        }

        $groupedProjects = $allProjects->unique('status_data.name');
        $html            = view('project::project.sidebar.filter_button_tabslist', compact('groupedProjects'))->render();

        return $html;
    }

    public function renderProjectList()
    {
        $user = Auth::user();
        if ($user->type == 'company') {
            $allProjects = Project::with('status_data')->get();
        } else {
            $allProjects = Project::with('status_data')
                ->leftJoin('client_projects', 'client_projects.project_id', 'projects.id')
                ->leftJoin('user_projects', 'user_projects.project_id', 'projects.id')
                ->leftJoin('estimate_quotes', 'estimate_quotes.project_id', 'projects.id')
                ->where(function ($query) use ($user) {
                    $query->where('client_projects.client_id', $user->id)
                        ->orWhere('user_projects.user_id', $user->id)
                        ->orWhere('estimate_quotes.user_id', $user->id);
                })
                ->select('projects.*')
                ->distinct()
                ->get();
        }

        $groupedProjects = $allProjects->groupBy('status_data.name');
        $html            = view('project::project.sidebar.filtered_project_lists', compact('groupedProjects', 'allProjects'))->render();

        return $html . $this->renderHtmlMenu();
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