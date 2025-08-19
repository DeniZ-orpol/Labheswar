@php
    $currentIndex = (($banks->currentPage() - 1) * $banks->perPage()) + 1;
@endphp
@foreach ($banks as $index => $bank)
    <tr class="border-b">
        <td> {{ $currentIndex++}}</td>
         <td>{{ $bank->bank_name }}</td>
        <td>{{ $bank->account_no }}</td>
        <td>{{ $bank->ifsc_code }}</td>
        <td>{{ $bank->opening_bank_balance }}</td>
        <td>
            <div class="flex gap-2">
                {{-- <a href="#" class="btn btn-primary">View</a> --}}
                <a href="{{ route('bank.edit', $bank->id) }}" class="btn btn-primary">Edit</a>
            </div>
        </td>
    </tr>
@endforeach
