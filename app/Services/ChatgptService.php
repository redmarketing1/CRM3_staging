<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\ApikeySetiings;

class ChatgptService
{
//	function get_response($content, $total_calls, $open_ai_key)
	function get_response($request_data)
	{
		$content 		= isset($request_data['content']) ? $request_data['content'] : '';
		$total_calls 	= isset($request_data['total_calls']) ? $request_data['total_calls'] : 1;
		$open_ai_key 	= isset($request_data['api_key']) ? $request_data['api_key'] : '';
		$model 			= 'gpt-3.5-turbo';
		if(isset($request_data['model']) && !empty($request_data['model'])) {
			$model 		= $request_data['model'];
		}

		$messages = [];
		if(isset($request_data['conversation_history']) && !empty($request_data['conversation_history'])) {
			$messages = $request_data['conversation_history'];
		} else {
			$messages = [
				[
					"role" => "user",
					"content" => $content
				]
			];
		}

		try {
			$response = Http::withHeaders([
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $open_ai_key,
				])
				->retry(3, 100) // Retry 3 times, with a 100ms delay between attempts
				->timeout(300) // Increase the timeout to 3000 seconds (5 minutes)
				->connectTimeout(10) // Set the connection timeout to 10 seconds
				->post("https://api.openai.com/v1/chat/completions", [
					// "model" => "gpt-3.5-turbo",
					// "model" => "gpt-4-0125-preview",
					// "model" => "gpt-4",
					"model" => $model,
					'temperature' => 0.2,
					'messages' => $messages,
					// 'timeout' => 5, // Response timeout
					// 'connect_timeout' => 1, // Connection timeout
					// 'functions' => [
					// 	[
					// 		"name" => "google_for_answers",
					// 		"description" => "Search Google with fully-formed http URL to enhance knowledge.",
					// 		"parameters" => [
					// 			"type" => "object",
					// 			"properties" => [
					// 				"url" => [
					// 					"type" => "string"
					// 				]
					// 			]
					// 		]
					// 	]
					// ],
					'n' => $total_calls
				])
				->json();

				$response_data = array();

				if (isset($response) && isset($response['error']) && !empty($response['error'])) {
					mail_to_admin($response['error'], $open_ai_key);
					if ($response['error']['code'] == "insufficient_quota") {
						$settings_data2 = ApikeySetiings::orderBy("id", "desc")->first();
						$chatgpt_key2 = "";
						if (isset($settings_data2->key)) {
							$chatgpt_key2 = $settings_data2->key;
						}
						ApikeySetiings::where('id','!=',$settings_data2->id)->update(array('key' => $chatgpt_key2));

						$settings_data2->key = $open_ai_key;
						$settings_data2->save();
					}
					$response_data = array('status' => false, 'message' => $response['error']['message']);
				}

				if (isset($response['choices'])) {
					$response_data = array('status' => true, 'data' => $response['choices']);
				}

				return $response_data;
		} catch (\Exception $e) {
			// Handle exceptions
			$errorMessage = $e->getMessage();

			mail_to_admin($errorMessage, $open_ai_key);
			
			$settings_data2 = ApikeySetiings::orderBy("id", "desc")->first();
			$chatgpt_key2 = "";
			if (isset($settings_data2->key)) {
				$chatgpt_key2 = $settings_data2->key;
			}
			ApikeySetiings::where('id','!=',$settings_data2->id)->update(array('key' => $chatgpt_key2));

			$settings_data2->key = $open_ai_key;
			$settings_data2->save();
			//	break;
			
			$response_data = array('status' => false, 'message' => $errorMessage);
			return $response_data;
			// Log or process the error message...
		}
	}
}
