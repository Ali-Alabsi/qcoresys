<?php

namespace Tests\Feature;

use App\Enums\JournalStatus;
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
            ->assertSee('111101', false);
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
