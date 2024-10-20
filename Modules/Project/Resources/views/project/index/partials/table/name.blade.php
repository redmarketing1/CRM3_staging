<div class="name-container">
    <a href="{{ $project->url() }}">
        <h2 class="data-name font-medium text-xl">{{ $project->name }}</h2>
    </a>
    @if (isset($project->statusData->name))
        <div class="data-project-status xlabel"
            style="background-color: {{ $project->statusData->background_color ?? '#ffffff' }};color: {{ $project->statusData->font_color ?? '#555555' }}">
            {{ $project->statusData->name }}
        </div>
    @endif
    <div class="data-sub-name">
        @if (isset($project->contactDetail->name))
            <div class="construction-client-name">
                <a href="{{ route('user', $project->contactDetail->id) }}" target="__blank" class="text-sm text-black">
                    {{ $project->contactDetail->name }}
                </a>
            </div>
        @endif
        @if (isset($project->contactDetail->address_1))
            <div class="text-sm text-black">
                {{ $project->contactDetail->address_1 }}
            </div>
        @endif
        @if (isset($project->contactDetail->mobile))
            <div class="text-sm text-black">
                {{ $project->contactDetail->mobile }}
            </div>
        @endif
        @if (isset($project->contactDetail->phone))
            <div class="text-sm text-black">
                {{ $project->contactDetail->phone }}
            </div>
        @endif
    </div>

</div>
