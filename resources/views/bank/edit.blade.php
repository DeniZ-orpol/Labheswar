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
        .error-text {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
@endpush
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit Bank Details
        </h2>
        <form action="{{ route('bank.update', $bank->id) }}" method="POST" enctype="multipart/form-data"
            class="form-updated validate-form">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="column">
                    <!-- Bank Name -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="bank_name" class="form-label w-full flex flex-col sm:flex-row">
                            Bank Name<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="bank_name" type="text" name="bank_name" class="form-control field-new @error('bank_name') is-invalid @enderror"
                            placeholder="Enter Bank name" maxlength="255" value="{{ old('bank_name', $bank->bank_name) }}">
                        @error('bank_name')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Account No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="account_no" class="form-label w-full flex flex-col sm:flex-row">
                            Account No<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="account_no" type="text" name="account_no" class="form-control field-new @error('account_no') is-invalid @enderror"
                            placeholder="Enter Bank name" maxlength="255" value="{{ old('account_no', $bank->account_no) }}">
                        @error('account_no')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- IFSC Code -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ifsc_code" class="form-label w-full flex flex-col sm:flex-row">
                            IFSC Code<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="ifsc_code" type="text" name="ifsc_code" class="form-control field-new @error('ifsc_code') is-invalid @enderror"
                            placeholder="Enter Bank name" maxlength="255" value="{{ old('ifsc_code', $bank->ifsc_code) }}">
                        @error('ifsc_code')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Opening Bank Balance -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="opening_balance" class="form-label w-full flex flex-col sm:flex-row">
                            Opening Balance
                        </label>
                        <input id="opening_balance" type="text" name="opening_balance" class="form-control field-new @error('opening_balance') is-invalid @enderror"
                            placeholder="Enter Bank name" maxlength="255" value="{{ old('opening_balance', $bank->opening_bank_balance) }}">
                        @error('opening_balance')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Close On -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="close_on" class="form-label w-full flex flex-col sm:flex-row">
                            Close On
                        </label>
                        <input id="close_on" type="text" name="close_on" class="form-control field-new @error('close_on') is-invalid @enderror"
                            placeholder="Enter Bank name" maxlength="255" value="{{ old('close_on', $bank->close_on) }}">
                        @error('close_on')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
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

@push('scripts')
<script>
    // Setup enter navigation for bank edit form
    function setupEnterNavigation() {
        let currentFieldIndex = 0;

        const formFields = [
            { selector: '#bank_name', type: 'input' },
            { selector: '#account_no', type: 'input' },
            { selector: '#ifsc_code', type: 'input' },
            { selector: '#opening_balance', type: 'input' },
            { selector: '#close_on', type: 'input' }
        ];

        function focusField(selector) {
            const element = document.querySelector(selector);
            if (element) {
                element.focus();
                if (element.tagName === 'SELECT') {
                    setTimeout(() => {
                        if (element.size <= 1) {
                            element.click();
                        }
                    }, 100);
                }
            }
        }

        function handleFormFieldNavigation(e, fieldIndex) {
            if (e.key === 'Enter') {
                e.preventDefault();

                if (fieldIndex < formFields.length - 1) {
                    currentFieldIndex = fieldIndex + 1;
                    focusField(formFields[currentFieldIndex].selector);
                } else {
                    const submitButton = document.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.focus();
                    }
                }
            }
        }

        formFields.forEach((field, index) => {
            const element = document.querySelector(field.selector);
            if (element) {
                element.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        handleFormFieldNavigation(e, index);
                    }
                });
            }
        });

        setTimeout(() => {
            focusField(formFields[0].selector);
        }, 500);
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupEnterNavigation();
    });
</script>
@endpush
