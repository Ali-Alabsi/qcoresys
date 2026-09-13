<?php

namespace Tests\Feature;

use App\Exceptions\DomainException;
use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use App\Services\AccountBalanceService;
use App\Services\JournalEntryService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountBalanceCheckTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Currency $usd;

    private JournalEntryService $journals;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
        $this->usd = Currency::query()->where('code', 'USD')->firstOrFail();
        $this->journals = app(JournalEntryService::class);
    }

    private function fundCash(float $amount = 100): void
    {
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();

        $entry = $this->journals->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Seed cash for tests',
            'currency_id' => $this->usd->id,
            'exchange_rate' => 1,
        ], [
            ['account_id' => $cash->id, 'debit' => $amount, 'credit' => 0],
            ['account_id' => $capital->id, 'debit' => 0, 'credit' => $amount],
        ], $this->admin->id);

        $this->journals->post($entry, $this->admin->id);
    }

    public function test_insufficient_cash_balance_rejects_create(): void
    {
        $this->fundCash(100);

        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $bank = Account::query()->where('account_code', '111201')->firstOrFail();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Insufficient balance|111101/i');

        $this->journals->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Overdraw cash',
            'currency_id' => $this->usd->id,
            'exchange_rate' => 1,
        ], [
            ['account_id' => $bank->id, 'debit' => 150, 'credit' => 0],
            ['account_id' => $cash->id, 'debit' => 0, 'credit' => 150],
        ], $this->admin->id);
    }

    public function test_sufficient_cash_balance_allows_transfer_and_updates_current_balance(): void
    {
        $this->fundCash(100);

        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $bank = Account::query()->where('account_code', '111201')->firstOrFail();

        $entry = $this->journals->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Cash to bank 40',
            'currency_id' => $this->usd->id,
            'exchange_rate' => 1,
        ], [
            ['account_id' => $bank->id, 'debit' => 40, 'credit' => 0],
            ['account_id' => $cash->id, 'debit' => 0, 'credit' => 40],
        ], $this->admin->id);

        $entry = $this->journals->post($entry, $this->admin->id);

        $this->assertSame('POSTED', $entry->status->value);
        $this->assertEqualsWithDelta(60.0, (float) $cash->fresh()->current_balance, 0.01);
        $this->assertEqualsWithDelta(40.0, (float) $bank->fresh()->current_balance, 0.01);
    }

    public function test_expense_fails_when_bank_has_no_funds(): void
    {
        $expense = Account::query()->where('account_code', '5111')->firstOrFail();
        $bank = Account::query()->where('account_code', '111201')->firstOrFail();

        $this->expectException(DomainException::class);

        $this->journals->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Expense without bank funds',
            'currency_id' => $this->usd->id,
            'exchange_rate' => 1,
        ], [
            ['account_id' => $expense->id, 'debit' => 25, 'credit' => 0],
            ['account_id' => $bank->id, 'debit' => 0, 'credit' => 25],
        ], $this->admin->id);
    }

    public function test_account_balance_service_matches_zero_opening_cash(): void
    {
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $balances = app(AccountBalanceService::class)->balanceAsOf($cash, now()->toDateString());

        $this->assertEqualsWithDelta(0.0, $balances['foreign'], 0.01);
        $this->assertEqualsWithDelta(0.0, $balances['base'], 0.01);
    }
}
