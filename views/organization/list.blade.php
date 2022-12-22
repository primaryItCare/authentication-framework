@extends('layouts.app')
@section('title','Organization List')
@section('content')
<div class="row img-search-wrapper">
    <div class="col">
        <div class="left-wrap-inner">
            <div class="form-group">
                <input type="text" class="form-control search" placeholder="Search Group" id="groupSearch">
            </div>
            <div class="new-group-wrap">
                <button type="button" class="btn green-bg-btn create-group-btn" id="addOrganization" title="Create New Organization">Create New Organization</button>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="img-wrap-inner">
            <img src="{{ asset('assets/image/dashboard-img.svg') }}" alt="Dashboard Image">
        </div>
    </div>
</div> 
<div class="col">
    <div class="table-block">
        <table class="table" id="organizationTable">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    @foreach($formData as $key=>$item)
                        @if($item['is_listing'])
                            <th scope="col">{{$item['label_name']}}</th>
                        @endif
                    @endforeach 
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody> 
            </tbody>
        </table>
    </div>
</div>
@include('modals.createOrganizationModel')
<div class="overlay"></div>
@endsection
@push('scripts') 
<script type="text/javascript" src="{{ asset('assets/js/organization/index.js') }}"></script>
@endpush
