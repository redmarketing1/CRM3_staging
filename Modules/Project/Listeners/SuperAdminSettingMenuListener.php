<?php

namespace Modules\Project\Listeners;
use App\Events\SuperAdminSettingMenuEvent;

class SuperAdminSettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminSettingMenuEvent $event) : void
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
