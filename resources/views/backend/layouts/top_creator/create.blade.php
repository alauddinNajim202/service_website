@extends('backend.app', ['title' => 'Add Top Creator'])

@push('styles')
<style>
    /* Fix for Select2 dropdown text color in dark theme */
    .select2-container--default .select2-results__option {
        color: #333; /* Make text dark so it's readable on the white dropdown background */
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #ddd;
        color: #333;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #CFA267;
        color: white;
    }
</style>
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
                    <h1 class="page-title">Add Top Creator</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.top-creator.index') }}">Top Creators</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW -->
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h3 class="card-title">Select a Creator</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.top-creator.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="creator_id" class="form-label">Creator <span class="text-danger">*</span></label>
                                            <select name="creator_id" id="creator_id" class="form-control form-select select2 @error('creator_id') is-invalid @enderror" required>
                                                <option value="" disabled selected>Select a Creator</option>
                                                @foreach($creators as $creator)
                                                    <option value="{{ $creator->id }}">{{ $creator->name }} ({{ $creator->email }})</option>
                                                @endforeach
                                            </select>
                                            @error('creator_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            @if($creators->isEmpty())
                                                <span class="text-warning mt-2 d-block"><i class="fa fa-info-circle"></i> No available creators found. Either there are no users with the 'creator' role, or they are all already marked as Top Creators.</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <button class="btn btn-primary" type="submit" @if($creators->isEmpty()) disabled @endif>Save</button>
                                    <a href="{{ route('admin.top-creator.index') }}" class="btn btn-danger">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ROW END -->

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#creator_id').select2({
            placeholder: "Select a Creator",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
