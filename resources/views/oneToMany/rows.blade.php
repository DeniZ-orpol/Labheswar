@php
    $currentIndex = ($oneToMany->currentPage() - 1) * $oneToMany->perPage() + 1;
@endphp
@foreach ($oneToMany as $index => $record)
    <tr class="border-b">
        <td> {{ $currentIndex++ }}</td>
        <td class="px-4 py-2">{{ $record->ledger->party_name }}</td>
        <td class="px-4 py-2">{{ $record->date }}</td>
        <td class="px-4 py-2">{{ $record->entry_no }}</td>
        <td class="px-4 py-2">{{ $record->rawItem->product_name }}</td>
        <td>
            <div class="flex gap-2">
                <a href="{{ route('one-to-many.show', $record->id) }}"
                    class="btn btn-primary">View</a>
                {{-- <a href="{{ route('one-to-many.edit', $record->id) }}"
                    class="btn btn-primary">Edit</a> --}}
                {{-- <form action="{{ route('one-to-many.destroy', $record->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form> --}}
            </div>
        </td>
    </tr>
@endforeach
