<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\JournalStatus;
use App\Enums\NormalBalance;
use App\Models\Account;
use App\Models\Currency;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\JournalEntryService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postJournal(array $payload)
    {
        $payload['attachments'] = $payload['attachments'] ?? [
            UploadedFile::fake()->image('voucher.jpg'),
        ];

        return $this->actingAs($this->admin)
            ->post(route('admin.journals.store'), $payload, [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]);
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
            ->assertSee('قيد محاسبي جديد', false)
            ->assertSee('المؤيدات', false)
            ->assertSee('id="entryAttachments"', false);

        $accountsIndex = $this->actingAs($this->admin)
            ->get(route('admin.accounts.index', ['per_page' => 50]))
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
            ->assertSee('name="q"', false)
            ->assertSee('type=ASSET', false)
            ->assertSee('type=LIABILITY', false)
            ->assertSee('is-active', false)
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

        $accountsIndex->assertSee(__('Per page'), false);

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.index', ['type' => AccountType::Asset->value, 'per_page' => 50]))
            ->assertOk()
            ->assertSee('111101', false)
            ->assertDontSee('>3111<', false);

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.index', ['q' => '111101']))
            ->assertOk()
            ->assertSee('111101', false)
            ->assertDontSee('111102', false);
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

        $response = $this->postJournal([
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
        $this->postJournal([
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

    public function test_account_row_links_to_ledger_and_exports_work(): void
    {
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();

        $this->postJournal([
                'entry_date' => now()->toDateString(),
                'description' => 'قيد لكشف الحساب',
                'lines' => [
                    ['account_code' => '111101', 'debit' => 250, 'credit' => 0],
                    ['account_code' => '3111', 'debit' => 0, 'credit' => 250],
                ],
            ])
            ->assertOk();

        $entry = JournalEntry::query()->latest('id')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertSee(route('admin.accounts.ledger', $cash), false);

        $ledgerResponse = $this->actingAs($this->admin)
            ->get(route('admin.accounts.ledger', [
                'account' => $cash,
                'from' => now()->startOfYear()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('كشف الحساب', false)
            ->assertSee('111101', false)
            ->assertSee('قيد لكشف الحساب', false)
            ->assertSee($entry->entry_no, false)
            ->assertSee('250.00', false)
            ->assertSee(__('Export PDF'), false)
            ->assertSee(__('Export Excel'), false);

        $ledgerResponse->assertSee(route('admin.journals.show', $entry), false);

        $this->actingAs($this->admin)
            ->get(route('admin.accounts.ledger.pdf', [
                'account' => $cash,
                'from' => now()->startOfYear()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $excel = $this->actingAs($this->admin)
            ->get(route('admin.accounts.ledger.excel', [
                'account' => $cash,
                'from' => now()->startOfYear()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk();

        $excel->assertHeader('content-disposition');
        $this->assertStringContainsString('ledger-111101.xls', (string) $excel->headers->get('content-disposition'));
        $this->assertStringContainsString('111101', $excel->streamedContent());
        $this->assertStringContainsString('قيد لكشف الحساب', $excel->streamedContent());
        $this->assertStringContainsString('250.00', $excel->streamedContent());

        $forbidden = User::factory()->create([
            'email' => 'no.accounts@qcoresys.test',
            'is_active' => true,
        ]);

        $this->actingAs($forbidden)
            ->get(route('admin.accounts.ledger', $cash))
            ->assertForbidden();

        $this->assertNotNull($capital);
    }

    public function test_ledger_pagination_preserves_running_balance_across_pages(): void
    {
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();

        for ($i = 1; $i <= 12; $i++) {
            $this->postJournal([
                    'entry_date' => now()->toDateString(),
                    'description' => 'قيد صفحة '.$i,
                    'lines' => [
                        ['account_code' => '111101', 'debit' => 10, 'credit' => 0],
                        ['account_code' => '3111', 'debit' => 0, 'credit' => 10],
                    ],
                ])
                ->assertOk();
        }

        $page1 = $this->actingAs($this->admin)
            ->get(route('admin.accounts.ledger', [
                'account' => $cash,
                'from' => now()->startOfYear()->toDateString(),
                'to' => now()->toDateString(),
                'per_page' => 10,
                'page' => 1,
            ]))
            ->assertOk()
            ->assertSee(__('Opening balance'), false)
            ->assertSee('قيد صفحة 1', false)
            ->assertSee('قيد صفحة 10', false)
            ->assertDontSee('قيد صفحة 11', false);

        $page1->assertSee('100.00', false);
        $page1->assertSee('120.00', false);

        $page2 = $this->actingAs($this->admin)
            ->get(route('admin.accounts.ledger', [
                'account' => $cash,
                'from' => now()->startOfYear()->toDateString(),
                'to' => now()->toDateString(),
                'per_page' => 10,
                'page' => 2,
            ]))
            ->assertOk()
            ->assertSee(__('Balance brought forward'), false)
            ->assertSee('قيد صفحة 11', false)
            ->assertSee('قيد صفحة 12', false)
            ->assertDontSee('قيد صفحة 10', false);

        $page2->assertSee('100.00', false);
        $page2->assertSee('110.00', false);
        $page2->assertSee('120.00', false);

        $this->assertNotNull($capital);
    }

    public function test_reverse_journal_via_http_creates_balancing_entry(): void
    {
        $this->postJournal([
                'entry_date' => now()->toDateString(),
                'description' => 'قيد للعكس',
                'lines' => [
                    ['account_code' => '111101', 'debit' => 300, 'credit' => 0],
                    ['account_code' => '3111', 'debit' => 0, 'credit' => 300],
                ],
            ])
            ->assertOk();

        $entry = JournalEntry::query()->latest('id')->firstOrFail();
        $this->assertSame(JournalStatus::Posted, $entry->status);

        $this->actingAs($this->admin)
            ->get(route('admin.journals.show', $entry))
            ->assertOk()
            ->assertSee(__('Reverse journal'), false);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.journals.reverse', $entry));

        $entry->refresh();
        $reversing = JournalEntry::query()
            ->where('reversed_entry_id', $entry->id)
            ->latest('id')
            ->firstOrFail();

        $response->assertRedirect(route('admin.journals.show', $reversing));
        $this->assertTrue($entry->is_reversed);
        $this->assertSame(JournalStatus::Posted, $entry->status);
        $this->assertSame(JournalStatus::Posted, $reversing->status);
        $this->assertEqualsWithDelta((float) $reversing->total_debit, (float) $reversing->total_credit, 0.01);

        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();
        $this->assertEqualsWithDelta(0.0, (float) $cash->fresh()->current_balance, 0.01);
        $this->assertEqualsWithDelta(0.0, (float) $capital->fresh()->current_balance, 0.01);

        $index = $this->actingAs($this->admin)
            ->get(route('admin.journals.index'))
            ->assertOk();
        $index->assertSee($entry->entry_no, false);
        $index->assertSee($reversing->entry_no, false);

        $this->actingAs($this->admin)
            ->get(route('admin.journals.show', $entry))
            ->assertOk()
            ->assertSee(__('Journal reversed (status)'), false)
            ->assertDontSee(__('Reverse journal'), false);

        $this->actingAs($this->admin)
            ->get(route('admin.journals.show', $reversing))
            ->assertOk()
            ->assertSee(__('Reversing journal (label)'), false)
            ->assertDontSee(__('Reverse journal'), false);

        $originalDebitLine = $entry->lines()->where('debit', '>', 0)->firstOrFail();
        $reversingCreditLine = $reversing->lines()->where('account_id', $originalDebitLine->account_id)->firstOrFail();
        $this->assertEqualsWithDelta((float) $originalDebitLine->debit, (float) $reversingCreditLine->credit, 0.01);
        $this->assertEqualsWithDelta(0.0, (float) $reversingCreditLine->debit, 0.01);
    }

    public function test_reverse_journal_rejects_draft_and_already_reversed(): void
    {
        $service = app(JournalEntryService::class);
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();
        $currencyId = Currency::query()->base()->firstOrFail()->id;

        $draft = $service->create([
            'entry_date' => now()->toDateString(),
            'description' => 'مسودة للعكس',
            'currency_id' => $currencyId,
            'exchange_rate' => 1,
        ], [
            ['account_id' => $cash->id, 'debit' => 100, 'credit' => 0],
            ['account_id' => $capital->id, 'debit' => 0, 'credit' => 100],
        ], $this->admin->id);

        $this->actingAs($this->admin)
            ->from(route('admin.journals.show', $draft))
            ->post(route('admin.journals.reverse', $draft))
            ->assertRedirect(route('admin.journals.show', $draft))
            ->assertSessionHasErrors('journal');

        $posted = $service->post($draft, $this->admin->id);
        $reversing = $service->reverse($posted, $this->admin->id);

        $this->actingAs($this->admin)
            ->from(route('admin.journals.show', $posted))
            ->post(route('admin.journals.reverse', $posted))
            ->assertRedirect(route('admin.journals.show', $posted))
            ->assertSessionHasErrors('journal');

        $this->assertSame(JournalStatus::Posted, $reversing->fresh()->status);
    }
}
