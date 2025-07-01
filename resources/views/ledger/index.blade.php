@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Ledger - {{ $type }}</h2>
            <a href="{{ route('ledger.create', ['type' => $type]) }}"
                class="btn btn-primary shadow-md btn-hover ml-auto">Create
                New Ledger</a>
        </div>

        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <table class="table table-bordered table-striped">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone No</th>
                            <th>GST No</th>
                            <th>State</th>
                            <th>Balancing Method</th>
                            <th>Pan No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ledgers as $index => $ledger)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $ledger->name }}</td>
                                <td>{{ $ledger->phone_no }}</td>
                                <td>{{ $ledger->gst_no }}</td>
                                <td>{{ $ledger->state }}</td>
                                <td>{{ $ledger->balancing_method }}</td>
                                <td>{{ $ledger->pan_no }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        {{-- <a href="#" class="btn btn-primary">View</a> --}}
                                        <a href="{{ route('ledger.edit', $ledger->id) }}?type={{ $type }}" class="btn btn-primary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="8">No Ledger Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- DataTables JS -->
    <script>
        $(document).ready(function() {
            $('#inventoryTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'excel', 'csv', 'pdf'
                ]
            });
        });
    </script>
@endpush
