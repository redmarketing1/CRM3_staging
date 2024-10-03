<div id="chat-widget" class="chat_widget d-none">
	<div class="chat-header">
		<div class="row">
			<div class="col-md-10 d-flex">
				<div class="mr-2 back_icon chat_back_btn d-none"><i class="fa fa-arrow-left"></i></div>
				{{ __('Smart Chat') }}
			</div>
			<div class="col-md-2 text-right">
				<i class="fa fa-times close_chat_widget"></i>
			</div>
		</div>
		
	</div>
	<div class="chat-body chat-conversations landing_page">
		<ul class="chat-list">
        </ul>
	</div>
	<div class="chat-body chat-messages chatting_page d-none">
		<div class="all_chats"></div>
		<div class="d-flex message-response message-wrapper default_chat d-none" >
			<div class="message">
				<div class="snippet" data-title="dot-elastic">
					<div class="stage">
						<div class="dot-elastic"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="chat-footer chatting_page d-none">

		<form id="smartChatForm" enctype="multipart/form-data">
			@csrf
			<div class="row">
				<div class="col-md-12 form-group">
					<input type="hidden" name="conversation_id" id="conversation_id" value="">
					<input type="hidden" name="chat_id" id="chat_id" value="">
					<textarea class="w-100 form-control tinyMCE" id="prompt_input" placeholder="{{ __('Type a message') }}..."></textarea>
				</div>
				<div class="col-md-12">
					<div id="smart-chat-image-previews"></div>
				</div>
				<div class="col-md-9 form-group d-flex chat-footer-options">
					<div class = "chat_uploaded_images">

					</div>
					<a href="javascript:void(0)" data-ajax-popup="true" class="btn btn-sm bg-secondary ms-2 file_upload_modal_button" style="padding-top: 4px!important;" data-bs-toggle="modal" data-bs-target="#commonModal" data-url="" data-bs-whatever="{{ __('Attach File') }}" data-title="{{ __('Attach File') }}" data-size="lg" data-default_url="{{ route('chat.file_upload_modal', ['conversation_id'=>'conversation_id']) }}">
						<span class="text-white">
							<i class="fas fa-paperclip" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Attach File') }}"> {{ __('Attach File') }}</i>
						</span>
					</a>

					{{-- <a href="javascript:void(0)" data-ajax-popup="true" data-toggle="tooltip" data-size="md" data-url="{{ route('projects.edit_form', [$project->id, 'project_status']) }}" data-bs-toggle="modal" data-bs-target="#exampleModal"  data-bs-whatever="{{ __('Select Final Estimation') }}"
						data-title="{{ __('Select Final Estimation') }}" class="dropdown-item" data-bs-toggle="tooltip" >{{ $status->name }}</a>
						 --}}
					@php
						$ai_notification_templates = ai_notification_templates();
						$ai_models = ai_models();
					@endphp
                    @if(isset($ai_notification_templates) && count($ai_notification_templates) > 0)
						<select name="" class="form-control" id="smart_template_id">
							<option value="">{{ __('Select Prompt') }}</option>
							@foreach($ai_notification_templates as $template)
								<option value="{{ $template->id }}">{{ $template->name }}</option>
							@endforeach
						</select>
                    @endif
					@if(isset($ai_models) && count($ai_models) > 0)
						<select name="" class="form-control" id="ai_model_id">
							<option value="">{{ __('Select AI Model') }}</option>
							@foreach($ai_models as $ai_model)
								<option value="{{ $ai_model->id }}">{{ $ai_model->model_label }}</option>
							@endforeach
						</select>
                    @endif
				</div>
				<div class="col-md-3 btn-group">
					<button type="submit" class="btn btn-sm btn-primary chat-send">{{ __('Send') }}</button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="smart-chat-btn-wrapper">
    <button class="btn btn-primary" id="smart-chat-btn" ><i class="fa-regular fa-comment-dots"></i></button>
</div>
<script>
	var chat_interval = null;
	var is_second_time_call = 0;
</script>