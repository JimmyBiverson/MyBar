@extends(auth()->check() ? 'layouts.app' : 'layouts.auth')
@section('title', 'Not Found')
@if(auth()->check())
    @section('page-title', '404 Not Found')
@endif

@section('content')
<div class="text-center py-5">
    <div class="mb-4">
        <i class="fas fa-search text-warning" style="font-size: 5rem;"></i>
    </div>
    <h3>404 - Page Not Found</h3>
    <p class="text-muted mb-4">The page you are looking for does not exist.</p>
    @auth
    <a href="{{ route('dashboard') }}" class="btn btn-primary">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>
    @endauth
</div>
@endsection
