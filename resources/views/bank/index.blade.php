@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Bank Details List</h2>
            <a href="{{ route('bank.create') }}"
                class="btn btn-primary shadow-md btn-hover ml-auto">Create
                New Bank Detail</a>
        </div>

        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <table class="table table-bordered table-striped">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Bank Name</th>
                            <th>Account No</th>
                            <th>IFSC Code</th>
                            <th>Opening Bank Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($banks as $index => $bank)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $bank->bank_name }}</td>
                                <td>{{ $bank->account_no }}</td>
                                <td>{{ $bank->ifsc_code }}</td>
                                <td>{{ $bank->opening_bank_balance }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        {{-- <a href="#" class="btn btn-primary">View</a> --}}
                                        <a href="{{ route('bank.edit', $bank->id) }}" class="btn btn-primary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="6">No Banks Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
