@extends('layouts.app')
@section('title', 'Forbidden')
@section('page-title', '403 Forbidden')

@section('content')
<div class="text-center py-5">
    <div class="mb-4">
        <i class="fas fa-lock text-danger" style="font-size: 5rem;"></i>
    </div>
    <h3>403 - Forbidden</h3>
    <p class="text-muted mb-4">You do not have permission to access this page.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>
</div>
@endsection
