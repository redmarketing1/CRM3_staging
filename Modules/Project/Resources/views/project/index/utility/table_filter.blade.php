<div class="filter-wrapper hide">
    {{-- <div class="d-flex">
        <select name="country" class="form-control" id="country-filter" data-placeholder="{{ __('Select Country') }}">
            <option value="" data-iso="">{{ __('Select Country') }}</option>
            @foreach ($countries as $country)
                <option value="{{ $country->id }}" data-iso="{{ $country->iso }}">
                    {{ __($country->name) }}</option>
            @endforeach
        </select>

        <select name="state" id="state-filter" class="form-control">
            <option value="">{{ __('Select State') }}</option>
            @foreach ($state as $state_row)
                <option value="{{ $state_row }}"> {{ $state_row }}</option>
            @endforeach
        </select>

        <select name="city" id="city-filter" class="form-control">
            <option value="">{{ __('Select City') }}</option>
            @foreach ($city as $city_row)
                <option value="{{ $city_row }}"> {{ $city_row }}</option>
            @endforeach
        </select>

        <select id="archive-filter">
            <option value="">All Projects</option>
            <option value="Not Archived">Not Archived</option>
        </select>
    </div> --}}
    <div class="search-table">
        <div class="form-group mb-5 w-50">
            <label for="search_project_name" class="form-label text-2xl mb-1">Search Project</label>
            <input type="search" class="form-control text-xl" placeholder="Search project name" name="search_project_name"
                id="search_project_name" />
        </div>
    </div>
</div>
