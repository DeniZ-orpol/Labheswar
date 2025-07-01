@extends('app')
@push('styles')
    <style>
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .column {
            width: 50%;
            padding: 10px;
            box-sizing: border-box;
        }
    </style>
@endpush
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Create Bank Details
        </h2>
        <form action="{{ route('bank.store') }}" method="POST" enctype="multipart/form-data"
            class="form-updated validate-form">
            @csrf
            <div class="row">
                <div class="column">
                    <!-- Bank Name -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="bank_name" class="form-label w-full flex flex-col sm:flex-row">
                            Bank Name<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="bank_name" type="text" name="bank_name" class="form-control field-new"
                            placeholder="Enter Bank name" required maxlength="255">
                    </div>

                    <!-- Account No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="account_no" class="form-label w-full flex flex-col sm:flex-row">
                            Account No<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="account_no" type="text" name="account_no" class="form-control field-new"
                            placeholder="Enter Bank name" required maxlength="255">
                    </div>

                    <!-- IFSC Code -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ifsc_code" class="form-label w-full flex flex-col sm:flex-row">
                            IFSC Code<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="ifsc_code" type="text" name="ifsc_code" class="form-control field-new"
                            placeholder="Enter Bank name" required maxlength="255">
                    </div>

                    <!-- Opening Bank Balance -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="opening_balance" class="form-label w-full flex flex-col sm:flex-row">
                            Opening Balance
                        </label>
                        <input id="opening_balance" type="text" name="opening_balance" class="form-control field-new"
                            placeholder="Enter Bank name" maxlength="255">
                    </div>

                    <!-- Close On -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="close_on" class="form-label w-full flex flex-col sm:flex-row">
                            Close On
                        </label>
                        <input id="close_on" type="text" name="close_on" class="form-control field-new"
                            placeholder="Enter Bank name" maxlength="255">
                    </div>
                </div>
            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
        </form>
        {{-- </div> --}}
        <!-- END: Validation Form -->
    </div>
@endsection
