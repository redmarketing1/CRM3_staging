<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\SmartPromptQueue;

class ProjectEstimation extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
		'title',
        'project_id',
        'issue_date',
		'technical_description',
		'created_by',
        'status',
        'is_active',
		'init_status'
	];

	protected $guarded = ['id', 'created_at', 'updated_at'];

	public static $statues = [
        'Draft',
        'Open',
        'Sent',
        'Close',
    ];
    public static $statuesColor = [
        'Draft' => 'dark',
        'Open' => 'info',
        'Sent' => 'success',
        'Close' => 'danger',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\ProjectEstimationFactory::new();
    }

	public function project()
    {
        return $this->hasOne(Project::class,'id','project_id')->first();
    }
    
    public function getProjectDetail()
    {
        return $this->hasOne(Project::class,'id','project_id');
    }

	public function quotes(){
        return $this->hasMany(EstimateQuote::class,'project_estimation_id')->where('is_display', 1);
    }

	public function user_quotes(){
        return $this->hasMany(EstimateQuote::class,'project_estimation_id')->where('user_id',Auth::user()->id);
    }

	public function all_quotes(){
        return $this->hasMany(EstimateQuote::class,'project_estimation_id');
    }

    public function all_quotes_list(){
        return $this->hasMany(EstimateQuote::class,'project_estimation_id')->whereNotNull('user_id')->groupBy('user_id', 'project_estimation_id');
    }

	public function estimation_groups()
    {
        return $this->hasMany(EstimationGroup::class,'estimation_id','id');
    }

	public function estimation_products()
    {
        return $this->hasMany(ProjectEstimationProduct::class,'project_estimation_id','id');
    }

	public function final_quote()
    {
        return $this->hasOne(EstimateQuote::class,'project_estimation_id','id')->where('is_final', 1);
    }

	public function estimationStatus(){
        return EstimateQuote::where('project_estimation_id',$this->id)->where('user_id',Auth::user()->id)->first();
    }

	public function reorderEstimationPos($estimation_id = ''){
        $return_data = array();
        $estimation = ProjectEstimation::with('estimation_groups')->find($estimation_id);
        $last_group_pos = "";
        $second_index = 0;
        $all_new_pos = array();
        if (!empty($estimation)) {
            foreach ($estimation->estimation_groups()->orderBy('group_pos')->get() as $key => $item_group) {
                foreach ($item_group->estimation_products as $row) {
                    $id = $row->id;
                    $grp_id = $item_group->id;
                    $grp_pos = $item_group->group_pos;
                    if ($last_group_pos == '') {
                        $last_group_pos = $grp_pos;
                    }
                    if ($grp_pos != $last_group_pos) {
                        $last_group_pos = $grp_pos;
                        $second_index = 0;
                    }
                    $second_index++;
                    $pos = $last_group_pos . '.' . str_pad($second_index, 2, 0, STR_PAD_LEFT);
                    $newpos = $pos;
                    ProjectEstimationProduct::find($id)->update([
                        'position' => $second_index,
                        'pos' => $pos,
                        'group_id' => $grp_id,
                    ]);
                    $all_new_pos[] = $newpos;
                }
            }
            if (!empty($all_new_pos)) {
                $return_data = ['status' => true, 'data' => $all_new_pos];
            } else {
                $return_data = ['status' => false];
            }
        } else {
            $return_data = ['status' => false];
        }
        return $return_data;
    }

	public function getQueuesProgress(){
        $queue_result = array();
        $prompt_details = SmartPromptQueue::where('estimation_id', $this->id)->get();
        if (!empty($prompt_details)) {
            $hash = array();
            $prompt_array = array();
            foreach ($prompt_details as $row) {
                $hash_key = $row->estimation_id.'|'.$row->smart_template_id;
                if (!array_key_exists($hash_key, $hash)) {
                    $hash[$hash_key] = sizeof($prompt_array);
                    array_push($prompt_array, array(
                        'project_id' => $row->project_id,
                        'project_title' => $this->getProjectDetail->title,
                        'estimation_id' => $row->estimation_id,
                        'estimation_title' => $this->title,
                        'type'              => $row->type,
                        'smart_template_main_title' => isset($row->smart_template->title) ? $row->smart_template->title : '',
                        'smart_template_id' => $row->smart_template_id,
                        'total_record' => 0,
                        'completed_record' => 0,
						'pending_record' => 0,
						'cancelled_record' => 0,
						'error_record' => 0,
						'error_message' => NULL,
						'quote_id' => $row->quote_id,
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
                $percentage = -1;
                foreach ($prompt_array as $row) {
                    $completed_percentage = ($row['completed_record'] / $row['total_record']) * 100;
                    $new_completed_percentage = ($completed_percentage >= 0) ? round($completed_percentage, 2) : $percentage;
                    $row['completed_percentage'] = $new_completed_percentage;
                    $queue_result['estimation_title'] = $row['estimation_title'];
                    $queue_result['estimation_queues_list'][] = $row;
                    // $completed_percentage = ($row['completed_record'] / $row['total_record']) * 100;
                    // $queue_result['completed_record'] = ($completed_percentage >= 0) ? round($completed_percentage, 2) : $percentage;
                    // $queue_result['project_title'] = $row['project_title'];
                    // $queue_result['estimation_title'] = $row['estimation_title'];
                    // $queue_result['smart_template_main_title'] = $row['smart_template_main_title'];
                }
            }
        }
        // dd($queue_result);
        return $queue_result;
    }
}
