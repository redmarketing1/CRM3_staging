<tbody>
    @foreach ($estimation->estimationGroups ?? [] as $estimationGroups)
        @include('estimation::estimation.show.table.partial.row', [
            'estimationGroup' => $estimationGroups,
            'ai_description_field' => $ai_description_field,
            'allQuotes' => $allQuotes,
        ])
    @endforeach

    <template x-for="(item, index) in items" :key="index">
        @include('estimation::estimation.show.table.prepend.add_item')
    </template>
</tbody>
