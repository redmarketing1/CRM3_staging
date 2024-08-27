@php
    $admin_settings = getAdminAllSetting();

    $company_settings = getCompanyAllSetting(creatorId());

    $color = !empty($company_settings['color']) ? $company_settings['color'] : 'theme-1';
	if (isset($company_settings['color_flag']) && $company_settings['color_flag'] == 'true') {
		$themeColor = 'custom-color';
	} else {
		$themeColor = $color;
	}
	$currantLang	= Auth::user()->lang;
	$datatable_language_path = "https://cdn.datatables.net/plug-ins/2.0.2/i18n/".$currantLang.".json";

	$body_class_array = array();
	$current_controller_name 	= current_controller();
	$current_method_name 		= current_method();
	$ai_notification_templates  = ai_notification_templates();
	$ai_models  				= ai_models();

	if ($current_controller_name != '') {
		$body_class_array[] = $current_controller_name;
	}
	if ($current_method_name != '') {
		$body_class_array[] = $current_method_name;
	}
	if ($currantLang != '') {
		$body_class_array[] = $currantLang;
	}

	$show_sidebar = true;
	if ($current_controller_name == 'project' && $current_method_name == 'project_progress') {
		$show_sidebar = false;
		$body_class_array[] = "no-sidebar";
	}
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ isset($company_settings['site_rtl']) && $company_settings['site_rtl'] == 'on' ? 'rtl' : '' }}">
<html lang="en">

@include('partials.head')
<body class="{{ isset($themeColor) ? $themeColor : 'theme-1' }} {{ implode(' ', $body_class_array) }}">
	<script type="text/javascript">
		var datatable_language_path = "{{ $datatable_language_path }}";
	</script>
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill">

            </div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ auth-signup ] end -->
    @include('partials.sidebar')
    @include('partials.header')
    <section class="dash-container">
        <div class="dash-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto">
                            <div class="page-header-title">
                                <h4 class="m-b-10">@yield('page-title')</h4>
                            </div>
                            <ul class="breadcrumb">
                                @php
                                    if (isset(app()->view->getSections()['page-breadcrumb'])) {
                                        $breadcrumb = explode(',', app()->view->getSections()['page-breadcrumb']);
                                    } else {
                                        $breadcrumb = [];
                                    }
                                @endphp
                                @if (!empty($breadcrumb))
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    @foreach ($breadcrumb as $item)
                                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                            {!! $item !!}</li>
                                    @endforeach
                                @endif

                            </ul>
                        </div>
                        <div class="col-auto row">
                            @yield('page-action')
                        </div>
                    </div>
                </div>
            </div>
            @yield('content')
        </div>
    </section>
@permission('chatbot manage')
	@include('conversation.index')
@endpermission
@include('partials.footer')
