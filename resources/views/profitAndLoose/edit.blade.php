@extends('app')
@push('styles')
    <style>
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .column {
            width: 50%;
            padding: 10px;
            box-sizing: border-box;
        }
        .error-text {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
@endpush
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Add Profit and Loose
        </h2>
        <form action="{{ route('profit-loose.update', $profitLoose->id) }}" method="POST" class="form-updated validate-form">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="column">
                    <!-- Select Profit or Loose -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="profit_loose" class="form-label w-full flex flex-col sm:flex-row">
                            Select Profit/Loose<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <select id="profit_loose" name="profit_loose" class="form-control field-new @error('profit_loose') is-invalid @enderror" >
                            <option value="" {{!$profitLoose->type ?? 'selected'}}>Select Profit/Loose...</option>
                            <option value="Profit" {{$profitLoose->type == 'Profit' ? 'selected' : ''}} >Profit</option>
                            <option value="Loose" {{$profitLoose->type == 'Loose' ? 'selected' : ''}} >Loose</option>
                        </select>
                        @error('profit_loose')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Amount -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="amount" class="form-label w-full flex flex-col sm:flex-row">
                            Amount
                        </label>
                        <input id="amount" type="text" name="amount" class="form-control field-new @error('amount') is-invalid @enderror" maxlength="255" value="{{ old('amount', $profitLoose->amount) }}">
                        @error('amount')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="description" class="form-label w-full flex flex-col sm:flex-row">
                            Description
                        </label>
                        <input id="description" type="text" name="description" class="form-control field-new @error('description') is-invalid @enderror" maxlength="255" value="{{ old('description', $profitLoose->description) }}">
                        @error('description')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
        </form>
        <!-- END: Validation Form -->
    </div>
@endsection

@push('scripts')
<script>
    // Setup enter navigation for profit and loose edit form
    function setupEnterNavigation() {
        let currentFieldIndex = 0;

        const formFields = [
            { selector: '#profit_loose', type: 'select' },
            { selector: '#amount', type: 'input' },
            { selector: '#description', type: 'input' }
        ];

        function focusField(selector) {
            const element = document.querySelector(selector);
            if (element) {
                element.focus();
                if (element.tagName === 'SELECT') {
                    setTimeout(() => {
                        if (element.size <= 1) {
                            element.click();
                        }
                    }, 100);
                }
            }
        }

        function handleFormFieldNavigation(e, fieldIndex) {
            if (e.key === 'Enter') {
                e.preventDefault();

                if (fieldIndex < formFields.length - 1) {
                    currentFieldIndex = fieldIndex + 1;
                    focusField(formFields[currentFieldIndex].selector);
                } else {
                    const submitButton = document.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.focus();
                    }
                }
            }
        }

        formFields.forEach((field, index) => {
            const element = document.querySelector(field.selector);
            if (element) {
                element.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        handleFormFieldNavigation(e, index);
                    }
                });
            }
        });

        setTimeout(() => {
            focusField(formFields[0].selector);
        }, 500);
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupEnterNavigation();
    });
</script>
@endpush
@
