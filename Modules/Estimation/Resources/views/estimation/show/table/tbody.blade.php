<tbody>
    @foreach ($estimation_groups as $estimation_group)
        @include('estimation::estimation.show.table.partial.row', [
            'estimation_group' => $estimation_group,
            'ai_description_field' => $ai_description_field,
            'allQuotes' => $allQuotes,
        ])
    @endforeach
</tbody>
