<div class="row">
	<div class="cf nestable-lists">
		<div class="dd" id="nestable">
			<ol class="dd-list">
				@foreach ($groups as $group)
					@include('taskly::project_estimations.sub_groups', ['group' => $group])
				@endforeach
			</ol>
		</div>
	</div>
</div>