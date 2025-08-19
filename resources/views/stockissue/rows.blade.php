@php
    $currentIndex = ($stockIssues->currentPage() - 1) * $stockIssues->perPage() + 1;
@endphp
@foreach($stockIssues as $index => $stockIssue)
    <tr class="border-b">
        <td> {{ $currentIndex++ }}</td>
        <td class="px-4 py-2">{{ $stockIssue->getRelation('ledger')->party_name }}</td>
        <td>
            <div class="flex gap-2">
                <a href="{{ route('stockissue.show', $stockIssue->id) }}" class="btn btn-primary">View</a>
                <!-- <a href="{{ route('stockissue.edit', $stockIssue->id) }}"
                                            class="btn btn-primary">Edit</a> -->
                <form action="{{ route('stockissue.destroy', $stockIssue->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach

