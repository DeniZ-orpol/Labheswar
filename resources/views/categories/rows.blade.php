@php
    $currentIndex = (($categories->currentPage() - 1) * $categories->perPage()) + 1;
@endphp
@foreach ($categories as $category)
    <tr data-id="{{ $category->id }}">
        <td class="px-4 py-2">{{ $currentIndex++ }}</td>
        <td class="px-4 py-2">{{ $category->name }}</td>
        <td class="px-4 py-2">{{ $category->type ?? 'N/A' }}</td>
        <td class="px-4 py-2">
            @if ($category->image)
                <img src="{{ asset($category->image) }}" alt="Category Image"
                    class="h-12 w-12 object-cover rounded">
            @else
                <span class="text-gray-500 italic">No Image</span>
            @endif
        </td>
        <td>
            <div class="flex gap-2">
                <a href="{{ route('categories.show', $category->id) }}"
                    class="btn btn-primary">View</a>
                <a href="{{ route('categories.edit', $category->id) }}"
                    class="btn btn-primary">Edit</a>
                <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
