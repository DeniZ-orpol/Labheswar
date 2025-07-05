@extends('app')

@section('content')
    <div class="content">
        <div class="intro-y grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-12">
                <div class="box p-5 rounded-md">
                    <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                        <div class="font-medium text-base truncate">Purchase Party Details</div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <!-- LEFT COLUMN -->
                        <div class="p-5 rounded-md bg-slate-100">
                            <div class="font-medium text-lg mb-3">Purchase Party Information</div>
                            <p><strong>Party Name:</strong> {{ $party->party_name }}</p>
                            <p><strong>GST No:</strong> {{ $party->gst_number ?? '-' }}</p>
                            <p><strong>Mobile No:</strong> {{ $party->mobile_no ?? '-' }}</p>
                            <p><strong>Email:</strong> {{ $party->email ?? '-' }}</p>
                            <p><strong>Address:</strong> {{ $party->address ?? '-' }}</p>
                            <p><strong>Station:</strong> {{ $party->station ?? '-' }}</p>
                            <p><strong>Account No:</strong> {{ $party->acc_no ?? '-' }}</p>
                            <p><strong>IFSC Code:</strong> {{ $party->ifsc_code ?? '-' }}</p>
                            <p><strong>Pin code:</strong> {{ $party->pincode ?? '-' }}</p>
                            {{-- <p><strong>IGST:</strong> {{ $party->address }}</p> --}}
                        </div>

                    </div>
                    <!-- Navigation -->
                    <div class="mt-5">
                        <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back </a>
                        {{-- <a href="{{ route('purchase.party.edit', $party->id) }}" class="btn btn-primary mr-1 mb-2"> Edit</a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
