@php
    $currentIndex = ($directReceipts->currentPage() - 1) * $directReceipts->perPage() + 1;
@endphp
@foreach($directReceipts as $index => $directReceipt)
    <tr class="border-b">
        <td> {{ $currentIndex++ }}</td>
        <td class="px-4 py-2">{{ $directReceipt->getRelation('ledger')->party_name }}</td>
        <td>
            <div class="flex gap-2">
                <a href="{{ route('directreceipt.show', $directReceipt->id) }}" class="btn btn-primary">View</a>
                <a href="{{ route('directreceipt.edit', $directReceipt->id) }}"
                                            class="btn btn-primary">Edit</a>
                <form action="{{ route('directreceipt.destroy', $directReceipt->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
