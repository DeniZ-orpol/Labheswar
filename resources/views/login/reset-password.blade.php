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
        height: 100%;
        margin: 0;
        padding: 0;
        background-color: #f1f5f9;
        /* Tailwind's slate-100 equivalent */
        font-family: 'Inter', sans-serif;
    }

    .login-page-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }

    .login-form-container {
        background-color: #ffffff;
        border-radius: 1rem;
        /* xl rounded */
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 2.5rem;
        width: 100%;
        max-width: 500px;
    }

    .login-page-input {
        margin-top: 20px;
        padding: 12px 16px;
        width: 100%;
        border: 1px solid #cbd5e1;
        /* Tailwind's slate-300 */
        border-radius: 0.5rem;
        font-size: 1rem;
        outline: none;
        transition: border 0.3s;
    }

    .login-page-input:focus {
        border-color: #3b82f6;
        /* Tailwind's blue-500 */
    }

    .login-page-button {
        margin-top: 20px;
        background-color: #2D5F72;
        /* Tailwind blue-500 */
        color: white;
        padding: 0.75rem 1.25rem;
        font-weight: 600;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 100%;
    }

    .login-page-button:hover {
        background-color: #2D5F72;
        /* Tailwind blue-600 */
    }

    .login-page-title {
        font-size: 1.75rem;
        font-weight: bold;
        text-align: center;
        margin-bottom: 0.5rem;
        color: #0f172a;
        /* Tailwind slate-900 */
    }

    .login-page-subtitle {
        font-size: 1rem;
        color: #94a3b8;
        /* Tailwind slate-400 */
        text-align: center;
        margin-bottom: 2rem;
    }

    @media (max-width: 1024px) {
        .login-form-container {
            padding: 2rem 1.5rem;
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
                        Reset Password
                    </h2>
                    <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">
                        A few more clicks to sign in to your account. Manage all your tasks in one place.
                    </div>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <div class="intro-x mt-8">
                            <input type="password" name="password"
                                class="intro-x login__input form-control py-3 px-4 block login-page-input"
                                placeholder="New Password" required>
                            <input style="margin-top: 20px" type="password" name="password_confirmation"
                                class="intro-x login__input form-control py-3 px-4 block login-page-input"
                                placeholder="Confirm Password" required>
                        </div>
                        @error('otp')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                        <div class="intro-x mt-5 xl:mt-4 text-center">
                            <button type="submit"
                                class="btn login-page-button btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3">Update
                                Password</button>
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
