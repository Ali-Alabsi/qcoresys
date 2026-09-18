@extends('layouts.admin')
@section('title', __('Setup'))
@section('content')
<h1 class="mb-2 text-2xl font-bold text-brand-navy">{{ __('Setup') }}</h1>
<p class="mb-6 text-sm text-slate-500">{{ __('Initialize default application data for accounts and portfolio.') }}</p>

<div class="grid gap-5 sm:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-brand-navy">{{ __('Initialize accounts') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Rebuild the default chart of accounts and related expense categories.') }}</p>
        <form method="POST" action="{{ route('admin.setup.accounts') }}" class="mt-5" data-setup-confirm data-confirm-message="{{ __('Are you sure you want to initialize the chart of accounts?') }}">
            @csrf
            <button type="submit" class="btn-primary">{{ __('Initialize accounts') }}</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-brand-navy">{{ __('Initialize portfolio') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Seed sample portfolio projects linked to existing services.') }}</p>
        <form method="POST" action="{{ route('admin.setup.portfolio') }}" class="mt-5" data-setup-confirm data-confirm-message="{{ __('Are you sure you want to initialize the portfolio?') }}">
            @csrf
            <button type="submit" class="btn-primary">{{ __('Initialize portfolio') }}</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const forms = document.querySelectorAll('form[data-setup-confirm]');
  forms.forEach((form) => {
    form.addEventListener('submit', async (event) => {
      if (form.dataset.confirmed === '1') {
        form.dataset.confirmed = '0';
        return;
      }

      event.preventDefault();

      const message = form.getAttribute('data-confirm-message') || '';
      const confirmed = window.AppDialog && typeof window.AppDialog.confirm === 'function'
        ? await window.AppDialog.confirm(message)
        : window.confirm(message);

      if (!confirmed) {
        return;
      }

      form.dataset.confirmed = '1';
      form.requestSubmit();
    });
  });
})();
</script>
@endpush
