@php
    $flashPayload = null;

    if (session()->has('status')) {
        $flashPayload = [
            'type' => 'success',
            'message' => (string) session('status'),
        ];
    } elseif (isset($errors) && $errors->any()) {
        $flashPayload = [
            'type' => 'error',
            'message' => $errors->all() ? implode("\n", $errors->all()) : (string) $errors->first(),
        ];
    }
@endphp
@if ($flashPayload)
    <script type="application/json" id="app-flash-data">@json($flashPayload)</script>
@endif
