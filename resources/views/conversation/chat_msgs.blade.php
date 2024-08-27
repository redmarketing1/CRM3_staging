@if(isset($smart_chats) && count($smart_chats) > 0)
	@foreach($smart_chats as $chat)
		@if($chat->type == 0)
			<div class="d-flex message-user message-wrapper	msg_{{$chat->id}}" data-id="{{ \Crypt::encrypt($chat->id) }}">
				<div class="chat-message-opt">
					<i class="fa fa-bars chat-opt-i" id="aaaa" data-bs-toggle="dropdown" aria-expanded="false"></i>
					<ul class="dropdown-menu" aria-labelledby="aaaa">
						<li onclick="chat_request_action('request_again','{{ \Crypt::encrypt($chat->id) }}')">
							<label class="dropdown-item">
								{{ __('Request Again') }}
							</label>
						</li>
						<li class="edit_chat_msg" data-id="{{ \Crypt::encrypt($chat->id) }}">
							<label class="dropdown-item">
								{{ __('Edit') }}
							</label>
						</li>
						<li class="delete_chat_msg" data-id="{{ \Crypt::encrypt($chat->id) }}">
							<label class="dropdown-item">
								{{ __('Delete') }}
							</label>
						</li>
					</ul>
				</div>
				<div class="message user">
					{!! $chat->message !!}
					@if(isset($chat->attachments) && count($chat->attachments) > 0)
						<div class="smart-chat-user-attachments">
						@foreach($chat->attachments as $file)
							<div>
								<img src="{{ get_file('uploads/smart_chats/' . $file->file) }}" alt="">
							</div>
						@endforeach
						</div>
					@endif
				</div>
			</div>
		@elseif($chat->type == 1)
			<div class="d-flex message-response message-wrapper ai_responce_msg msg_{{$chat->id}}" data-id="{{ \Crypt::encrypt($chat->id) }}">
				<div class="message">
					@if (str_contains($chat->message, '</table>'))
						{!! $chat->message !!}
					@else
						{!! nl2br(e($chat->message)) !!}
					@endif
				</div>
				@if(isset($chat->conversation->project_id))
					<div class="chat-message-opt">
						<i class="fa fa-bars chat-opt-i" id="aaaa" data-bs-toggle="dropdown" aria-expanded="false"></i>
						<ul class="dropdown-menu" aria-labelledby="aaaa">
							<li onclick="smart_chat_response_action('save_as_technical_desc','{{ \Crypt::encrypt($chat->id) }}','{{ \Crypt::encrypt($chat->conversation->project_id) }}')">
								<label class="dropdown-item">
									{{ __('Save as technical description') }}
								</label>
							</li>
							<li onclick="smart_chat_response_action('save_as_new_estimation','{{ \Crypt::encrypt($chat->id) }}','{{ \Crypt::encrypt($chat->conversation->project_id) }}')">
								<label class="dropdown-item">
									{{ __('Save as new estimation') }}
								</label>
							</li>
						</ul>
					</div>
				@endif
			</div>
		@endif
	@endforeach
@endif