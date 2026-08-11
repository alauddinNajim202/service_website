@php
$url = 'admin.cms.'.$page.'.'.$section;
@endphp

@extends('backend.app', ['title' => $page . ' - ' . $section])

@push('styles')
<link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
@endpush


@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">


            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">CMS : {{ ucwords(str_replace('_', ' ', $page ?? '')) }} Page {{ ucwords(str_replace('_', ' ', $section ?? '')) }} Section.</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <button onclick="window.location.href=`{{ route($url . '.display') }}`" class="btn me-2 {{ isset($data->is_display) && $data->is_display == 0 ? 'btn-danger' : 'btn-primary' }}">Display</button>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-4 -->
            <div class="row">

                <div class="{{ isset($sections) && $sections ? 'col-md-5' : 'col-md-12' }}">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between border-bottom">
                            <h3 class="card-title">Content Edit</h3>
                            <!-- Add New Page Button -->
                            @if($data)
                            <a href="{{route($url.'.show', $data->id)}}" class="btn btn-primary">
                                <i class="bx bx-plus me-sm-1 "></i> Show Section
                            </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <form class="form form-horizontal" method="POST" action="{{ route($url.'.content') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                    <div class="col-md-12 mb-4">
                                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="pills-en-tab" data-bs-toggle="pill" data-bs-target="#pills-en" type="button" role="tab" aria-controls="pills-en" aria-selected="true">English</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="pills-fr-tab" data-bs-toggle="pill" data-bs-target="#pills-fr" type="button" role="tab" aria-controls="pills-fr" aria-selected="false">Français</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="pills-es-tab" data-bs-toggle="pill" data-bs-target="#pills-es" type="button" role="tab" aria-controls="pills-es" aria-selected="false">Español</button>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="pills-tabContent">
                                            {{-- English Tab --}}
                                            <div class="tab-pane fade show active" id="pills-en" role="tabpanel" aria-labelledby="pills-en-tab">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <x-form.text name="title_en" label="Title (EN)" placeholder="Enter title in English" :value="$data->title_en ?? ($data->title ?? '')" />
                                                    </div>
                                                    <div class="col-md-12 mt-3">
                                                        <x-form.textarea name="description_en" label="Description (EN)" placeholder="Enter description in English" :value="$data->description_en ?? ($data->description ?? '')" />
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- French Tab --}}
                                            <div class="tab-pane fade" id="pills-fr" role="tabpanel" aria-labelledby="pills-fr-tab">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <x-form.text name="title_fr" label="Title (FR)" placeholder="Enter title in French" :value="$data->title_fr ?? ''" />
                                                    </div>
                                                    <div class="col-md-12 mt-3">
                                                        <x-form.textarea name="description_fr" label="Description (FR)" placeholder="Enter description in French" :value="$data->description_fr ?? ''" />
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Spanish Tab --}}
                                            <div class="tab-pane fade" id="pills-es" role="tabpanel" aria-labelledby="pills-es-tab">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <x-form.text name="title_es" label="Title (ES)" placeholder="Enter title in Spanish" :value="$data->title_es ?? ''" />
                                                    </div>
                                                    <div class="col-md-12 mt-3">
                                                        <x-form.textarea name="description_es" label="Description (ES)" placeholder="Enter description in Spanish" :value="$data->description_es ?? ''" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <div class="row {{ in_array('sub_description', $component) ? '' : 'd-none' }}">
                                    <div class="col-md-12">
                                        <x-form.textarea name="sub_description" label="Sub Description" placeholder="Enter here sub description" :value="$data->sub_description ?? ''" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4  {{ in_array('btn_text', $component) ? '' : 'd-none' }}">
                                        <x-form.text name="btn_text" label="Button Text" placeholder="Enter here button text" :value="$data->btn_text ?? ''" />
                                    </div>
                                    <div class="col-md-4  {{ in_array('btn_link', $component) ? '' : 'd-none' }}">
                                        <x-form.text name="btn_link" label="Button Link" placeholder="Enter here button link" :value="$data->btn_link ?? ''" />
                                    </div>
                                    <div class="col-md-4  {{ in_array('btn_color', $component) ? '' : 'd-none' }}">
                                        <x-form.colour name="btn_color" label="Button Color" placeholder="Enter here button color" :value="$data->btn_color ?? ''" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 {{ in_array('image', $component) ? '' : 'd-none' }}">
                                        <x-form.file name="image" label="Image" :file="$data->image ?? ''">
                                            <p class="textTransform">Image Size Less than 5MB and Image Type must be jpeg,jpg,png.</p>
                                        </x-form.file>
                                    </div>
                                    <div class="col-md-6 {{ in_array('bg', $component) ? '' : 'd-none' }}">
                                        <x-form.file name="bg" label="Background Image" :file="$data->bg ?? ''">
                                            <p class="textTransform">Image Size Less than 5MB and Image Type must be jpeg,jpg,png.</p>
                                        </x-form.file>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12 text-center">
                                        <button class="submit btn btn-primary" type="submit">Submit</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>


                <div class="{{ isset($sections) && $sections ? 'col-md-7' : 'd-none' }}">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between border-bottom">
                            <h3 class="card-title">All {{ ucwords(str_replace('_', ' ', $page ?? '')) }} {{ ucwords(str_replace('_', ' ', $section ?? '')) }} Items</h3>
                            <!-- Add New Page Button -->
                            @if(isset($sections) && $sections)
                            <a href="{{route($url.'.create')}}" class="btn btn-primary">
                                <i class="bx bx-plus me-sm-1 "></i> Add New
                            </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                    <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">#</th>
                                            <th class="wd-15p border-bottom-0">Title</th>
                                            <th class="wd-15p border-bottom-0">Image</th>
                                            <th class="wd-20p border-bottom-0">Status</th>
                                            <th class="wd-15p border-bottom-0">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- dynamic data --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ROW-4 END -->

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
@if(isset($sections) && $sections)
<script>
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            }
        });
        if (!$.fn.DataTable.isDataTable('#datatable')) {
            let dTable = $('#datatable').DataTable({
                order: [],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                processing: true,
                responsive: true,
                serverSide: true,

                language: {
                    processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                        </div>`
                },

                scroller: {
                    loadingIndicator: false
                },
                pagingType: "full_numbers",
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                ajax: {
                    url: "{{ route($url.'.index') }}",
                    type: "GET",
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title',
                        name: 'title',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: true,
                        searchable: true,
                        className: 'dt-center text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        className: 'dt-center text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'dt-center text-center'
                    },
                ],
            });

            dTable.buttons().container().appendTo('#file_exports');
            new DataTable('#example', {
                responsive: true
            });
        }
    });

    // Status Change Confirm Alert
    function showStatusChangeAlert(id) {
        event.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to update the status?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                statusChange(id);
            }
        });
    }

    // Status Change
    function statusChange(id) {
        let url = "{{ route($url.'.status', ':id') }}";
        $.ajax({
            type: "GET",
            url: url.replace(':id', id),
            success: function(resp) {
                console.log(resp);
                // Reloade DataTable
                $('#datatable').DataTable().ajax.reload();
                if (resp.success === true) {
                    // show toast message
                    toastr.success(resp.message);
                } else if (resp.errors) {
                    toastr.error(resp.errors[0]);
                } else {
                    toastr.error(resp.message);
                }
            },
            error: function(error) {
                // location.reload();
            }
        });
    }

    // delete Confirm
    function showDeleteConfirm(id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure you want to delete this record?',
            text: 'If you delete this, it will be gone forever.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                deleteItem(id);
            }
        });
    }

    // Delete Button
    function deleteItem(id) {
        let url = "{{ route($url.'.destroy', ':id') }}";
        let csrfToken = '{{ csrf_token() }}';
        $.ajax({
            type: "DELETE",
            url: url.replace(':id', id),
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(resp) {
                $('#datatable').DataTable().ajax.reload();
                if (resp['t-success']) {
                    toastr.success(resp.message);
                } else {
                    toastr.error(resp.message);
                }
            },
            error: function(error) {
                toastr.error('An error occurred. Please try again.');
            }
        });
    }

    function editItem(id) {
        event.preventDefault();
        let url = "{{ route($url.'.edit', ':id') }}";
        window.location.href = url.replace(':id', id);
    }

    function goToShow(id) {
        event.preventDefault();
        let url = "{{ route($url.'.show', ':id') }}";
        window.location.href = url.replace(':id', id);
    }
</script>
@endif
@endpush