@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\AbstractPaginator $paginator */
    $paginator = $paginator ?? null;
@endphp

@if ($paginator && method_exists($paginator, 'total') && $paginator->total() > 0)
  <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-slate-500">
      {{ __('Showing :from to :to of :total results', [
          'from' => $paginator->firstItem(),
          'to' => $paginator->lastItem(),
          'total' => $paginator->total(),
      ]) }}
    </p>

    <div>{{ $paginator->onEachSide(1)->links('pagination::simple-tailwind') }}</div>
  </div>
@elseif ($paginator && method_exists($paginator, 'links'))
  <div class="mt-5">{{ $paginator->links() }}</div>
@endif
