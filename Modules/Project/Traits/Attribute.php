<?php

namespace Modules\Project\Traits;

use Carbon\Carbon;
use Modules\Lead\Entities\Label;
use Illuminate\Support\Facades\File;
use Modules\Taskly\Entities\ProjectFile;

trait Attribute
{
    /**
     * Extract status short name
     * @return string
     */
    public function getShortNameAttribute()
    {
        $shortName = $this->statusData->name ?? 'NA';

        $words = explode(' ', $shortName);

        if (count($words) >= 2) {
            $shortName = '';
            foreach ($words as $word) {
                if (! empty($word)) {
                    $shortName .= substr($word, 0, 1);
                }
            }
        }

        return strtoupper(mb_substr($shortName, 0, 2));
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
        return $this->statusData->background_color ?? '#eeeeee';
    }

    /**
     * Get project font color
     * @return string
     */
    public function getFontColorAttribute()
    {
        return $this->statusData->font_color ?? '#777777';
    }

    public function getProjectCountAttribute()
    {
        return self::where('is_archive', 0)->whereHas('statusData', function ($query) {
            $query->where('name', $this->statusData->name);
        })->count();
    }

    public function getProjectCountByContactDetailAttribute()
    {
        return self::whereHas('statusData', function ($query) {
            $query->where('name', $this->statusData->name);
        })->whereHas('contactDetail', function ($query) {
            $query->whereNotNull('id')->whereNotNull('lat')->whereNotNull('long');
        })->count();
    }

    public function getThumbnailOrDefaultAttribute()
    {
        if ($this->hasMedia('projects')) {
            return $this->getFirstMediaUrl('projects', 'thumb');
        }

        $imageExtensions = ['jpg', 'jpeg', 'png'];

        $thumbnail = $this->hasOne(ProjectFile::class, 'project_id', 'id')
            ->where('is_default', 1)
            ->where(function ($query) use ($imageExtensions) {
                foreach ($imageExtensions as $extension) {
                    $query->orWhere('file_path', 'like', "%.{$extension}");
                }
            })
            ->orderBy('is_default', 'desc')
            ->first();

        if (! $thumbnail) {
            $thumbnail = $this->hasOne(ProjectFile::class, 'project_id', 'id')
                ->where('is_default', 0)
                ->where(function ($query) use ($imageExtensions) {
                    foreach ($imageExtensions as $extension) {
                        $query->orWhere('file_path', 'like', "%.{$extension}");
                    }
                })
                ->orderBy('is_default', 'desc')
                ->first();
        }

        if (isset($thumbnail->file_path) && File::exists(public_path($thumbnail->file_path))) {
            $this->addMedia(public_path($thumbnail->file_path))
                ->preservingOriginal()
                ->toMediaCollection('projects', 'projects');
        }

        if (empty($thumbnail->file_path)) {
            return asset('assets/images/default_thumbnail3.png'); // Default image
        }

        return asset($thumbnail->file_path);
    }

    public function getConstructionTypeDataAttribute()
    {
        if (empty($this->construction_type))
            return;

        $constructionIds = explode(',', $this->construction_type);
        return Label::whereIn('id', $constructionIds)->get();
    }

    public function getPropertyTypeDataAttribute()
    {
        if (empty($this->property_type))
            return;

        $propertyIds = explode(',', $this->property_type);
        return Label::whereIn('id', $propertyIds)->get();
    }

    public function getProjectLabelDataAttribute()
    {
        if (empty($this->label))
            return;

        $labelIds = explode(',', $this->label);
        return Label::whereIn('id', $labelIds)->get();
    }

    public function getProjectPriorityDataAttribute()
    {
        if (empty($this->priority))
            return;

        $priorityIds = explode(',', $this->priority);
        return Label::whereIn('id', $priorityIds)->get();
    }

}