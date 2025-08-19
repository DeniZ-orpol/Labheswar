@php
    $currentIndex = (($companies->currentPage() - 1) * $companies->perPage()) + 1;
@endphp
@foreach ($companies as $company)
    <tr>
        <td>{{ $currentIndex++ }}</td>
        <td>{{ $company->name }}</td>
        <td>
            <div class="flex gap-2 justify-content-left">
                <a href="{{ route('company.show', $company->id) }}" class="btn btn-primary mr-1 mb-2">View</a>
                <a href="{{ route('company.edit', $company->id) }}" class="btn btn-primary mr-1 mb-2">Edit</a>
                <form action="{{ route('company.destroy', $company->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this company?');"
                    style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach