<tbody>
    @foreach ($estimation->estimationGroups ?? [] as $estimationGroups)
        @include('estimation::estimation.show.table.partial.row', [
            'estimationGroup' => $estimationGroups,
            'ai_description_field' => $ai_description_field,
            'allQuotes' => $allQuotes,
        ])
    @endforeach
</tbody>

<template x-for="(item, index) in items" :key="item.id">
    <tbody>
        <template x-if="item.type === 'item'">
            @include('estimation::estimation.show.table.prepend.add_item')
        </template>

        <template x-if="item.type === 'group'">
            @include('estimation::estimation.show.table.prepend.add_group')
        </template>

        <template x-if="item.type === 'comment'">
            @include('estimation::estimation.show.table.prepend.add_comment')
        </template>
    </tbody>
</template>
