<label for="construction_type" class="form-label">Construction Type</label>
<div class="select2-custom">
    <select name="construction_type" id="constructionType" data-labelType="construction_type"
        class="form-control filter_select2" multiple>
        @foreach ($projectLabel['construction_type'] ?? [] as $construction_type)
            @php
                $selected = isset($project->construction_type)
                    ? in_array($construction_type->id, explode(',', $project->construction_type))
                    : false;
            @endphp
            <option value="{{ $construction_type->id }}"
                data-background_color="{{ $construction_type->background_color }}"
                data-font_color="{{ $construction_type->font_color }}" {{ $selected ? 'selected' : '' }}>
                {{ $construction_type->name }}
            </option>
        @endforeach
    </select>
</div>
