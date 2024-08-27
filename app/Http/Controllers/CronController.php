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
use App\Services\ChatgptService;
use App\Services\GeminiAI;
use App\Models\ContentTemplateLang;
use App\Models\ApikeySetiings;
use App\Models\Content;
use App\Models\SmartPromptQueue;
use App\Models\SmartPromptQueueResult;
use Modules\Taskly\Entities\ProjectEstimation;
use Modules\Taskly\Entities\EstimateQuote;
use Modules\Taskly\Entities\ProjectEstimationProduct;
use Modules\Taskly\Entities\EstimateQuoteItem;
use JanDrda\LaravelGoogleCustomSearchEngine\LaravelGoogleCustomSearchEngine;
use Batch;

class CronController extends Controller
{
	public $open_ai_key;

    public function __construct(){
		$settings_data = ApikeySetiings::first();
		$this->open_ai_key = "";
		if(isset($settings_data->key)){
			$this->open_ai_key = $settings_data->key;
		}
    }

	public function run_all_queues()
	{
		$this->run_number_queue();
		$this->run_save_quate();
		$this->run_description_queue();
	}

	public function run_number_queue()
	{
		ini_set("max_execution_time", "-1");
		ini_set("memory_limit", "-1");

		$notification_template 	= Content::where('slug','extract_number_prompt')->first();

		$curr_noti_tempLanges  	= ContentTemplateLang::where('parent_id', '=', $notification_template->id)->get();
		$notification_languages	= array();
		if(count($curr_noti_tempLanges) > 0){
			foreach($curr_noti_tempLanges as $nt_langs) {
				$notification_languages[$nt_langs->lang] = $nt_langs->content;
			}
		}
		$prompts 				= SmartPromptQueue::where('type', 1)->where('status', 0)->with('spq_results','smart_template')->get();

		$google_search_engine 	= new LaravelGoogleCustomSearchEngine();
		$gse_parameters 		= array(
									'start' => 10, // start from the 10th results,
									'num' => 10 // number of results to get, 10 is maximum and also default value
								);
		$chatgptService 	= new ChatgptService();
		$geminiAI 			= new GeminiAI();
		$update_spq 		= array();
		$spq_results 		= array();
		$prompt_ids 		= array();
		
		foreach($prompts as $prompt) {
			$genetated_results 	= count($prompt->spq_results);
			$total_requests 	= intval($prompt->number_of_request);
			$remaining_request 	= $total_requests - $genetated_results;

			if($remaining_request < 0) {
				SmartPromptQueueResult::where('spq_id', $prompt->id)->delete();
				$remaining_request = $total_requests;
				continue;
			}
			if(isset($prompt->result_number)) {
				continue;
			}

			if ($remaining_request > 0) {
				$current_prompt = $prompt->prompt;
				// $results = $google_search_engine->getResults($current_prompt);
				// $rawResults = $google_search_engine->getRawResult(); // get complete response from Google
				// $google_search_prompt = "";
				// if(isset($rawResults->items)){
				// 	$formatted = [];
				// 	foreach ($rawResults->items as $item) {
				// 		$formatted[] = $item->title . ": " . $item->snippet;
				// 	}
				// 	$google_search_prompt =implode("\n", $formatted);
				// }
				// if(!empty($google_search_prompt)) {
				// 	$current_prompt .= $google_search_prompt;
				// }

				$extractNumberTemplate 	=  '';
				if(isset($notification_languages[$prompt->language])) {
					$extractNumberTemplate 	=  $notification_languages[$prompt->language];
				} else {
					$extractNumberTemplate 	=  isset($notification_languages['en']) ? $notification_languages['en'] : '';
				}

				$promptData = "";
				$allVariable = [
					'{extracted_value}',
				];
				$allVariabelValues = [
					$prompt->prompt_title,
				];
				if(isset($extractNumberTemplate) && !empty($extractNumberTemplate)) {
					$promptData = str_replace($allVariable, $allVariabelValues, $extractNumberTemplate);
					$promptData = str_replace(array("\r", "<p>", "</p>"), "", $promptData);
				}

				$request_data = array(
					'content' 		=> $current_prompt,
					'total_calls' 	=> $remaining_request,
					'model'			=> isset($prompt->ai_model_name) ? $prompt->ai_model_name : '',
				);
				$ai_provider = isset($prompt->ai_model_provider) ? $prompt->ai_model_provider : '';

				$arrayData = array();
				if($ai_provider == "ChatGPT") {
					$request_data['api_key'] 	= $this->open_ai_key;
					$response 	= $chatgptService->get_response($request_data);

					if(isset($response['status']) && $response['status'] == false) {
						// $prompt->update([
						// 	'status' => 3,
						// 	'error_message' => __('Error') . ' : ' . $response['message'],
						// ]);
						SmartPromptQueue::where('product_id', $prompt->product_id)->where('quote_id', $prompt->quote_id)->where('estimation_id', $prompt->estimation_id)->update([
							'status' => 3,
							'error_message' => __('Error') . ' : ' . $response['message'],
						]);
						return false;
					}
					if (isset($response['status']) && $response['status'] == true && count($response['data']) > 0) {
						foreach ($response['data'] as $value) {
							$arrayData[] = ltrim($value['message']['content']) . "\r\n\r\n\r\n";
						}
					}
				} else if($ai_provider == "GeminiAI") {
					$request_data['api_key'] 	= env("GEMINI_API_KEY");

					for ($x = 0; $x < $request_data['total_calls']; $x++) {
						$response 					= $geminiAI->get_response($request_data);
						if(isset($response['status']) && $response['status'] == false) {
							// $prompt->update([
							// 	'status' => 3,
							// 	'error_message' => __('Error') . ' : ' . $response['message'],
							// ]);
							SmartPromptQueue::where('product_id', $prompt->product_id)->where('quote_id', $prompt->quote_id)->where('estimation_id', $prompt->estimation_id)->update([
								'status' => 3,
								'error_message' => __('Error') . ' : ' . $response['message'],
							]);
							return false;
						}
						if (isset($response['status']) && $response['status'] == true && isset($response['data'])) {
							$arrayData[] = $response['data'];
						}
					}
				}

				if (isset($arrayData) && !empty($arrayData)) {
					$newAverageData = array();
					foreach($arrayData as $vData) {
						$newDescription = $promptData .
							'--------------------------------' .
							$vData;
						
						$res_numbers = 0;
						$request_data['total_calls'] 	= 1;
						$request_data['content'] 		= $newDescription;
						$request_data['model'] 			= isset($prompt->extraction_ai_model_name) ? $prompt->extraction_ai_model_name : '';
						$ext_ai_provider 				= isset($prompt->extraction_ai_model_provider) ? $prompt->extraction_ai_model_provider : '';
						if($ext_ai_provider == "ChatGPT") {
							$request_data['api_key'] 	= $this->open_ai_key;
							$averageresponse 				= $chatgptService->get_response($request_data);
							if ($averageresponse['status'] == true && isset($averageresponse['data']) && count($averageresponse['data']) >= 1) {
								foreach ($averageresponse['data'] as $value) {
									$res_numbers 		= floatval($value['message']['content']);
									$newAverageData[] 	= floatval($value['message']['content']);
								}
							}
						} else if($ext_ai_provider == "GeminiAI") {
							$request_data['api_key'] 	= env("GEMINI_API_KEY");
							$averageresponse 			= $geminiAI->get_response($request_data);

							if ($averageresponse['status'] == true && isset($averageresponse['data']) && isset($averageresponse['data'])) {
								$newAverageData[] 	= floatval($averageresponse['data']);
								$res_numbers 		= floatval($averageresponse['data']);
							}
						}
						SmartPromptQueueResult::create([
							"spq_id" 				=> $prompt->id,
							"result_description" 	=> $vData,
							"result_number" 		=> $res_numbers,
						]);
					}
				}
			}
			$prompt_ids[] = $prompt->id;
		}
		$this->run_generate_trimmean($prompt_ids);
	}

