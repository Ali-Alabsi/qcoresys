@extends('layouts.admin')
@section('title', __('Financial operations'))
@section('content')
@php
    $journalPayload = [
        'accounts' => $accountsPayload ?? [],
        'storeUrl' => route('admin.journals.store'),
        'nextRef' => $nextRef ?? '',
        'csrf' => csrf_token(),
        'operationsUrl' => route('admin.journals.index'),
        'openOnLoad' => request()->boolean('new'),
    ];
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <h1 class="text-2xl font-bold text-brand-navy">{{ __('Financial operations') }}</h1>
  <div class="flex flex-wrap items-center gap-3">
    @isset($paginator)
      @include('admin.shared.per-page', ['paginator' => $paginator, 'selectId' => 'journals_per_page'])
    @endisset
    @if(auth()->user()->hasPermission('journals.create'))
      <button type="button" id="btnOpenJournal" class="btn-primary !px-4 !py-2">{{ __('Post journal') }}</button>
    @endif
  </div>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="border-b border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-brand-navy">
    سجل القيود المرحلة
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-xs uppercase text-slate-500">
        <tr>
          <th class="px-4 py-3 text-start">المرجع</th>
          <th class="px-4 py-3 text-start">التاريخ</th>
          <th class="px-4 py-3 text-start">البيان</th>
          <th class="px-4 py-3 text-start">الأطراف المحاسبية</th>
          <th class="px-4 py-3 text-end">المبلغ الإجمالي</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse ($entries as $ent)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 font-mono text-xs font-bold text-cyan-700">
              <a href="{{ route('admin.journals.show', $ent['id']) }}" class="hover:underline">{{ $ent['ref'] }}</a>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $ent['date'] }}</td>
            <td class="px-4 py-3">{{ $ent['desc'] }}</td>
            <td class="px-4 py-3">
              <ul class="space-y-1 text-xs">
                @foreach ($ent['lines'] as $line)
                  @if ($line['debit'] > 0)
                    <li><span class="font-bold text-cyan-700">مدين:</span> {{ $line['account_name'] }} (<span class="font-mono">{{ number_format($line['debit'], 2) }}</span>)</li>
                  @else
                    <li><span class="font-bold text-emerald-700">دائن:</span> {{ $line['account_name'] }} (<span class="font-mono">{{ number_format($line['credit'], 2) }}</span>)</li>
                  @endif
                @endforeach
              </ul>
            </td>
            <td class="px-4 py-3 text-end font-mono text-xs font-bold">{{ number_format($ent['total'], 2) }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">لا توجد قيود مرحلة حتى الآن.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if (isset($paginator) && method_exists($paginator, 'links'))
    @include('admin.shared.pagination', ['paginator' => $paginator])
  @endif
</div>
@endsection

@push('scripts')
<style>
  #journalModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 100;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    box-sizing: border-box;
  }
  #journalModal.is-open {
    display: flex;
  }
  #journalModalBackdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
  }
  #journalModalPanel {
    position: relative;
    z-index: 1;
    width: min(100%, 840px);
    max-height: calc(100vh - 6rem);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 25px 50px rgba(0,0,0,.25);
    border: 1px solid #e2e8f0;
  }
  #journalModalPanel .modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
  }
  #journalForm {
    padding: 14px 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  #journalForm .row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  #journalForm .field label {
    display: block;
    margin-bottom: 4px;
    font-size: 11px;
    font-weight: 700;
    color: #475569;
  }
  #journalForm .entry-line {
    display: grid;
    grid-template-columns: minmax(0, 2.2fr) minmax(0, 1fr) minmax(0, 1fr) 28px;
    gap: 8px;
    align-items: center;
  }
  #journalForm .totals {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    align-items: center;
    background: #f8fafc;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 12px;
    font-weight: 700;
  }
</style>

