<?php

namespace Modules\Project\Traits;

trait Scope
{
    // public function scopeProjectOnly($query)
    // {
    //     return $query->where('type', 'project');
    // }

    /**
     * Scope a query to get company project.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $clientId
     * @param string $workspace
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCompany($query, $userID)
    {
        return $query->whereCreatedBy($userID)
            ->with([
                'statusData',
                'priorityData',
                'constructionData',
                'constructionDetail',
                'contactDetail',
                'property',
                'thumbnail',
                'comments',
            ]);
    }

    /**
     * Scope a query to only include projects for a specific client.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $clientId
     * @param string $workspace
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForClient($query, $userID, $workspace)
    {
        return $query->select('projects.*')
            ->leftJoin('client_projects', 'client_projects.project_id', '=', 'projects.id')
            ->leftJoin('estimate_quotes', 'estimate_quotes.project_id', '=', 'projects.id')
            ->leftJoin('user_projects', 'user_projects.project_id', '=', 'projects.id')
            ->where(function ($query) use ($userID) {
                $query->where('client_projects.client_id', $userID)
                    ->orWhere('user_projects.user_id', $userID)
                    ->orWhere('estimate_quotes.user_id', $userID);
            })
            // ->where('client_projects.client_id', $userID)
            // ->where('projects.type', 'project')
            // ->where('projects.workspace', $workspace)
            ->groupBy('projects.id')
            ->with([
                'statusData',
                'priorityData',
                'constructionData',
                'constructionDetail',
                'property',
                'thumbnail',
                'comments',
            ]);
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