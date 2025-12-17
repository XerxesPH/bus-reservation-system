@extends('layouts.app')

@section('content')
<style>
    /* 1. We renamed 'body' to '.login-wrapper' so it only affects this section */
    .login-wrapper {
        background-color: #f5f5f5;
        display: flex;
        justify-content: center;
        align-items: center;
        /* Adjust height to account for the navbar padding (100px) */
        min-height: calc(100vh - 100px);
        width: 100%;
        padding: 20px;
    }

    /* 2. Reset standard Bootstrap margins if needed */
    .login-wrapper * {
        font-family: 'Poppins', sans-serif;
    }

    .main-container {
        width: 100%;
        max-width: 450px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .login-card {
        width: 100%;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        padding: 40px 30px;
        margin-bottom: 20px;
    }

    .header {
        text-align: left;
        margin-bottom: 30px;
    }

    .header h2 {
        color: #333;
        font-size: 26px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .header p {
        color: #777;
        font-size: 14px;
        margin-bottom: 0;
    }

    .google-btn {
        width: 100%;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        font-size: 14px;
        color: #555;
        transition: background 0.2s;
        text-decoration: none;
    }

    .google-btn:hover {
        background-color: #f9f9f9;
        text-decoration: none;
        color: #555;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 25px 0;
        color: #aaa;
        font-size: 12px;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px dotted #ccc;
    }

    .divider span {
        padding: 0 10px;
    }

    .input-group {
        margin-bottom: 20px;
        width: 100%;
        /* Ensure full width in bootstrap context */
    }

    .input-group label {
        display: block;
        color: #666;
        font-size: 14px;
        margin-bottom: 8px;
        text-align: left;
    }

    .input-group input {
        width: 100%;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 14px;
        outline: none;
        background-color: #fdfdfd;
    }

    .input-group input.is-invalid {
        border-color: #dc3545;
    }

    .input-group input:focus {
        border-color: #8a3360;
        box-shadow: none;
        /* Remove bootstrap blue glow */
    }

    .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: block;
        text-align: left;
    }

    .actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        font-size: 13px;
        width: 100%;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #888;
    }

    .forgot-pass {
        color: #8a3360;
        text-decoration: none;
        font-weight: 600;
    }

    .login-btn {
        width: 100%;
        background-color: #8a3360;
        color: white;
        border: none;
        padding: 14px;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .login-btn:hover {
        background-color: #6e274b;
    }

    .footer-link {
        font-size: 14px;
        color: #888;
    }

    .footer-link a {
        color: #8a3360;
        text-decoration: none;
        font-weight: 600;
        margin-left: 5px;
    }

    .alert-box {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 4px;
        font-size: 13px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
        width: 100%;
    }
</style>

<div class="login-wrapper">
    <div class="main-container">
        <div class="login-card">

            <div class="header">
                <h2>Login to your Account</h2>
                <p>See what is going on with your business</p>
            </div>

            <button type="button" class="google-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px" height="20px">
                    <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z" />
                    <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z" />
                    <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z" />
                    <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z" />
                </svg>
                Continue with Google
            </button>

            <div class="divider">
                <span>or Sign in with Email</span>
            </div>

            @if($errors->any())
            <div class="alert-box">
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="mail@abc.com" value="{{ old('email') }}" required class="@error('email') is-invalid @enderror">
                    @error('email')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="................" required>
                    @error('password')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="actions">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="#" class="forgot-pass">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

        </div>

        <div class="footer-link">
            Not Registered Yet? <a href="{{ route('register') }}">Create an account</a>
        </div>
    </div>
</div>
@endsection