{{-- Wider centered popup with paired fields and top/bottom margin --}}
<div id="journalModal" aria-hidden="true">
  <div id="journalModalBackdrop"></div>
  <div id="journalModalPanel">
    <div class="modal-head">
      <h2 class="text-sm font-bold text-brand-navy">قيد محاسبي جديد</h2>
      <button type="button" id="btnCloseJournal" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="إغلاق">✕</button>
    </div>
    <form id="journalForm">
      <div class="row-2">
        <div class="field">
          <label for="entryDate">التاريخ</label>
          <input type="date" class="input-public !px-2 !py-1.5 text-sm" id="entryDate" required>
        </div>
        <div class="field">
          <label for="entryRef">المرجع</label>
          <input type="text" class="input-public !px-2 !py-1.5 font-mono text-sm" id="entryRef" value="{{ $nextRef }}" readonly>
        </div>
      </div>
      <div class="field">
        <label for="entryDesc">البيان</label>
        <input type="text" class="input-public !px-2 !py-1.5 text-sm" id="entryDesc" placeholder="وصف القيد..." required>
      </div>

      <div>
        <div style="margin-bottom:6px;font-size:11px;font-weight:700;color:#475569">المدين / الدائن</div>
        <div id="entryLinesContainer" style="display:flex;flex-direction:column;gap:8px">
          @foreach ([1, 2] as $_)
          <div class="entry-line">
            <select class="input-public account-select !px-2 !py-1.5 text-xs" required></select>
            <input type="number" step="any" class="input-public debit-input !px-2 !py-1.5 font-mono text-xs" placeholder="مدين" value="0">
            <input type="number" step="any" class="input-public credit-input !px-2 !py-1.5 font-mono text-xs" placeholder="دائن" value="0">
            <button type="button" class="btn-remove-row text-sm font-bold text-red-500">✕</button>
          </div>
          @endforeach
        </div>
        <button type="button" id="btnAddRow" class="mt-2 w-full rounded-lg border border-dashed border-slate-300 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50">+ طرف جديد</button>
      </div>

      <div class="totals">
        <span>مدين: <span id="lblTotalDebit" class="font-mono text-cyan-700">0.00</span></span>
        <span style="text-align:end">دائن: <span id="lblTotalCredit" class="font-mono text-emerald-700">0.00</span></span>
      </div>
      <div style="text-align:center">
        <span id="lblBalanceStatus" class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">أدخل المبالغ</span>
      </div>

      <button type="submit" class="btn-primary w-full !py-2 text-sm" id="btnPostEntry" disabled>
        ترحيل القيد
      </button>
    </form>
  </div>
</div>

