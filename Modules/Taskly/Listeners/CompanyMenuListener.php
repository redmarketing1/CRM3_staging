<?php

namespace Modules\Taskly\Listeners;

use App\Events\CompanyMenuEvent;
use Modules\Taskly\Entities\Project;

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
            'route'      => 'projects.index',
            'module'     => $module,
            'permission' => 'project manage',
        ]);
        // $menu->add([
        //     'category'   => 'Productivity',
        //     'title'      => __('Project Report'),
        //     'icon'       => '',
        //     'name'       => 'project-report',
        //     'parent'     => 'projects',
        //     'order'      => 20,
        //     'ignore_if'  => [],
        //     'depend_on'  => [],
        //     'route'      => 'project_report.index',
        //     'module'     => $module,
        //     'permission' => 'project report manage',
        // ]);
        // $menu->add([
        //     'category'   => 'Productivity',
        //     'title'      => __('System Setup'),
        //     'icon'       => '',
        //     'name'       => 'system-setup',
        //     'parent'     => 'projects',
        //     'order'      => 30,
        //     'ignore_if'  => [],
        //     'depend_on'  => [],
        //     'route'      => 'pipelines.index',
        //     'module'     => $module,
        //     'permission' => 'taskly setup manage',
        // ]);
    }
}