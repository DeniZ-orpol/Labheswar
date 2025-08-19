@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Profit and Loose List</h2>
            <a href="{{ route('profit-loose.create') }}"
                class="btn btn-primary shadow-md btn-hover ml-auto">Add Profit-Loose</a>
        </div>
        @if (session('success'))
            <div id="success-alert" class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif
    
        @if (session('error'))
            <div id="error-alert" class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif

        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
             <div id="scrollable-table" style="max-height: calc(100vh - 200px); overflow-y: auto; border: 1px solid #ddd;">
                    <table id="DataTable" class="display table table-bordered w-full">
                        <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr class="bg-primary text-white">
                            <th>#</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                     <tbody id="TableBody">
                            @include('profitAndLoose.rows', ['page' => 1])
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="loading" style="display: none; text-align: center; padding: 10px;">
                <p>Loading more...</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let page = 1;
        let loading = false;

        const scrollContainer = document.getElementById('scrollable-table');

        scrollContainer.addEventListener('scroll', function () {
            const scrollBottom = scrollContainer.scrollTop + scrollContainer.clientHeight;
            const scrollHeight = scrollContainer.scrollHeight;
            
            if (scrollBottom >= scrollHeight - 100 && !loading) {
                loading = true;
                page++;
                console.log('Fetching more data for page:', page);
                fetchMoreData(page);
            }
        });

        function fetchMoreData(page) {
            const url = new URL('{{ route('profit-loose.index') }}');
            url.searchParams.set('page', page);

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newRows = doc.querySelectorAll('#DataTable tbody tr');
                    const tableBody = document.querySelector('#DataTable tbody');

                    newRows.forEach(row => {
                        tableBody.appendChild(row);
                    });

                    loading = false;
                })
                .catch(error => {
                    console.error('Error fetching more data:', error);
                    loading = false;
                });
        }
    </script>
@endpush()
