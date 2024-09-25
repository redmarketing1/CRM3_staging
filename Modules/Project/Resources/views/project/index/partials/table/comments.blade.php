<ul class="project-comments">
    @foreach ($comments as $item)
        <li>{{ strip_tags(html_entity_decode($item->comment)) }}</li>
    @endforeach
    <li class="description">{{ strip_tags(html_entity_decode($description)) }}</li>
</ul>
