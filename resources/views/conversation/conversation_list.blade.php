<li class="chat-item" data-conversation_id="{{ \Crypt::encrypt(0) }}">
	<img src="https://via.placeholder.com/40" alt="Avatar" class="chat-avatar">
	<div class="chat-details">
		<p class="chat-name">{{ __('Create New Conversation') }}</p>
	</div>
</li>
@if(isset($conversations) && count($conversations) > 0)
	@foreach($conversations as $conversation)
		<li class="chat-item" data-conversation_id="{{ \Crypt::encrypt($conversation->id) }}">
			<img src="https://via.placeholder.com/40" alt="Avatar" class="chat-avatar">
			<div class="row chat-details d-flex">
				<div class="col-md-11">
					<div class="conversation_name_last_msg">
						<p class="chat-name">{{ isset($conversation->name) ? $conversation->name : 'Chat '.$conversation->id  }}</p>
						@if(isset($conversation->last_chat_msg->id))
							@if($conversation->last_chat_msg->type == 0)
								<p class="chat-last-message">{!! mb_strimwidth($conversation->last_chat_msg->message, 0, 30, "...") !!}</p>
							@else
								<p class="chat-last-message">{{ mb_strimwidth($conversation->last_chat_msg->message, 0, 30, "...") }}</p>
							@endif
						@endif
					</div>
					<div class="conversation_rename d-none">
						<input type="text" class="form-control input_rename" name="" id="" value="{{ isset($conversation->name) ? $conversation->name : ''  }}" placeholder="{{ __('Enter Conversation Name') }}">
						<button type="button" class="btn btn-danger btn-sm btn_rename_cancel">{{ __('Cancel') }}</button>
						<button type="button" class="btn btn-primary btn-sm btn_rename_save">{{ __('Save') }}</button>
					</div>
				</div>
				<div class="col-md-1 chat-item-actions">
					<i class="fa fa-ellipsis-v float-end" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false"></i>
					<ul class="dropdown-menu quote_options" aria-labelledby="dropdownMenuButton">
						<li class="btn_conversation_rename">
							<a class="dropdown-item" href="javascript:void(0)">{{ __('Rename') }}</a>
						</li>
						<li class="btn_conversation_delete">
							<a class="dropdown-item" href="javascript:void(0)"></i>{{ __('Delete') }}</a>
						</li>
					</ul>
				</div>
			</div>
		</li>
	@endforeach
@endif