	public function run_generate_trimmean($prompt_ids = array())
	{
		if(count($prompt_ids) > 0) {
			$prompts	= SmartPromptQueue::whereIn("id", $prompt_ids)->where('type', 1)->where('status', 0)->with('spq_results')->get();
			$generate_again = 0;
			foreach($prompts as $prompt) {
				$genetated_results 	= count($prompt->spq_results);
				$total_requests 	= intval($prompt->number_of_request);
				if($genetated_results == $total_requests) {
					$newAvgData = array();
					foreach($prompt->spq_results as $spq_res) {
						$newAvgData[] = floatval($spq_res->result_number);
					}
					$average = NULL;
					if (!empty($newAvgData)) {
						$average = 0;
						$trimmean 	= floatval($prompt->outliner / 100);
						$average	= trimMean($newAvgData, $trimmean);
					}
					if(isset($average)) {
						$prompt->update([
							'result_number' => $average,
							'status' => 1,
						]);
					}
				} else {
					SmartPromptQueueResult::where('spq_id', $prompt->id)->delete();
					$generate_again = 1;
				}
			}
			if($generate_again > 0) {
			//	$this->run_number_queue();
			}
		}
	}

	public function run_save_quate()
	{
		ini_set("max_execution_time", "-1");
		ini_set("memory_limit", "-1");

		$sp_queues = SmartPromptQueue::where('type', 1)->where('status', 1)->groupBy('smart_template_id', 'quote_id')->get();

		$update_spq = array();
		if (count($sp_queues) > 0) {
			foreach ($sp_queues as $queue) {
				if (!empty($queue->estimation_id)) {
					$project_estimation = ProjectEstimation::with("estimation_products")->find($queue->estimation_id);
					if (isset($project_estimation->id)) {
						$quoteItem 		= $project_estimation->estimation_products;
						$type 			= 'estimation_products';
						$est_quote_id 	= $queue->quote_id;
						$old_quote 		= EstimateQuote::with("quoteItem")->find($est_quote_id);
						if(isset($old_quote->id) && count($old_quote->quoteItem) > 0){
							$quoteItem 			= $old_quote->quoteItem;
							$type 				= 'quote';
						}

						if ($est_quote_id > 0) {
							$spq_data_queue = SmartPromptQueue::where('type', 1)->where('status', 1)->where('quote_id', $est_quote_id)->get();
							$item_ids 				= array();
							$spq_ids 				= array();
							if (count($spq_data_queue) > 0) {
								foreach($spq_data_queue as $value) {
									$item_ids[]	= $value->product_id;
									$spq_ids[]	= $value->id;
								}
							}
							
							if(count($spq_ids) > 0){
								foreach($spq_ids as $spq_detail) {
									$update_spq[$spq_detail] = array(
										'id' => $spq_detail,
										'status' => 2
									);
								}	
							}

							$insert_array = array();
							foreach ($quoteItem as $item) {
								$product_id 	= ($type == 'quote') ? $item->product_id : $item->id;
								$insert_item 	= array(
									"estimate_quote_id" => $est_quote_id,
									"product_id" 		=> $product_id,
									"base_price" 		=> ($type == 'quote') ? $item->base_price : 0,
									"price" 			=> ($type == 'quote') ? $item->price : 0,
									"total_price" 		=> ($type == 'quote') ? $item->total_price : 0,
									"smart_template_data" 	=> ($type == 'quote') ? $item->smart_template_data : NULL,
									"all_results" 			=> ($type == 'quote') ? $item->all_results : NULL,
									"created_at" 		=> date('Y-m-d h:i:s'),
									"updated_at" 		=> date('Y-m-d h:i:s')
									);
									$insert_array[$product_id] 		= $insert_item;
							}

							$projectEstimateProduct = ProjectEstimationProduct::whereIn("id", $item_ids)->get();

							foreach ($projectEstimateProduct as $v) {
								if(isset($queue->result_operation) && !empty($queue->result_operation)){
									$queue_data = SmartPromptQueue::with('spq_results')->where('type', 1)->where('status', 1)->where('product_id', $v->id)->where('quote_id', $est_quote_id)->get();
									
									$prompt_res_slug = array();
									$prompt_res_value = array();
									$smart_costs = array();
									$all_costs_data = array();
									if(count($queue_data) > 0){
										foreach($queue_data as $s_prompt_queue) {
											$notification_template_name = isset($s_prompt_queue->smart_template_name) ? $s_prompt_queue->smart_template_name : '';
                    						$notification_template_slug = isset($s_prompt_queue->smart_template_slug) ? $s_prompt_queue->smart_template_slug : '';
											$prompt_res_slug[] = $s_prompt_queue->prompt_slug;
											$prompt_res_value[] = $s_prompt_queue->result_number;
											$all_prompt_costs = array();
											if(isset($s_prompt_queue->spq_results) && count($s_prompt_queue->spq_results) > 0) {
												foreach($s_prompt_queue->spq_results as $spq_result) {
													$spq_res_details = array(
														'result_number' =>$spq_result->result_number,
														'result_description' =>$spq_result->result_description,
													);
													$all_costs_data[] = $spq_res_details;
													$all_prompt_costs[] = $spq_res_details;
												}
											}
											$smart_costs[$notification_template_slug] = array(
												'label' => $s_prompt_queue->prompt_title,
												'value' => $s_prompt_queue->result_number,
												'details' => $all_prompt_costs
											);
										}
									}
									
									$result_operation = str_replace($prompt_res_slug, $prompt_res_value,$queue->result_operation);
									// Remove whitespaces
									$result_operation = preg_replace('/\s+/', '', $result_operation);
								
									$new_quote_price = eval('return '.$result_operation.';');

									if(isset($insert_array[$v->id])) {
										$smart_template_data = array(
											'template_id'   => $queue->smart_template_id,
											'result'        => $smart_costs,
										);
										$quote_qty 								= $v->quantity;
										$total_price 							= round($quote_qty * $new_quote_price, 2);
										$insert_array[$v->id]['base_price'] 	= $new_quote_price;
										$insert_array[$v->id]['price'] 			= $new_quote_price;
										$insert_array[$v->id]['total_price'] 	= $total_price;
										$insert_array[$v->id]['smart_template_data'] = json_encode($smart_template_data);
										$insert_array[$v->id]['all_results'] = json_encode($all_costs_data);
									}
								}
							}

							if (count($insert_array) > 0) {
								if($type == 'quote') {
									EstimateQuoteItem::where('estimate_quote_id', $est_quote_id)->delete();
								}
								EstimateQuoteItem::insert($insert_array);
							}

						}
					}
				}
			}

			if (count($update_spq) > 0) {
				Batch::update(new SmartPromptQueue, $update_spq, 'id');
			}
		}
	}

