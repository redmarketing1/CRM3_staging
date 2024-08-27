<?php

namespace Modules\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Label extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'background_color',
		'font_color',
		'order',
        'pipeline_id',
        'workspace_id',
        'created_by',
    ];

    public static $colors = [
        'primary',
        'secondary',
        'danger',
        'warning',
        'info',
    ];

    protected static function newFactory()
    {
        return \Modules\Lead\Database\factories\LabelFactory::new();
    }

	public static function get_project_dropdowns($pipiline = array()) {
        $labels    = Label::select('labels.*', 'pipelines.name as pipeline', 'colors.code')->join('pipelines', 'pipelines.id', '=', 'labels.pipeline_id')->leftJoin('colors', 'colors.id', '=', 'labels.background_color')->where('pipelines.created_by', '=', creatorId())->where('labels.created_by', '=', creatorId())->orderBy('labels.pipeline_id')->orderBy('labels.order', 'ASC')->get();

		$pipelines = [];

		foreach($labels as $label) {
			if (!array_key_exists($label->pipeline_id, $pipelines)) {
				$pipelines[$label->pipeline_id]           = [];
				$pipelines[$label->pipeline_id]['name']   = $label['pipeline'];
				$pipelines[$label->pipeline_id]['labels'] = [];
			}
			$pipelines[$label->pipeline_id]['labels'][] = $label;
		}

        $data = array();

		if (count($pipelines) > 0) {
			foreach($pipelines as $pipeline) {
				$key_name 			= str_replace(" ", "_", strtolower($pipeline['name']));
				$data[$key_name] 	= $pipeline['labels'];
			}
		}

        return $data;
    }
}
