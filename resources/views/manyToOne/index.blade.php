@extends('app')

@section('content')
    @php
        $isPaginated = method_exists($manyToOne, 'links');
    @endphp
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Many To One
        </h2>
        @if (session('success'))
            <div id="success-alert" class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{route('many-to-one.create')}}" class="btn btn-primary shadow-md mr-2 btn-hover">New Conversion</a>
            </div>

            <div class="intro-y col-span-12 overflow-auto">
               <div id="scrollable-table" style="max-height: calc(100vh - 200px); overflow-y: auto; border: 1px solid #ddd;">
                    <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                        <thead style="position: sticky; top: 0; z-index: 10;" class="font-bold text-white bg-primary">
                        <tr class="bg-primary text-white">
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Ledger Name</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Entry No</th>
                            <th class="px-4 py-2">Product</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                        <tbody id="data">
                            @include('manyToOne.rows', ['page' => 1])
                        </tbody>
                    </table>
               </div>
                <!-- Show pagination when data is paginated -->
                {{-- @if ($isPaginated && $manyToOne->count() > 0)
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            Showing {{ $manyToOne->firstItem() }} to {{ $manyToOne->lastItem() }} of
                            {{ $manyToOne->total() }} entries
                        </div>
                        <div class="pagination-nav">
                            <nav role="navigation" aria-label="Pagination Navigation">
                                <ul class="pagination">
                                    <!-- Previous Page Link -->
                                    @if ($manyToOne->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">‹</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $manyToOne->previousPageUrl() }}"
                                                rel="prev">‹</a>
                                        </li>
                                    @endif

                                    <!-- Page Numbers -->
                                    @for ($i = 1; $i <= $manyToOne->lastPage(); $i++)
                                        @if ($i == $manyToOne->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $manyToOne->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    <!-- Next Page Link -->
                                    @if ($manyToOne->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $manyToOne->nextPageUrl() }}"
                                                rel="next">›</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">›</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                @endif --}}
            </div>
            <div id="loading" style="display: none; text-align: center; padding: 10px;">
                <p>Loading more ...</p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom Pagination Styles */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding: 0 1rem;
        }

        .pagination-info {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .pagination-nav {
            display: flex;
            align-items: center;
        }

        .pagination {
            display: flex;
            align-items: center;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 0;
        }

        .pagination li {
            margin: 0;
        }

        .pagination a,
        .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            color: #374151;
            transition: all 0.15s ease;
        }

        /* First page button */
        .pagination .page-item:first-child a {
            border-radius: 6px 0 0 6px;
        }

        /* Last page button */
        .pagination .page-item:last-child a {
            border-radius: 0 6px 6px 0;
        }

        /* Single page item (when only one page) */
        .pagination .page-item:only-child a {
            border-radius: 6px;
        }

        /* Active page */
        .pagination .page-item.active span,
        .pagination .page-item.active a {
            background-color: #3b82f6;
            border-color: #3b82f6;
            /* color: #ffffff; */
            font-weight: 600;
        }

        /* Hover effects */
        .pagination a:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
            color: #111827;
        }

        .pagination .page-item.active a:hover,
        .pagination .page-item.active span:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        /* Disabled state */
        .pagination .page-item.disabled span,
        .pagination .page-item.disabled a {
            color: #9ca3af;
            background-color: #f9fafb;
            border-color: #e5e7eb;
            cursor: not-allowed;
        }

        .pagination .page-item.disabled:hover span,
        .pagination .page-item.disabled:hover a {
            background-color: #f9fafb;
            border-color: #e5e7eb;
        }

        /* Previous/Next arrow styling */
        .pagination .page-item:first-child a,
        .pagination .page-item:last-child a {
            font-weight: 600;
        }

        /* Remove border between adjacent items */
        .pagination .page-item+.page-item a,
        .pagination .page-item+.page-item span {
            border-left: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .pagination-wrapper {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }

            .pagination-info {
                order: 2;
            }

            .pagination {
                order: 1;
            }
        }
    </style>
@endpush

@push('scripts')
<script>
    let page = 1;
    const loading = document.getElementById('loading');
    const dataContainer = document.getElementById('data');
    const pagination = document.getElementById('pagination');
    const paginationInfo = document.getElementById('pagination-info');
    const scrollableTable = document.getElementById('scrollable-table');
    const table = document.getElementById('table');
    const tableBody = document.getElementById('table-body');
    const dataTable = document.getElementById('DataTable');

    scrollableTable.addEventListener('scroll', () => {
        if (scrollableTable.scrollTop + scrollableTable.clientHeight >= scrollableTable.scrollHeight) {
            if (!loading.style.display || loading.style.display === 'none') {
                loading.style.display = 'block';
                fetchMoreData();
            }
        }
    });

    function fetchMoreData() {
        page++;
        fetch(`{{ route('many-to-one.index') }}?page=${page}`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newRows = doc.querySelectorAll('#data tr');
                if (newRows.length > 0) {
                    newRows.forEach(row => {
                        dataContainer.appendChild(row);
                    });
                    loading.style.display = 'none';
                } else {
                    loading.style.display = 'none';
                    scrollableTable.removeEventListener('scroll', fetchMoreData);
                }
            })
            .catch(error => {
                console.error('Error fetching more data:', error);
                loading.style.display = 'none';
            });
    }
</script>
@endpush