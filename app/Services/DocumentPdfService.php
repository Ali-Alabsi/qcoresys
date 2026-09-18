<?php

namespace App\Services;

use Illuminate\Http\Response;

class DocumentPdfService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function make(string $view, array $data, string $orientation = 'portrait'): \Barryvdh\DomPDF\PDF
    {
        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = app('dompdf.wrapper');

        return $pdf->loadView($view, array_merge($data, ['forPdf' => true]))
            ->setPaper('a4', $orientation)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'Tajawal')
            ->setOption('fontDir', storage_path('fonts'))
            ->setOption('fontCache', storage_path('fonts'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function download(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        return $this->make($view, $data, $orientation)->download($filename);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function stream(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        return $this->make($view, $data, $orientation)->stream($filename);
    }
}
