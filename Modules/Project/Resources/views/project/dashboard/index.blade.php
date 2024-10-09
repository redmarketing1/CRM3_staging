@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="theme-avtar bg-primary">
                        <i class="fas fa-tasks bg-primary text-white"></i>
                    </div>
                    <p class="text-muted text-sm"></p>
                    <a href="{{ route('project.index') }}">
                        <h5 class="mt-4 mb-4 text-primary">{{ __('Total Project') }}</h5>
                    </a>
                    <h3 class="mb-0">{{ $totalProject }} <span class="text-success text-sm"></span></h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="theme-avtar bg-info">
                        <i class="fas fa-tag bg-info text-white"></i>
                    </div>
                    <p class="text-muted text-sm "></p>
                    <h5 class="mt-4 mb-4 text-info">{{ __('Total Task') }}</h5>
                    <h3 class="mb-0">{{ $totalTasks }} <span class="text-success text-sm"></span></h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="theme-avtar bg-danger">
                        <i class="fas fa-bug bg-danger text-white"></i>
                    </div>
                    <p class="text-muted text-sm"></p>
                    <h5 class="mt-4 mb-4 text-danger">{{ __('Total Bug') }}</h5>
                    <h3 class="mb-0">{{ $totalBugs }} <span class="text-success text-sm"></span></h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="theme-avtar bg-success">
                        <i class="fas fa-users bg-success text-white"></i>
                    </div>
                    <p class="text-muted text-sm"></p>
                    <a href="{{ route('users.index') }}">
                        <h5 class="mt-4 mb-4 text-success">{{ __('Total Members') }}</h5>
                    </a>
                    <h3 class="mb-0">{{ $totalMembers }} <span class="text-success text-sm"></span></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-md-12">
            @include('project::project.dashboard.card.task')
        </div>
        <div class="col-xl-6 col-md-12">
            @include('project::project.dashboard.card.tasks_overview')
            @include('project::project.dashboard.card.project_status')
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        (function() {
            var options = {
                chart: {
                    height: 170,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: {!! json_encode($processData['percentage']) !!},
                colors: ['#FF3A6E', '#6fd943', '#ffa21d'],
                labels: {!! json_encode($processData['label']) !!},
                grid: {
                    borderColor: '#e7e7e7',
                    row: {
                        colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                        opacity: 0.5
                    },
                },
                markers: {
                    size: 1
                },
                legend: {
                    show: false
                }
            };
            var chart = new ApexCharts(document.querySelector("#projects-chart"), options);
            chart.render();
        })();
    </script>
    <script>
        (function() {
            var options = {
                chart: {
                    height: 150,
                    type: 'line',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [
                    @foreach ($chartData['stages'] as $id => $name)
                        {
                            name: "{{ __($name) }}",
                            data: {!! json_encode($chartData[$id]) !!}
                        },
                    @endforeach
                ],
                xaxis: {
                    categories: {!! json_encode($chartData['label']) !!},
                },
                colors: {!! json_encode($chartData['color']) !!},

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                yaxis: {
                    tickAmount: 5,
                    min: 1,
                    max: 40,
                },
            };
            var chart = new ApexCharts(document.querySelector("#task-area-chart"), options);
            chart.render();
        })();
    </script>
@endpush
