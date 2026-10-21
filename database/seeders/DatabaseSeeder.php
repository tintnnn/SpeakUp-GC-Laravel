<?php

namespace Database\Seeders;

use App\Models\Representative;
use App\Models\User;
use App\Support\SpeakUp;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUser(
            env('SEED_ADMIN_EMAIL'),
            env('SEED_ADMIN_PASSWORD'),
            'SpeakUp GC Admin',
            true,
            'admin',
        );

        $codi = $this->seedUser(
            env('SEED_CODI_EMAIL'),
            env('SEED_CODI_PASSWORD'),
            'CODI Member',
            false,
            'handler',
        );

        if ($codi) {
            Representative::query()->updateOrCreate(
                ['email' => $codi->email],
                [
                    'user_id' => $codi->id,
                    'display_name' => $codi->display_name,
                    'role' => 'handler',
                    'department' => 'DEIU',
                    'position' => 'CODI Member',
                    'is_active' => true,
                    'can_escalate_cases' => true,
                    'can_resolve_cases' => true,
                ]
            );
        }

        $this->call(CampusLocationSeeder::class);
    }

    private function seedUser(mixed $email, mixed $password, string $name, bool $isAdmin, string $role): ?User
    {
        $email = strtolower(trim((string) $email));
        $password = (string) $password;

        if ($email === '' || $password === '') {
            return null;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'display_name' => $name,
                'password' => $password,
                'provider' => 'email',
                'is_admin' => $isAdmin,
                'is_active' => true,
                'is_deleted' => false,
                'is_registered' => true,
                'email_verified_at' => now(),
                'role' => $role,
                'terms_accepted' => true,
                'privacy_accepted' => true,
                'profile_setup_complete' => true,
                'registered_at' => now(),
            ]
        );

        if ($isAdmin && ! SpeakUp::isAdminEmail($email)) {
            $this->command?->warn("{$email} is not in ADMIN_EMAILS; is_admin was still set on the user row.");
        }

        return $user;
    }
}
