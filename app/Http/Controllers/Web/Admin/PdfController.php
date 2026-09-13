<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Services\DocumentPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PdfController extends Controller
{
    public function __construct(protected DocumentPdfService $pdfService) {}

    public function quotation(Request $request, Quotation $quotation): Response|View
    {
        $quotation->load(['customer', 'currency', 'items']);

        return $this->respond(
            $request,
            'pdf.quotation',
            ['quotation' => $quotation],
            $quotation->quotation_no.'.pdf',
            'quotation',
            $quotation
        );
    }

    public function invoice(Request $request, Invoice $invoice): Response|View
    {
        $invoice->load(['customer', 'currency', 'items']);

        return $this->respond(
            $request,
            'pdf.invoice',
            ['invoice' => $invoice],
            $invoice->invoice_no.'.pdf',
            'invoice',
            $invoice
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function respond(
        Request $request,
        string $view,
        array $data,
        string $filename,
        string $kind,
        Quotation|Invoice $record
    ): Response|View {
        if ($request->boolean('preview')) {
            return view($view, array_merge($data, [
                'forPdf' => false,
                'previewActions' => [
                    'back' => route('admin.'.$kind.'s.show', $record),
                    'print' => route('admin.'.$kind.'s.pdf', $record).'?print=1',
                    'export' => route('admin.'.$kind.'s.pdf', $record),
                ],
            ]));
        }

        if ($request->boolean('print')) {
            return $this->pdfService->stream($view, $data, $filename);
        }

        return $this->pdfService->download($view, $data, $filename);
    }
}
