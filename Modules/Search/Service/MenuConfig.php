<?php
namespace Modules\Search\Service;

use Illuminate\Support\Facades\Auth;

class MenuConfig
{
    public static function getMenuList()
    {
        return [
            [
                'name'       => 'Dashboard',
                'route'      => '',
                'icon'       => 'fas fa-tachometer-alt',
                'key'        => 'dashboard',
                'permission' => 'crm dashboard manage',
                'children'   => [
                    [
                        'name'       => __('CRM Dashboard'),
                        'route'      => route('lead.dashboard'),
                        'icon'       => 'fas fa-chart-line',
                        'key'        => 'brand_settings',
                        'permission' => 'crm dashboard manage',
                    ],
                    [
                        'name'       => __('Project Dashboard'),
                        'route'      => route('taskly.dashboard'),
                        'icon'       => 'fas fa-project-diagram',
                        'key'        => 'brand_settings',
                        'permission' => 'taskly dashboard manage',
                    ],
                ],
            ],
            [
                'name'       => __('Projects'),
                'route'      => route('project.index'),
                'icon'       => 'fas fa-tasks',
                'key'        => 'project',
                'permission' => 'project manage',
                'children'   => [],
            ],
            [
                'name'       => __('Maps'),
                'route'      => route('project.map.index'),
                'icon'       => 'fas fa-map-marked-alt',
                'key'        => 'project',
                'permission' => 'project manage',
                'children'   => [],
            ],
            [
                'name'       => __('User'),
                'route'      => route('users.index'),
                'icon'       => 'fas fa-user',
                'key'        => 'user',
                'permission' => 'user manage',
                'children'   => [],
            ],
            [
                'name'       => __('User Role'),
                'route'      => route('roles.index'),
                'icon'       => 'fas fa-user-shield',
                'key'        => 'user-management',
                'permission' => 'user-management',
                'children'   => [],
            ],
            [
                'name'       => __('Proposal'),
                'route'      => route('proposal.index'),
                'icon'       => 'fas fa-file-signature',
                'key'        => 'proposal',
                'permission' => 'proposal manage',
                'children'   => [],
            ],
            [
                'name'       => __('Estimations'),
                'route'      => route('estimations.index'),
                'icon'       => 'fas fa-calculator',
                'key'        => 'estimations',
                'permission' => 'estimation manage',
                'children'   => [],
            ],
            [
                'name'       => __('Invoice'),
                'route'      => route('invoice.index'),
                'icon'       => 'fas fa-file-invoice-dollar',
                'key'        => 'invoice',
                'permission' => 'invoice manage',
                'children'   => [],
            ],
            [
                'name'       => __('Helpdesk'),
                'route'      => route('helpdesk.index'),
                'icon'       => 'fas fa-headset',
                'key'        => 'helpdesk',
                'permission' => 'helpdesk ticket manage',
                'children'   => [],
            ],
            [
                'name'       => __('Settings'),
                'route'      => route('settings.index'),
                'icon'       => 'fas fa-gear',
                'key'        => 'settings',
                'permission' => 'setting manage',
                'children'   => [
                    [
                        'name'       => __('Brand Settings'),
                        'route'      => route('settings.index') . '#site-settings',
                        'icon'       => 'fas fa-paint-brush',
                        'key'        => 'brand_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('System Settings'),
                        'route'      => route('settings.index') . '#system-settings',
                        'icon'       => 'fas fa-server',
                        'key'        => 'system_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('Company Settings'),
                        'route'      => route('settings.index') . '#company-setting-sidenav',
                        'icon'       => 'fas fa-building',
                        'key'        => 'company_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('Currency Settings'),
                        'route'      => route('settings.index') . '#currency-setting-sidenav',
                        'icon'       => 'fas fa-money-bill-alt',
                        'key'        => 'currency_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('Proposal Print Settings'),
                        'route'      => route('settings.index') . '#proposal-print-sidenav',
                        'icon'       => 'fas fa-file-pdf',
                        'key'        => 'proposal_print_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('Invoice Print Settings'),
                        'route'      => route('settings.index') . '#invoice-print-sidenav',
                        'icon'       => 'fas fa-file-pdf',
                        'key'        => 'invoice_print_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('Purchase Print Settings'),
                        'route'      => route('settings.index') . '#purchase-print-sidenav',
                        'icon'       => 'fas fa-file-pdf',
                        'key'        => 'purchase_print_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('Email Settings'),
                        'route'      => route('settings.index') . '#email-sidenav',
                        'icon'       => 'fas fa-envelope-open',
                        'key'        => 'email_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('Email Notification Settings'),
                        'route'      => route('settings.index') . '#email-notification-sidenav',
                        'icon'       => 'fas fa-bell',
                        'key'        => 'email_notification_settings',
                        'permission' => 'setting manage',
                    ],
                    [
                        'name'       => __('Bank Transfer'),
                        'route'      => route('settings.index') . '#bank-transfer-sidenav',
                        'icon'       => 'fas fa-university',
                        'key'        => 'bank_transfer',
                        'permission' => 'setting manage',
                    ],
                ],
            ],
        ];
    }

    public static function getAllowedMenus()
    {
        $menus        = self::getMenuList();
        $allowedMenus = [];

        foreach ($menus as $menu) {

            if (self::hasMenuPermission($menu)) {
                $menuCopy             = $menu;
                $menuCopy['children'] = [];

                if (! empty($menu['children'])) {
                    foreach ($menu['children'] as $submenu) {
                        if (self::hasMenuPermission($submenu)) {
                            $menuCopy['children'][] = $submenu;
                        }
                    }
                }

                if (empty($menu['children']) || ! empty($menuCopy['children'])) {
                    $allowedMenus[] = $menuCopy;
                }
            }
        }

        return $allowedMenus;
    }

    public static function getAllowedFlatMenus()
    {
        $menus     = self::getAllowedMenus();
        $flatMenus = [];

        foreach ($menus as $menu) {

            $flatMenus[] = $menu;


            if (! empty($menu['children'])) {
                foreach ($menu['children'] as $submenu) {

                    $submenu['parent'] = [
                        'name'  => $menu['name'],
                        'key'   => $menu['key'],
                        'route' => $menu['route'],
                    ];
                    $flatMenus[]       = $submenu;
                }
            }
        }

        return $flatMenus;
    }

    private static function hasMenuPermission($menu)
    {
        return true;
        if (! isset($menu['permission'])) {
        }

        return Auth::user()->can($menu['permission']);
    }

    public static function getMenuByKey($key)
    {
        $allMenus = self::getAllowedFlatMenus();

        foreach ($allMenus as $menu) {
            if ($menu['key'] === $key && self::hasMenuPermission($menu)) {
                return $menu;
            }
        }

        return null;
    }
}