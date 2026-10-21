<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TopProductsSheet implements FromArray, WithHeadings, WithTitle
{
    /** @param array<int, array<string, mixed>> $products */
    public function __construct(private readonly array $products) {}

    public function array(): array
    {
        return array_map(fn (array $product): array => [
            $product['product_name'],
            $product['quantity'],
            $product['revenue_minor'],
        ], $this->products);
    }

    public function headings(): array
    {
        return ['Produit', 'Quantité', 'Chiffre d’affaires (unité mineure)'];
    }

    public function title(): string
    {
        return 'Top produits';
    }
}
