@extends('app')

@section('content')
    <div class="content">
        <div class="intro-y grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-12">
                <div class="box p-5 rounded-md">
                    <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                        <div class="font-medium text-base truncate">Hsn Code Details</div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <!-- LEFT COLUMN -->
                        <div class="p-5 rounded-md bg-slate-100">
                            <div class="font-medium text-lg mb-3">Hsn Code Information</div>
                            <p><strong>Code:</strong> {{ $hsn->hsn_code }}</p>
                            <p><strong>CGST:</strong> {{ $hsn->gst / 2 }}%</p>
                            <p><strong>SGST:</strong> {{ $hsn->gst / 2 }}%</p>
                            <p><strong>IGST:</strong> {{ $hsn->gst * 2 }}%</p>
                        </div>

                    </div>
                    <!-- Navigation -->
                    <div class="mt-5">
                        <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back </a>
                        <a href="{{ route('hsn_codes.edit', $hsn->id) }}" class="btn btn-primary mr-1 mb-2"> Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
