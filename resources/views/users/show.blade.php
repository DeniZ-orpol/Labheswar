@extends('app')

@section('content')
<div class="content">
    <h2 class="text-lg font-medium mt-10">User Details</h2>
    <div class="box p-5 mt-5">
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Mobile:</strong> {{ $user->mobile }}</p>
        <p><strong>Role:</strong> {{ $user->role }}</p>
        <p><strong>Date of Birth:</strong> {{ $user->dob }}</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-primary mt-5">Back to List</a>
</div>
@endsection
