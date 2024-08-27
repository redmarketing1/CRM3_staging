<li class="dd-item" data-id="{{ $group->id }}">
	<div class="dd-handle">{{ $group->group_name }}</div>
    @if ($group->children->isNotEmpty())
		<ol class="dd-list">
			@foreach ($group->children as $sub_group)
				@include('taskly::project_estimations.sub_groups', ['group' => $sub_group])
			@endforeach
		</ol>
    @endif
</li>