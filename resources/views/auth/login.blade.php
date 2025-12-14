@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Login</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <p class="mt-3 text-center">
                        Don't have an account? <a href="{{ route('register') }}">Sign up</a>
                    </p>
                </div>
            </div>

            @if($errors->any())
            <div class="alert alert-danger mt-3">
                {{ $errors->first() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection