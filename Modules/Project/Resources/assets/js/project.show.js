$(document).on("click", ".status", function (event) {
    event.preventDefault();

    const projectID = $(this).attr('data-id');
    const statusID = $(this).attr('data-status');
    const statusName = $(this).text();

    $('.project-statusName').text(statusName);

    $.ajax({
        url: route('project.update', projectID),
        type: 'PUT',
        data: {
            ids: projectID,
            statusID: statusID,
            type: "changeStatus"
        },
        success: function (response) {
            toastrs('Success', response.success, 'success');
        }
    });
});

$(document).on('change', '#construction-select', function () {
    var selectedOption = $('#construction-select option:selected');
    var selectedType = selectedOption.data('type');
    var clientTypeInput = document.getElementById('client_type1');

    if (selectedType !== undefined && selectedType !== null) {
        clientTypeInput.value = selectedType;
    } else {
        clientTypeInput.value = 'new';
    }

    var url = route('users.get_user');
    var user_id = this.value;

    // Get the selected values
    if (user_id) {
        axios.post(url, {
            'user_id': user_id,
            'from': 'construction'
        }).then((response) => {

            var clientDetailsElement = document.getElementById('construction-details');

            $('#construction-details').html(response.data.html_data);
            $('#construction_detail_id').val(response.data.user_id);

            if ($('#construction_detail-company_notes').length > 0) {
                init_tiny_mce('#construction_detail-company_notes');
            }

            // Remove the d-none class if the element is found
            if (clientDetailsElement) {
                clientDetailsElement.classList.remove('d-none');
            }

            initGoogleMapPlaced('construction_detail-autocomplete', 'construction_detail');


            $(".country_select2").select2({
                placeholder: "Country",
                multiple: false,
                dropdownParent: $("#title_form"),
                placeholder: "Select an country",
                allowClear: true,
                dropdownAutoWidth: true,
            });
        })
    } else {
        var clientDetailsElement = document.getElementById('construction-details');
        // Remove the d-none class if the element is found
        if (clientDetailsElement) {
            clientDetailsElement.classList.add('d-none');
        }
    }
});

$(document).on('change', '#client-select', function () {
    var selectedOption = $('#client-select option:selected');
    var selectedType = selectedOption.data('type');
    var clientTypeInput = document.getElementById('client_type');

    if (selectedType !== undefined && selectedType !== null) {
        clientTypeInput.value = selectedType;
    } else {
        clientTypeInput.value = 'new';
    }
    var url;

    var url = route('users.get_user');

    init_tiny_mce('.client-company_notes');

    // Get the selected values
    if (this.value) {
        axios.post(url, {
            'user_id': this.value,
            'from': 'client'
        }).then((response) => {
            var clientDetailsElement = document.getElementById('client-details');

            $('#client-details').html(response.data.html_data);
            $('#client_id').val(response.data.user_id);
            // initialize();

            if ($('#client-company_notes').length > 0) {
                init_tiny_mce('#client-company_notes');
            }

            // Remove the d-none class if the element is found
            if (clientDetailsElement) {
                clientDetailsElement.classList.remove('d-none');
            }

            initGoogleMapPlaced('invoice-autocomplete', 'invoice');

        })
    } else {
        var clientDetailsElement = document.getElementById('client-details');
        // Remove the d-none class if the element is found
        if (clientDetailsElement) {
            clientDetailsElement.classList.add('d-none');
        }
    }
});

$(document).on('click', '.change-archive', function (event) {
    event.preventDefault();

    const id = $(this).data('id');
    const title = $(this).data('title');
    const text = $(this).data('text');
    const type = $(this).data('type');

    Swal.fire({
        title: title,
        text: text,
        showCancelButton: true,
        confirmButtonText: `Yes, ${type} it`,
        cancelButtonText: "No, cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route('project.update', 1),
                type: "PUT",
                data: { type: type, ids: [id] },
                success: function (response) {
                    console.log(response);
                    window.location.reload();
                }
            });

            Swal.fire({
                icon: 'success',
                title: `${type.charAt(0).toUpperCase() + type.slice(1)} Successful!`,
                html: `Project have been moved to ${type}`,
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        }
    });

});

$(document).on('click', '#copyProjectShareLinks', function () {
    const copyText = document.getElementById('copyText');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand('copy');

    this.textContent = 'Copied!';
    this.style.backgroundColor = '#6fd943';
    setTimeout(() => {
        this.textContent = 'Copy';
        this.style.backgroundColor = '';
    }, 2000);

    toastrs('success', 'Project\'s shared links has copied to clipboard', 'success');
});


function initGoogleMapPlaced(inputSelector, fieldInput) {
    const input = document.getElementById(inputSelector);
    const autocomplete = new google.maps.places.Autocomplete(input);

    autocomplete.addListener('place_changed', function () {
        let place = autocomplete.getPlace();

        if (place.geometry) {
            setGoogleMapsPlaced(place, fieldInput);
        }
    });

    function setGoogleMapsPlaced(place, selector = "") {
        let result = {};

        if (!place || !place.geometry || !place.geometry.location) return result;

        result['latitude'] = place.geometry.location.lat();
        result['longitude'] = place.geometry.location.lng();

        let street_number = '';

        place.address_components.forEach(component => {

            const componentType = component.types[0];

            switch (componentType) {
                case "street_number":
                    street_number = component.long_name;
                    break;
                case "route":
                    result['address_1'] = `${component.long_name}${street_number ? ', ' + street_number : ''}`;
                    break;
                case "locality":
                    result['city'] = component.long_name;
                    break;
                case "sublocality_level_1":
                    result['district_1'] = component.long_name;
                    break;
                case "administrative_area_level_3":
                    result['district_2'] = component.long_name;
                    break;
                case "administrative_area_level_1":
                    result['state'] = component.long_name;
                    break;
                case "postal_code":
                case "postal_code_suffix":
                    result['zip_code'] = component.long_name;
                    break;
                case "country":
                    result['country'] = component.short_name;
                    break;
                default:
                    result[componentType] = component.long_name;
                    break;
            }
        });

        if (selector !== '') {

            const addressFields = [
                'address_1',
                'address_2',
                'city',
                'district_1',
                'district_2',
                'state',
                'zip_code',
                'country'
            ];

            const setFieldValue = (key, value = '') => {

                const itemSelector = $(`#${selector}-${key}`);

                if (itemSelector.length) {
                    if (key === 'country') {
                        const selectedOption = itemSelector.find(`option[data-iso="${value}"]`);
                        if (selectedOption.length) {
                            selectedOption.prop('selected', true);
                            itemSelector.trigger('change');
                        }
                    } else {
                        itemSelector.val(value);
                    }
                }
            };

            addressFields.forEach(field => setFieldValue(field));
            Object.entries(result).forEach(([key, value]) => setFieldValue(key, value));
        }

        return result;
    }
}

