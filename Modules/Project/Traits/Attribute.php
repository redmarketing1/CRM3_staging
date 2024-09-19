<?php

namespace Modules\Project\Traits;

use Carbon\Carbon;

trait Attribute
{
    /**
     * Extract status short name
     * @return string
     */
    public function getShortNameAttribute()
    {
        $shortName = $this->status_data->name ?? 'NA';

        $words = explode(' ', $shortName);

        if (count($words) >= 2) {
            $shortName = '';
            foreach ($words as $word) {
                if (! empty($word)) {
                    $shortName .= substr($word, 0, 1);
                }
            }
        }

        return strtoupper(substr($shortName, 0, 2));
    }

    /**
     * Calculate project "Days Left" for show projcet
     * Calculate the number of days between the start and end dates.
     *
     * @return string|int The difference in days between the start date and end date. 
     */
    public function getExpiredDateAttribute()
    {
        $endDate     = Carbon::parse($this->end_date);
        $currentDate = Carbon::today();

        if ($endDate->isPast()) {
            return "Expired on " . $endDate->toFormattedDateString();
        }

        return $endDate->diffInDays($currentDate);
    }

    /**
     * Get project background color
     * @return string
     */
    public function getBackgroundColorAttribute()
    {
        return $this->status_data->background_color ?? '#eeeeee';
    }

    /**
     * Get project font color
     * @return string
     */
    public function getFontColorAttribute()
    {
        return $this->status_data->font_color ?? '#777777';
    }

    public function getProjectCountAttribute()
    {
        return self::whereHas('status_data', function ($query) {
            $query->where('name', $this->status_data->name);
        })->count();
    }
}