<div class="card">
    <div class="card-header ">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('Contact') }}</h5>
            </div>
        </div>
    </div>
    <div class="card-body top-10-scroll project_all_address">
        <div class="address-box">
            @empty(!$project->contactDetail)
                <div class="construction_detail_address">
                    <div class="personal-detail-class font-semibold">
                        <span>{{ $project->contactDetail->name }}</span>
                        <span>{{ $project->contactDetail->email }}</span>
                    </div>
                    <div class="address-class">
                        <span class="address_1">{{ $project->contactDetail->address_1 }}</span>
                        <span class="address_2">{{ $project->contactDetail->address_2 }}</span>
                        <span class="zip_city">{{ $project->contactDetail->zip_code }}</span>
                        <span class="state">{{ $project->contactDetail->state }}</span>
                        <span class="country_name">{{ $project->contactDetail->countryDetail->name }}</span>
                    </div>
                </div>
            @endempty

            @if (!$project->clientData && $project->is_same_invoice_address === 0)
                <div class="d-flex invoice_address2 ">
                    <div class="client_invoice_address">
                        <div class="personal-detail-class font-semibold">
                            <span>{{ $project->clientData->name }}</span>
                            <span>{{ $project->clientData->email }}</span>
                        </div>
                        <div class="address-class">
                            <span class="address_1">{{ $project->clientData->address_1 }}</span>
                            <span class="address_2">{{ $project->clientData->address_2 }}</span>
                            <span class="zip_city">{{ $project->clientData->zip_code }}</span>
                            <span class="state">{{ $project->clientData->state }}</span>
                            <span class="country_name">{{ $project->clientData->countryDetail->name }}</span>
                        </div>
                    </div>
                </div>
            @endempty
    </div>
</div>
</div>
