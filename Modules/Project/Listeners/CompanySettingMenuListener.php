<?php

namespace Modules\Project\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event) : void
    {
        // $module = 'Project';
        // $menu = $event->menu;
        // $menu->add([
        //     'title' => 'Project',
        //     'name' => 'project',
        //     'order' => 100,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'home',
        //     'navigation' => 'sidenav',
        //     'module' => $module,
        //     'permission' => 'manage-dashboard'
        // ]);
    }
}
