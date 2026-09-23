<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AttachmentService
{
    /**
     * Validation rules for mandatory journal vouchers (PDF / images).
     *
     * @return array<string, list<string>>
     */
    public static function journalVoucherRules(): array
    {
        return [
            'attachments' => ['required', 'array', 'min:1', 'max:5'],
            'attachments.*' => ['file', 'mimes:pdf,png,jpg,jpeg', 'max:5120'],
        ];
    }

    /**
     * @param  array<int, UploadedFile|null>  $files
     */
    public function storeMany(
        Model $attachable,
        array $files,
        string $directory,
        ?int $uploadedBy = null,
        string $description = 'Attachment'
    ): void {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $disk = 'local';
            $storedName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $storedName, $disk);

            $attachable->attachments()->create([
                'file_name' => $storedName,
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => $disk,
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'description' => $description,
                'uploaded_by' => $uploadedBy,
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile|null>  $files
     */
    public function storeJournalVouchers(Model $journalEntry, array $files, ?int $uploadedBy = null): void
    {
        $this->storeMany(
            $journalEntry,
            $files,
            'journals/'.$journalEntry->getKey(),
            $uploadedBy,
            'Journal entry attachment'
        );
    }
}
