<label for="priority" class="form-label">{{ __('Priority Type') }}</label>
<select name="priority" id="priorityType" data-labelType="priority" class="form-control filter_select2" multiple>
    @foreach ($projectLabel['priority'] ?? [] as $priority)
        @php
            $selected = isset($project->priority) ? in_array($priority->id, explode(',', $project->priority)) : false;
        @endphp
        <option value="{{ $priority->id }}" data-background_color="{{ $priority->background_color }}"
            data-font_color="{{ $priority->font_color ?? '#fff' }}" {{ $selected ? 'selected' : '' }}>
            {{ $priority->name }}
        </option>
    @endforeach
</select>
