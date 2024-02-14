<?php
return [
    "app_name" => env("AUDIT_APP_NAME", "audit"),
    "connection" => config("database.default"),
    "audit_model" => Iqbalatma\LaravelAudit\Model\Audit::class,
    "is_role_from_spatie" => false,
    "actor_key" => [
        "email" => "email",
        "phone" => "phone",
        "name" => "name",
    ]
];