<script>
(function () {
  const config = @json($journalPayload);
  let accountsData = (config.accounts || []).slice();
  const modal = document.getElementById('journalModal');

  function openModal() {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  }

  function bindLineEvents(root) {
    root.querySelectorAll('.account-select, .debit-input, .credit-input').forEach(el => {
      el.addEventListener('input', calculateJournalTotals);
      el.addEventListener('change', calculateJournalTotals);
    });
    root.querySelectorAll('.btn-remove-row').forEach(btn => {
      btn.addEventListener('click', () => removeEntryRow(btn));
    });
  }

  function populateAccountDropdowns() {
    document.querySelectorAll('.account-select').forEach(select => {
      const currentVal = select.value;
      select.innerHTML = '<option value="">-- حساب --</option>';
      accountsData.forEach(acc => {
        const opt = document.createElement('option');
        opt.value = acc.code;
        opt.textContent = acc.type_label
          ? `${acc.code} - ${acc.name} (${acc.type_label})`
          : `${acc.code} - ${acc.name}`;
        select.appendChild(opt);
      });
      select.value = currentVal;
    });
  }

  function addEntryRow() {
    const container = document.getElementById('entryLinesContainer');
    const row = document.createElement('div');
    row.className = 'entry-line';
    row.innerHTML = `
      <select class="input-public account-select !px-2 !py-1.5 text-xs" required></select>
      <input type="number" step="any" class="input-public debit-input !px-2 !py-1.5 font-mono text-xs" placeholder="مدين" value="0">
      <input type="number" step="any" class="input-public credit-input !px-2 !py-1.5 font-mono text-xs" placeholder="دائن" value="0">
      <button type="button" class="btn-remove-row text-sm font-bold text-red-500">✕</button>`;
    container.appendChild(row);
    bindLineEvents(row);
    populateAccountDropdowns();
    calculateJournalTotals();
  }

  function notify(type, message, onClose) {
    if (window.AppDialog && typeof window.AppDialog[type] === 'function') {
      window.AppDialog[type](message, onClose);
      return;
    }
    window.alert(message);
    if (typeof onClose === 'function') onClose();
  }

  function removeEntryRow(btn) {
    if (document.querySelectorAll('.entry-line').length <= 2) {
      notify('warning', 'يجب طرفان على الأقل.');
      return;
    }
    btn.closest('.entry-line').remove();
    calculateJournalTotals();
  }

  function calculateJournalTotals() {
    let totalDebit = 0;
    let totalCredit = 0;
    document.querySelectorAll('.entry-line').forEach(row => {
      totalDebit += parseFloat(row.querySelector('.debit-input').value) || 0;
      totalCredit += parseFloat(row.querySelector('.credit-input').value) || 0;
    });
    document.getElementById('lblTotalDebit').innerText = totalDebit.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('lblTotalCredit').innerText = totalCredit.toLocaleString(undefined, {minimumFractionDigits: 2});
    const btnPost = document.getElementById('btnPostEntry');
    const statusBadge = document.getElementById('lblBalanceStatus');
    if (Math.abs(totalDebit - totalCredit) < 0.001 && totalDebit > 0) {
      statusBadge.className = 'inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700';
      statusBadge.innerText = 'متوازن';
      btnPost.disabled = false;
    } else {
      statusBadge.className = 'inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700';
      statusBadge.innerText = totalDebit === 0 ? 'أدخل المبالغ' : 'غير متوازن';
      btnPost.disabled = true;
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('entryDate').valueAsDate = new Date();
    bindLineEvents(document);
    populateAccountDropdowns();
    calculateJournalTotals();

    document.getElementById('btnOpenJournal')?.addEventListener('click', openModal);
    document.getElementById('btnCloseJournal')?.addEventListener('click', closeModal);
    document.getElementById('journalModalBackdrop')?.addEventListener('click', closeModal);
    document.getElementById('btnAddRow')?.addEventListener('click', addEntryRow);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('is-open') && !document.documentElement.classList.contains('app-dialog-open')) {
        closeModal();
      }
    });

    if (config.openOnLoad) openModal();

    document.getElementById('journalForm').addEventListener('submit', async function (e) {
      e.preventDefault();
      let lines = [];
      let isValid = true;
      document.querySelectorAll('.entry-line').forEach(row => {
        const accCode = row.querySelector('.account-select').value;
        const debit = parseFloat(row.querySelector('.debit-input').value) || 0;
        const credit = parseFloat(row.querySelector('.credit-input').value) || 0;
        if (!accCode) { notify('warning', 'اختر الحساب.'); isValid = false; return; }
        if (debit > 0 && credit > 0) { notify('warning', 'لا مدين ودائن معاً.'); isValid = false; return; }
        if (debit > 0 || credit > 0) lines.push({ account_code: accCode, debit, credit });
      });
      if (!isValid || lines.length < 2) {
        if (isValid) notify('warning', 'طرفان على الأقل.');
        return;
      }

      const btn = document.getElementById('btnPostEntry');
      btn.disabled = true;
      const original = btn.innerHTML;
      btn.innerHTML = '...';

      try {
        const response = await fetch(config.storeUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': config.csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({
            entry_date: document.getElementById('entryDate').value,
            description: document.getElementById('entryDesc').value,
            lines,
          }),
        });

        let data = {};
        try {
          data = await response.json();
        } catch (_) {
          data = {};
        }

        if (!response.ok) {
          const validation = data.errors
            ? Object.values(data.errors).flat().filter(Boolean).join('\n')
            : '';
          throw new Error(data.message || validation || 'فشل الترحيل.');
        }

        notify('success', data.message || 'تم ترحيل القيد بنجاح.', () => {
          window.location.href = config.operationsUrl;
        });
      } catch (err) {
        notify('error', err.message || 'حدث خطأ.');
        btn.innerHTML = original;
        calculateJournalTotals();
      }
    });
  });
})();
</script>
@endpush
