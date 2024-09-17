<div class="col-xl-12">
    <div class="card">
        <div class="card-body table-border-style">
            <div class="row mb-2 additional_filters">
                <label class="pb-3">Additional Filters </label>
                <div class="col-sm-3 form-group">
                    <select name="country" class="form-control filter_select2"
                        data-placeholder="{{ __('Select Country') }}">
                        <option value="" data-iso="">{{ __('Select Country') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" data-iso="{{ $country->iso }}">
                                {{ __($country->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 form-group">
                    <select name="state" id="" class="form-control">
                        <option value="">{{ __('Select State') }}</option>
                        @foreach ($state as $state_row)
                            <option value="{{ $state_row }}"> {{ $state_row }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 form-group">
                    <select name="city" id="" class="form-control">
                        <option value="">{{ __('Select City') }}</option>
                        @foreach ($city as $city_row)
                            <option value="{{ $city_row }}"> {{ $city_row }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 form-group">
                    <select class="form-control" name="project_type">
                        <option value="not_archieve" selected="">{{ __('Not archive projects') }}</option>
                        <option value="archieve">{{ __('Archive Projects') }}</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="project_table_filter" id="project_table_filter" value="">
            <div class="table-responsive">
                <table class="table project-list" id="pc-dt-simple-project">
                    <thead>
                        <tr class="form-group">
                            <th></th>
                            <th></th>
                            <th>
                                <select name="filter_project_status" id="filter_project_status"
                                    class="form-control filter_select2 form_filter_field" multiple
                                    data-placeholder="{{ __('Status') }}">
                                    <option value="">{{ __('Status') }}</option>
                                    @if (isset($project_dropdown['project_status']))
                                        @foreach ($project_dropdown['project_status'] as $project_status)
                                            <option value="{{ $project_status->id }}"
                                                title="{{ $project_status->code }}">{{ $project_status->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </th>
                            <th style="" class="range_slider">
                                <input type="text" class="form-control form_filter_field"
                                    placeholder="{{ __('Name') }}" name="filter_name" id="filter_name">
                            </th>
                            <th><input type="text" class="form-control form_filter_field"
                                    placeholder="{{ __('Comments') }}" name="filter_comment" id="filter_comment">
                            </th>
                            <th>
                                <select name="filter_priority" id="filter_priority"
                                    class="form-control filter_select2 form_filter_field" multiple
                                    data-placeholder="{{ __('Priority') }}">
                                    <option value="">{{ __('Priority') }}</option>
                                    @if (isset($project_dropdown['priority']))
                                        @foreach ($project_dropdown['priority'] as $priority)
                                            <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </th>

                            <th>
                                <select name="filter_construction_type" id="filter_construction_type"
                                    class="form-control filter_select2 form_filter_field" multiple
                                    data-placeholder="{{ __('Construction') }}">
                                    <option value="">{{ __('Construction') }}</option>
                                    @if (isset($project_dropdown['construction_type']))
                                        @foreach ($project_dropdown['construction_type'] as $construction_types)
                                            <option value="{{ $construction_types->id }}"
                                                title="{{ $construction_types->code }}">
                                                {{ $construction_types->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </th>
                            <th class="range_slider">
                                <div class="price-input price_input">
                                    <div class="field">
                                        <input type="number" id="filter_price_from" class="input-min form_filter_field"
                                            value="0">
                                    </div>
                                    <div class="separator">-</div>
                                    <div class="field">
                                        <input type="number" id="filter_price_to" class="input-max form_filter_field"
                                            value="{{ $half_price }}">
                                    </div>
                                </div>
                                <div class="slider_filter price_slider">
                                    <div class="progress2"></div>
                                </div>
                                <div class="range-input price_range_input">
                                    <input type="range" class="range-min" min="0" max="{{ $projectmaxprice }}"
                                        value="0" step="10" onmouseup="set_filter_values()">
                                    <input type="range" class="range-max" min="0" max="{{ $projectmaxprice }}"
                                        value="{{ $half_price }}" onmouseup="set_filter_values()" step="10">
                                </div>
                            </th>
                            <th>
                                <input type='text' class="form-control daterange form_filter_field"
                                    placeholder="{{ __('Date') }}" id="filter_date" name="filter_date" />
                            </th>
                            <th>
                                <select name="filter_users" id="filter_users"
                                    class="form-control filter_select2 form_filter_field" multiple
                                    data-placeholder="{{ __('Users') }}">
                                    <option value="">{{ __('Users') }}</option>
                                    @foreach ($projectUser as $key => $p_user)
                                        <option value="{{ $p_user->id }}">{{ $p_user->name }}</option>
                                    @endforeach
                                </select>
                            </th>
                        </tr>
                        <tr>
                            <th scope="col" class="sort check_all" data-sort="">
                                <div class="form-check">
                                    <input type="checkbox" class="select_all_projects form-check-input"
                                        value="all">
                                </div>
                            </th>
                            <th scope="col" class="sort project-image" data-sort="image">{{ __('Image') }}
                            </th>
                            <th scope="col" class="sort project-status" data-sort="status">
                                {{ __('Status') }}</th>
                            <th scope="col" class="sort project-name" data-sort="name">{{ __('Name') }}
                            </th>
                            <th scope="col" class="sort project-comments" data-sort="comment">
                                {{ __('Comments') }}</th>
                            <th scope="col" class="sort project-priority" data-sort="priority">
                                {{ __('Priority') }}</th>
                            <th scope="col" class="sort project-construction-type" data-sort="construction_type">
                                {{ __('Construction') }}</th>
                            <th scope="col" class="sort project-budget" data-sort="budget">
                                {{ __('Project Net') }}</th>
                            <th scope="col" class="sort project-created-at" data-sort="created_at">
                                {{ __('Date') }}</th>
                            <th scope="col" class="text-right project-action">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
