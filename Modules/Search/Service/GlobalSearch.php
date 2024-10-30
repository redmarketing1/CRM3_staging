<?php

namespace Modules\Search\Service;

use App\Models\User;
use App\Models\Proposal;
use Modules\Project\Entities\Project;

class GlobalSearch
{
    public function search($keywords)
    {
        $results = [];

        $results = array_merge($results, $this->projects($keywords));
        $results = array_merge($results, $this->users($keywords));

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

    private function proposal($keywords)
    {
        return Proposal::where('name', 'LIKE', "%{$keywords}%")
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
}