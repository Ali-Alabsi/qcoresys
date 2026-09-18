<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Database\Seeders\ChartOfAccountsSeeder;
use Database\Seeders\PortfolioProjectSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function index(): View
    {
        return view('admin.setup.index');
    }

    public function accounts(): RedirectResponse
    {
        app(ChartOfAccountsSeeder::class)->run();

        return back()->with('status', __('Chart of accounts initialized.'));
    }

    public function portfolio(): RedirectResponse
    {
        app(PortfolioProjectSeeder::class)->run();

        return back()->with('status', __('Portfolio initialized.'));
    }
}
