@extends('app')

@section('content')


<div class="content">
    <div class="intro-y grid grid-cols-12 gap-5 mt-5">
        <div class="col-span-12">
            <div class="box p-5 rounded-md">
                <!-- Header -->
                <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                    <div class="font-medium text-base truncate">User Details</div>
                </div>

                <!-- Grid Content -->
                <div class="grid grid-cols-2 gap-5">
                    <!-- User Info -->
                    <div class="p-5 rounded-md bg-slate-100">
                        <div class="font-medium text-lg mb-3">User Information</div>
                        <div class="flex items-center">
                            <i data-lucide="user" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Name: {{ $user->name }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Email: {{ $user->email ?? 'N/A' }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Mobile: {{ $user->mobile ?? 'N/A' }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-500 mr-2"></i>
                            DOB: {{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('d-m-Y') : 'N/A' }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="shield" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Role: {{ $user->role->role_name ?? '-' }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="shield" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Branch: {{ $branch->name ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-5">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to User List</a>
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary ml-2">Edit User</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
