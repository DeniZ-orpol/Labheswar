@php
    $currentIndex = ($profitsLooses->currentPage() - 1) * $profitsLooses->perPage() + 1;
@endphp
@foreach($profitsLooses as $index => $profitLoose)
    <tr class="border-b">
        <td> {{ $currentIndex++ }}</td>
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
@endforeach
