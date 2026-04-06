<?php

namespace Database\Seeders;

use App\Models\Printer;
use App\Models\PricingRule;
use Illuminate\Database\Seeder;

class PrinterSeeder extends Seeder
{
    public function run(): void
    {
        Printer::updateOrCreate(
            ['code' => 'demo'],
            [
                'name' => 'Demo Printer',
                'location' => 'Main Lobby',
                'ip_address' => '192.168.1.100',
                'port' => 631,
                'protocol' => 'ipp',
                'is_active' => true,
            ]
        );

        $pricingRules = [
            ['name' => 'A4 Color Simplex', 'paper_size' => 'A4', 'color_mode' => 'color', 'duplex_mode' => 'simplex', 'price_per_sheet' => 10.00],
            ['name' => 'A4 Color Duplex', 'paper_size' => 'A4', 'color_mode' => 'color', 'duplex_mode' => 'duplex', 'price_per_sheet' => 9.00],
            ['name' => 'A4 BW Simplex', 'paper_size' => 'A4', 'color_mode' => 'bw', 'duplex_mode' => 'simplex', 'price_per_sheet' => 5.00],
            ['name' => 'A4 BW Duplex', 'paper_size' => 'A4', 'color_mode' => 'bw', 'duplex_mode' => 'duplex', 'price_per_sheet' => 4.50],
            ['name' => 'A5 Color Simplex', 'paper_size' => 'A5', 'color_mode' => 'color', 'duplex_mode' => 'simplex', 'price_per_sheet' => 7.00],
            ['name' => 'A5 BW Simplex', 'paper_size' => 'A5', 'color_mode' => 'bw', 'duplex_mode' => 'simplex', 'price_per_sheet' => 3.50],
            ['name' => 'Letter Color', 'paper_size' => 'Letter', 'color_mode' => 'color', 'duplex_mode' => 'simplex', 'price_per_sheet' => 11.00],
            ['name' => 'Letter BW', 'paper_size' => 'Letter', 'color_mode' => 'bw', 'duplex_mode' => 'simplex', 'price_per_sheet' => 5.50],
        ];

        foreach ($pricingRules as $rule) {
            PricingRule::updateOrCreate(
                [
                    'paper_size' => $rule['paper_size'],
                    'color_mode' => $rule['color_mode'],
                    'duplex_mode' => $rule['duplex_mode'],
                    'printer_id' => null,
                ],
                $rule + ['is_active' => true]
            );
        }
    }
}