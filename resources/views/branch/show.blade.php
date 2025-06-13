@extends('app')

@section('content')


<div class="content">
    <div class="intro-y grid grid-cols-12 gap-5 mt-5">
        <div class="col-span-12">
            <div class="box p-5 rounded-md">
                <!-- Header -->
                <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                    <div class="font-medium text-base truncate">Branch Details</div>
                </div>

                <!-- Grid Content -->
                <div class="grid grid-cols-2 gap-5">
                    <!-- User Info -->
                    <div class="p-5 rounded-md bg-slate-100">
                        <div class="font-medium text-lg mb-3">Branch Information</div>
                        <div class="flex items-center">
                            <i data-lucide="user" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Name: {{ $branch->name }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Address: {{ $branch->location ?? 'N/A' }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Latitude: {{ $branch->latitude ?? 'N/A' }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Longitude: {{ $branch->longitude ?? 'N/A' }}
                        </div>
                        <div class="flex items-center mt-3">
                            <i data-lucide="shield" class="w-4 h-4 text-slate-500 mr-2"></i>
                            GST No: {{ $branch->gst_no }}
                        </div>
                        {{-- <div class="flex items-center mt-3">
                            <i data-lucide="shield" class="w-4 h-4 text-slate-500 mr-2"></i>
                            Branch Admin: {{ $branch->admin_name ?? 'No Admin Assigned' }}
                        </div> --}}
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-5">
                    <a href="{{ route('branch.index') }}" class="btn btn-secondary">Back to Branch List</a>
                    <a href="{{ route('branch.edit', $branch->id) }}" class="btn btn-primary ml-2">Edit Branch</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
