@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Bank Details List</h2>
            <a href="{{ route('bank.create') }}" class="btn btn-primary shadow-md btn-hover ml-auto">Create
                New Bank Detail</a>
        </div>
        @if (session('success'))
            <div id="success-alert" class="alert alert-success"
                style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div id="error-alert" class="alert alert-danger"
                style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif

        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <div id="scrollable-table"
                    style="max-height: calc(100vh - 200px); overflow-y: auto; border: 1px solid #ddd;">
                    <table id="DataTable" class="display table table-bordered w-full">
                        <thead style="position: sticky; top: 0; z-index: 10;">
                            <tr class="bg-primary text-white">
                                <th>#</th>
                                <th>Bank Name</th>
                                <th>Account No</th>
                                <th>IFSC Code</th>
                                <th>Opening Bank Balance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($banks)
                                @include('bank.rows', ['page' => 1])
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500 py-4">No Data found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        let page = 1;
        let loading = false;

        const scrollContainer = document.getElementById('scrollable-table');

        scrollContainer.addEventListener('scroll', function() {
            const scrollBottom = scrollContainer.scrollTop + scrollContainer.clientHeight;
            const scrollHeight = scrollContainer.scrollHeight;

            if (scrollBottom >= scrollHeight - 100 && !loading) {
                page++;
                loadMoreData(page);
            }
        });

        function loadMoreData(page) {
            loading = true;
            document.getElementById('loading').style.display = 'block';

            fetch("?page=" + page, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim().length == 0) {
                        document.getElementById('loading').innerHTML = "";
                        return;
                    }
                    document.getElementById('order-data').insertAdjacentHTML('beforeend', data);
                    document.getElementById('loading').style.display = 'none';
                    loading = false;
                })
                .catch(error => {
                    console.error("Error fetching more order:", error);
                    loading = false;
                });
        }
    </script>
@endpush
