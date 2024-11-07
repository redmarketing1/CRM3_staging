<tbody>
    @foreach ($estimation->estimationGroups ?? [] as $estimationGroups)
        @include('estimation::estimation.show.table.partial.row', [
            'estimationGroup' => $estimationGroups,
            'ai_description_field' => $ai_description_field,
            'allQuotes' => $allQuotes,
        ])
    @endforeach
</tbody>
