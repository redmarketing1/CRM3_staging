<tbody id="estimation-items">
    @foreach ($estimation->estimationGroups ?? [] as $estimationGroups)
        @include('estimation::estimation.show.table.partial.item_group', [
            'estimationGroup' => $estimationGroups,
        ])

        @foreach ($estimationGroups->estimation_products ?? [] as $product)
            @includeWhen($product->type == 'item', 'estimation::estimation.show.table.partial.item_row', [
                'product' => $product,
                'estimationGroup' => $estimationGroups,
            ])

            @includeWhen(
                $product->type == 'comment',
                'estimation::estimation.show.table.partial.item_comment',
                [
                    'estimationGroup' => $estimationGroups,
                ]
            )
        @endforeach
    @endforeach
</tbody>


<script type="text/template" id="add-item-template">
    @include('estimation::estimation.show.table.prepend.add_item')
</script>
<script type="text/template" id="add-group-template">
    @include('estimation::estimation.show.table.prepend.add_group')
</script>
<script type="text/template" id="add-comment-template">
    @include('estimation::estimation.show.table.prepend.add_comment')
</script>
