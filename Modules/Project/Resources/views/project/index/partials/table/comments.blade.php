<ul class="project-comments">
    @foreach ($comments as $item)
        <li>{{ strip_tags($item->comment) }}</li>
    @endforeach
    <li class="description">{{ strip_tags($description) }}</li>
</ul>
