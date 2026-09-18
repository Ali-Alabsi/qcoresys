<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Allowed page sizes for list pagination.
     *
     * @var list<int>
     */
    protected const PER_PAGE_OPTIONS = [10, 20, 30, 50];

    protected function perPage(Request $request, int $default = 20): int
    {
        $value = (int) $request->integer('per_page', $default);

        return in_array($value, self::PER_PAGE_OPTIONS, true) ? $value : $default;
    }
}
