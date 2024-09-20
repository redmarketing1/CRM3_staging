<?php

namespace Modules\Project\Listeners;
use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminMenuEvent $event) : void
    {
        // $module = 'Project';
        // $menu = $event->menu;
        // $menu->add([
        //     'title' => 'Project',
        //     'icon' => 'home',
        //     'name' => 'project',
        //     'parent' => null,
        //     'order' => 2,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'home',
        //     'module' => $module,
        //     'permission' => 'manage-dashboard'
        // ]);
    }
}
