<?php

namespace Tests\Feature;

use App\Enums\ExchangeRateType;
use App\Enums\JournalStatus;
use App\Exceptions\DomainException;
use App\Models\Account;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\ExchangeRateService;
use App\Services\JournalEntryService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyExchangeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Currency $usd;

    private Currency $sar;

    private Currency $yer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
        $this->usd = Currency::query()->where('code', 'USD')->firstOrFail();
        $this->sar = Currency::query()->where('code', 'SAR')->firstOrFail();
        $this->yer = Currency::query()->where('code', 'YER')->firstOrFail();
    }

    public function test_exchange_rate_lookup_returns_seeded_rate(): void
    {
        $service = app(ExchangeRateService::class);
        $sarToUsd = $service->rate($this->sar->id, $this->usd->id, now()->toDateString());
        $usdToYer = $service->rate($this->usd->id, $this->yer->id, now()->toDateString());
        $sarToYer = $service->rate($this->sar->id, $this->yer->id, now()->toDateString());

        $this->assertEqualsWithDelta(1 / 3.80, $sarToUsd, 0.0000001);
        $this->assertEqualsWithDelta(535.0, $usdToYer, 0.0001);
        $this->assertEqualsWithDelta(140.0, $sarToYer, 0.0001);

        $this->actingAs($this->admin)
            ->getJson(route('admin.exchange-rates.lookup', [
                'from' => $this->sar->id,
                'to' => $this->usd->id,
                'date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertJsonFragment(['rate' => $sarToUsd]);
    }

    public function test_admin_can_create_exchange_rate(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.exchange-rates.store'), [
                'from_currency_id' => $this->sar->id,
                'to_currency_id' => $this->usd->id,
                'rate' => 0.27,
                'rate_date' => now()->addDay()->toDateString(),
                'rate_type' => ExchangeRateType::Manual->value,
                'source' => 'test',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.exchange-rates.index'));

        $this->assertTrue(
            ExchangeRate::query()
                ->where('from_currency_id', $this->sar->id)
                ->where('to_currency_id', $this->usd->id)
                ->whereDate('rate_date', now()->addDay()->toDateString())
                ->where('rate', 0.27)
                ->exists()
        );
    }

    public function test_multi_currency_journal_balances_in_base(): void
    {
        $yerCash = Account::query()->where('account_code', '111103')->firstOrFail();
        $usdCash = Account::query()->where('account_code', '111101')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();
        $yerToUsd = app(ExchangeRateService::class)->rate($this->yer->id, $this->usd->id);
        $journals = app(JournalEntryService::class);

        $seed = $journals->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Fund USD cash',
            'currency_id' => $this->usd->id,
            'exchange_rate' => 1,
        ], [
            ['account_id' => $usdCash->id, 'currency_id' => $this->usd->id, 'exchange_rate' => 1, 'debit' => 10, 'credit' => 0],
            ['account_id' => $capital->id, 'currency_id' => $this->usd->id, 'exchange_rate' => 1, 'debit' => 0, 'credit' => 10],
        ], $this->admin->id);
        $journals->post($seed, $this->admin->id);

        $usdAmount = 10;
        $yerAmount = 5350; // 10 USD * 535

        $this->actingAs($this->admin)
            ->postJson(route('admin.journals.store'), [
                'entry_date' => now()->toDateString(),
                'description' => 'USD cash to YER cash',
                'lines' => [
                    [
                        'account_id' => $yerCash->id,
                        'debit' => $yerAmount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $usdCash->id,
                        'debit' => 0,
                        'credit' => $usdAmount,
                    ],
                ],
            ])
            ->assertOk();

        $entry = JournalEntry::query()->where('description', 'USD cash to YER cash')->firstOrFail();
        $this->assertTrue($entry->is_balanced);
        $this->assertSame(JournalStatus::Posted, $entry->status);
        $this->assertEqualsWithDelta(10.0, (float) $entry->total_debit, 0.01);
        $this->assertEqualsWithDelta(10.0, (float) $entry->total_credit, 0.01);
    }

    public function test_unbalanced_base_journal_cannot_be_posted(): void
    {
        $service = app(JournalEntryService::class);
        $sarCash = Account::query()->where('account_code', '111104')->firstOrFail();
        $usdBank = Account::query()->where('account_code', '111201')->firstOrFail();

        $this->expectException(DomainException::class);

        $service->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Unbalanced FX',
            'currency_id' => $this->usd->id,
            'exchange_rate' => 1,
        ], [
            [
                'account_id' => $usdBank->id,
                'currency_id' => $this->usd->id,
                'exchange_rate' => 1,
                'debit' => 100,
                'credit' => 0,
            ],
            [
                'account_id' => $sarCash->id,
                'currency_id' => $this->sar->id,
                'exchange_rate' => round(1 / 3.80, 10),
                'debit' => 0,
                'credit' => 100,
            ],
        ], $this->admin->id);
    }

    public function test_annual_fx_closing_creates_draft_for_year_end(): void
    {
        $year = (int) now()->year;
        $yearEnd = sprintf('%d-12-31', $year);
        $sarCash = Account::query()->where('account_code', '111104')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();
        $sarToUsd = round(1 / 3.80, 10);

        ExchangeRate::query()->updateOrCreate(
            [
                'from_currency_id' => $this->sar->id,
                'to_currency_id' => $this->usd->id,
                'rate_date' => $yearEnd,
            ],
            [
                'rate' => 0.30,
                'rate_type' => ExchangeRateType::Manual,
                'source' => 'closing',
                'is_active' => true,
            ]
        );

        $journals = app(JournalEntryService::class);

        // Fund SAR cash at 3.80: 380 SAR → 100 USD base
        $opening = $journals->create([
            'entry_date' => sprintf('%d-06-01', $year),
            'description' => 'Fund SAR cash',
            'currency_id' => $this->usd->id,
            'exchange_rate' => 1,
        ], [
            [
                'account_id' => $sarCash->id,
                'currency_id' => $this->sar->id,
                'exchange_rate' => $sarToUsd,
                'debit' => 380,
                'credit' => 0,
            ],
            [
                'account_id' => $capital->id,
                'currency_id' => $this->usd->id,
                'exchange_rate' => 1,
                'debit' => 0,
                'credit' => 100,
            ],
        ], $this->admin->id);
        $journals->post($opening, $this->admin->id);

        $this->actingAs($this->admin)
            ->post(route('admin.exchange-rates.annual-closing'), ['year' => $year])
            ->assertRedirect();

        $closing = JournalEntry::query()
            ->where('reference_type', 'annual_fx_closing')
            ->where('reference_id', $year)
            ->firstOrFail();

        $this->assertSame(JournalStatus::Draft, $closing->status);
        $this->assertSame($yearEnd, $closing->entry_date->toDateString());
        $this->assertTrue($closing->is_balanced);
        $this->assertTrue($closing->lines()->where('account_id', $sarCash->id)->exists());
    }
}
