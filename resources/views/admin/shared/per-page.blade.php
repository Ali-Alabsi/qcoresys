@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\AbstractPaginator|null $paginator */
    $paginator = $paginator ?? null;
    $perPageOptions = $perPageOptions ?? [10, 20, 30, 50];
    $currentPerPage = $paginator && method_exists($paginator, 'perPage')
        ? (int) $paginator->perPage()
        : (int) request()->integer('per_page', 20);
    $selectId = $selectId ?? 'per_page_select_top';
@endphp

<form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
  @foreach (request()->except(['page', 'per_page']) as $key => $value)
    @if (is_array($value))
      @foreach ($value as $nested)
        <input type="hidden" name="{{ $key }}[]" value="{{ $nested }}">
      @endforeach
    @else
      <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endif
  @endforeach
  <label for="{{ $selectId }}" class="text-xs font-bold text-slate-500 whitespace-nowrap">{{ __('Per page') }}</label>
  <select
    id="{{ $selectId }}"
    name="per_page"
    class="input-public !w-auto !px-2 !py-1.5 text-sm"
    onchange="this.form.submit()"
  >
    @foreach ($perPageOptions as $option)
      <option value="{{ $option }}" @selected($currentPerPage === (int) $option)>{{ $option }}</option>
    @endforeach
  </select>
</form>
