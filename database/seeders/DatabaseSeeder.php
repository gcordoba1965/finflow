<?php

namespace Database\Seeders;

use App\Models\IncomeSource;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────
        $admin = User::create([
            'name'           => 'Admin FinFlow',
            'email'          => 'admin@finflow.com',
            'password'       => Hash::make('Admin1234!'),
            'role'           => 'admin',
            'monthly_income' => 0,
            'is_active'      => true,
            'email_verified_at' => now(),
        ]);

        // ── Sample consumers ───────────────────────────────────
        $consumers = [
            ['name' => 'María González', 'email' => 'maria@email.com',  'income' => 4500],
            ['name' => 'Carlos Mendoza', 'email' => 'carlos@email.com', 'income' => 6200],
            ['name' => 'Ana Ruiz',       'email' => 'ana@email.com',    'income' => 3100],
            ['name' => 'Luis Torres',    'email' => 'luis@email.com',   'income' => 5000, 'active' => false],
            ['name' => 'Sofía Herrera',  'email' => 'sofia@email.com',  'income' => 7800],
            ['name' => 'Diego Vargas',   'email' => 'diego@email.com',  'income' => 2900],
        ];

        foreach ($consumers as $data) {
            $user = User::create([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'password'          => Hash::make('User1234!'),
                'role'              => 'user',
                'monthly_income'    => $data['income'],
                'is_active'         => $data['active'] ?? true,
                'email_verified_at' => now(),
            ]);

            $this->seedUserData($user);
        }
    }

    private function seedUserData(User $user): void
    {
        // Income source
        IncomeSource::create([
            'user_id'   => $user->id,
            'name'      => 'Salario mensual',
            'icon'      => '💼',
            'amount'    => $user->monthly_income,
            'frequency' => 'monthly',
            'is_active' => true,
        ]);

        // Optional: freelance income
        if ($user->monthly_income > 4000) {
            IncomeSource::create([
                'user_id'   => $user->id,
                'name'      => 'Ingresos freelance',
                'icon'      => '💻',
                'amount'    => round($user->monthly_income * 0.15),
                'frequency' => 'variable',
                'is_active' => true,
            ]);
        }

        // Savings goals
        SavingsGoal::create([
            'user_id'      => $user->id,
            'name'         => 'Fondo de emergencia',
            'icon'         => '🛡️',
            'target_amount' => $user->monthly_income * 3,
            'saved_amount'  => $user->monthly_income * 0.6,
            'deadline'      => now()->addYear(),
        ]);

        SavingsGoal::create([
            'user_id'      => $user->id,
            'name'         => 'Vacaciones',
            'icon'         => '✈️',
            'target_amount' => 3000,
            'saved_amount'  => rand(500, 2000),
            'deadline'      => now()->addMonths(8),
        ]);

        // Transactions for the last 3 months
        $categories = [
            ['type' => 'income',  'cat' => 'income',  'icon' => '💼', 'desc' => 'Salario mensual',   'amt_pct' => 1.0],
            ['type' => 'expense', 'cat' => 'needs',   'icon' => '🏠', 'desc' => 'Renta',             'amt_pct' => 0.30],
            ['type' => 'expense', 'cat' => 'needs',   'icon' => '🛒', 'desc' => 'Supermercado',      'amt_pct' => 0.08],
            ['type' => 'expense', 'cat' => 'needs',   'icon' => '⚡', 'desc' => 'Electricidad',      'amt_pct' => 0.04],
            ['type' => 'expense', 'cat' => 'needs',   'icon' => '🚌', 'desc' => 'Transporte',        'amt_pct' => 0.03],
            ['type' => 'expense', 'cat' => 'wants',   'icon' => '🎬', 'desc' => 'Streaming',         'amt_pct' => 0.01],
            ['type' => 'expense', 'cat' => 'wants',   'icon' => '🍽️', 'desc' => 'Restaurantes',     'amt_pct' => 0.04],
            ['type' => 'expense', 'cat' => 'wants',   'icon' => '👗', 'desc' => 'Ropa',              'amt_pct' => 0.02],
            ['type' => 'expense', 'cat' => 'savings', 'icon' => '🏦', 'desc' => 'Ahorro mensual',   'amt_pct' => 0.15],
            ['type' => 'expense', 'cat' => 'savings', 'icon' => '📈', 'desc' => 'Inversión ETF',    'amt_pct' => 0.05],
        ];

        $income = (float) $user->monthly_income;

        for ($m = 2; $m >= 0; $m--) {
            foreach ($categories as $tx) {
                Transaction::create([
                    'user_id'     => $user->id,
                    'type'        => $tx['type'],
                    'category'    => $tx['cat'],
                    'description' => $tx['desc'],
                    'amount'      => round($income * $tx['amt_pct'] * (0.9 + rand(0, 20) / 100), 2),
                    'icon'        => $tx['icon'],
                    'date'        => now()->subMonths($m)->startOfMonth()->addDays(rand(0, 25)),
                ]);
            }
        }
    }
}
