<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\SmartTemplate;
use App\Models\SmartTemplatesDetail;
use App\Models\ContentTemplateLang;
use App\Models\Content;
use App\Models\AiModel;

class SmartTemplateController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		$smart_templates = SmartTemplate::get();
		return view('smart_template.index',compact('smart_templates'));
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$validator = Validator::make(
			$request->all(), [
				'title' => 'required',
				'type' => 'required',
				'ai_model' => 'required',
				// 'result_operation' => 'required',
			]
		);
		if ($validator->fails()) {
			$messages = $validator->getMessageBag();
			return redirect()->back()->with('error', $messages->first());
		} else {
			$template_id = isset($request->template_id) ? Crypt::decrypt($request->template_id) : 0;

			$request_count 	= 0;
			$outliner 		= 0;
			if($request->type == 1){
				$request_count 	= $request->request_count;
				$outliner 		= $request->outliner;
			}
			$smart_template	= new SmartTemplate();
			if ($template_id > 0) {
				$smart_template	= SmartTemplate::find($template_id);
			}
            $smart_template->title				= $request->title;
            $smart_template->type				= $request->type;
            $smart_template->request_count		= $request_count;
            $smart_template->outliner			= $outliner;
            $smart_template->result_operation 	= $request->result_operation;
			$smart_template->ai_model_id			= $request->ai_model;
			$smart_template->extraction_ai_model_id	= $request->extraction_ai_model_id;
            $smart_template->created_by 		= Auth::user()->id;
            $smart_template->save();

			$template_id = $smart_template->id;

			if(!empty($request->smart_block_name) && !empty($request->smart_block_description)){
				SmartTemplatesDetail::where('template_id', $template_id)->delete();

				$total_record = count($request->smart_block_name);
				for($i = 0; $i < $total_record; $i++){
					$smart_template_details					= new SmartTemplatesDetail();
					$smart_template_details->template_id	= $template_id;
					$smart_template_details->prompt_id		= $request->smart_block_ids[$i];
					$smart_template_details->prompt_title	= $request->smart_block_name[$i];
					$smart_template_details->prompt_slug	= $request->smart_block_slug[$i];
					$smart_template_details->prompt_desc	= $request->smart_block_description[$i];
					$smart_template_details->created_by		= Auth::user()->id;
					$smart_template_details->save();
				}
			}
			return redirect()->route('smart-templates.index')->with('success', __('Smart Template successfully created.'));
		}
	}

	/**
	 * Display the specified resource.
	 */
	public function show(string $id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(string $id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, string $id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, string $id)
	{
		$template_id = isset($id) ? Crypt::decrypt($id) : 0;

		if ($template_id > 0) {
			SmartTemplate::where('id', $template_id)->delete();
			SmartTemplatesDetail::where('template_id', $template_id)->delete();

			return redirect()->route('smart-templates.index')->with('success', __('Smart Template successfully deleted.'));
		} else {
			return redirect()->back()->with('error', __('Something went wrong.'));
		}
	}

	public function setup(Request $request, $id = 0)
	{
		$template_id = ($id > 0) ? Crypt::decrypt($id) : 0;

		$template		 = new SmartTemplate();
		$selected_prompts = array();
		if ($template_id > 0) {
			$template = SmartTemplate::where('id', $template_id)->with('template_details')->first();
			foreach ($template->template_details as $prompt) {
				$selected_prompts[] = $prompt->prompt_id;
			}
		}

		$smart_prompts  = Content::whereIn('slug', ['total_costs', 'descriptions', 'disposal_costs', 'labor_cost', 'material_cost',])->where('is_ai', 1)->pluck('name', 'id')->toArray();

		$ai_models  	= AiModel::where('status', 1)->get();

		return view('smart_template.setup', compact('template_id', 'smart_prompts', 'template', 'selected_prompts', 'ai_models'));
	}

	public function all_data(Request $request)
	{
		$search         = $request->search;
		$start          = intval($request->start);
		$length         = intval($request->length);
		$order          = $request->order;
		$filters 		= isset($request->project_table_filter) ? json_decode($request->project_table_filter) : array();
		$filters_request = (array) $filters;
		$column_array   = array(
			'title',
			'type',
			'ai_model_id',
			'created_at',
			''
		);

		$templates = new SmartTemplate();

		$filter_count = $total_count = $templates->count();

		if (isset($search) && $search['value'] != "") {
			$search = $search['value'];
			$templates->where(function ($query) use ($search) {
				$query->where('title', 'LIKE', '%' . $search . '%')
					->orWhere('type', 'LIKE', '%' . $search . '%')
					->orwhereRaw("DATE_FORMAT(created_at,'%m.%d.%Y') like ?", ["%$search%"]);
			});

			$filter_count   = $templates->count();
		}

		if (count($filters_request) > 0) {
			$filter_count   = $templates->count();
		}

		if (!empty($order)) {
			$order_field =  $column_array[$order[0]['column']];
			$order_value = $order[0]['dir'];
			if ($order_field != '') {
				$templates->orderBy($order_field, $order_value);
			}
		}

		$record = $templates->skip($start)->take($length)->get();
		$data 	= array();

		foreach ($record as $key => $value) {

			$action = '<div class="action_btn">';
			$action .= '<div class="btn btn-sm bg-info ms-2" style="padding-top: 4px!important;">
			<a href="' . route('smart-template.edit', Crypt::encrypt($value->id)) . '"
				class="mx-3 btn btn-sm d-inline-flex align-items-center"
				data-bs-whatever="' . __('Edit Project') . '" data-bs-toggle="tooltip"
				data-bs-original-title="' . __('Edit') . '"> <span class="text-white">
				<i class="ti ti-edit"></i></span></a></div>
				';

			$action .= '<div class="btn btn-sm bg-danger ms-2" style="padding-top: 4px!important;">
					<form method="POST" action="' . route('smart-template.destroy', Crypt::encrypt($value->id)) . '" accept-charset="UTF-8">
					' . method_field('DELETE') . csrf_field() . '
					<a href="#!"
					class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
					<i class="ti ti-trash text-white" data-bs-toggle="tooltip"
						data-bs-original-title="' . __('Delete') . '"></i>
				</a>
			</form></div>';

			$action .= '</div>';

			$row                    = array();
			$row['name']         	= '<a href="' . route('smart-template.edit',  Crypt::encrypt($value->id)) . '">' . $value->title . '</a>';
			$row['type']          	= ($value->type == "0") ? __('Main Response') : __('Number');
			$row['ai_model']       	= isset($value->ai_model->model_label) ? $value->ai_model->model_label : '';
			$row['date'] 			= show_date_time($value->created_at);
			$row['users'] 			= '';
			$row['action']  		= $action;

			$data[]  = $row;
		}

		$response = array(
			"recordsTotal"          => $total_count,
			"recordsFiltered"       => $filter_count,
			"data"                  => $data
		);

		return $response;
	}

	public function getSmartBlock(Request $request){
		$prompts_html = "";
		if (isset($request->prompts) && !empty($request->prompts)) {
			$template_id 	= $request->template_id;
			$promt_old_data = array();

			$smart_block_description 	= getNotificationTemplateDataByID($request->prompts);
			$prompt_title_slug 			= isset($request->prompts_data) ? json_decode($request->prompts_data) : array();

			if (count($prompt_title_slug) > 0) {
				foreach($prompt_title_slug as $prompt_data) {
					$p_details['id'] 			= $prompt_data->prompt_id;
					$p_details['title'] 		= $prompt_data->title;
					$p_details['slug'] 			= $prompt_data->slug;
					$p_details['description']	= $prompt_data->description;
					$promt_old_data[$prompt_data->prompt_id] = $p_details;
				}
			} else if ($template_id > 0) {
				$template = SmartTemplate::where('id', $template_id)->with('template_details')->first();
				foreach($template->template_details as $prompt_data) {
					$p_details['id'] 			= $prompt_data->prompt_id;
					$p_details['title'] 		= $prompt_data->prompt_title;
					$p_details['slug'] 			= $prompt_data->prompt_slug;
					$p_details['description']	= $prompt_data->prompt_desc;
					$promt_old_data[$prompt_data->prompt_id] = $p_details;
				}
			}

			$prompts_html = view('smart_template.smart_block_template', compact('smart_block_description','promt_old_data'))->render();
		}

        return response()->json(array('success' => true, 'html'=> $prompts_html));
    }
}
