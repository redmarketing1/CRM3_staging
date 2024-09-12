<div class="page-header">
    <div class="page-block">
        <div class="row gap-4 align-items-center justify-content-between">
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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
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
