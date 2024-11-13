<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AuthorSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => 'Author User',
            'email' => 'author@example.com',
            'password' => Hash::make('author123'),
        ]);

        $user->assignRole('Author');
    }
}
