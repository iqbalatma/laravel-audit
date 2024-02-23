<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        $this->connection = config("laravel_audit.connection");
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("audits", function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->string("message")->nullable();
            $table->string("action")->nullable();

            $table->string("ip_address")->nullable();
            $table->string("endpoint")->nullable();
            $table->string("method")->nullable();
            $table->string("user_agent")->nullable();
            $table->text("user_request")->nullable();

            $table->string("actor_table")->nullable();
            $table->string("actor_id")->nullable();
            $table->string("actor_name")->nullable();
            $table->string("actor_email")->nullable();
            $table->string("actor_phone")->nullable();

            $table->string("object_table")->nullable();
            $table->string("object_id")->nullable();

            $table->text("trail")->nullable();
            $table->text("tag")->nullable();
            $table->text("additional")->nullable();

            $table->string("app_name")->nullable();
            $table->timestamps();

            $table->index(["object_table", "object_id"]);
            $table->index(["actor_table", "actor_id"]);
            $table->index(["action"]);
            $table->index(["app_name"]);
            $table->index(["endpoint", "method"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("audits");
    }
};
