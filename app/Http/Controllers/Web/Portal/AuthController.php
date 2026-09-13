<?php

namespace App\Http\Controllers\Web\Portal;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('portal.auth.login');
    }

    public function showRegister(): View
    {
        return view('portal.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'customer_type' => ['required', 'in:INDIVIDUAL,COMPANY'],
            'company_name' => ['nullable', 'required_if:customer_type,COMPANY', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => true,
            ]);

            Customer::create([
                'portal_user_id' => $user->id,
                'customer_type' => CustomerType::from($data['customer_type']),
                'name' => $data['name'],
                'company_name' => $data['company_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => CustomerStatus::Active,
                'customer_source' => 'PORTAL',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('portal.dashboard')->with('status', __('Welcome to your customer portal.'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('Invalid credentials.')])->onlyInput('email');
        }

        $request->session()->regenerate();
        if (! $request->user()->portalCustomer()->exists()) {
            Auth::logout();
            return back()->withErrors(['email' => __('This account has no customer profile.')]);
        }

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
