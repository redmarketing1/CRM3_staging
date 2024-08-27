<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ChatgptService;
use App\Services\GeminiAI;
use App\Models\ApikeySetiings;
use App\Models\Conversation;
use App\Models\Chat;
use DB;

class SmartChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $open_ai_key;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
		$settings_data = ApikeySetiings::first();
		$this->open_ai_key = "";
		if(isset($settings_data->key)){
			$this->open_ai_key = $settings_data->key;
		}
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set("max_execution_time", "-1");
		ini_set("memory_limit", "-1");
		$conversations = Conversation::get();
		if(isset($conversations) && count($conversations) > 0) {
			foreach($conversations as $conversation) {
				$check_chat = Chat::where('conversation_id', $conversation->id)->where('status', 0)->count();
				if($check_chat > 0) {
					$chats = Chat::with('attachments','ai_model')->where('conversation_id', $conversation->id)->orderBy('id', 'ASC')->get();
					if(isset($chats) && count($chats) > 0) {
						$conversationHistory = array();
						$content = "";
						$is_pending_request = 0;
						foreach($chats as $smart_chat) {
							$content 	= $smart_chat->message;
							if (isset($smart_chat->attachments) && count($smart_chat->attachments) > 0) {
								$content_text = array(
									'type' => 'text',
									'text' => $smart_chat->message,
								);
								$content_data[] 	=  $content_text;
								foreach ($smart_chat->attachments as $file) {
									$content_image 	= array(
										'type' 		=> 'image_url',
										'image_url' => get_file('uploads/smart_chats/' . $file->file),
									);
									$content_data[] = $content_image;
								}
								$content 			= json_encode($content_data);
							}
							$conversation_data['content'] = $content;
							if($smart_chat->type == 0) {
								$conversation_data['role'] = 'user';
							} else {
								$conversation_data['role'] = 'assistant';
							}
							$conversationHistory[] = $conversation_data;
							if($smart_chat->type == 0 && $smart_chat->status == 0) {
								$is_pending_request = 1;
							}
						}
	
						if(isset($conversationHistory) && count($conversationHistory) > 0 && $is_pending_request > 0) {
							$all_requests = Chat::with('attachments','ai_model')->where('conversation_id', $conversation->id)->where('type', 0)->where('status', 0)->orderBy('id', 'ASC')->get();
							if(isset($all_requests) && count($all_requests) > 0) {
								$chatgptService 	= new ChatgptService();
								$geminiAI 			= new GeminiAI();
								foreach($all_requests as $smart_chat_msg) {
									$request_data 		= array(
										'total_calls' 			=> intval(1),
										'api_key'				=> $this->open_ai_key,
										'model'					=> isset($smart_chat_msg->ai_model->model) ? $smart_chat_msg->ai_model->model : '',
									);
									$ai_provider = isset($smart_chat_msg->ai_model->provider) ? $smart_chat_msg->ai_model->provider : 'ChatGPT';
							
									$ai_description 	= "";
									$request_data['conversation_history'] 	= $conversationHistory;
									if($ai_provider == "ChatGPT") {
										$response 			= $chatgptService->get_response($request_data);
										if (isset($response['data']) && count($response['data']) > 0) {
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
										$response 					= $geminiAI->get_conversations($request_data);
										if (isset($response['status']) && $response['status'] == true && isset($response['data'])) {
											$ai_description = ltrim($response['data']);
											$ai_description = str_replace('SERVICE POINTS:', '', $ai_description);
											$ai_description = str_replace('END', '', $ai_description);
											$ai_description = trim($ai_description);
										}
									}
					
									$insert_data['conversation_id'] = $smart_chat_msg->conversation_id;
									$insert_data['message'] 		= $ai_description;
									$insert_data['ai_model_id'] 	= $smart_chat_msg->ai_model_id;
									$insert_data['type'] 			= 1;
									$insert_data['status'] 			= 1;
									$new_chat 						= Chat::create($insert_data);
							
									Chat::find($smart_chat_msg->id)->update([
										'status' => 1
									]);
			
									$conversation 	= Conversation::find($smart_chat_msg->conversation_id);
									$conversation->touch();
								}
							}
						}
					}
				}
			}
		}
    }
}
