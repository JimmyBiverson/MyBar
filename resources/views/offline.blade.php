@extends('layouts.app')
@section('title', 'Offline')
@section('page-title', 'Offline')

@section('content')
<div class="text-center py-5">
    <div class="mb-4">
        <i class="fas fa-wifi-slash text-muted" style="font-size: 5rem;"></i>
    </div>
    <h3>You're Offline</h3>
    <p class="text-muted mb-4">Please check your internet connection and try again.</p>
    <button class="btn btn-primary btn-lg" onclick="location.reload()">
        <i class="fas fa-sync me-2"></i> Retry
    </button>
</div>
@endsection
