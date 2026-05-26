@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('breadcrumb-plugins')
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                @include('users.form')
            </div>
        </div>
    </div>
</div>
@endsection
