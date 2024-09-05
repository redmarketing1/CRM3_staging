<?php

namespace Modules\Backup\Listeners;

use App\Events\SuperAdminMenuEvent;

class BackupMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminMenuEvent $event) : void
    {
        $module = 'Base';
        $menu   = $event->menu;

        $menu->add([
            'category'   => 'Backup',
            'title'      => __('Backup'),
            'icon'       => 'file-upload',
            'name'       => 'backup',
            'parent'     => null,
            'order'      => 180,
            'ignore_if'  => [],
            'depend_on'  => [],
            'route'      => '',
            'module'     => $module,
            'permission' => 'backup.view',
        ]);

        $menu->add([
            'category'   => 'Backup',
            'title'      => __('View History'),
            'icon'       => '',
            'name'       => 'backup-history',
            'parent'     => 'backup',
            'order'      => 180,
            'ignore_if'  => [],
            'depend_on'  => [],
            'route'      => 'backup.index',
            'module'     => $module,
            'permission' => 'backup.view',
        ]);

        $menu->add([
            'category'   => 'Backup',
            'title'      => __('Settings'),
            'icon'       => '',
            'name'       => 'backup-settings',
            'parent'     => 'backup',
            'order'      => 180,
            'ignore_if'  => [],
            'depend_on'  => [],
            'route'      => '',
            'module'     => $module,
            'permission' => 'backup.settings',
        ]);
    }
}