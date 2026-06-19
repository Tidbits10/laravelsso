@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Student registration</h1>
                <form method="post" action="{{ route('register.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Student number</label>
                        <input class="form-control" name="student_number" placeholder="2026-00001-SP-0" value="{{ old('student_number') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PUP email</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input class="form-control" name="username" value="{{ old('username') }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm password</label>
                            <input class="form-control" type="password" name="password_confirmation" required>
                        </div>
                    </div>
                    <button class="btn btn-danger w-100">Register</button>
                </form>
                <a class="d-inline-block mt-3" href="{{ route('login') }}">Back to login</a>
            </div>
        </div>
    </div>
</div>
@endsection
