@extends('layouts.app')

@section('content')
<style>
    /* Scoped wrapper to ensure we don't break the Navbar from layouts.app */
    .register-wrapper {
        font-family: 'Roboto', sans-serif;
        background-color: #f5f5f5;
        display: flex;
        justify-content: center;
        align-items: center;
        /* Subtracting navbar padding */
        min-height: calc(100vh - 100px);
        width: 100%;
        padding: 20px;
    }

    /* Main Form Container */
    .register-card {
        background-color: #ffffff;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 420px;
        /* Slightly wider to accommodate content */
    }

    /* Form Title */
    .register-card h1 {
        text-align: center;
        color: #444;
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 2rem;
    }

    /* Styling for each input group */
    .form-group {
        margin-bottom: 1.25rem;
    }

    /* Labels */
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #888;
        font-size: 0.9rem;
        font-weight: 500;
        text-align: left;
    }

    /* Standard Inputs */
    .register-card input[type="text"],
    .register-card input[type="password"],
    .register-card input[type="email"],
    .register-card input[type="date"] {
        width: 100%;
        padding: 0.9rem 1rem;
        border: 1px solid #eee;
        border-radius: 8px;
        box-sizing: border-box;
        font-size: 1rem;
        color: #333;
        background-color: #fff;
        transition: border-color 0.2s;
    }

    /* Error Styling */
    .register-card input.is-invalid {
        border-color: #dc3545;
    }

    .register-card input:focus {
        border-color: #bbb;
        outline: none;
        box-shadow: none;
        /* Override bootstrap */
    }

    .error-msg {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 5px;
        display: block;
    }

    /* Date Input Specifics */
    .date-input-wrapper {
        position: relative;
        width: 100%;
    }

    .date-input-wrapper input {
        padding-left: 2.8rem !important;
    }

    .date-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #555;
        font-size: 1.2rem;
        pointer-events: none;
    }

    /* Sign Up Button */
    .btn-signup {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 8px;
        background-color: #7E2E5F;
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.2s;
        margin-top: 0.5rem;
    }

    .btn-signup:hover {
        background-color: #65244b;
    }

    /* "or" Divider */
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
        color: #aaa;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #eee;
    }

    .divider span {
        padding: 0 1rem;
        font-size: 0.9rem;
    }

    /* Google Button */
    .btn-google {
        width: 100%;
        padding: 0.9rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #ffffff;
        color: #666;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
        text-decoration: none;
    }

    .btn-google:hover {
        background-color: #f9f9f9;
        border-color: #ccc;
        text-decoration: none;
        color: #666;
    }

    .google-icon {
        width: 20px;
        height: 20px;
        margin-right: 12px;
    }

    /* Footer */
    .form-footer {
        text-align: center;
        margin-top: 1.8rem;
        color: #888;
        font-size: 0.95rem;
    }

    .form-footer a {
        color: #5b86e5;
        text-decoration: none;
        font-weight: 600;
    }

    .alert-box {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }
</style>

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
                <input type="password" id="password" name="password" placeholder="................" class="@error('password') is-invalid @enderror" required>
                @error('password')
                <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="................" required>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <div class="date-input-wrapper">
                    <span class="date-icon">📅</span>
                    <input type="text" id="dob" name="dob" placeholder="12 December 2025" onfocus="(this.type='date')" onblur="(this.type='text')" value="{{ old('dob') }}">
                </div>
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