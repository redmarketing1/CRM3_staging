<?php

namespace Modules\Project\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event) : void
    {
        $module = 'Taskly';
        $menu   = $event->menu;
        $menu->add([
            'category'   => 'General',
            'title'      => __('Project Dashboard'),
            'icon'       => '',
            'name'       => 'taskly-dashboards',
            'parent'     => 'dashboard',
            'order'      => 10,
            'ignore_if'  => [],
            'depend_on'  => [],
            'route'      => 'taskly.dashboard',
            'module'     => $module,
            'permission' => 'taskly dashboard manage',
        ]);

        $menu->add([
            'category'   => 'Productivity',
            'title'      => __('Projects'),
            'icon'       => 'square-check',
            'name'       => 'projects',
            'parent'     => null,
            'order'      => 300,
            'ignore_if'  => [],
            'depend_on'  => ['tabs'],
            'route'      => 'project.index',
            'module'     => $module,
            'permission' => 'project manage',
        ]);
    }
}
