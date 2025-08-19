@php
    $currentIndex = ($purchaseReceipt->currentPage() - 1) * $purchaseReceipt->perPage() + 1;
@endphp 
@if ($purchaseReceipt && $purchaseReceipt->count())
    @foreach ($purchaseReceipt as $purchaseRec)
        <tr>
           <td>{{ $currentIndex++ }}</td>
            <td>{{ $purchaseRec->purchaseParty->party_name }}</td>
            <td>{{ $purchaseRec->bill_date }}</td>
            <td>{{ $purchaseRec->delivery_date }}</td>
            <td>{{ $purchaseRec->gst_status }}</td>
            <td>{{ $purchaseRec->total_amount }}</td>
            <td>
                <div class="flex gap-2 justify-content-left">
                    <form action="{{ route('purchase.destroy', $purchaseRec->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this role?');"
                        style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                    </form>
                    <a href="{{ route('purchase.edit', $purchaseRec->id) }}" class="btn btn-primary mr-1 mb-2">
                        Edit
                    </a>
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="7" class="text-center">No Purchase found.</td>
    </tr>
@endif
