@extends('layouts.app')
@section('title', 'New Purchase')
@section('page-title', 'New Purchase')

@section('breadcrumb-plugins')
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf
            @include('purchases.form')
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Purchase</button>
            </div>
        </form>
    </div>
</div>
@endsection
