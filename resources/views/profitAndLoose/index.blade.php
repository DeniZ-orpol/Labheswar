@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Profit and Loose List</h2>
            <a href="{{ route('profit-loose.create') }}"
                class="btn btn-primary shadow-md btn-hover ml-auto">Add Profit-Loose</a>
        </div>

        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <table class="table table-bordered table-striped">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($profitsLooses as $index => $profitLoose)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $profitLoose->type }}</td>
                                <td>{{ $profitLoose->amount }}</td>
                                <td>{{ $profitLoose->description ?? '-' }}</td>
                                <td>{{ $profitLoose->status ?? '-' }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        {{-- <a href="#" class="btn btn-primary">View</a> --}}
                                        <a href="{{ route('profit-loose.edit', $profitLoose->id) }}" class="btn btn-primary">Edit</a>
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
