<label for="property" class="form-label">Property Type</label>
<div class="select2-custom">
    <select name="property" class="form-control filter_select2" onchange="store_to_project_data('property_type', this)"
        multiple>
        <option value="" disabled>Select</option>
        @foreach ($projectLabel['property'] ?? [] as $property)
            @php
                $selected = isset($project->property_type)
                    ? in_array($property->id, explode(',', $project->property_type))
                    : false;
            @endphp
            <option value="{{ $property->id }}" data-background_color="{{ $property->background_color }}"
                data-font_color="{{ $property->font_color ?? '' }}" {{ $selected ? 'selected' : '' }}>
                {{ $property->name }}
            </option>
        @endforeach
    </select>
</div>
