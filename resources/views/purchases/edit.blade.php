@extends('layouts.app')
@section('title', 'Edit Purchase')
@section('page-title', 'Edit Purchase')

@section('breadcrumb-plugins')
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('purchases.update', $purchase->id) }}">
            @csrf
            @method('PUT')
            @include('purchases.form')
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Purchase</button>
            </div>
        </form>
    </div>
</div>
@endsection
