@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Purchase
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <!-- BEGIN: Modal Toggle -->
                {{-- <div class="text-center"> <a href="javascript:;" data-tw-toggle="modal"
                        data-tw-target="#header-footer-modal-preview" class="btn btn-primary">Make New Purchase</a> </div> --}}
                <!-- END: Modal Toggle --> <!-- BEGIN: Modal Content -->
                {{-- <div id="header-footer-modal-preview" class="modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content"> <!-- BEGIN: Modal Header -->
                            <div class="modal-header">
                                <h2 class="font-medium text-base mr-auto">Select Purchase Party</h2> --}}
                {{-- </div> <!-- END: Modal Header --> <!-- BEGIN: Modal Body --> --}}
                {{-- <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                                <div class="col-span-12 sm:col-span-6"> <label for="modal-form-6" class="form-label">Select Purchase Party
                                    </label>
                                    <select id="modal-form-6" class="form-select">
                                        <option value="">Select Party</option>
                                        @foreach ($parties as $party)
                                            <option value="{{ $party->id}} "> {{$party->party_name}} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> <!-- END: Modal Body --> <!-- BEGIN: Modal Footer --> --}}
                {{-- <div class="modal-footer">
                                <button type="button" data-tw-dismiss="modal"
                                    class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                                <a href="{{ Route('purchase.create') }}"
                                    class="btn btn-primary shadow-md mr-2 btn-hover">Send</a>
                            </div> <!-- END: Modal Footer --> --}}
                {{-- </div>
                    </div> --}}
                {{-- </div> <!-- END: Modal Content --> --}}
                <a href="{{ Route('purchase.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Make New
                    Purchase</a>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                <thead>
                    <tr class="bg-primary font-bold text-white">
                        <th>#</th>
                        <th>Party Name</th>
                        <th>Bill Date</th>
                        <th>Delivery Date</th>
                        <th>GST</th>
                        <th>Total</th>
                        <th style="TEXT-ALIGN: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($purchaseReceipt && $purchaseReceipt->count())
                        @foreach ($purchaseReceipt as $purchaseRec)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $purchaseRec->purchaseParty->party_name }}</td>
                                <td>{{ $purchaseRec->bill_date }}</td>
                                <td>{{ $purchaseRec->delivery_date }}</td>
                                <td>{{ $purchaseRec->gst_status }}</td>
                                <td>{{ $purchaseRec->total_amount }}</td>
                                <td>
                                    <div class="flex gap-2 justify-content-left">
                                        <form action="{{ route('purchase.destroy', $purchaseRec->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this role?');"
                                            style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                        </form>
                                    </div>
                                    <a href="{{route('purchase.edit', $purchaseRec->id)}}"
                                        class="btn btn-primary mr-1 mb-2">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No Purchase found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- END: Users Layout -->
        </div>
    </div>
@endsection
