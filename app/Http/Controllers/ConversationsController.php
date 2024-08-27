<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use App\Jobs\SmartChatJob;
use App\Services\ChatgptService;
use App\Services\GeminiAI;
use Modules\Taskly\Entities\Project;
use Modules\Taskly\Entities\ProjectFile;
use Modules\Taskly\Entities\ProjectEstimation;
use Modules\Taskly\Entities\EstimateQuote;
use Modules\Taskly\Entities\EstimationGroup;
use Modules\Taskly\Entities\ProjectEstimationProduct;
use Modules\Taskly\Entities\EstimateQuoteItem;
use App\Models\User;
use App\Models\ApikeySetiings;
use App\Models\Content;
use App\Models\Conversation;
use App\Models\Chat;
use App\Models\ChatAttachment;
use DB;


use function GuzzleHttp\Promise\all;

class ConversationsController extends Controller
{
	public function get_all_conversations(Request $request)
    {
        try {
			$user 					= Auth::user();
			$conversations 			= Conversation::where('user_id', $user->id)->orderBy('updated_at', 'DESC')->get();
			$html_data 				= view('conversation.conversation_list', compact('conversations'))->render();
			return response(['status' => true, 'html_data' => $html_data]);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function get_chat(Request $request)
    {
        try {
			$user 					= Auth::user();
			$conversation_id 		= Crypt::decrypt($request->conversation_id);
			$action 				= isset($request->action) ? $request->action : '';
			if ($conversation_id == 0) {
				$insert_data['user_id'] 		= $user->id;
				$insert_data['workspace'] 		= getActiveWorkSpace();
				$conversation 		= new Conversation;
				$current_count 		= 0;
				$conversation_name 	= "";
				if (isset($request->chat_project_id) && !empty($request->chat_project_id)) {
					$project_id					= Crypt::decrypt($request->chat_project_id);
					$current_count 				= $conversation->where('project_id', $project_id)->count();
					$project 					= Project::find($project_id);
					if (isset($project->id)) {
						$insert_data['project_id'] 	= $project->id;
						$conversation_name 			= $project->name;
					}
				} else {
					$current_count				= $conversation->whereNull('project_id')->count();
					$conversation_name 			= "Chat";
				}
				$new_number 					= $current_count + 1;
				$insert_data['name'] 			= $conversation_name . " " . $new_number;
				$new_conversation 				= Conversation::create($insert_data);
				$conversation_id 				= $new_conversation->id;
			}

			$smart_chats 			= Chat::with('attachments')->where('conversation_id', $conversation_id)->orderBy('id', 'ASC')->get();
			$ai_model_id 			= "";
			$conversation 			= Conversation::find($conversation_id);
			if(isset($conversation->last_chat_model)){
				$ai_model_id 			= $conversation->last_chat_model;
			}

			if ($action == "refresh_chat") {
				$last_message 			= Chat::with('attachments')->where('conversation_id', $conversation_id)->orderBy('id', 'desc')->first();
				if ($last_message->type == 0) {
					return response(['status' => false]);
				} else {
					$smart_chats 		= Chat::where('id', $last_message->id)->get();
				}
			}

			$html_data 				= view('conversation.chat_msgs', compact('smart_chats'))->render();
			return response(['status' => true, 'html_data' => $html_data, 'conversation_id' => Crypt::encrypt($conversation_id), 'ai_model_id' => $ai_model_id]);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function rename_conversation(Request $request)
    {
        try {
			$user 					= Auth::user();
			$conversation_id 		= Crypt::decrypt($request->conversation_id);
			if(isset($request->name) && !empty($request->name)) {
				$conversation 			= Conversation::where('user_id', $user->id)->where('id', $conversation_id)->first();
				if(isset($conversation->id)) {
					$conversation->name = $request->name;
					$conversation->save();
					return response(['status' => true, 'message' => __('Conversation rename successfully!')]);
				}
				return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
			} else {
				return response()->json(['status' => false, 'message' => __('Conversation name required')]);
			}
			
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function delete_conversation(Request $request)
    {
        try {
			$user 					= Auth::user();
			$conversation_id 		= Crypt::decrypt($request->conversation_id);
			$conversation 			= Conversation::where('user_id', $user->id)->where('id', $conversation_id)->first();
			if(isset($conversation->id)) {
				$conversation->delete();
				return response(['status' => true, 'message' => __('Conversation deleted successfully!')]);
			}
			return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function chat_request(Request $request)
    {
        try {
			ini_set("max_execution_time", "-1");
			ini_set("memory_limit", "-1");

			$user 							= Auth::user();
			$conversation_id 				= Crypt::decrypt($request->conversation_id);
			$insert_data['conversation_id'] = $conversation_id;
			$insert_data['message'] 		= $request->prompt;
			$insert_data['ai_model_id'] 	= $request->ai_model_id;

			$new_chat 						= Chat::create($insert_data);
			$edit_msg 						= false;
			$edit_msg_data 					= array();
			if(isset($request->chat_id)) {
				$from_chat_id 	= Crypt::decrypt($request->chat_id);
				Chat::where('conversation_id', $conversation_id)->where('id', '>=', $from_chat_id)->where('id', '<', $new_chat->id)->delete();
				$edit_msg 		= true;
				$edit_msg_data	= array(
					'from_chat_id' => $from_chat_id,
					'to_chat_id' => $new_chat->id
				);
			}

			$fileData = $request->input('files');
			if (isset($fileData) && count($fileData) > 0) {
				foreach ($fileData as $file_detail) {
					// Remove the part before the comma (data:image/png;base64,)
					list($type, $file_detail) = explode(';', $file_detail);
					list(, $file_detail) = explode(',', $file_detail);

					// Extract the MIME type and get the extension
					$mimeType = str_replace('data:image/', '', $type);
					$extension = $mimeType == 'jpeg' ? 'jpg' : $mimeType;

					// Decode the Base64 string
					$file = base64_decode($file_detail);

					// Get the file size in bytes
					$image_size = strlen($file);

					
					$request = new Request();
					date_default_timezone_set('Europe/Berlin');
					$currentTime = date('His'); // Format: HHMMSS
					$fileName = uniqid() . '_' . $new_chat->id . '_' . $currentTime . '.' . $extension;


					$url = '';

					$dir_path = 'uploads/smart_chats/';
					if (!is_dir($dir_path)) {
						mkdir($dir_path, 0777);
					}

					$file_url    = $dir_path."/".$fileName;
					$result     = file_put_contents($file_url, $file);

					$attachments 				= new ChatAttachment();
					$attachments->chat_id 		= $new_chat->id;
					$attachments->file 			= $fileName;
					$attachments->save();
					
				}
			}
			$conversation 	= Conversation::find($new_chat->conversation_id);
			$conversation->touch();

			$smart_chats 	= Chat::where('id', $new_chat->id)->get();
			$display_dots 	= true;
			$view_data 		= view('conversation.chat_msgs', compact('smart_chats'))->render();
			$dots_data 		= "";
			if(isset($display_dots) && $display_dots == true) {
				$dots_data =	'<div class="d-flex message-response message-wrapper dots-msg">
									<div class="message">
										<div class="snippet" data-title="dot-elastic">
											<div class="stage">
												<div class="dot-elastic"></div>
											</div>
										</div>
									</div>
								</div>';
			}
			$html_data 		= $view_data . $dots_data;

			//SmartChatJob::dispatch();
			dispatch(new SmartChatJob());
			return response(['status' => true, 'html_data' => $html_data, 'latest_request_id' => Crypt::encrypt($new_chat->id), 'edit_msg' => $edit_msg, 'edit_msg_data' => $edit_msg_data]);

        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function file_upload_modal($id)
    {
		$conversation_id 		= Crypt::decrypt($id);
		$conversation 			= Conversation::find($conversation_id);
		$project_files = array();
		if(isset($conversation->project_id)) {
			$project_files 		= ProjectFile::where('project_id', $conversation->project_id)->get();
		}

		return view('conversation.file_select_modal', compact('conversation','project_files'));
    }

	public function get_latest_chat(Request $request)
    {
        try {
			$user 					= Auth::user();
			$conversation_id 		= Crypt::decrypt($request->conversation_id);
			$last_request_id 		= Crypt::decrypt($request->latest_request_id);

			$check_last_request 	=  Chat::where('conversation_id', $conversation_id)->where('type', 0)->where('status', 0)->count();
			$display_dots 			= false;
			if($check_last_request > 0) {
				$display_dots 		= true;
			}

			$smart_chats 			= Chat::with('attachments')->where('conversation_id', $conversation_id)->where('type', 1)->where('id', '>', $last_request_id)->orderBy('id', 'ASC')->get();
			$last_msg_id 			= $request->latest_request_id;
			$last_msg_response 		= "";
			$last_msg 				= Chat::where('conversation_id', $conversation_id)->where('id', '>', $last_request_id)->orderBy('id', 'DESC')->first();
			if(isset($last_msg->id)) {
				$last_msg_id 		= Crypt::encrypt($last_msg->id);
				$last_msg_response 	= $last_msg->message;
			}

			$view_data 		= view('conversation.chat_msgs', compact('smart_chats'))->render();
			$dots_data 		= "";
			if(isset($display_dots) && $display_dots == true) {
				$dots_data =	'<div class="d-flex message-response message-wrapper dots-msg">
									<div class="message">
										<div class="snippet" data-title="dot-elastic">
											<div class="stage">
												<div class="dot-elastic"></div>
											</div>
										</div>
									</div>
								</div>';
			}
			$html_data 		= $view_data . $dots_data;
			return response(['status' => true, 'html_data' => $html_data, 'display_dots' => $display_dots, 'latest_msg_id' => $last_msg_id, 'last_msg_response' => $last_msg_response]);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function get_prompt_data(Request $request)
    {
        $data = '';

        if(isset($request->template_id) && !empty($request->template_id)){
			$template = Content::find($request->template_id);
            $smartBlockDescription = getNotificationTemplateData($template->slug);
            // dd($smartBlockDescription);
            $smartBlockDescription = (isset($smartBlockDescription) && !empty($smartBlockDescription)) ? $smartBlockDescription : null;

            if(!empty($smartBlockDescription)){

                // $data = strip_tags($smartBlockDescription);
                // $content = str_replace("&nbsp;", "", $data);
                
                $data = html_entity_decode($smartBlockDescription);
            }
        }
        return $data;
    }

	public function chat_request_action(Request $request)
    {
        try {
			$user 			= Auth::user();
			if(isset($request->action) && isset($request->chat_id)) {
				$chat_id 	= Crypt::decrypt($request->chat_id);
				$chat 		= Chat::with('attachments')->find($chat_id);
				if(isset($chat->id)) {
					if($request->action == "request_again") {
					//	$data = html_entity_decode($chat->message);
						$insert_data['conversation_id'] = $chat->conversation_id;
						$insert_data['message'] 		= $chat->message;
						$insert_data['ai_model_id'] 	= $chat->ai_model_id;
						$new_chat 						= Chat::create($insert_data);
						if(isset($chat->attachments) && count($chat->attachments) > 0) {
							foreach($chat->attachments as $file) {
								$attachment_data['chat_id'] = $new_chat;
								$attachment_data['file'] 	= $file->file;
								ChatAttachment::create($attachment_data);
							}
						}
						
						$conversation 	= Conversation::find($new_chat->conversation_id);
						$conversation->touch();

						$smart_chats 	= Chat::where('id', $new_chat->id)->get();
						$display_dots 	= true;
						$view_data 		= view('conversation.chat_msgs', compact('smart_chats'))->render();
						$dots_data 		= "";
						if(isset($display_dots) && $display_dots == true) {
							$dots_data =	'<div class="d-flex message-response message-wrapper dots-msg">
												<div class="message">
													<div class="snippet" data-title="dot-elastic">
														<div class="stage">
															<div class="dot-elastic"></div>
														</div>
													</div>
												</div>
											</div>';
						}
						$html_data 		= $view_data . $dots_data;
						dispatch(new SmartChatJob());

						return response(['status' => true, 'html_data' => $html_data, 'latest_request_id' => Crypt::encrypt($new_chat->id), 'message' => __('Again requested!')]);
					//	return response()->json(['status' => true, 'message' => __('Again requested!')]);
					}
				}
			}
			return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function edit_chat(Request $request)
    {
        try {
			$user 			= Auth::user();
			if(isset($request->chat_id)) {
				$chat_id 	= Crypt::decrypt($request->chat_id);
				$chat 		= Chat::find($chat_id);
				if(isset($chat->id)) {
					$data 	= html_entity_decode($chat->message);
					return response(['status' => true, 'data' => $data, 'chat_id' => $request->chat_id]);
				}
			}
			return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function delete_chat(Request $request)
    {
        try {
			$user 			= Auth::user();
			if(isset($request->chat_id)) {
				$chat_id 	= Crypt::decrypt($request->chat_id);
				$chat 		= Chat::find($chat_id);
				if(isset($chat->id)) {
					$chat->delete();
					return response(['status' => true, 'message' => __('Message deleted successfully!')]);
				}
			}
			return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function chat_response_action(Request $request)
    {
        try {
			$user 					= Auth::user();
			if(isset($request->action) && isset($request->project_id) && isset($request->smart_chat_id)) {
				$project_id 	= Crypt::decrypt($request->project_id);
				$smart_chat_id 	= Crypt::decrypt($request->smart_chat_id);
				$smart_chat 	= Chat::find($smart_chat_id);
				if(isset($smart_chat->id)) {
					if($request->action == "save_as_technical_desc") {
						$project 		= Project::find($project_id);
						if(!isset($project->id)) {
							return response()->json(['status' => false, 'message' => __('Project not found')]);
						}
						$project->technical_description = $smart_chat->message;
						$project->save();

						return response(['status' => true, 'message' => __('Save Successfully!')]);
					} else if($request->action == "save_as_new_estimation") {
						$chatgpt_response = $this->generare_template_wise_data($smart_chat->message);
						$newDescription =
						'Please prepare array with field and value from the following description'.
						'--------------------------------'.
						$chatgpt_response;
						$settings_data = ApikeySetiings::first();
						$open_ai_key = "";
						if(isset($settings_data->key)){
							$open_ai_key = $settings_data->key;
						}

						$chatgptService 	= new ChatgptService();
						$geminiAI 			= new GeminiAI();
						$request_data = array(
							'content' 		=> $newDescription,
							'total_calls' 	=> intval(1),
							'api_key'		=> $open_ai_key,
						//	'model'			=> isset($smart_chat->ai_model->model) ? $smart_chat->ai_model->model : '',
						);
						$ai_provider = isset($smart_chat->ai_model->provider) ? $smart_chat->ai_model->provider : 'ChatGPT';
						
						$table_data 	= array();
						if($ai_provider == "ChatGPT") {
							$response 			= $chatgptService->get_response($request_data);
							$ai_description 	= "";
	
							if (isset($response['data']) && count($response['data']) > 0) {
								foreach ($response['data'] as $value) {
									if (isset($value['message']['content'])) {
										$ai_description = ltrim($value['message']['content']);
										$ai_description = str_replace('SERVICE POINTS:', '', $ai_description);
										$ai_description = str_replace('END', '', $ai_description);
										$ai_description = str_replace('```', '', $ai_description);
										$ai_description = str_replace('json', '', $ai_description);
										$ai_description = trim($ai_description);
									}
								}
								$smart_table_data = json_decode($ai_description);
	
								if(isset($smart_table_data) && count($smart_table_data) > 0) {
									foreach($smart_table_data as $smart_data) {
										$table_data[] = (array) $smart_data;
									}
								}
							}
						}
						
						if(isset($table_data) && count($table_data) > 0) {
							$project 	= Project::find($project_id);
							if(!isset($project->id)) {
								return response()->json(['status' => false, 'message' => __('Project not found')]);
							}
							$estimation = ProjectEstimation::where('project_id', $project->id)->count();
							$title 		= !empty($estimation) ? __("Estimation") . " " . ($estimation + 1) : __("Estimation") . " 1";
							$estimation 			= new ProjectEstimation();
							$estimation->project_id = $project->id;
							$estimation->title 		= $title;
							$estimation->issue_date = date('Y-m-d');
							$estimation->status 	= 1;
							$estimation->created_by = $user->id;
							if(isset($project->technical_description) && !empty($project->technical_description)) {
								$estimation->technical_description = $project->technical_description;
							}
							$estimation->save();
					
							$estimation_id 		= $estimation->id;
							$net_total 			= 0;
							$last_group_id  	= "";
							$last_group_name	= "";
							$group_position  	= 0;
							$item_prices 		= array();
							foreach($table_data as $product) {
								$pos 			= isset($product['Pos']) ? $product['Pos'] : '';
								if($pos == ""){
									$pos 		= isset($product['Position']) ? $product['Position'] : '';
								}
								$group_name 	= isset($product['Trade']) ? $product['Trade'] : '';
								$name 			= isset($product['Short Text']) ? $product['Short Text'] : '';
								$description 	= isset($product['Description']) ? $product['Description'] : '';
								$unit 			= isset($product['Unit']) ? $product['Unit'] : '';
								$quantity 		= isset($product['Quantity']) ? floatval($product['Quantity']) : 0;
								$price 			= 0;
								$optional 		= 1;

								$total_price 	= ($optional == 0) ? 0 : round($quantity * $price, 2);
								$net_total 		+= $total_price;
								
								if ($group_name != $last_group_name) {
									$last_group_name = $group_name;
									$group_position++;
				
									$est_grp_data = array();
									$est_grp_data['group_pos'] 		= str_pad($group_position, 2, 0, STR_PAD_LEFT);
									$est_grp_data['group_name'] 	= $group_name;
									$est_grp_data['estimation_id'] 	= $estimation_id;
									$est_grp_data['position'] 		= $group_position;
				
									$new_group 		= EstimationGroup::create($est_grp_data);
									$last_group_id  = $new_group->id;
								}

								$item 							= new ProjectEstimationProduct();
								$item->project_estimation_id 	= $estimation_id;
								$item->group_id 				= $last_group_id;
								$item->pos 						= $pos;
								$item->type 					= "item";
								$item->name 					= $name;
								$item->is_optional 				= $optional;
								$item->description 				= $description;
								$item->unit 					= $unit;
								$item->quantity 				= $quantity;
								$item->save();

								$item_id 					= $item->id;
								$item_data['price'] 		= round($price, 2);
								$item_data['total_price'] 	= $total_price;
								$item_prices[$item_id] 		= $item_data;
							}

							$quote = ProjectEstimation::find($estimation_id);;
							$quote->quoteItem = $quote->estimation_products;
							$quote->project_estimation_id = $quote->id;

							$company_details 		= getCompanyAllSetting();
							$company_name 			= $company_details['company_name'];
							$user 					= Auth::user();
							$quate_title 			= $company_name;
							if($user->type != "company") {
								$quate_title = $user->name;
							}
							$user_id = null;
							$user_data = User::find($quote->created_by);
							if (!empty($user_data)) {
								if ($user_data->type != "company") {
									$user_id = ($quote->created_by) ? $quote->created_by : null;
								} else {
									if ($user->type != "company") {
										$user_id = $user->id;
									}
								}
							}

							$new_quote = EstimateQuote::create([
								"title" => $quate_title,
								"net" => $net_total,
								"net_with_discount" => $net_total,
								"gross" => $net_total,
								"gross_with_discount" => $net_total,
								"discount" => 0,
								"tax" => 0,
								"is_clone" => 0,
								"markup" => 0,
								"project_estimation_id" => $quote->project_estimation_id,
								"project_id" => $quote->project_id,
								'is_final' => 1,
								'user_id' => $user_id,
							]);
							foreach ($quote->quoteItem as $item) {
								EstimateQuoteItem::create([
									"estimate_quote_id" => $new_quote->id,
									"product_id" => $item->id,
									"price" => $item_prices[$item->id]['price'],
									"base_price" => $item_prices[$item->id]['price'],
									"total_price" => $item_prices[$item->id]['total_price'],
								]);
							}
							return response()->json(['status' => true, 'message' => __('Save Successfully!')]);
						}
					}
				}
			}
			return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function generare_template_wise_data($string)
    {
		$progressFinalizeEmailTemplate = getNotificationTemplateData('generate_estimation');
		$new_output = $progressFinalizeEmailTemplate. "\r\n";
		$new_output .= $string;

		return $new_output;
    }
}
