<tbody>
    @foreach ($estimation->estimationGroups ?? [] as $estimationGroups)
        @include('estimation::estimation.show.table.partial.item_group', [
            'estimationGroup' => $estimationGroups,
        ])

        @foreach ($estimationGroups->estimation_products ?? [] as $product)
            @includeWhen($product->type == 'item', 'estimation::estimation.show.table.partial.item_row', [
                'product' => $product,
            ])

            @includeWhen($product->type == 'comment', 'estimation::estimation.show.table.partial.item_comment')
        @endforeach
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
