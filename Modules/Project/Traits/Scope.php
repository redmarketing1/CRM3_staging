<?php

namespace Modules\Project\Traits;

trait Scope
{
    public function scopeProjectOnly($query)
    {
        return $query->where('type', 'project');
    }

    /**
     * Scope a query to only include projects for a specific client.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $clientId
     * @param string $workspace
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForClient($query, $clientId, $workspace)
    {
        return $query->select('projects.*')
            ->join('client_projects', 'projects.id', '=', 'client_projects.project_id')
            ->projectonly()
            ->where('client_projects.client_id', $clientId)
            ->where('projects.workspace', $workspace);
    }

    /**
     * Scope a query to only include projects for a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @param string $workspace
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId, $workspace)
    {
        return $query->select('projects.*')
            ->join('user_projects', 'projects.id', '=', 'user_projects.project_id')
            ->projectonly()
            ->where('user_projects.user_id', $userId)
            ->where('projects.workspace', $workspace);
    }

}