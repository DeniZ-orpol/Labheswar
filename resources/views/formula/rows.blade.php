@php
    $currentIndex = ($formulas->currentPage() - 1) * $formulas->perPage() + 1;
@endphp
@foreach ($formulas as $index => $formula)
    <tr class="border-b">
        <td> {{ $currentIndex++ }}</td>
        <td class="px-4 py-2">{{ $formula->product->product_name }}</td>
        <td>
            <div class="flex gap-2">
                <a href="{{ route('formula.show', $formula->id) }}" class="btn btn-primary">View</a>
                <a href="{{ route('formula.edit', $formula->id) }}" class="btn btn-primary">Edit</a>
                <form action="{{ route('formula.destroy', $formula->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
