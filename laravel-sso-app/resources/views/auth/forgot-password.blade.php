@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Reset password</h1>
                <form method="post" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Student number</label>
                        <input class="form-control" name="student_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" required>
                    </div>
                    <button class="btn btn-danger w-100">Generate temporary password</button>
                </form>
                <a class="d-inline-block mt-3" href="{{ route('login') }}">Back to login</a>
            </div>
        </div>
    </div>
</div>
@endsection
