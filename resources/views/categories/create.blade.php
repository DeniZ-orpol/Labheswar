@extends('app')
@section('content')
    <style>
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .column {
            width: 50%;
            /* Adjust as needed */
            /* background-color: #f2f2f2; */
            padding: 10px;
            /* border: 1px solid #ddd; */
            box-sizing: border-box;
        }

        <style>.custom-dropzone {
            border: 2px dashed #1abc9c;
            border-radius: 12px;
            height: 300px;
            width: 100%;
            position: relative;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .custom-dropzone:hover {
            border-color: #16a085;
        }

        .custom-dropzone input[type="file"] {
            opacity: 0;
            position: absolute;
            height: 100%;
            width: 100%;
            cursor: pointer;
        }

        .custom-dropzone span {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #bbb;
            font-size: 16px;
            pointer-events: none;
        }
    </style>
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Create Category
        </h2>
        <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data"
            class="form-updated validate-form">
            @csrf <!-- CSRF token for security -->
            <div class="row">
                <div class="column">
                    <!-- Name -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="name" class="form-label w-full flex flex-col sm:flex-row">
                            Name<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="name" type="text" name="name" class="form-control field-new"
                            placeholder="Enter Category name" required maxlength="255">
                    </div>

                    <!-- Product Image -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="fileInput" class="form-label w-full flex flex-col sm:flex-row">
                            Category Image
                        </label>
                        <div class="input-form col-span-3 mt-3"
                            style="position: relative; border: 2px dashed #ccc; border-radius: 8px; padding: 50px 40px; text-align: center; background-color: #f9f9f9; cursor: pointer;">
                            <div class="fallback">
                                <input name="image" type="file" id="fileInput" accept="image/*"
                                    style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 1;"
                                    onchange="previewImage(this)" />
                            </div>
                            <div id="uploadMessage" style="color: #666; font-size: 16px; pointer-events: none;">
                                Drop image file here or click to upload.
                            </div>
                            <div id="imagePreview" style="display: none; max-width: 300px; margin: 0 auto;">
                                <img id="previewImg"
                                    style="width: 100%; height: auto; border-radius: 8px; margin-top: 10px;" />
                                <div style="margin-top: 10px; font-size: 14px; color: #666;">
                                    <span id="fileName"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- </form> --}}
                </div>
            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
        </form>
        {{-- </div> --}}
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
        <div id="failed-notification-content" class="toastify-content hidden flex">
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
    // preview image box

    function previewImage(input) {
        const file = input.files[0];
        const uploadMessage = document.getElementById('uploadMessage');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const fileName = document.getElementById('fileName');

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();

            reader.onload = function(e) {
                previewImg.src = e.target.result;
                fileName.textContent = file.name;
                uploadMessage.style.display = 'none';
                imagePreview.style.display = 'block';
            };

            reader.readAsDataURL(file);
        }
    }

    // Handle drag and drop
    const dropArea = document.querySelector('.input-form');

    dropArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#007bff';
        this.style.backgroundColor = '#f0f8ff';
    });

    dropArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ccc';
        this.style.backgroundColor = '#f9f9f9';
    });

    dropArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ccc';
        this.style.backgroundColor = '#f9f9f9';

        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type.startsWith('image/')) {
            document.getElementById('fileInput').files = files;
            previewImage(document.getElementById('fileInput'));
        }
    });
    // end preview image box
</script>
