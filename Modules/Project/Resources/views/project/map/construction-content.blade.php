<div class="construction-detail">

    <div class="bold mb-4 text-left text-xl">
        <a target="__blank" href="{{ $project->url() }}">{{ $project->name }}</a>
    </div>

    {{-- Group 1: Company and Name --}}
    @if (!empty($detail->company_name) || !empty($detail->first_name) || !empty($detail->last_name))
        <div class="group separator">
            @if (!empty($detail->company_name))
                <span class="construction-company-name">{{ $detail->company_name }}</span>
            @endif
            @if (!empty($detail->first_name) || !empty($detail->last_name))
                <span class="construction-name">{{ $detail->first_name }} {{ $detail->last_name }}</span>
            @endif
        </div>
    @endif

    {{-- Group 2: Address --}}
    @if (!empty($detail->address_1))
        <div class="group separator">
            <span class="construction-address">
                {{ $detail->address_1 }}
                @if (!empty($detail->address_2))
                    , {{ $detail->address_2 }}
                @endif
            </span>
        </div>
    @endif

    {{-- Group 3: Email, Phone, Mobile --}}
    @if (!empty($detail->email) || !empty($detail->phone) || !empty($detail->mobile))
        <div class="group separator">
            @if (!empty($detail->email))
                <span class="construction-email">{{ $detail->email }}</span>
            @endif
            @if (!empty($detail->phone))
                <span class="construction-phone">{{ $detail->phone }}</span>
            @endif
            @if (!empty($detail->mobile))
                <span class="construction-mobile">{{ $detail->mobile }}</span>
            @endif
        </div>
    @endif

    {{-- Group 4: Tax Number, Website, Notes --}}
    @if (!empty($detail->tax_number) || !empty($detail->website) || !empty($detail->notes))
        <div class="group separator">
            @if (!empty($detail->tax_number))
                <span class="construction-tax-number">{{ $detail->tax_number }}</span>
            @endif
            @if (!empty($detail->website))
                <span class="construction-website">{{ $detail->website }}</span>
            @endif
            @if (!empty($detail->notes))
                <span class="construction-notes">{{ $detail->notes }}</span>
            @endif
        </div>
    @endif
</div>
