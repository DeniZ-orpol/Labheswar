<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="utf-8">
    <link href="{{ asset('/dist/images/Lavish Jewels Logo_Final.png') }}" rel="shortcut icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Enigma admin is super flexible, powerful, clean & modern responsive Tailwind admin template with unlimited possibilities.">
    <meta name="keywords"
        content="admin template, Enigma Admin Template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="LEFT4CODE">
    <title>Login - J - ERP</title>

    <!-- PWA Support -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="{{ config('laravelpwa.theme_color', '#ffffff') }}">

    <!-- BEGIN: CSS Assets -->
    <link rel="stylesheet" href="{{ asset('/dist/css/app.css') }}" />
    <!-- END: CSS Assets -->
</head>
<style>
    html,
    body {
        overflow: hidden;
        height: 100%;
        font-family: 'Inter', sans-serif;
        background-color: #f1f5f9;
    }

    .login-page-logo {
        width: 77% !important;
    }

    .login-page-block {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-page-intro-x {
        font-size: 1.875rem;
        line-height: 2.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    .login-page-input {
        padding: 14px 16px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        margin-bottom: 10px;
        width: 400px;
        font-size: 1rem;
        color: #1e293b;
        background-color: #fff;
        transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .login-page-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        outline: none;
    }

    .login-page-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin-left: auto;
        margin-right: auto;
        overflow: hidden;
        background-color: #f8fafc;
    }

    .login-form-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: auto;
        max-width: 480px;
        width: 100%;
        background-color: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
    }

    .login-page-button {
        width: 435px;
        background-color: #2D5F72;
        color: white;
        border: none;
        padding: 14px 20px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 6px;
        transition: background-color 0.3s ease;
        cursor: pointer;
    }

    h2.intro-x {
        text-align: center;
        margin-bottom: 1.5rem;
        color: #1e293b;
    }

    .login-page-button:hover {
        background-color: #2D5F72;
    }

    .text-danger {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 1024px) {
        .login-page-block {
            flex-direction: column;
            height: 100vh;
            justify-content: start;
        }

        .login-form-container {
            padding: 1.5rem;
            width: 90%;
        }

        .login-page-intro-x {
            font-size: 1.5rem;
        }
    }
</style>

<body>
    <div class="container login-page-container">
        <div class="block xl:grid grid-cols-2 gap-4 login-page-block">

            <!-- BEGIN: Login Form -->
            <div class="flex justify-center items-center w-full">
                <div
                    class="login-form-container mt-10 bg-white dark:bg-darkmode-600 px-5 sm:px-8 py-8 rounded-md shadow-md w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center login-page-intro-x">
                        Verify OTP
                    </h2>
                    <form method="POST" action="{{ route('password.verifyOtp') }}">
                        @csrf
                        <div class="intro-x mt-8">
                            <input type="text" name="otp"
                                class="intro-x login__input form-control py-3 px-4 block login-page-input"
                                placeholder="Enter OTP" required>
                        </div>
                        @error('otp')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                        <div class="intro-x mt-5 xl:mt-4 text-center">
                            <button type="submit"
                                class="btn login-page-button btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- END: Login Form -->
        </div>
    </div>

    <!-- BEGIN: JS Assets -->
    <script src="{{ asset('/dist/js/app.js') }}"></script>
    <!-- END: JS Assets -->

    <!-- Register Service Worker for PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/serviceworker.js')
                    .then(function(registration) {
                        console.log('Service Worker registered:', registration);
                    }).catch(function(error) {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
    </script>
</body>

</html>
