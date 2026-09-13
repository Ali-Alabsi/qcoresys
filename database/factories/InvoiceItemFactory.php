<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 50);
        $unitPrice = fake()->randomFloat(2, 100, 5000);
        $subtotal = round($quantity * $unitPrice, 2);
        $taxAmount = round($subtotal * 0.15, 2);

        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->sentence(4),
            'quantity' => $quantity,
            'unit' => 'hour',
            'unit_price' => $unitPrice,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_percentage' => 15,
            'tax_amount' => $taxAmount,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal + $taxAmount,
            'sort_order' => 1,
        ];
    }
}
