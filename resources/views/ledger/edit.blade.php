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
            /* Adjust as needed */
            /* background-color: #f2f2f2; */
            /* padding: 10px; */
            /* border: 1px solid #ddd; */
            box-sizing: border-box;
        }
    </style>
@endpush

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit Ledger - {{ $type }}
        </h2>
        <form action="{{ route('ledger.update', $ledger->id) }}?type={{ $type }}" method="POST"
            class="form-updated validate-form">
            @csrf
            @method('PUT')
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
                        <input id="ledger_name" type="text" name="ledger_name" class="form-control field-new"
                        value ="{{ $ledger->name }}"
                        required>
                    </div>

                    <!-- Pin code -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="pin_code" class="form-label w-full flex flex-col sm:flex-row">
                            Pin Code
                        </label>
                        <input id="pin_code" type="number" name="pin_code" class="form-control field-new"
                        value="{{ $ledger->pin_code }}"
                        >
                    </div>

                    <!-- Email -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="email" class="form-label w-full flex flex-col sm:flex-row">
                            Email
                        </label>
                        <input id="email" type="email" name="email" class="form-control field-new"
                        value="{{ $ledger->email }}"
                        >
                    </div>

                    <!-- Phone No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="phone_no" class="form-label w-full flex flex-col sm:flex-row">
                            Phone No
                        </label>
                        <input id="phone_no" type="number" name="phone_no" class="form-control field-new"
                        value="{{ $ledger->phone_no }}"
                        >
                    </div>

                    <!-- Station -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="station" class="form-label w-full flex flex-col sm:flex-row">
                            Station
                        </label>
                        <input id="station" type="text" name="station" class="form-control field-new"
                        value="{{ $ledger->station }}"
                        >
                    </div>

                    <!-- State -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="state" class="form-label w-full flex flex-col sm:flex-row">
                            State
                        </label>
                        <input id="state" type="text" name="state" class="form-control field-new"
                        value="{{ $ledger->state }}"
                        >
                    </div>

                    <!-- Balancing Method -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="balancing_method" class="form-label w-full flex flex-col sm:flex-row">
                            Balancing Method
                        </label>
                        <select id="balancing_method" name="balancing_method" class="form-control field-new">
                            <option value="" {{ !$ledger->balancing_method ?? 'selected'}} >Select Balancing Method...</option>
                            <option value="Fifo Base" {{ $ledger->balancing_method == 'Fifo Base' ? 'selected' : '' }}>Fifo Base</option>
                            <option value="On Account" {{ $ledger->balancing_method == 'On Account' ? 'selected' : '' }}>On Account</option>
                        </select>
                    </div>

                    <!-- Mail To -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="mail_to" class="form-label w-full flex flex-col sm:flex-row">
                            Mail To
                        </label>
                        <input id="mail_to" type="text" name="mail_to" class="form-control field-new"
                        value="{{ $ledger->mail_to }}"
                        >
                    </div>

                    <!-- Address -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="address" class="form-label w-full flex flex-col sm:flex-row">
                            Address
                        </label>
                        <textarea name="address" id="address" class="form-control field-new">{{$ledger->address}}</textarea>
                    </div>
                </div>

                <div class="column p-5">
                    <!-- Contact person -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="contact_person" class="form-label w-full flex flex-col sm:flex-row">
                            Contact Person
                        </label>
                        <input id="contact_person" type="text" name="contact_person" class="form-control field-new"
                        value="{{ $ledger->contact_person }}"
                        >
                    </div>

                    <!-- Designation -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="designation" class="form-label w-full flex flex-col sm:flex-row">
                            Designation
                        </label>
                        <input id="designation" type="text" name="designation" class="form-control field-new"
                        value="{{ $ledger->designation }}"
                        >
                    </div>

                    <!-- GST No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="gst_no" class="form-label w-full flex flex-col sm:flex-row">
                            GST No
                        </label>
                        <input id="gst_no" type="text" name="gst_no" class="form-control field-new"
                        value="{{ $ledger->gst_no }}"
                        >
                    </div>

                    <!-- GST Heading -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="gst_heading" class="form-label w-full flex flex-col sm:flex-row">
                            GST Heading
                        </label>
                        <input id="gst_heading" type="text" name="gst_heading" class="form-control field-new"
                        value="{{ $ledger->gst_heading }}"
                        >
                    </div>

                    <!-- Note -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="note" class="form-label w-full flex flex-col sm:flex-row">
                            Note
                        </label>
                        <textarea name="note" id="note" class="form-control field-new">{{$ledger->note}} </textarea>
                    </div>

                    <!-- Ledger Category -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ledger_category" class="form-label w-full flex flex-col sm:flex-row">
                            Ledger Category
                        </label>
                        <select id="ledger_category" name="ledger_category" class="form-control field-new">
                            <option value="" {{ !$ledger->ledger_category ?? 'selected'}}>Select Ledger Category...</option>
                            <option value="Retailer" {{$ledger->ledger_category == 'Retailer' ? 'selected' : ''}}>Retailer</option>
                            <option value="Stock List" {{$ledger->ledger_category == 'Stock List' ? 'selected' : ''}}>Stock List</option>
                            <option value="Distributor" {{$ledger->ledger_category == 'Distributor' ? 'selected' : ''}}>Distributor</option>
                            <option value="Other" {{$ledger->ledger_category == 'Other' ? 'selected' : ''}}>Other</option>
                        </select>
                    </div>

                    <!-- Country -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="country" class="form-label w-full flex flex-col sm:flex-row">
                            Country
                        </label>
                        <input id="country" type="text" name="country" class="form-control field-new" value="{{ $ledger->country }}">
                    </div>

                    <!-- Pan No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="pan_no" class="form-label w-full flex flex-col sm:flex-row">
                            Pan No
                        </label>
                        <input id="pan_no" type="text" name="pan_no" class="form-control field-new" value="{{ $ledger->pan_no }}">
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
