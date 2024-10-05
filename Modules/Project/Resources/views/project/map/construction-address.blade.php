<div class="address-class">
    <span>{{ $detail->address_1 }}</span>
    @if (!empty($detail->zipcode))
        <span class="clas-zip">{{ $detail->zipcode }}</span>
        <span class="clas-city">{{ $detail->city ?? '' }}</span>
    @endif
    @if (!empty($detail->countryDetail->name))
        <span>{{ $detail->countryDetail->name }}</span>
    @endif
</div>
