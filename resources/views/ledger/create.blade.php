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
            box-sizing: border-box;
        }
    </style>
@endpush

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Create Ledger - {{ $type }}
        </h2>
        <form action="{{ route('ledger.store', ['type' => $type]) }}" method="POST" class="form-updated validate-form">
            @csrf <!-- CSRF token for security -->
            <div class="row">
                <div class="column p-5">
                    <!-- Account Group -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="acc_group" class="form-label w-full flex flex-col sm:flex-row">
                            Account Group
                        </label>
                        <input id="acc_group" type="text" name="acc_group" value="{{ $type }}"
                            class="form-control field-new" disabled>
                    </div>

                    <!-- Name -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ledger_name" class="form-label w-full flex flex-col sm:flex-row">
                            Ledger Name<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="ledger_name" type="text" name="ledger_name" class="form-control field-new" required>
                    </div>

                    <!-- Pin code -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="pin_code" class="form-label w-full flex flex-col sm:flex-row">
                            Pin Code
                        </label>
                        <input id="pin_code" type="number" name="pin_code" class="form-control field-new">
                    </div>

                    <!-- Email -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="email" class="form-label w-full flex flex-col sm:flex-row">
                            Email
                        </label>
                        <input id="email" type="email" name="email" class="form-control field-new">
                    </div>

                    <!-- Phone No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="phone_no" class="form-label w-full flex flex-col sm:flex-row">
                            Phone No
                        </label>
                        <input id="phone_no" type="number" name="phone_no" class="form-control field-new">
                    </div>

                    <!-- Station -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="station" class="form-label w-full flex flex-col sm:flex-row">
                            Station
                        </label>
                        <input id="station" type="text" name="station" class="form-control field-new">
                    </div>

                    <!-- State -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="state" class="form-label w-full flex flex-col sm:flex-row">
                            State
                        </label>
                        <input id="state" type="text" name="state" class="form-control field-new">
                    </div>

                    <!-- Balancing Method -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="balancing_method" class="form-label w-full flex flex-col sm:flex-row">
                            Balancing Method
                        </label>
                        <select id="balancing_method" name="balancing_method" class="form-control field-new">
                            <option value="" selected>Select Balancing Method...</option>
                            <option value="Fifo Base">Fifo Base</option>
                            <option value="On Account">On Account</option>
                        </select>
                    </div>

                    <!-- Mail To -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="mail_to" class="form-label w-full flex flex-col sm:flex-row">
                            Mail To
                        </label>
                        <input id="mail_to" type="text" name="mail_to" class="form-control field-new">
                    </div>

                    <!-- Address -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="address" class="form-label w-full flex flex-col sm:flex-row">
                            Address
                        </label>
                        <textarea name="address" id="address" class="form-control field-new"></textarea>
                    </div>
                </div>

                <div class="column p-5">
                    <!-- Contact person -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="contact_person" class="form-label w-full flex flex-col sm:flex-row">
                            Contact Person
                        </label>
                        <input id="contact_person" type="text" name="contact_person" class="form-control field-new">
                    </div>

                    <!-- Designation -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="designation" class="form-label w-full flex flex-col sm:flex-row">
                            Designation
                        </label>
                        <input id="designation" type="text" name="designation" class="form-control field-new">
                    </div>

                    <!-- GST No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="gst_no" class="form-label w-full flex flex-col sm:flex-row">
                            GST No
                        </label>
                        <input id="gst_no" type="text" name="gst_no" class="form-control field-new">
                    </div>

                    <!-- GST Heading -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="gst_heading" class="form-label w-full flex flex-col sm:flex-row">
                            GST Heading
                        </label>
                        <input id="gst_heading" type="text" name="gst_heading" class="form-control field-new">
                    </div>

                    <!-- Note -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="note" class="form-label w-full flex flex-col sm:flex-row">
                            Note
                        </label>
                        <textarea name="note" id="note" class="form-control field-new"></textarea>
                    </div>

                    <!-- Ledger Category -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ledger_category" class="form-label w-full flex flex-col sm:flex-row">
                            Ledger Category
                        </label>
                        <select id="ledger_category" name="ledger_category" class="form-control field-new">
                            <option value="" selected>Select Ledger Category...</option>
                            <option value="Retailer">Retailer</option>
                            <option value="Stock List">Stock List</option>
                            <option value="Distributor">Distributor</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Country -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="country" class="form-label w-full flex flex-col sm:flex-row">
                            Country
                        </label>
                        <input id="country" type="text" name="country" class="form-control field-new">
                    </div>

                    <!-- Pan No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="pan_no" class="form-label w-full flex flex-col sm:flex-row">
                            Pan No
                        </label>
                        <input id="pan_no" type="text" name="pan_no" class="form-control field-new">
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
