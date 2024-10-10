<div class="d-flex flex-column text-left">
    <a href="{{ $project->url() }}">
        <h2 class="data-name font-medium text-xl">{{ $project->name }}</h2>
    </a>
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
