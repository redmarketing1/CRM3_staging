<?php

namespace App\Services;
use DB;

use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\SafetySetting;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Content;
use Gemini\Data\Blob;
use Gemini\Enums\HarmBlockThreshold;
use Gemini\Enums\Role;
use Gemini\Enums\HarmCategory;

class GeminiAI
{
	function get_response($request_data)
	{
		$content 		= isset($request_data['content']) ? $request_data['content'] : '';
		$total_calls 	= isset($request_data['total_calls']) ? $request_data['total_calls'] : 1;
		$api_key 		= isset($request_data['api_key']) ? $request_data['api_key'] : '';
		$model 			= 'gemini-1.5-pro-latest';
		if(isset($request_data['model']) && !empty($request_data['model'])) {
			$model 		= $request_data['model'];
		}


		try {
			$safetySettingDangerousContent = new SafetySetting(
				category: HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT,
				threshold: HarmBlockThreshold::BLOCK_ONLY_HIGH
			);
			
			$safetySettingHateSpeech = new SafetySetting(
				category: HarmCategory::HARM_CATEGORY_HATE_SPEECH,
				threshold: HarmBlockThreshold::BLOCK_ONLY_HIGH
			);

			$generationConfig = new GenerationConfig(
				stopSequences: [
					'Title',
				],
				maxOutputTokens: 800,
				candidateCount: 1,
				temperature: 1,
				topP: 0.8,
				topK: 10
			);

			$result = Gemini::geminiPro()
			->withSafetySetting($safetySettingDangerousContent)
 			->withSafetySetting($safetySettingHateSpeech)
		//	->withGenerationConfig($generationConfig)
			->generateContent($content);

			$response_data = array('status' => true, 'data' => $result->text());
			return $response_data;
		} catch (\Exception $e) {
			// Handle exceptions
			$errorMessage = $e->getMessage();
			// echo $errorMessage;
			// die;
			$response_data = array('status' => false, 'message' => $errorMessage);
			return $response_data;
			// Log or process the error message...
		}
	}

	function get_conversations($request_data)
	{
		$last_message = "";
		$history_data = array();
		if (isset($request_data['conversation_history']) && count($request_data['conversation_history']) > 0) {
			$total_messages = count($request_data['conversation_history']);
			$last_message_number = $total_messages - 1;
			foreach ($request_data['conversation_history'] as $key => $chat_msg) {
				if ($last_message_number == $key) {
					$last_message = $chat_msg['content'];
					continue;
				}
				if ($chat_msg['role'] == "user") {
					$history_data[] = Content::parse(part: $chat_msg['content']);
				} else {
					$history_data[] = Content::parse(part: $chat_msg['content'], role: Role::MODEL);
				}
			}
		}

		try {
			$generationConfig = new GenerationConfig(
				maxOutputTokens: 800,
			);

			$chat = Gemini::geminiPro()
			->withGenerationConfig($generationConfig)
			->startChat(history: $history_data);
			$response = $chat->sendMessage($last_message);

			$response_data = array('status' => true, 'data' => $response->text());
			return $response_data;
		} catch (\Exception $e) {
			// Handle exceptions
			$errorMessage = $e->getMessage();

			$response_data = array('status' => false, 'message' => $errorMessage);
			return $response_data;
		}
	}
}
