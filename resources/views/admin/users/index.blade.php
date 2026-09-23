@extends('layouts.admin')
@section('title', __('Users'))
@section('content')
@php
    $canCreate = auth()->user()->hasPermission('users.create');
    $canUpdate = auth()->user()->hasPermission('users.update');
    $canDelete = auth()->user()->hasPermission('users.delete');
    $openModal = ($canCreate || $canUpdate) && (
        request()->boolean('new')
        || request()->filled('edit')
        || $errors->any()
    );
    $isEditing = $editingUser !== null;
    $formAction = $isEditing
        ? route('admin.users.update', $editingUser)
        : route('admin.users.store');
@endphp
<style>
  #userModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 100;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    box-sizing: border-box;
  }
  #userModal.is-open {
    display: flex;
  }
  #userModalBackdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
  }
  #userModalPanel {
    position: relative;
    z-index: 1;
    width: min(100%, 640px);
    max-height: calc(100vh - 6rem);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 25px 50px rgba(0,0,0,.25);
    border: 1px solid #e2e8f0;
  }
  #userModalPanel .modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
  }
  #userForm {
    padding: 14px 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  #userForm .row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  #userForm .field label {
    display: block;
    margin-bottom: 4px;
    font-size: 11px;
    font-weight: 700;
    color: #475569;
  }
  @media (max-width: 640px) {
    #userForm .row-2 {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex items-center gap-3">
    <h1 class="text-2xl font-bold text-brand-navy">{{ __('Users') }}</h1>
    <span class="rounded-full bg-slate-200 px-3 py-1 font-mono text-xs text-slate-600">{{ $users->total() }}</span>
  </div>
  <div class="flex flex-wrap items-center gap-3">
    @include('admin.shared.per-page', ['paginator' => $users])
    @if($canCreate)
      <button type="button" id="btnOpenUser" class="btn-primary !px-4 !py-2">{{ __('New user') }}</button>
    @endif
  </div>
</div>

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
  <table class="min-w-full text-sm">
    <thead class="bg-slate-50 text-start text-xs uppercase text-slate-500">
      <tr>
        <th class="px-4 py-3 text-start">{{ __('Name') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Username') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Email') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Role') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Active') }}</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse ($users as $user)
        <tr class="hover:bg-slate-50">
          <td class="whitespace-nowrap px-4 py-3 font-medium text-brand-navy">{{ $user->full_name }}</td>
          <td class="whitespace-nowrap px-4 py-3">{{ $user->username }}</td>
          <td class="whitespace-nowrap px-4 py-3">{{ $user->email }}</td>
          <td class="whitespace-nowrap px-4 py-3">{{ $user->roles->first()?->name ?: '—' }}</td>
          <td class="whitespace-nowrap px-4 py-3">{{ $user->is_active ? __('Yes') : __('No') }}</td>
          <td class="whitespace-nowrap px-4 py-3">
            <div class="flex flex-row flex-nowrap items-center justify-end gap-3">
              <a class="font-semibold text-cyan-600" href="{{ route('admin.users.show', $user) }}">{{ __('View') }}</a>
              @if($canUpdate)
                <button
                  type="button"
                  class="btn-edit-user font-semibold text-cyan-600"
                  data-id="{{ $user->id }}"
                  data-name="{{ $user->name }}"
                  data-username="{{ $user->username }}"
                  data-email="{{ $user->email }}"
                  data-phone="{{ $user->phone }}"
                  data-department-id="{{ $user->department_id }}"
                  data-role-id="{{ $user->roles->first()?->id }}"
                  data-is-active="{{ $user->is_active ? '1' : '0' }}"
                  data-update-url="{{ route('admin.users.update', $user) }}"
                >{{ __('Edit') }}</button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="px-4 py-10 text-center text-slate-500">{{ __('No records found.') }}</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@include('admin.shared.pagination', ['paginator' => $users])

