@extends('layouts.auth')
@section('title', 'PIN Login')

@section('content')
    <form method="POST" action="{{ route('pin.login.verify') }}" x-data="pinLogin()">
        @csrf

        <div class="text-center mb-4">
            <p class="text-muted small">Enter your PIN to sign in</p>
            <div class="pin-display d-flex justify-content-center gap-2 mb-3">
                <template x-for="(dot, index) in 4" :key="index">
                    <div class="pin-dot" :class="{ filled: pin.length > index }">
                        <span x-text="pin.length > index ? '●' : '○'" class="fs-3"></span>
                    </div>
                </template>
            </div>
            @error('pin') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
            <input type="hidden" name="pin" x-model="pin">
        </div>

        <div class="numpad">
            <div class="row g-2 mb-2">
                <template x-for="num in [1,2,3]" :key="num">
                    <div class="col-4">
                        <button type="button" class="btn btn-outline-secondary w-100 py-3 fs-5" @click="addDigit(num)" x-text="num"></button>
                    </div>
                </template>
            </div>
            <div class="row g-2 mb-2">
                <template x-for="num in [4,5,6]" :key="num">
                    <div class="col-4">
                        <button type="button" class="btn btn-outline-secondary w-100 py-3 fs-5" @click="addDigit(num)" x-text="num"></button>
                    </div>
                </template>
            </div>
            <div class="row g-2 mb-2">
                <template x-for="num in [7,8,9]" :key="num">
                    <div class="col-4">
                        <button type="button" class="btn btn-outline-secondary w-100 py-3 fs-5" @click="addDigit(num)" x-text="num"></button>
                    </div>
                </template>
            </div>
            <div class="row g-2">
                <div class="col-4">
                    <button type="button" class="btn btn-outline-secondary w-100 py-3 fs-5" @click="clearPin()"><i class="fas fa-times"></i></button>
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-outline-secondary w-100 py-3 fs-5" @click="addDigit(0)" x-text="0"></button>
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-outline-secondary w-100 py-3 fs-5" @click="backspace()"><i class="fas fa-delete-left"></i></button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3" :disabled="pin.length !== 4">
            <i class="fas fa-arrow-right me-1"></i> Sign In
        </button>
    </form>

    <div class="auth-footer">
        <a href="{{ route('login') }}"><i class="fas fa-envelope me-1"></i> Email Login</a>
    </div>

    @push('scripts')
    <script>
        function pinLogin() {
            return {
                pin: '',
                addDigit(d) { if (this.pin.length < 4) this.pin += d; },
                backspace() { this.pin = this.pin.slice(0, -1); },
                clearPin() { this.pin = ''; }
            }
        }
    </script>
    @endpush

    <style>
        .pin-dot { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
        .pin-dot span { transition: all 0.2s; }
        .pin-dot.filled span { color: var(--primary, #7367f0); }
        .numpad .btn-outline-secondary { border-color: #e0e0e0; }
        .numpad .btn-outline-secondary:hover { background: #7367f0; color: #fff; border-color: #7367f0; }
    </style>
@endsection
