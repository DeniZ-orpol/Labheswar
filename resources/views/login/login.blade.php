<!DOCTYPE html>
<!--
Template Name: Enigma - HTML Admin Dashboard Template
Author: Left4code
Website: http://www.left4code.com/
Contact: muhammadrizki@left4code.com
Purchase: https://themeforest.net/user/left4code/portfolio
Renew Support: https://themeforest.net/user/left4code/portfolio
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<html lang="en" class="light">
<!-- BEGIN: Head -->
<head>
    <meta charset="utf-8">
    <link href="{{ asset('images/logo.png') }}" rel="shortcut icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Enigma admin is super flexible, powerful, clean & modern responsive tailwind admin template with unlimited possibilities.">
    <meta name="keywords"
        content="admin template, Enigma Admin Template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="LEFT4CODE">
    <title>Login - Sweetler</title>
    <!-- BEGIN: CSS Assets-->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <!-- END: CSS Assets-->
</head>
<!-- END: Head -->
<body class="login">
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <!-- BEGIN: Login Info -->
            <div class="hidden xl:flex flex-col min-h-screen">
                {{-- <a href="{{ url('') }}" class="-intro-x flex items-center pt-5">
                    <img alt="Sweetler" class="w-12" src="{{ asset('images/logo.png') }}">
                </a> --}}
                <div class="my-auto">
                    <img alt="Sweetler" class="-intro-x w-1/2 -mt-16"
                        src="{{ asset('images/logo.png') }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">
                        Sweetler Dairy & Sweets
                    </div>
                    <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">sign in to your account.</div>
                </div>
            </div>
            <!-- END: Login Info -->
            <!-- BEGIN: Login Form -->
            <div class="flex justify-center items-center w-full">
                <div
                    class="login-form-container mt-10 bg-white dark:bg-darkmode-600 px-5 sm:px-8 py-8 rounded-md shadow-md w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center login-page-intro-x">
                        Sign In
                    </h2>
                    <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">
                        A few more clicks to sign in to your account. Manage all your tasks in one place.
                    </div>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="intro-x mt-8">
                            <input type="email" name="email"
                                class="intro-x login__input form-control py-3 px-4 block login-page-input"
                                placeholder="Email" required>
                            <input type="password" name="password"
                                class="intro-x login__input form-control py-3 px-4 block mt-4 login-page-input"
                                placeholder="Password" required>
                        </div>
                        <div class="intro-x flex text-slate-600 dark:text-slate-500 text-xs sm:text-sm mt-4">
                                <div class="flex items-center mr-auto">
                                    <input id="remember-me" type="checkbox" name="remember" class="form-check-input border mr-2">
                                    <label class="cursor-pointer select-none" for="remember-me">Remember me</label>
                                </div>
                                <a class="text-primary dark:text-slate-200" href="/forgot-password">Forgot Password ?</a>
                            </div>
                        <div class="intro-x mt-5 xl:mt-4 text-center">
                            <button type="submit"
                                class="btn login-page-button btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3">Login</button>
                        </div>
                    </form>
                    <div class="intro-x mt-2 xl:mt-6 text-slate-600 dark:text-slate-500 ">

                    </div>
                    {{-- <div class="intro-x mt-2 xl:mt-6 text-slate-600 dark:text-slate-500 text-center">
                            By signing up, you agree to our
                            <a class="text-primary dark:text-slate-200" href="/">Terms and Conditions</a> &
                            <a class="text-primary dark:text-slate-200" href="/">Privacy Policy</a>.
                        </div> --}}
                </div>
            </div>
            <!-- END: Login Form -->
        </div>
    </div>
    <!-- BEGIN: Dark Mode Switcher-->
    {{-- <div data-url="login-dark-login.html"
        class="dark-mode-switcher cursor-pointer shadow-md fixed bottom-0 right-0 box dark:bg-dark-2 border rounded-full w-40 h-12 flex items-center justify-center z-50 mb-10 mr-10">
        <div class="mr-4 text-gray-700 dark:text-gray-300">Dark Mode</div>
        <div class="dark-mode-switcher__toggle border"></div>
    </div> --}}
    <!-- END: Dark Mode Switcher-->
    <!-- BEGIN: JS Assets-->
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- END: JS Assets-->
</body>
</html>
