@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-brand-navy">{{ $title }}</h1>
        <p class="text-sm text-slate-500">{{ $user->email }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if(auth()->user()->hasPermission('users.update'))
            <a class="btn-secondary !px-4 !py-2" href="{{ route('admin.users.index', ['edit' => $user->id]) }}">{{ __('Edit') }}</a>
        @endif
        @if(auth()->user()->hasPermission('users.delete') && auth()->id() !== $user->id)
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm(@json(__('Delete this user?')))">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary !px-4 !py-2 text-red-600">{{ __('Delete') }}</button>
            </form>
        @endif
        <a class="btn-secondary !px-4 !py-2" href="{{ route('admin.users.index') }}">{{ __('Back') }}</a>
    </div>
</div>

@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

<div class="mb-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Username') }}</p>
        <p class="mt-2 text-sm font-medium text-slate-800">{{ $user->username ?: '—' }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Phone') }}</p>
        <p class="mt-2 text-sm font-medium text-slate-800">{{ $user->phone ?: '—' }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Department') }}</p>
        <p class="mt-2 text-sm font-medium text-slate-800">{{ $user->department?->name ?: '—' }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Role') }}</p>
        <p class="mt-2 text-sm font-medium text-slate-800">{{ $role?->name ?: '—' }}</p>
        @if($role?->description)
            <p class="mt-1 text-xs text-slate-500">{{ $role->description }}</p>
        @endif
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Active') }}</p>
        <p class="mt-2 text-sm font-medium text-slate-800">{{ $user->is_active ? __('Yes') : __('No') }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Last login') }}</p>
        <p class="mt-2 text-sm font-medium text-slate-800">{{ $user->last_login_at?->format('Y-m-d H:i') ?: '—' }}</p>
    </div>
</div>

<div class="rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('Permissions') }}</h2>
    <p class="mb-4 text-sm text-slate-500">{{ __('Permissions are inherited from the assigned role.') }}</p>
    @if($permissions->isEmpty())
        <p class="text-sm text-slate-500">{{ __('No permissions assigned.') }}</p>
    @else
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($permissions as $permission)
                <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    <span class="font-medium">{{ $permission->name }}</span>
                    <span class="block text-xs text-slate-400">{{ $permission->code }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
