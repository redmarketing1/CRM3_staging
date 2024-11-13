@include('estimation::estimation.show.table.partial.group_row')
@include('estimation::estimation.show.table.partial.item_row')


{{-- @foreach ($estimationGroup->children_data as $child)
    @include('taskly::project_estimations.partial_setup', [
        'estimationGroup' => $child,
        'ai_description_field' => $ai_description_field,
        'allQuotes' => $allQuotes,
        'nested' => 0,
    ])
@endforeach --}}
