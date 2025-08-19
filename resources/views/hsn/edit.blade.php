@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Hsn Code
        </h2>

        @if (session('success'))
            <div id="success-alert" class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif
    
        @if (session('error'))
            <div id="error-alert" class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif
        <form action="{{ route('hsn_codes.update', $hsn->id) }}" method="POST" class="form-updated">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="col-span-3 mt-3">
                    <label for="hsn_code" class="form-label">Hsn Code<span style="color: red;margin-left: 3px;">
                            *</span></label>
                    <input type="text" name="hsn_code" id="hsn_code" class="form-control field-new" 
                        value="{{ $hsn->hsn_code }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    @error('hsn_code')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-span-3 mt-3">
                    <label for="gst" class="form-label">GST(%)<span style="color: red;margin-left: 3px;">
                            *</span></label>
                    <input type="text" name="gst" id="gst" class="form-control field-new" 
                        value="{{ $hsn->gst }}"oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                    @error('gst')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-span-3 mt-3">
                    <label for="short_name" class="form-label">Short Name</label>
                    <input type="text" name="short_name" id="short_name" class="form-control field-new" 
                        value="{{ $hsn->short_name }}">
                </div>
            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back </a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Save</button>
        </form>

        <!-- END: Validation Form -->
        <!-- BEGIN: Success Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Registration success!</div>
                <div class="text-slate-500 mt-1"> Please check your e-mail for further info! </div>
            </div>
        </div>
        <!-- END: Success Notification Content -->
        <!-- BEGIN: Failed Notification Content -->
        <div id`="failed-notification-content" class="toastify-content hidden flex">
            <i class="text-danger" data-lucide="x-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Registration failed!</div>
                <div class="text-slate-500 mt-1"> Please check the fileld form. </div>
            </div>
        </div>
        <!-- END: Failed Notification Content -->
    </div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function setupEnterNavigation() {
            let currentFieldIndex = 0;

            const formFields = [
                { selector: '#hsn_code', type: 'input' },
                { selector: '#gst', type: 'input' },
                { selector: '#short_name', type: 'input' }
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

        setupEnterNavigation();
    });
</script>