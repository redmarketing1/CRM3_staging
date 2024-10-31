<?php

namespace Modules\Search\Service;

use App\Models\User;
use App\Models\Proposal;
use Modules\Project\Entities\Project;
use Modules\Search\Service\MenuConfig;

class GlobalSearch
{
    public function search($keywords)
    {
        $results = [];

        $results = array_merge($results, $this->projects($keywords));
        $results = array_merge($results, $this->users($keywords));
        $results = array_merge($results, $this->menus($keywords));

        // Sort results by priority
        usort($results, function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        return $results;
    }
    private function projects($keywords)
    {
        return Project::where('name', 'LIKE', "%{$keywords}%")
            ->orWhere('description', 'LIKE', "%{$keywords}%")
            ->get()
            ->map(function ($project) {
                return [
                    'type'     => 'Project',
                    'view'     => view('search::partials.project_result', compact('project'))->render(), // Rendered project view
                    'priority' => 1,
                ];
            })
            ->toArray();
    }

    private function users($keywords)
    {
        return User::where('name', 'LIKE', "%{$keywords}%")
            ->orWhere('email', 'LIKE', "%{$keywords}%")
            ->get()
            ->map(function ($user) {
                return [
                    'type'     => 'User',
                    'view'     => view('search::partials.user_result', compact('user'))->render(),
                    'priority' => 2,
                ];
            })
            ->toArray();
    }

    private function menus($keywords)
    {
        $results      = [];
        $keywords     = strtolower($keywords);
        $allowedMenus = MenuConfig::getAllowedFlatMenus();

        foreach ($allowedMenus as $menu) {
            if (str_contains(strtolower($menu['name']), $keywords)) {
                $results[] = [
                    'type'     => isset($menu['parent']) ? 'Submenu' : 'Menu',
                    'view'     => view('search::partials.menu_result', [
                        'name'       => $menu['name'],
                        'route'      => $menu['route'],
                        'icon'       => $menu['icon'],
                        'parentName' => isset($menu['parent']) ? $menu['parent']['name'] : null,
                        'isParent'   => ! empty($menu['children']),
                        'key'        => $menu['key'],
                    ])->render(),
                    'priority' => isset($menu['parent']) ? 4 : 3
                ];
            }
        }

        return $results;
    }
}