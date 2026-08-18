<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Creates the first administrator.
     *
     * The password is never hardcoded. It comes from ADMIN_PASSWORD when that
     * is set; otherwise a random one is generated and printed once to the
     * console. That means a production deploy that forgets to set the variable
     * ends up with an unguessable password rather than a well-known default
     * that is identical across every install of this codebase.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@rehletsehha.test');
        $name = (string) env('ADMIN_NAME', 'مدير النظام');

        $configured = env('ADMIN_PASSWORD');
        $password = is_string($configured) && $configured !== ''
            ? $configured
            : Str::password(20);

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );

        $user->syncRoles(['admin']);

        if ($user->wasRecentlyCreated) {
            $this->command?->newLine();
            $this->command?->info('Admin user created:');
            $this->command?->line("  email:    {$email}");

            if (is_string($configured) && $configured !== '') {
                $this->command?->line('  password: (taken from ADMIN_PASSWORD)');
            } else {
                $this->command?->line("  password: {$password}");
                $this->command?->warn('  Generated password — shown once. Store it now.');
            }

            $this->command?->newLine();

            return;
        }

        $this->command?->line("Admin user {$email} already exists; password left unchanged.");
    }
}
