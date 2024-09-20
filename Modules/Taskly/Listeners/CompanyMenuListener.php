<?php

namespace Modules\Taskly\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event) : void
    {
        /**
         * Outdated Version 
         * Afer version  0.1 
         * Remove menu sidebar and move to under tabwise projects list
         * @see: Modules\Project\Sidebar\projectsTabs.php
         * @author: Fxcjahid
         */
        // $menu->add([
        //     'category'   => 'Productivity',
        //     'title'      => __('All Projects'),
        //     'icon'       => 'square-check',
        //     'name'       => 'all-projects',
        //     'parent'     => null,
        //     'ignore_if'  => [],
        //     'depend_on'  => [],
        //     'route'      => 'projects.index',
        //     'module'     => $module,
        //     'permission' => 'project manage',
        // ]);
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