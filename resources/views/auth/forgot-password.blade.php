@extends('layouts.auth')
@section('title', 'Forgot Password')

@section('content')
    <p class="text-muted small text-center mb-4">Enter your email address and we'll send you a password reset link.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-paper-plane me-1"></i> Send Reset Link
        </button>
    </form>

    <div class="auth-footer">
        <a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
    </div>
@endsection
