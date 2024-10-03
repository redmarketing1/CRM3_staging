<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRTLVersion }}">

<head>
    {!! Meta::toHtml() !!}
    @stack('css')
    @stack('availabilitylink')
    @routes
</head>

<body class="{{ $bodyClasses }}" data-theme-color="{{ $themeColorCode }}" style="{{ $style }}">
 
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill">
            </div>
        </div>
    </div>
    @include('partials.sidebar')
    @include('partials.header')
    <section class="dash-container">
        <div class="dash-content">
            @include('partials.breadcrumb')
            @yield('content')
        </div>
    </section>
    @permission('chatbot manage')
        @include('conversation.index')
    @endpermission
</body>
@include('partials.footer')
