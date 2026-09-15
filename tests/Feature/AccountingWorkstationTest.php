<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\JournalStatus;
use App\Enums\NormalBalance;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingWorkstationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
    }

    public function test_accounting_pages_render_and_journal_opens_as_popup(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.journals.create'))
            ->assertRedirect(route('admin.journals.index', ['new' => 1]));

        $this->actingAs($this->admin)
            ->get(route('admin.journals.index', ['new' => 1]))
            ->assertOk()
            ->assertSee('سجل القيود المرحلة', false)
            ->assertSee('قيد محاسبي جديد', false);

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertSee('دليل الحسابات', false)
            ->assertSee('111101', false)
            ->assertSee('نوع الحساب', false)
            ->assertSee('الكل', false)
            ->assertSee('أصول', false)
            ->assertSee('خصوم', false)
            ->assertSee('حقوق ملكية', false)
            ->assertSee('إيرادات', false)
            ->assertSee('مصروفات', false)
            ->assertSee("setFilter('ASSET')", false)
            ->assertSee("setFilter('LIABILITY')", false)
            ->assertSee('is-active', false)
            ->assertSee("filter === 'ASSET'", false)
            ->assertSee('3211', false)
            ->assertSee('3212', false)
            ->assertSee('3311', false)
            ->assertSee('3312', false)
            ->assertSee('3411', false)
            ->assertSee('3412', false)
            ->assertSee('2121', false)
            ->assertSee('2122', false)
            ->assertSee('مسحوبات راس مال محمد المحفدي - دولار امريكي', false)
            ->assertSee('مسحوبات راس مال علي نبيل - دولار امريكي', false)
            ->assertSee('صافي ربح محمد المحفدي - دولار امريكي', false)
            ->assertSee('صافي ربح علي نبيل - دولار امريكي', false)
            ->assertSee('أرباح محتجزة / أرباح مرحلة محمد المحفدي - دولار امريكي', false)
            ->assertSee('أرباح محتجزة / أرباح مرحلة علي نبيل - دولار امريكي', false)
            ->assertSee('توزيعات أرباح مستحقة محمد المحفدي - دولار امريكي', false)
            ->assertSee('توزيعات أرباح مستحقة علي نبيل - دولار امريكي', false);
    }

    public function test_partner_leaf_accounts_are_postable_usd(): void
    {
        $expected = [
            '2121' => ['توزيعات أرباح مستحقة محمد المحفدي - دولار امريكي', NormalBalance::Credit, AccountType::Liability],
            '2122' => ['توزيعات أرباح مستحقة علي نبيل - دولار امريكي', NormalBalance::Credit, AccountType::Liability],
            '3211' => ['مسحوبات راس مال محمد المحفدي - دولار امريكي', NormalBalance::Debit, AccountType::Equity],
            '3212' => ['مسحوبات راس مال علي نبيل - دولار امريكي', NormalBalance::Debit, AccountType::Equity],
            '3311' => ['صافي ربح محمد المحفدي - دولار امريكي', NormalBalance::Credit, AccountType::Equity],
            '3312' => ['صافي ربح علي نبيل - دولار امريكي', NormalBalance::Credit, AccountType::Equity],
            '3411' => ['أرباح محتجزة / أرباح مرحلة محمد المحفدي - دولار امريكي', NormalBalance::Credit, AccountType::Equity],
            '3412' => ['أرباح محتجزة / أرباح مرحلة علي نبيل - دولار امريكي', NormalBalance::Credit, AccountType::Equity],
        ];

        foreach ($expected as $code => [$nameAr, $normal, $type]) {
            $account = Account::query()->where('account_code', $code)->firstOrFail();

            $this->assertTrue($account->is_active, $code);
            $this->assertTrue($account->allow_posting, $code);
            $this->assertSame($type, $account->account_type, $code);
            $this->assertSame($normal, $account->normal_balance, $code);
            $this->assertSame('USD', $account->currency?->code, $code);
            $this->assertSame($nameAr, $account->account_name_ar, $code);
        }

        foreach (['1121', '1122', '1123'] as $code) {
            $account = Account::query()->where('account_code', $code)->firstOrFail();
            $this->assertTrue($account->is_control_account, $code);
            $this->assertFalse($account->allow_posting, $code);
        }
    }

    public function test_posting_balanced_journal_persists_and_updates_balances(): void
    {
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.journals.store'), [
                'entry_date' => now()->toDateString(),
                'description' => 'تغذية رأس المال نقداً',
                'lines' => [
                    ['account_code' => '111101', 'debit' => 500, 'credit' => 0],
                    ['account_code' => '3111', 'debit' => 0, 'credit' => 500],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('entry.total', 500)
            ->assertJsonPath('entry.desc', 'تغذية رأس المال نقداً');

        $entry = JournalEntry::query()->latest('id')->firstOrFail();
        $this->assertSame(JournalStatus::Posted, $entry->status);
        $this->assertCount(2, $entry->lines);
        $this->assertEqualsWithDelta(500.0, (float) $cash->fresh()->current_balance, 0.01);
        $this->assertEqualsWithDelta(500.0, (float) $capital->fresh()->current_balance, 0.01);

        $this->actingAs($this->admin)
            ->get(route('admin.journals.index'))
            ->assertOk()
            ->assertSee($entry->entry_no, false)
            ->assertSee('تغذية رأس المال نقداً', false);

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertSee('500.00', false);
    }

    public function test_unbalanced_journal_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.journals.store'), [
                'entry_date' => now()->toDateString(),
                'description' => 'قيد غير متوازن',
                'lines' => [
                    ['account_code' => '111101', 'debit' => 100, 'credit' => 0],
                    ['account_code' => '3111', 'debit' => 0, 'credit' => 50],
                ],
            ])
            ->assertStatus(422);

        $this->assertSame(0, JournalEntry::query()->count());
    }
}
