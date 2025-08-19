@php
    $currentIndex = ($ledgers->currentPage() - 1) * $ledgers->perPage() + 1;
@endphp
@foreach ($ledgers as $index => $ledger)
    <tr>
        <td>{{ $currentIndex++ }}</td>
        <td>{{ $ledger->party_name }}</td>
        <td>{{ $ledger->mobile_no }}</td>
        <td>{{ $ledger->gst_number }}</td>
        <td>{{ $ledger->state }}</td>
        <td>{{ $ledger->balancing_method }}</td>
        <td>{{ $ledger->ledger_group }}</td>
        <td>
            <div class="flex gap-2">
                <a href="{{ route('ledger.show', $ledger->id) }}" class="btn btn-primary">View</a>
                <a href="{{ route('purchase.party.edit', $ledger->id) }}" class="btn btn-primary">Edit</a>
            </div>
        </td>
    </tr>
@endforeach
