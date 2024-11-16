<div class="tools-btn">

    <div class="search mb-10">
        <input type="search" placeholder="{{ __('Search') }}..." class="w-50 px-3" id="table-search" x-model="searchQuery">
    </div>

    <div class="d-flex gap-2 mb-2 mt-3 align-items-center">
        <button type="button" data-bs-toggle="tooltip" title="{{ __('Update POS Numbers') }}"
            x-on:click="updatePOS({{ $estimation->id }})">
            <i class="fa-solid fa-list-ol"></i>
        </button>

        {{-- <button class="reorder_group_btn" @click="reorderGroup" type="button" data-bs-toggle="tooltip"
                        title="{{ __('Reorder Group') }}">
                        <i class="fa-solid fa-list"></i>
                    </button> --}}

        @if (auth()->user()->type == 'company')
            {{-- @permission('estimation download option')
                            <div class="dropdown download-dropdown">
                                <div title="{{ __('Download') }}">
                                    <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                                        aria-haspopup="false" aria-expanded="false">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    <div class="dropdown-menu dash-h-dropdown">
                                        <div class="export-table-btn">
                                            <a href="{{ route('estimation.export.excel', ['id' => \Crypt::encrypt($estimation->id), 'type' => 'download']) }}"
                                                target="_blank" class="dropdown-item">
                                                <span>{{ __('Excel') }}</span>
                                            </a>

                                            <a href="{{ route('estimation.export.csv', ['id' => \Crypt::encrypt($estimation->id), 'type' => 'download']) }}"
                                                target="_blank" class="dropdown-item">
                                                <span>{{ __('CSV') }}</span>
                                            </a>

                                            <a href="{{ route('estimation.export.gaeb', ['id' => \Crypt::encrypt($estimation->id), 'type' => 'download']) }}"
                                                target="_blank" class="dropdown-item">
                                                <span>{{ __('GAEB') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endpermission --}}

            {{-- <div class="dropdown column-dropdown">
                            <div title="{{ __('Show / Hide Columns') }}">
                                <a class="dropdown-toggle" data-bs-toggle="dropdown" data-bs-placement="top"
                                    href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    <i class="fa-solid fa-table-columns"></i>
                                </a>
                                <div class="dropdown-menu dash-h-dropdown">
                                    <div class="title">
                                        {{ __('Show / Hide Columns') }}
                                    </div>
                                    <div class="table-col-customized">

                                        <label class="dropdown-item">
                                            <input type="checkbox" class="column-toggle" data-column="column_pos"
                                                checked>
                                            <span>{{ __('Pos') }}</span>
                                        </label>

                                        <label class="dropdown-item">
                                            <input type="checkbox" class="column-toggle" data-column="column_name"
                                                checked>
                                            <span>{{ __('Name') }}</span>
                                        </label>

                                        <label class="dropdown-item">
                                            <input type="checkbox" class="column-toggle"
                                                data-column="column_quantity" checked>
                                            <span>{{ __('Quantity') }}</span>
                                        </label>

                                        <label class="dropdown-item">
                                            <input type="checkbox" class="column-toggle" data-column="column_unit"
                                                checked>
                                            <span>{{ __('Unit') }}</span>
                                        </label>

                                        <label class="dropdown-item">
                                            <input type="checkbox" class="column-toggle"
                                                data-column="column_optional" checked>
                                            <span>{{ __('Opt') }}</span>
                                        </label>

                                        @if (isset($ai_description_field))
                                            <label class="dropdown-item">
                                                <input type="checkbox" class="column-toggle"
                                                    data-column="column_ai_description" checked>
                                                <span>{{ __('Auto Description') }}</span>
                                            </label>
                                        @endif

                                        @foreach ($allQuotes as $key => $quotes)
                                            @php
                                                $quote_title = isset($quotes->subContractor->name)
                                                    ? $quotes->subContractor->name
                                                    : $quotes->title;
                                            @endphp
                                            <label class="dropdown-item">
                                                <input type="checkbox" class="column-toggle" data-column="quote_th"
                                                    data-quote="{{ $quotes->id }}" checked>
                                                <span>{{ $quote_title }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div> --}}

            @permission('estimation add item option')
                <button type="button" @click="addItem('item')">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Item') }}
                </button>
            @endpermission

            @permission('estimation add group option')
                <button type="button" @click="addItem('group')">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Group') }}
                </button>
            @endpermission

            @permission('estimation add comment option')
                <button type="button" @click="addItem('comment')">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Comment') }}
                </button>
            @endpermission

            @permission('estimation remove option')
                <button type="button" @click="removeItem">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Remove') }}
                </button>
            @endpermission
        @endif
    </div>
</div>
