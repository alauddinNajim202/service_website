@extends('backend.app')

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-1 (STATISTICS CARDS) -->
            <div class="row">
                <!-- Total Packages -->
                <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                    <div class="card overflow-hidden">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h3 class="mb-2 fw-semibold">{{ $packagesCount }}</h3>
                                    <p class="text-muted fs-13 mb-0">Total Packages</p>
                                </div>
                                <div class="col col-auto top-icn dash">
                                    <div class="counter-icon bg-primary dash ms-auto box-shadow-primary">
                                        <!-- Package/Box icon SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-white" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Categories -->
                <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                    <div class="card overflow-hidden">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h3 class="mb-2 fw-semibold">{{ $categoriesCount }}</h3>
                                    <p class="text-muted fs-13 mb-0">Total Categories</p>
                                </div>
                                <div class="col col-auto top-icn dash">
                                    <div class="counter-icon bg-secondary dash ms-auto box-shadow-secondary">
                                        <!-- Grid icon SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-white" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                    <div class="card overflow-hidden">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h3 class="mb-2 fw-semibold">{{ $usersCount }}</h3>
                                    <p class="text-muted fs-13 mb-0">Total Users</p>
                                </div>
                                <div class="col col-auto top-icn dash">
                                    <div class="counter-icon bg-info dash ms-auto box-shadow-info">
                                        <!-- Users icon SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-white" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.004.007-.026.022-.056l.086-.177a3 3 0 0 0 .19-.454c.05-.165.111-.383.168-.629a9 9 0 0 0 .161-1.011c.014-.153.023-.314.024-.482v-.004c0-.142-.008-.282-.022-.416.03-.01.063-.016.096-.016.3 0 .583.086.82.234.254.16.48.397.68.68.232.327.424.717.558 1.147.135.434.223.901.243 1.377h-.002a2 2 0 0 0 .015.199H7.022zM11 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0m3.1 1.5a1.5 1.5 0 1 0-3-0.003 1.5 1.5 0 0 0 3 .003m-9.223 0a1.5 1.5 0 1 0-3-0.003 1.5 1.5 0 0 0 3 .003M8 13c1.102 0 2.113-.302 2.871-.806C11.666 11.659 12 11.026 12 10c0-1.89-1.921-3.666-4.57-3.96a1.7 1.7 0 0 0-.86 0C3.922 6.334 2 8.11 2 10c0 1.026.333 1.66 1.129 2.194C3.887 12.698 4.898 13 6 13z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Creators -->
                <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                    <div class="card overflow-hidden">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h3 class="mb-2 fw-semibold">{{ $creatorsCount }}</h3>
                                    <p class="text-muted fs-13 mb-0">Total Creators</p>
                                </div>
                                <div class="col col-auto top-icn dash">
                                    <div class="counter-icon bg-warning dash ms-auto box-shadow-warning">
                                        <!-- Creator/Star icon SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-white" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ROW-1 END-->

            <!-- ROW-2 (CHARTS COLUMN 1) -->
            <div class="row">
                <!-- User Registration Trends -->
                <div class="col-lg-6 col-sm-12 col-md-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h3 class="card-title">User Registration Trends</h3>
                        </div>
                        <div class="card-body">
                            <div id="userRegistrationChart"></div>
                        </div>
                    </div>
                </div>

                <!-- Package Creation Trends -->
                <div class="col-lg-6 col-sm-12 col-md-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h3 class="card-title">Package Creation Trends</h3>
                        </div>
                        <div class="card-body">
                            <div id="packageCreationChart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ROW-2 END -->

            <!-- ROW-3 (CHARTS COLUMN 2) -->
            <div class="row">
                <!-- Category Distribution -->
                <div class="col-lg-6 col-sm-12 col-md-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h3 class="card-title">Category Distribution (Creators per Category)</h3>
                        </div>
                        <div class="card-body">
                            <div id="categoryDistributionChart"></div>
                        </div>
                    </div>
                </div>

                <!-- Creator Growth Overview -->
                <div class="col-lg-6 col-sm-12 col-md-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h3 class="card-title">Creator Growth Overview</h3>
                        </div>
                        <div class="card-body">
                            <div id="creatorGrowthChart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ROW-3 END -->

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.36.3/dist/apexcharts.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const months = @json($months);
        const userTrends = @json($userTrends);
        const packageTrends = @json($packageTrends);
        const creatorTrends = @json($creatorTrends);
        const categoryData = @json($categoryDistribution);

        // Grid lines helper style
        const gridColor = '#e7e7e7';

        // 1. User Registration Trends (Area Chart)
        const userOptions = {
            series: [{
                name: 'Users Registered',
                data: userTrends
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#0d6efd'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: months },
            grid: { borderColor: gridColor },
            tooltip: {
                theme: 'dark'
            }
        };
        new ApexCharts(document.querySelector("#userRegistrationChart"), userOptions).render();

        // 2. Package Creation Trends (Bar/Column Chart)
        const packageOptions = {
            series: [{
                name: 'Packages Created',
                data: packageTrends
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#6f42c1'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '45%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: { categories: months },
            grid: { borderColor: gridColor },
            tooltip: {
                theme: 'dark'
            }
        };
        new ApexCharts(document.querySelector("#packageCreationChart"), packageOptions).render();

        // 3. Category Distribution (Donut Chart)
        const categoryLabels = categoryData.map(item => item.name);
        const categoryCounts = categoryData.map(item => item.count);

        const finalLabels = categoryLabels.length > 0 ? categoryLabels : ['No Categories'];
        const finalCounts = categoryCounts.length > 0 ? categoryCounts : [0];

        const categoryOptions = {
            series: finalCounts,
            chart: {
                type: 'donut',
                height: 350,
                fontFamily: 'Inter, sans-serif'
            },
            labels: finalLabels,
            colors: ['#0dcaf0', '#fd7e14', '#198754', '#ffc107', '#20c997', '#d63384'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: true },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: { position: 'bottom' }
                }
            }],
            tooltip: {
                theme: 'dark'
            }
        };
        new ApexCharts(document.querySelector("#categoryDistributionChart"), categoryOptions).render();

        // 4. Creator Growth Overview (Line Chart: Cumulative and Monthly)
        let cumulativeCreatorTrends = [];
        let totalCreators = 0;
        for (let i = 0; i < creatorTrends.length; i++) {
            totalCreators += creatorTrends[i];
            cumulativeCreatorTrends.push(totalCreators);
        }

        const creatorOptions = {
            series: [{
                name: 'Total Creators (Cumulative)',
                data: cumulativeCreatorTrends
            }, {
                name: 'New Creators (Monthly)',
                data: creatorTrends
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#ffc107', '#fd7e14'],
            stroke: { width: [3, 2], curve: 'smooth', dashArray: [0, 4] },
            xaxis: { categories: months },
            grid: { borderColor: gridColor },
            legend: { position: 'top' },
            tooltip: {
                theme: 'dark'
            }
        };
        new ApexCharts(document.querySelector("#creatorGrowthChart"), creatorOptions).render();
    });
</script>
@endpush