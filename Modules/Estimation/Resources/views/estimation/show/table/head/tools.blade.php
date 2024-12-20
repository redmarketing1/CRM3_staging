<div class="tools-btn m-b-10">

    <div class="d-inline-block mb-10 mr-2 search">
        <input type="search" placeholder="{{ __('Search') }}..." class="px-3" id="table-search">
    </div>

    <div class="d-inline-flex gap-2 heading-option-button m-l-10">

        @permission('estimation download option')
            <div class="button dropdown download-dropdown">
                <div title="{{ __('Download') }}">
                    <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false"
                        aria-expanded="false">
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
        @endpermission

        <div class="button dropdown column-dropdown">
            <div title="{{ __('Show / Hide Columns') }}">
                <a class="dropdown-toggle" data-bs-toggle="dropdown" data-bs-placement="top" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="fa-solid fa-table-columns"></i>
                </a>
                <div class="dropdown-menu dash-h-dropdown">
                    <div class="title">
                        {{ __('Show / Hide Columns') }}
                    </div>
                    <div class="table-col-customized">

                        {{-- <label class="dropdown-item">
                                <input type="checkbox" class="columnToggle" id="columnPos" data-column="pos" checked>
                                <span>{{ __('Pos') }}</span>
                            </label>

                            <label class="dropdown-item">
                                <input type="checkbox" class="columnToggle" id="columnName" data-column="name" checked>
                                <span>{{ __('Name') }}</span>
                            </label>

                            <label class="dropdown-item">
                                <input type="checkbox" class="columnToggle" id="columnQuantity" data-column="quantity"
                                    checked>
                                <span>{{ __('Quantity') }}</span>
                            </label>

                            <label class="dropdown-item">
                                <input type="checkbox" class="columnToggle" id="columnUnit" data-column="unit" checked>
                                <span>{{ __('Unit') }}</span>
                            </label>

                            <label class="dropdown-item">
                                <input type="checkbox" class="columnToggle" id="columnOptional" data-column="optional"
                                    checked>
                                <span>{{ __('Opt') }}</span>
                            </label> --}}

                        @if (isset($ai_description_field))
                            <label class="dropdown-item">
                                <input type="checkbox" class="columnToggle" id="columnOptional"
                                    data-column="column_ai_description" checked>
                                <span>{{ __('Auto Description') }}</span>
                            </label>
                        @endif

                        @foreach ($allQuotes as $quote)
                            <label class="dropdown-item">
                                <input type="checkbox" class="columnToggle" id="columnQuote" data-column="quote"
                                    data-quoteID="{{ $quote->id }}" checked>
                                <span>{{ $quote->subContractor->name ?? $quote->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="button" id="toggleFullScreen">
            <i class="fa-solid fa-expand"></i>
        </div>
    </div>
</div>
