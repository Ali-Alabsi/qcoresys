@extends('layouts.admin')
@section('title', $journal->entry_no)
@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold">{{ $journal->entry_no }}</h1>
        <p class="text-sm text-slate-500">{{ $journal->description }} · {{ $journal->status->value }} · {{ $journal->entry_date?->toDateString() }}</p>
        @if($journal->reversedEntry)
            <p class="mt-1 text-sm text-slate-600">
                {{ __('Reversal of') }}
                <a class="text-brand-navy underline" href="{{ route('admin.journals.show', $journal->reversedEntry) }}">{{ $journal->reversedEntry->entry_no }}</a>
            </p>
        @endif
        @if($journal->is_reversed && $journal->reversingEntries->isNotEmpty())
            <p class="mt-1 text-sm text-slate-600">
                {{ __('Reversed by') }}
                @foreach($journal->reversingEntries as $reversing)
                    <a class="text-brand-navy underline" href="{{ route('admin.journals.show', $reversing) }}">{{ $reversing->entry_no }}</a>@if(! $loop->last), @endif
                @endforeach
            </p>
        @endif
    </div>
    <div class="flex items-center gap-2">
        @if($journal->is_reversed)
            <span class="text-sm font-bold text-slate-600">{{ __('Journal reversed (status)') }}</span>
        @elseif($journal->reversed_entry_id)
            <span class="text-sm font-bold text-slate-600">{{ __('Reversing journal (label)') }}</span>
        @elseif(auth()->user()->hasPermission('journals.post') && $journal->status->value === 'DRAFT')
            <form method="POST" action="{{ route('admin.journals.post', $journal) }}">@csrf<button class="btn-primary">{{ __('Post') }}</button></form>
        @elseif(auth()->user()->hasPermission('journals.reverse') && $journal->status->value === 'POSTED')
            <form method="POST" action="{{ route('admin.journals.reverse', $journal) }}" onsubmit="return confirm(@json(__('Confirm reverse journal?')))">
                @csrf
                <button type="submit" class="btn-secondary">{{ __('Reverse journal') }}</button>
            </form>
        @endif
    </div>
</div>
<div class="overflow-x-auto rounded-2xl border bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="p-3 text-start">{{ __('Account') }}</th>
                <th class="p-3 text-start">{{ __('Currency') }}</th>
                <th class="p-3 text-start">{{ __('Rate') }}</th>
                <th class="p-3 text-start">{{ __('Description') }}</th>
                <th class="p-3 text-center">{{ __('Debit') }}</th>
                <th class="p-3 text-center">{{ __('Credit') }}</th>
                <th class="p-3 text-center">{{ __('Debit base') }}</th>
                <th class="p-3 text-center">{{ __('Credit base') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach($journal->lines as $line)
                <tr>
                    <td class="p-3">{{ $line->account->account_code }} — {{ $line->account->localized_name }}</td>
                    <td class="p-3">{{ $line->currency?->code }}</td>
                    <td class="p-3 font-mono">{{ rtrim(rtrim(number_format((float) $line->exchange_rate, 10, '.', ''), '0'), '.') }}</td>
                    <td class="p-3">{{ $line->description }}</td>
                    <td class="p-3 text-center">{{ number_format((float) $line->debit, 2) }}</td>
                    <td class="p-3 text-center">{{ number_format((float) $line->credit, 2) }}</td>
                    <td class="p-3 text-center">{{ number_format((float) $line->debit_base, 2) }}</td>
                    <td class="p-3 text-center">{{ number_format((float) $line->credit_base, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-slate-50 font-bold">
            <tr>
                <td colspan="6" class="p-3 text-end">{{ __('Base totals (USD)') }}</td>
                <td class="p-3 text-center">{{ number_format((float) $journal->total_debit, 2) }}</td>
                <td class="p-3 text-center">{{ number_format((float) $journal->total_credit, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-6 rounded-2xl border bg-white p-5">
    <h2 class="mb-3 text-lg font-semibold">{{ __('Supporting documents') }}</h2>
    @forelse($journal->attachments as $attachment)
        @php
            $isImage = in_array(strtolower((string) $attachment->extension), ['png', 'jpg', 'jpeg'], true)
                || str_starts_with((string) $attachment->mime_type, 'image/');
            $viewUrl = route('admin.journals.attachments.view', [$journal, $attachment]);
            $downloadUrl = route('admin.journals.attachments.download', [$journal, $attachment]);
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-2 text-sm last:border-0">
            <span>{{ $attachment->original_name }} <span class="text-slate-400">({{ number_format(($attachment->file_size ?? 0) / 1024, 1) }} KB)</span></span>
            <div class="flex items-center gap-3">
                @if($isImage)
                    <button
                        type="button"
                        class="text-brand-navy underline"
                        data-voucher-preview
                        data-preview-url="{{ $viewUrl }}"
                        data-preview-name="{{ $attachment->original_name }}"
                    >{{ __('View') }}</button>
                @else
                    <a class="text-brand-navy underline" href="{{ $viewUrl }}" target="_blank" rel="noopener" data-no-loading>{{ __('View') }}</a>
                @endif
                <a class="text-brand-navy underline" href="{{ $downloadUrl }}" data-no-loading>{{ __('Download') }}</a>
            </div>
        </div>
    @empty
        <p class="text-sm text-slate-500">{{ __('No attachments.') }}</p>
    @endforelse
</div>

<div id="voucherPreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4" aria-hidden="true">
    <div class="relative max-h-[90vh] max-w-4xl overflow-auto rounded-2xl bg-white p-4 shadow-xl">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h3 id="voucherPreviewTitle" class="text-sm font-semibold text-brand-navy"></h3>
            <button type="button" id="voucherPreviewClose" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="{{ __('Cancel') }}">✕</button>
        </div>
        <img id="voucherPreviewImage" src="" alt="" class="max-h-[75vh] max-w-full rounded-lg object-contain">
    </div>
</div>

@push('scripts')
<script>
(function () {
  const modal = document.getElementById('voucherPreviewModal');
  const title = document.getElementById('voucherPreviewTitle');
  const image = document.getElementById('voucherPreviewImage');
  const closeBtn = document.getElementById('voucherPreviewClose');
  if (!modal) return;

  function openPreview(url, name) {
    title.textContent = name || '';
    image.src = url;
    image.alt = name || '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closePreview() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    image.src = '';
  }

  document.querySelectorAll('[data-voucher-preview]').forEach((btn) => {
    btn.addEventListener('click', () => openPreview(btn.dataset.previewUrl, btn.dataset.previewName));
  });
  closeBtn?.addEventListener('click', closePreview);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closePreview();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closePreview();
  });
})();
</script>
@endpush
@endsection
