@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/register.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="register-wrapper">
    <div class="register-card">
        <h1>Create your Account</h1>

        @if($errors->any())
        <div class="alert-box">
            Please check the form for errors.
        </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" placeholder="Juan Dela Cruz" value="{{ old('name') }}" class="@error('name') is-invalid @enderror" required>
                @error('name')
                <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="" class="@error('password') is-invalid @enderror" required>
                @error('password')
                <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="" class="@error('password_confirmation') is-invalid @enderror" required>
                @error('password_confirmation')
                <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <div class="date-input-wrapper">
                    <span class="date-icon">📅</span>
                    <input type="text" id="dob" name="dob" placeholder="12 December 2025" onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'" value="{{ old('dob') }}">
                </div>
                @error('dob')
                <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="juandelacruz@gmail.com" value="{{ old('email') }}" class="@error('email') is-invalid @enderror" required>
                @error('email')
                <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-signup">Sign up</button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <button type="button" class="btn-google">
            <svg class="google-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.28-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                <path fill="none" d="M0 0h48v48H0z" />
            </svg>
            Continue with Google
        </button>

        <div class="form-footer">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
</div>
@endsection