@if($canCreate || $canUpdate)
<div id="userModal" class="{{ $openModal ? 'is-open' : '' }}" aria-hidden="{{ $openModal ? 'false' : 'true' }}">
  <div id="userModalBackdrop"></div>
  <div id="userModalPanel" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
    <div class="modal-head">
      <h2 id="userModalTitle" class="text-sm font-bold text-brand-navy">{{ $isEditing ? __('Edit user') : __('New user') }}</h2>
      <button type="button" id="btnCloseUser" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="{{ __('Cancel') }}">✕</button>
    </div>
    <form id="userForm" method="POST" action="{{ $formAction }}">
      @csrf
      <input type="hidden" name="_method" id="userFormMethod" value="{{ $isEditing ? 'PUT' : 'POST' }}">

      <div class="row-2">
        <div class="field">
          <label for="user_name">{{ __('Name') }}</label>
          <input class="input-public !px-2 !py-1.5 text-sm" type="text" id="user_name" name="name" value="{{ old('name', $editingUser?->name) }}" required>
          @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label for="user_username">{{ __('Username') }}</label>
          <input class="input-public !px-2 !py-1.5 text-sm" type="text" id="user_username" name="username" value="{{ old('username', $editingUser?->username) }}" required>
          @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="row-2">
        <div class="field">
          <label for="user_email">{{ __('Email') }}</label>
          <input class="input-public !px-2 !py-1.5 text-sm" type="email" id="user_email" name="email" value="{{ old('email', $editingUser?->email) }}" required>
          @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label for="user_phone">{{ __('Phone') }}</label>
          <input class="input-public !px-2 !py-1.5 text-sm" type="text" id="user_phone" name="phone" value="{{ old('phone', $editingUser?->phone) }}">
          @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="row-2">
        <div class="field">
          <label for="user_department_id">{{ __('Department') }}</label>
          <select class="input-public !px-2 !py-1.5 text-sm" id="user_department_id" name="department_id">
            <option value="">{{ __('Select') }}</option>
            @foreach ($departments as $id => $label)
              <option value="{{ $id }}" @selected((string) old('department_id', $editingUser?->department_id) === (string) $id)>{{ $label }}</option>
            @endforeach
          </select>
          @error('department_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label for="user_role_id">{{ __('Role') }}</label>
          <select class="input-public !px-2 !py-1.5 text-sm" id="user_role_id" name="role_id" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($roles as $id => $label)
              <option value="{{ $id }}" @selected((string) old('role_id', $editingUser?->roles->first()?->id) === (string) $id)>{{ $label }}</option>
            @endforeach
          </select>
          @error('role_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="row-2">
        <div class="field">
          <label for="user_password">{{ __('Password') }}</label>
          <input class="input-public !px-2 !py-1.5 text-sm" type="password" id="user_password" name="password" autocomplete="new-password" @if(!$isEditing) required @endif>
          <p id="userPasswordHint" class="mt-1 text-[11px] text-slate-400 {{ $isEditing ? '' : 'hidden' }}">{{ __('Leave blank to keep the current password.') }}</p>
          @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label for="user_password_confirmation">{{ __('Password confirmation') }}</label>
          <input class="input-public !px-2 !py-1.5 text-sm" type="password" id="user_password_confirmation" name="password_confirmation" autocomplete="new-password">
        </div>
      </div>

      <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="user_is_active" name="is_active" value="1" @checked(old('is_active', $editingUser?->is_active ?? true))>
        {{ __('Active') }}
      </label>

      <div class="mt-1 flex gap-2">
        <button type="submit" class="btn-primary flex-1 !py-2 text-sm">{{ __('Save') }}</button>
        <button type="button" id="btnCancelUser" class="btn-secondary !py-2 text-sm">{{ __('Cancel') }}</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection

@if($canCreate || $canUpdate)
@push('scripts')
<script>
(function () {
  const modal = document.getElementById('userModal');
  if (!modal) return;

  const form = document.getElementById('userForm');
  const title = document.getElementById('userModalTitle');
  const methodInput = document.getElementById('userFormMethod');
  const passwordInput = document.getElementById('user_password');
  const passwordHint = document.getElementById('userPasswordHint');
  const storeUrl = @json(route('admin.users.store'));
  const labels = {
    create: @json(__('New user')),
    edit: @json(__('Edit user')),
  };

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

  function setCreateMode() {
    title.textContent = labels.create;
    form.action = storeUrl;
    methodInput.value = 'POST';
    form.reset();
    document.getElementById('user_is_active').checked = true;
    passwordInput.required = true;
    passwordHint.classList.add('hidden');
  }

  function setEditMode(data) {
    title.textContent = labels.edit;
    form.action = data.updateUrl;
    methodInput.value = 'PUT';
    document.getElementById('user_name').value = data.name || '';
    document.getElementById('user_username').value = data.username || '';
    document.getElementById('user_email').value = data.email || '';
    document.getElementById('user_phone').value = data.phone || '';
    document.getElementById('user_department_id').value = data.departmentId || '';
    document.getElementById('user_role_id').value = data.roleId || '';
    document.getElementById('user_is_active').checked = data.isActive === '1' || data.isActive === true;
    passwordInput.value = '';
    document.getElementById('user_password_confirmation').value = '';
    passwordInput.required = false;
    passwordHint.classList.remove('hidden');
  }

  document.getElementById('btnOpenUser')?.addEventListener('click', () => {
    setCreateMode();
    openModal();
    document.getElementById('user_name')?.focus();
  });

  document.querySelectorAll('.btn-edit-user').forEach((btn) => {
    btn.addEventListener('click', () => {
      setEditMode({
        updateUrl: btn.dataset.updateUrl,
        name: btn.dataset.name,
        username: btn.dataset.username,
        email: btn.dataset.email,
        phone: btn.dataset.phone,
        departmentId: btn.dataset.departmentId,
        roleId: btn.dataset.roleId,
        isActive: btn.dataset.isActive,
      });
      openModal();
      document.getElementById('user_name')?.focus();
    });
  });

  document.getElementById('btnCloseUser')?.addEventListener('click', closeModal);
  document.getElementById('btnCancelUser')?.addEventListener('click', closeModal);
  document.getElementById('userModalBackdrop')?.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open') && !document.documentElement.classList.contains('app-dialog-open')) {
      closeModal();
    }
  });

  if (modal.classList.contains('is-open')) {
    document.body.classList.add('overflow-hidden');
  }
})();
</script>
@endpush
@endif
