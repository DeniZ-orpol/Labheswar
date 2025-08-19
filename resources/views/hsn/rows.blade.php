@php
    $currentIndex = (($hsns->currentPage() - 1) * $hsns->perPage()) + 1;
@endphp
@foreach ($hsns as $hsn)
    <tr>
        {{-- {{dd($hsn->gst)}} --}}
        <td>{{ $currentIndex++ }}</td>
        <td>{{ $hsn->hsn_code }}</td>
        <td>{{ number_format((float) $hsn->gst / 2, 2) }}%</td>
        <td>{{ number_format((float) $hsn->gst / 2, 2) }}%</td>
        <td>{{ number_format((float) $hsn->gst, 2) }}%</td>
        <td>{{ $hsn->short_name }}</td>
        <td>
            <!-- Add buttons for actions like 'View', 'Edit' etc. -->
            <!-- <button class="btn btn-primary">Message</button> -->
            <div class="flex gap-2 justify-content-left">
                <a href="{{ route('hsn_codes.show', $hsn->id) }}" class="btn btn-primary mr-1 mb-2">
                    View
                    {{-- {{ dd($hsn->id) }} --}}
                </a>
                <a href="{{ route('hsn_codes.edit', $hsn->id) }}" class="btn btn-primary mr-1 mb-2">
                    Edit
                    {{-- {{ dd($hsn->id) }} --}}
                </a>
                <form action="{{ route('hsn_codes.destroy', $hsn->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this hsn?');"
                    style="display: inline-block;">
                    @csrf
                    @method('DELETE') <!-- Add this line -->
                    <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
