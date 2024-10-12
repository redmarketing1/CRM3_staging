<ul class="nav dash-item-tabs" id="project" role="tablist">
    @empty(!$tabItems)
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="tab-allprojects" href="#allprojects" role="tab" style="background-color:#eee;"
                data-bs-toggle="tab" data-bs-placement="top" title="all project">
                <i class="fa-solid fa-list"></i>
            </a>
        </li>
    @endempty

    @foreach ($tabItems as $item)
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="tab-{{ $item->tabID }}" href="#{{ $item->tabID }}" role="tab"
                data-bs-toggle="tab" data-bs-placement="top" title="{{ $item->name }}"
                style="background-color: {{ $item->background_color }}; color:{{ $item->font_color }}!important;">
                {{ $item->shortName }} <span>{{ $item->total }}</span>
            </a>
        </li>
    @endforeach
</ul>
