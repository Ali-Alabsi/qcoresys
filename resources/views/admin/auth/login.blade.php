<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('Staff login') }} — QCoreSys</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="grid min-h-screen place-items-center bg-brand-navy p-4">
<form method="POST" action="{{ route('admin.login.store') }}" class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
    @csrf
    <h1 class="mb-1 text-2xl font-bold text-brand-navy">QCore<span class="text-cyan-500">Sys</span></h1>
    <p class="mb-7 text-sm text-slate-500">{{ __('Staff administration') }}</p>
    @if($errors->any())<p class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
    <label class="label-public">{{ __('Email') }}</label><input class="input-public mb-4" type="email" name="email" value="{{ old('email') }}" required autofocus>
    <label class="label-public">{{ __('Password') }}</label><input class="input-public mb-4" type="password" name="password" required>
    <label class="mb-6 flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> {{ __('Remember me') }}</label>
    <button class="btn-primary w-full">{{ __('Login') }}</button>
</form>
</body>
</html>
