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
                'icon'       => 'fas fa-home',
                'key'        => 'dashboard',
                'permission' => 'crm dashboard manage',
                'children'   => [
                    [
                        'name'       => __('CRM Dashboard'),
                        'route'      => route('lead.dashboard'),
                        'icon'       => 'fas fa-palette',
                        'key'        => 'brand_settings',
                        'permission' => 'crm dashboard manage',
                    ],
                    [
                        'name'       => __('Project Dashboard'),
                        'route'      => route('taskly.dashboard'),
                        'icon'       => 'fas fa-palette',
                        'key'        => 'brand_settings',
                        'permission' => 'taskly dashboard manage',
                    ],
                ],
            ],
            [
                'name'       => __('Projects'),
                'route'      => route('project.index'),
                'icon'       => 'fas fa-file-contract',
                'key'        => 'project',
                'permission' => 'project manage',
                'children'   => [],
            ],
            [
                'name'       => __('Maps'),
                'route'      => route('project.map.index'),
                'icon'       => 'fas fa-file-contract',
                'key'        => 'project',
                'permission' => 'project manage',
                'children'   => [],
            ],
            [
                'name'       => __('User'),
                'route'      => route('users.index'),
                'icon'       => 'fas fa-users',
                'key'        => 'user',
                'permission' => 'user manage',
                'children'   => [],
            ],
            [
                'name'       => __('User Role'),
                'route'      => route('roles.index'),
                'icon'       => 'fas fa-users',
                'key'        => 'user-management',
                'permission' => 'user-management',
                'children'   => [],
            ],
            [
                'name'       => __('Proposal'),
                'route'      => route('proposal.index'),
                'icon'       => 'fas fa-file-contract',
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
                'icon'       => 'fas fa-file-invoice',
                'key'        => 'invoice',
                'permission' => 'invoice manage',
                'children'   => [],
            ],
            [
                'name'       => __('Helpdesk'),
                'route'      => route('headphones'),
                'icon'       => 'fas fa-file-invoice',
                'key'        => 'helpdesk ticket manage',
                'permission' => 'helpdesk.index',
                'children'   => [],
            ],
            [
                'name'       => 'Settings',
                'route'      => '/settings',
                'icon'       => 'fas fa-cog',
                'key'        => 'settings',
                'permission' => 'settings.view',
                'children'   => [
                    [
                        'name'  => 'Brand Settings',
                        'route' => '/settings/brand',
                        'icon'  => 'fas fa-palette',
                        'key'   => 'brand_settings',
                    ],
                    [
                        'name'  => 'System Settings',
                        'route' => '/settings/system',
                        'icon'  => 'fas fa-server',
                        'key'   => 'system_settings',
                    ],
                    [
                        'name'  => 'Company Settings',
                        'route' => '/settings/company',
                        'icon'  => 'fas fa-building',
                        'key'   => 'company_settings',
                    ],
                    [
                        'name'  => 'Currency Settings',
                        'route' => '/settings/currency',
                        'icon'  => 'fas fa-dollar-sign',
                        'key'   => 'currency_settings',
                    ],
                    [
                        'name'  => 'Proposal Print Settings',
                        'route' => '/settings/proposal-print',
                        'icon'  => 'fas fa-print',
                        'key'   => 'proposal_print_settings',
                    ],
                    [
                        'name'  => 'Invoice Print Settings',
                        'route' => '/settings/invoice-print',
                        'icon'  => 'fas fa-file-invoice',
                        'key'   => 'invoice_print_settings',
                    ],
                    [
                        'name'  => 'Purchase Print Settings',
                        'route' => '/settings/purchase-print',
                        'icon'  => 'fas fa-print',
                        'key'   => 'purchase_print_settings',
                    ],
                    [
                        'name'  => 'Email Settings',
                        'route' => '/settings/email',
                        'icon'  => 'fas fa-envelope',
                        'key'   => 'email_settings',
                    ],
                    [
                        'name'  => 'Email Notification Settings',
                        'route' => '/settings/email-notification',
                        'icon'  => 'fas fa-bell',
                        'key'   => 'email_notification_settings',
                    ],
                    [
                        'name'  => 'Bank Transfer',
                        'route' => '/settings/bank-transfer',
                        'icon'  => 'fas fa-university',
                        'key'   => 'bank_transfer',
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
            // Check if user has permission for the main menu
            if (self::hasMenuPermission($menu)) {
                $menuCopy             = $menu;
                $menuCopy['children'] = [];

                // Check permissions for children
                if (! empty($menu['children'])) {
                    foreach ($menu['children'] as $submenu) {
                        if (self::hasMenuPermission($submenu)) {
                            $menuCopy['children'][] = $submenu;
                        }
                    }
                }

                // Only add menu if it has permission and either has no children or has at least one allowed child
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
            // Add main menu
            $flatMenus[] = $menu;

            // Add allowed submenus with parent information
            if (! empty($menu['children'])) {
                foreach ($menu['children'] as $submenu) {
                    // Add parent name and key for proper breadcrumb display
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
        if (! isset($menu['permission'])) {
            return true; // If no permission specified, allow access
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