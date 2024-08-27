<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Taskly\Entities\Project;
use Modules\Taskly\Entities\ProjectEstimation;
use Illuminate\Support\Facades\DB;

class SmartPromptQueue extends Model
{
    use HasFactory;

	protected $fillable = [
        'project_id',
        'estimation_id',
        'quote_id',
        'product_id',
        'smart_template_id',
		'smart_template_main_title',
		'smart_template_name',
		'smart_template_slug',
        'prompt_id',
		'prompt',
		'prompt_title',
		'prompt_slug',
		'number_of_request',
		'outliner',
		'result_operation',
		'language',
		'type',
        'result_description',
        'result_number',
		'ai_model_name',
		'ai_model_provider',
		'extraction_ai_model_name',
		'extraction_ai_model_provider',
		'status',
		'error_message',
    ];

	public function project(){
        return $this->hasOne(Project::class,'id','project_id');
    }

    public function estimations(){
        return $this->hasOne(ProjectEstimation::class,'id','estimation_id')->withTrashed();
    }

    public function smart_template(){
        return $this->hasOne(SmartTemplate::class,'id','smart_template_id');
    }

	public function spq_results(){
        return $this->hasMany(SmartPromptQueueResult::class,'spq_id','id');
    }

	public static function get_record($estimation_id = 0) {
        $queue_result   = array();
        $prompt_details = SmartPromptQueue::with('project')->with('estimations')->with('smart_template')->orderBy("smart_prompt_queues.created_at", "DESC")->get();
		// $running_jobs 	= DB::table('jobs')->count();
		// if ($running_jobs == 0) {
		// 	$check_pending_results = SmartPromptQueue::where('type', 1)->whereNull('result_number')->count();
		// 	if ($check_pending_results > 0) {
		// 	//	dispatch(new GPTProcess());
		// 	}
		// }
        if (!empty($prompt_details)) {
            $hash = array();
            $prompt_array = array();
            foreach ($prompt_details as $row) {
                $hash_key = $row->project_id . '|' . $row->estimation_id . '|' . $row->smart_template_id;
                if (!array_key_exists($hash_key, $hash)) {
                    $hash[$hash_key] = sizeof($prompt_array);
                    array_push($prompt_array, array(
                        'project_id' => $row->project_id,
                        'project_title' => $row->project->name,
                        'estimation_id' => $row->estimation_id,
                        'estimation_title' => isset($row->estimations->title) ? $row->estimations->title : '',
                        'smart_template_main_title' => isset($row->smart_template->title) ? $row->smart_template->title : '',
                        'smart_template_id' => $row->smart_template_id,
                        'total_record' => 0,
                        'completed_record' => 0,
						'pending_record' => 0,
						'cancelled_record' => 0,
						'error_record' => 0,
						'error_message' => NULL,
                        'created_at'    => company_datetime_formate($row->created_at)
                    ));
                }

                $prompt_array[$hash[$hash_key]]['total_record'] += 1;
                if ((isset($row->result_number) && ($row->result_number != NULL)) || (isset($row->result_description) && ($row->result_description != NULL))) {
                    $prompt_array[$hash[$hash_key]]['completed_record'] += 1;
                }
				if($row->status == 0) {
					$prompt_array[$hash[$hash_key]]['pending_record'] += 1;
				}
				if($row->status == 3) {
					$prompt_array[$hash[$hash_key]]['error_record'] += 1;
				}
				if($row->status == 4) {
					$prompt_array[$hash[$hash_key]]['cancelled_record'] += 1;
				}
				if(isset($row->error_message) && $row->error_message != NULL) {
					$prompt_array[$hash[$hash_key]]['error_message'] 	= $row->error_message;
				}
            }
            if (!empty($prompt_array)) {
                foreach ($prompt_array as $row) {
                    $row['completed_percentage'] = round((($row['completed_record'] / $row['total_record']) * 100), 2);

                    $queue_result[$row['project_id']]['project_title']  = $row['project_title'];
                    $queue_result[$row['project_id']]['created_at']     = $row['created_at'];
                    $queue_result[$row['project_id']]['estimations_list'][$row['estimation_id']]['estimation_id'] = $row['estimation_id'];
                    $queue_result[$row['project_id']]['estimations_list'][$row['estimation_id']]['estimation_id'] = $row['estimation_id'];
                    $queue_result[$row['project_id']]['estimations_list'][$row['estimation_id']]['estimation_title'] = $row['estimation_title'];
                    $queue_result[$row['project_id']]['estimations_list'][$row['estimation_id']]['estimation_queues_list'][] = $row;
                }
            }
        }

        return $queue_result;
    }
}
