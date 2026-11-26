<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'admin@rentease.test')->exists()) {
            return; // demo data already seeded
        }

        // All demo accounts use the password: password
        User::create([
            'name' => 'System Admin', 'email' => 'admin@rentease.test',
            'phone' => '254700000001', 'role' => 'admin', 'password' => 'password',
        ]);

        $landlord = User::create([
            'name' => 'Grace Mwangi', 'email' => 'landlord@rentease.test',
            'phone' => '254700000002', 'role' => 'landlord', 'password' => 'password',
        ]);

        $tenants = collect([
            ['Alice Wanjiru', 'alice@rentease.test', '254711000001'],
            ['Brian Otieno', 'brian@rentease.test', '254711000002'],
            ['Cynthia Achieng', 'cynthia@rentease.test', '254711000003'],
            ['David Kamau', 'david@rentease.test', '254711000004'],
        ])->map(fn ($t) => User::create([
            'name' => $t[0], 'email' => $t[1], 'phone' => $t[2],
            'role' => 'tenant', 'password' => 'password',
        ]))->values();

        $propertyData = [
            ['Sunrise Apartments', 'Kilimani, Nairobi', 'Modern apartments near Yaya Centre.', 18000],
            ['Greenview Court', 'Kahawa Sukari, Nairobi', 'Quiet family estate with parking.', 12000],
        ];

        $units = collect();
        foreach ($propertyData as [$name, $location, $description, $baseRent]) {
            $property = $landlord->properties()->create([
                'name' => $name, 'location' => $location, 'description' => $description,
            ]);

            foreach (['A1', 'A2', 'A3', 'A4'] as $i => $number) {
                $units->push($property->units()->create([
                    'unit_number' => $number,
                    'type' => $i < 2 ? 'Bedsitter' : '1 Bedroom',
                    'rent_amount' => $baseRent + ($i * 3000),
                    'status' => 'vacant',
                ]));
            }
        }

        // Tenants take A1 and A2 of each property
        $assignedUnits = [$units[0], $units[1], $units[4], $units[5]];

        foreach ($tenants as $i => $tenant) {
            $unit = $assignedUnits[$i];
            $unit->update(['status' => 'occupied']);

            $lease = Lease::create([
                'unit_id' => $unit->id,
                'tenant_id' => $tenant->id,
                'start_date' => now()->subMonths(5)->startOfMonth(),
                'rent_amount' => $unit->rent_amount,
                'deposit' => $unit->rent_amount,
                'status' => 'active',
            ]);

            // Five months of invoices, oldest first
            for ($m = 4; $m >= 0; $m--) {
                $due = now()->subMonths($m)->startOfMonth()->addDays(4);

                // Brian (index 1) missed last month and this month; David (index 3) has not paid this month
                $shouldBePaid = $m > 0
                    ? ! ($i === 1 && $m === 1)
                    : ($i === 0 || $i === 2);

                $invoice = Invoice::create([
                    'lease_id' => $lease->id,
                    'period' => $due->format('F Y'),
                    'amount' => $lease->rent_amount,
                    'balance' => $shouldBePaid ? 0 : $lease->rent_amount,
                    'due_date' => $due,
                    'status' => $shouldBePaid ? 'paid' : ($due->isPast() ? 'overdue' : 'unpaid'),
                ]);

                if ($shouldBePaid) {
                    $paidAt = $due->copy()->addDay();
                    if ($paidAt->isFuture()) {
                        $paidAt = now();
                    }

                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'tenant_id' => $tenant->id,
                        'phone' => $tenant->phone,
                        'amount' => $lease->rent_amount,
                        'checkout_request_id' => 'ws_CO_' . Str::random(12),
                        'mpesa_receipt' => 'QK' . strtoupper(Str::random(8)),
                        'status' => 'success',
                        'result_desc' => 'The service request is processed successfully.',
                        'paid_at' => $paidAt,
                    ]);
                }
            }
        }

        MaintenanceRequest::create([
            'unit_id' => $assignedUnits[0]->id, 'tenant_id' => $tenants[0]->id,
            'title' => 'Leaking kitchen tap',
            'description' => 'The kitchen tap drips constantly and needs a new washer.',
            'priority' => 'medium', 'status' => 'open',
        ]);

        MaintenanceRequest::create([
            'unit_id' => $assignedUnits[1]->id, 'tenant_id' => $tenants[1]->id,
            'title' => 'Broken door lock',
            'description' => 'The main door lock is jammed and the door cannot be locked.',
            'priority' => 'high', 'status' => 'in_progress',
        ]);
    }
}
