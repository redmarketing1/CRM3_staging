<ul class="project-comments">
    @foreach ($comments as $item)
        <li>{{ $item->comment }}</li>
    @endforeach
    <li class="description">{{ $description }}</li>
</ul>
