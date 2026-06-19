@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Sign in</h1>
                <form method="post" action="{{ route('login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email or username</label>
                        <input class="form-control" name="login_id" value="{{ old('login_id') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input class="form-control" type="password" name="password" required>
                    </div>
                    <button class="btn btn-danger w-100">Login</button>
                </form>
                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('register') }}">Create student account</a>
                    <a href="{{ route('password.request') }}">Forgot password</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
