<div class="d-flex flex-column text-left">
    <a href="{{ $project->url() }}" target="__blank">
        <h2 class="data-name font-medium text-xl">{{ $project->name }}</h2>
    </a>
    <div class="d-flex data-sub-name flex-column font-normal">
        @if (isset($project->constructionDetail->user->name))
            <span class="construction-client-name">
                <a href="{{ route('user', $project->constructionDetail->user->id) }}" target="__blank"
                    class="text-sm text-black">
                    {{ $project->constructionDetail->user->name }}
                </a>
            </span>
        @endif
        @if (isset($project->constructionDetail->address_1))
            <span class="text-sm text-black">
                {{ $project->constructionDetail->address_1 }}
            </span>
        @endif
        @if (isset($project->constructionDetail->mobile))
            <span class="text-sm text-black">
                {{ $project->constructionDetail->mobile }}
            </span>
        @endif
        @if (isset($project->constructionDetail->phone))
            <span class="text-sm text-black">
                {{ $project->constructionDetail->phone }}
            </span>
        @endif
    </div>
</div>
