<label for="status_label" class="form-label">Project Label</label>
<select name="status_label" class="form-control filter_select2" onchange="store_to_project_data('label', this)" multiple>
    <option value="">Select</option>
    @foreach ($projectLabel['project_label'] ?? [] as $status_label)
        @php
            $selected = isset($project->label) ? in_array($status_label->id, explode(',', $project->label)) : false;
        @endphp
        <option value="{{ $status_label->id }}" data-background_color="{{ $status_label->background_color }}"
            data-font_color="{{ $status_label->font_color ?? '#fff' }}" {{ $selected ? 'selected' : '' }}>
            {{ $status_label->name }}
        </option>
    @endforeach
</select>
