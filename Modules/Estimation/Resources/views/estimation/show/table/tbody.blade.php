<template x-for="(item, index) in newItems" :key="item.id">
    <tbody>
        @include('estimation::estimation.show.table.prepend.add_group')
        @include('estimation::estimation.show.table.prepend.add_item')
        @include('estimation::estimation.show.table.prepend.add_comment')
    </tbody>
</template>
