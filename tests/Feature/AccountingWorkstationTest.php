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
            ->assertSee('ابحث في الحسابات...', false)
            ->assertSee('حساب جديد', false)
            ->assertSee('id="btnOpenAccount"', false)
            ->assertSee('id="accountModal"', false)
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

    public function test_admin_can_create_leaf_account_under_control_parent(): void
    {
        $parent = Account::query()->where('account_code', '1111')->firstOrFail();
        $usd = \App\Models\Currency::query()->where('code', 'USD')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.create'))
            ->assertRedirect(route('admin.accounts.index', ['new' => 1]));

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.index', ['new' => 1]))
            ->assertOk()
            ->assertSee('id="accountModal" class="is-open"', false)
            ->assertSee('حساب جديد', false);

        $this->actingAs($this->admin)
            ->post(route('admin.accounts.store'), [
                'account_code' => '111199',
                'account_name' => 'Petty Cash USD',
                'account_name_ar' => 'صندوق نثرية دولار',
                'account_type' => AccountType::Asset->value,
                'parent_id' => $parent->id,
                'currency_id' => $usd->id,
                'is_cash_account' => '1',
                'is_bank_account' => '0',
            ])
            ->assertRedirect(route('admin.accounts.index'));

        $account = Account::query()->where('account_code', '111199')->firstOrFail();
        $this->assertSame($parent->id, $account->parent_id);
        $this->assertTrue($account->allow_posting);
        $this->assertFalse($account->is_control_account);
        $this->assertTrue($account->is_cash_account);
        $this->assertSame(NormalBalance::Debit, $account->normal_balance);
        $this->assertSame($parent->account_level + 1, $account->account_level);

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertSee('111199', false)
            ->assertSee('صندوق نثرية دولار', false);
    }

    public function test_duplicate_account_code_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.accounts.index', ['new' => 1]))
            ->post(route('admin.accounts.store'), [
                'account_code' => '111101',
                'account_name' => 'Duplicate',
                'account_type' => AccountType::Asset->value,
            ])
            ->assertRedirect(route('admin.accounts.index', ['new' => 1]))
            ->assertSessionHasErrors('account_code');
    }

    public function test_user_with_accounts_view_only_cannot_create_account(): void
    {
        $accountant = User::factory()->create([
            'email' => 'accountant.view@qcoresys.test',
            'is_active' => true,
        ]);
        $accountant->assignRole('ACCOUNTANT');

        $this->actingAs($accountant)
            ->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertDontSee('id="btnOpenAccount"', false)
            ->assertDontSee('id="accountModal"', false);

        $this->actingAs($accountant)
            ->get(route('admin.accounts.create'))
            ->assertForbidden();

        $this->actingAs($accountant)
            ->post(route('admin.accounts.store'), [
                'account_code' => '111198',
                'account_name' => 'Forbidden account',
                'account_type' => AccountType::Asset->value,
            ])
            ->assertForbidden();
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
