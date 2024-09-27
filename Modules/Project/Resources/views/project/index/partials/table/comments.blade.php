<ul class="project-comments">
    @foreach ($comments as $item)
        <li>{!! nl2br(strip_tags(html_entity_decode($item->comment), '<p>')) !!}</li>
    @endforeach
    <li class="description">{!! nl2br(strip_tags(html_entity_decode($description), '<p>')) !!}</li>
</ul>
