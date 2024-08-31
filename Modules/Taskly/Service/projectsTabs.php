<?php

namespace Modules\Taskly\Service;

use Modules\Taskly\Entities\Project;

class projectsTabs
{
    public function render()
    {
        return $this->renderTabItems() . $this->renderProjectList();
    }

    protected function renderTabItems()
    {
        $allProjects     = Project::with('status_data')->get();
        $groupedProjects = $allProjects->unique('status_data.name');

        $html = view('project::project.tabs.items_button', compact('groupedProjects'))->render();

        return $html;
    }

    protected function renderProjectList()
    {
        $allProjects     = Project::with('status_data')->get();
        $groupedProjects = $allProjects->groupBy('status_data.name');

        $html = view('project::project.tabs.items_content', compact('groupedProjects', 'allProjects'))->render();

        return $html;
    }
}