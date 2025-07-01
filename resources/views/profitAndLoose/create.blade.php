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
            Add Profit and Loose
        </h2>
        <form action="{{ route('profit-loose.store') }}" method="POST" class="form-updated validate-form">
            @csrf
            <div class="row">
                <div class="column">
                    <!-- Select Profit or Loose -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="profit_loose" class="form-label w-full flex flex-col sm:flex-row">
                            Select Profit/Loose<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <select id="profit_loose" name="profit_loose" class="form-control field-new" required>
                            <option value="" selected>Select Profit/Loose...</option>
                            <option value="Profit">Profit</option>
                            <option value="Loose">Loose</option>
                        </select>
                    </div>

                    <!-- Amount -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="amount" class="form-label w-full flex flex-col sm:flex-row">
                            Amount
                        </label>
                        <input id="amount" type="text" name="amount" class="form-control field-new" maxlength="255">
                    </div>

                    <!-- Description -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="description" class="form-label w-full flex flex-col sm:flex-row">
                            Description
                        </label>
                        <input id="description" type="text" name="description" class="form-control field-new" maxlength="255">
                    </div>
                </div>
            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
        </form>
        <!-- END: Validation Form -->
    </div>
@endsection
