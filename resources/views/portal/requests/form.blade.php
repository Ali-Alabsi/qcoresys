@extends('layouts.portal')
@section('title', __('New request'))
@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('Submit a request') }}</h1>
<form method="POST" action="{{ route('portal.requests.store') }}" class="max-w-3xl rounded-2xl border bg-white p-6">@csrf
<div class="grid gap-5 sm:grid-cols-2">
    <div><label class="label-public">{{ __('Request type') }}</label><select class="input-public" name="request_type" required>@foreach($types as $value=>$label)<option value="{{ $value }}" @selected(old('request_type')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div><label class="label-public">{{ __('Service') }}</label><select class="input-public" name="service_id"><option value="">{{ __('Not sure') }}</option>@foreach($services as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></div>
    <div class="sm:col-span-2"><label class="label-public">{{ __('Subject') }}</label><input class="input-public" name="subject" value="{{ old('subject') }}" required></div>
    <div class="sm:col-span-2"><label class="label-public">{{ __('Description and requirements') }}</label><textarea class="input-public" name="description" rows="6" required>{{ old('description') }}</textarea></div>
    <div><label class="label-public">{{ __('Priority') }}</label><select class="input-public" name="priority">@foreach($priorities as $value=>$label)<option value="{{ $value }}" @selected(old('priority','MEDIUM')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div><label class="label-public">{{ __('Estimated budget (USD)') }}</label><input class="input-public" type="number" min="0" step="0.01" name="estimated_budget" value="{{ old('estimated_budget') }}"></div>
</div>
<button class="btn-primary mt-6">{{ __('Submit request') }}</button>
</form>
@endsection