	public function run_description_queue()
	{
		ini_set("max_execution_time", "-1");
		ini_set("memory_limit", "-1");
		$prompts 			= SmartPromptQueue::with('smart_template')->where('type', 0)->where('status', 0)->get();
		$chatgptService 	= new ChatgptService();
		$geminiAI 			= new GeminiAI();
		$update_spq 		= array();
		$update_ai_desc 	= array();
		foreach($prompts as $prompt) {
			$request_data = array(
				'content' 		=> $prompt->prompt,
				'total_calls' 	=> intval($prompt->number_of_request),
				'api_key'		=> $this->open_ai_key,
				'model'			=> isset($prompt->ai_model_name) ? $prompt->ai_model_name : '',
			);
			$ai_provider = isset($prompt->ai_model_provider) ? $prompt->ai_model_provider : '';

			$ai_description = "";    
			if($ai_provider == "ChatGPT") {
				$response 	= $chatgptService->get_response($request_data);
				if(isset($response['status']) && $response['status'] == false) {
					$prompt->update([
						'status' => 3,
						'error_message' => __('Error') . ' : ' . $response['message'],
					]);
				}
				if (isset($response['status']) && $response['status'] == true && count($response['data']) > 0) {
					foreach ($response['data'] as $value) {
						if (isset($value['message']['content'])) {
							$ai_description = ltrim($value['message']['content']);
							$ai_description = str_replace('SERVICE POINTS:', '', $ai_description);
							$ai_description = str_replace('END', '', $ai_description);
							$ai_description = trim($ai_description);
						}
					}
				}
			} else if($ai_provider == "GeminiAI") {
				$request_data['api_key'] 	= env("GEMINI_API_KEY");
				$response 					= $geminiAI->get_response($request_data);
				if(isset($response['status']) && $response['status'] == false) {
					$prompt->update([
						'status' => 3,
						'error_message' => __('Error') . ' : ' . $response['message'],
					]);
				}
				if (isset($response['status']) && $response['status'] == true && isset($response['data'])) {
					$ai_description = ltrim($response['data']);
					$ai_description = str_replace('SERVICE POINTS:', '', $ai_description);
					$ai_description = str_replace('END', '', $ai_description);
				}
			}
			if($ai_description != "") {
				$update_spq[] = array(
					'id' 					=> $prompt->id,
					'result_description' 	=> $ai_description,
					'status' => 1
				);
				$update_ai_desc[] = array(
					'id' 					=> $prompt->product_id,
					'ai_description' 		=> $ai_description
				);
			}
		}
		
		if (count($update_spq) > 0) {
			Batch::update(new SmartPromptQueue, $update_spq, 'id');
		}

		if (count($update_ai_desc) > 0) {
			Batch::update(new ProjectEstimationProduct, $update_ai_desc, 'id');
		}
	}
}
