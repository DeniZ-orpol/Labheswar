@if($packagings)
    @php
        $currentIndex = ($packagings->currentPage() - 1) * $packagings->perPage() + 1;
    @endphp
    @foreach ($packagings as $index => $packaging)
        <tr class="border-b">
            <td> {{ $currentIndex++ }}</td>
            <td class="px-4 py-2">{{ $packaging->group }}</td>
            <td>
                <div class="flex gap-2">
                    <a href="{{ route('packaging.edit', $packaging->group_id) }}" class="btn btn-primary">Edit</a>
                    <form action="{{ route('packaging.destroy', $packaging->group_id) }}" method="POST"
                        onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
@endif
