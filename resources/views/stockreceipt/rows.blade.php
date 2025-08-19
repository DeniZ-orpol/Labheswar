@php
    $currentIndex = ($stockReceipts->currentPage() - 1) * $stockReceipts->perPage() + 1;
@endphp
@foreach($stockReceipts as $index => $stockReceipt)
    <tr class="border-b">
        <td> {{ $currentIndex++ }}</td>
        <td class="px-4 py-2">{{ $stockReceipt->getRelation('ledger')->party_name }}</td>
        <td>
            <div class="flex gap-2">
                <a href="{{ route('stockreceipt.show', $stockReceipt->id) }}" class="btn btn-primary">View</a>
                <!-- <a href="{{ route('stockreceipt.edit', $stockReceipt->id) }}"
                                            class="btn btn-primary">Edit</a> -->
                <form action="{{ route('stockreceipt.destroy', $stockReceipt->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
