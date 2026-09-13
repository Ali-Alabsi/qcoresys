@extends('layouts.admin')
@section('title', __('Accounts'))
@section('content')
<style>
  .acc-type { font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 700; }
  .type-الصناديق { background: #E0F2FE; color: #0369A1; }
  .type-العملاء { background: #FEF9C3; color: #854D0E; }
  .type-الشركاء { background: #FEF3C7; color: #92400E; }
  .type-الإيرادات { background: #DCFCE7; color: #166534; }
  .type-المصروفات { background: #F3E8FF; color: #6B21A8; }
  .type-أخرى { background: #F1F5F9; color: #475569; }
</style>

<div class="mb-6 flex items-center justify-between gap-4">
  <h1 class="text-2xl font-bold text-brand-navy">{{ __('Accounts') }}</h1>
  <span class="rounded-full bg-slate-200 px-3 py-1 font-mono text-xs text-slate-600">{{ count($accounts) }} حساب</span>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="border-b border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-brand-navy">
    دليل الحسابات
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-xs uppercase text-slate-500">
        <tr>
          <th class="px-4 py-3 text-start">رقم الحساب</th>
          <th class="px-4 py-3 text-start">اسم الحساب</th>
          <th class="px-4 py-3 text-start">التصنيف</th>
          <th class="px-4 py-3 text-end">الرصيد الحالي</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse ($accounts as $acc)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 font-mono text-xs font-bold text-slate-500">{{ $acc['code'] }}</td>
            <td class="px-4 py-3 font-medium">{{ $acc['name'] }}</td>
            <td class="px-4 py-3"><span class="acc-type type-{{ $acc['type'] }}">{{ $acc['type'] }}</span></td>
            <td class="px-4 py-3 text-end font-mono text-xs font-bold {{ $acc['balance'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
              {{ number_format($acc['balance'], 2) }}
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">لا توجد حسابات نشطة.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
