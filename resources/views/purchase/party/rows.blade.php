@php
    $currentIndex = (($parties->currentPage() - 1) * $parties->perPage()) + 1;
@endphp
@if ($parties && $parties->count())
    @foreach ($parties as $party)
        <tr>
<td>{{ $currentIndex++ }}</td>
            <td>{{ $party->party_name }}</td>
            <td>
                <div class="flex gap-2 justify-content-left">
                    <a href="{{ route('purchase.party.show', $party->id) }}" class="btn btn-primary">View</a>
                    <a href="{{ route('purchase.party.edit', $party->id) }}" class="btn btn-primary">Edit</a>
                    <form action="{{ route('purchase.party.destroy', $party->id) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this role?');"
                          style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
@endif
