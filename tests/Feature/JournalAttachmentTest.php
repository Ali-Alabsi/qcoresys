<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\Currency;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\JournalEntryService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JournalAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_post_journal_and_attach_file_manually(): void
    {
        Storage::fake('local');

        $admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
        $usd = Currency::query()->where('code', 'USD')->firstOrFail();
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $capital = Account::query()->where('account_code', '3111')->firstOrFail();
        $journals = app(JournalEntryService::class);

        $entry = $journals->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Capital with voucher',
            'currency_id' => $usd->id,
            'exchange_rate' => 1,
        ], [
            ['account_id' => $cash->id, 'currency_id' => $usd->id, 'exchange_rate' => 1, 'debit' => 25, 'credit' => 0],
            ['account_id' => $capital->id, 'currency_id' => $usd->id, 'exchange_rate' => 1, 'debit' => 0, 'credit' => 25],
        ], $admin->id);
        $entry = $journals->post($entry, $admin->id);

        $file = UploadedFile::fake()->create('voucher.pdf', 120, 'application/pdf');
        $path = $file->storeAs('journals/'.$entry->id, 'voucher.pdf', 'local');

        $attachment = $entry->attachments()->create([
            'file_name' => 'voucher.pdf',
            'original_name' => 'voucher.pdf',
            'file_path' => $path,
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => $file->getSize(),
            'description' => 'Journal entry attachment',
            'uploaded_by' => $admin->id,
        ]);

        $this->assertInstanceOf(Attachment::class, $attachment);
        Storage::disk('local')->assertExists($attachment->file_path);

        $this->actingAs($admin)
            ->get(route('admin.journals.attachments.download', [$entry, $attachment]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.journals.attachments.view', [$entry, $attachment]))
            ->assertOk();
    }

    public function test_workstation_rejects_journal_without_voucher(): void
    {
        $admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.journals.store'), [
                'entry_date' => now()->toDateString(),
                'description' => 'Missing voucher',
                'lines' => [
                    ['account_code' => '111101', 'debit' => 25, 'credit' => 0],
                    ['account_code' => '3111', 'debit' => 0, 'credit' => 25],
                ],
            ], [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['attachments']);

        $this->assertFalse(
            JournalEntry::query()->where('description', 'Missing voucher')->exists()
        );
    }

    public function test_workstation_posts_journal_with_voucher(): void
    {
        Storage::fake('local');

        $admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.journals.store'), [
                'entry_date' => now()->toDateString(),
                'description' => 'JSON post capital',
                'lines' => [
                    ['account_code' => '111101', 'debit' => 25, 'credit' => 0],
                    ['account_code' => '3111', 'debit' => 0, 'credit' => 25],
                ],
                'attachments' => [
                    UploadedFile::fake()->image('voucher.jpg'),
                ],
            ], [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->assertOk();

        $entry = JournalEntry::query()->where('description', 'JSON post capital')->posted()->first();
        $this->assertNotNull($entry);
        $this->assertTrue($entry->attachments()->exists());
        Storage::disk('local')->assertExists($entry->attachments()->first()->file_path);

        $this->actingAs($admin)
            ->get(route('admin.journals.show', $entry))
            ->assertOk()
            ->assertSee(__('Supporting documents'), false)
            ->assertSee(__('View'), false)
            ->assertSee(__('Download'), false);
    }
}
