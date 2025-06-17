@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit Purchase Party
        </h2>
        <form action="{{ route('purchase.party.update', $party->id) }}" method="POST" class="form-updated validate-form">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="col-span-6 mt-3">
                    <label class="form-label w-full flex flex-col sm:flex-row">
                        Party Name <p style="color: red;margin-left: 3px;">*</p>
                    </label>
                    <input type="text" name="party_name" class="form-control field-new"
                        value="{{ old('party_name', $party->party_name) }}" placeholder="Enter Purchase Party name" required
                        maxlength="255">
                </div>

            </div>

            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2 mt-5">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Update</button>
        </form>
    </div>
@endsection
