<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class defaultActorSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->create([
                'name' => config("default_audit_actor.name"),
                'username' => config("default_audit_actor.username"),
                'email' =>  config("default_audit_actor.email"),
                'password' => config("default_audit_actor.password"),
            ]);
    }


}