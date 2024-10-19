<div class="name-container">
    <a href="{{ $project->url() }}">
        <h2 class="data-name font-medium text-xl">{{ $project->name }}</h2>
    </a>
    @if (isset($project->statusData->name))
        <span class="data-project-status"
            style="background-color: {{ $project->statusData->background_color ?? '#ffffff' }};color: {{ $project->statusData->font_color ?? '#555555' }};">
            {{ $project->statusData->name }}
        </span>
    @endif
    <div class="d-flex data-sub-name flex-column font-normal">
        @if (isset($project->contactDetail->name))
            <span class="construction-client-name">
                <a href="{{ route('user', $project->contactDetail->id) }}" target="__blank" class="text-sm text-black">
                    {{ $project->contactDetail->name }}
                </a>
            </span>
        @endif
        @if (isset($project->contactDetail->address_1))
            <span class="text-sm text-black">
                {{ $project->contactDetail->address_1 }}
            </span>
        @endif
        @if (isset($project->contactDetail->mobile))
            <span class="text-sm text-black">
                {{ $project->contactDetail->mobile }}
            </span>
        @endif
        @if (isset($project->contactDetail->phone))
            <span class="text-sm text-black">
                {{ $project->contactDetail->phone }}
            </span>
        @endif
    </div>

</div>
