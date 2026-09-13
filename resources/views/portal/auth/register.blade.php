<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('Register') }} — QCoreSys</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="grid min-h-screen place-items-center bg-brand-navy p-4">
<form method="POST" action="{{ route('portal.register.store') }}" class="my-8 w-full max-w-xl rounded-2xl bg-white p-8 shadow-2xl" x-data="{type:'{{ old('customer_type', 'INDIVIDUAL') }}'}">
    @csrf
    <h1 class="mb-6 text-2xl font-bold">{{ __('Create customer account') }}</h1>
    @if($errors->any())<div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2"><label class="label-public">{{ __('Account type') }}</label><select class="input-public" name="customer_type" x-model="type"><option value="INDIVIDUAL">{{ __('Individual') }}</option><option value="COMPANY">{{ __('Company') }}</option></select></div>
        <div class="sm:col-span-2"><label class="label-public">{{ __('Name') }}</label><input class="input-public" name="name" value="{{ old('name') }}" required></div>
        <div class="sm:col-span-2" x-show="type==='COMPANY'"><label class="label-public">{{ __('Company name') }}</label><input class="input-public" name="company_name" value="{{ old('company_name') }}"></div>
        <div><label class="label-public">{{ __('Email') }}</label><input class="input-public" type="email" name="email" value="{{ old('email') }}" required></div>
        <div><label class="label-public">{{ __('Phone') }}</label><input class="input-public" name="phone" value="{{ old('phone') }}"></div>
        <div><label class="label-public">{{ __('Password') }}</label><input class="input-public" type="password" name="password" required></div>
        <div><label class="label-public">{{ __('Confirm password') }}</label><input class="input-public" type="password" name="password_confirmation" required></div>
    </div>
    <button class="btn-primary mt-6 w-full">{{ __('Register') }}</button>
    <p class="mt-4 text-center text-sm"><a href="{{ route('portal.login') }}">{{ __('Already registered? Login') }}</a></p>
</form>
</body>
</html>
