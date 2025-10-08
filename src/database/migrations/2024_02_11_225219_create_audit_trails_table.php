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
        Schema::create("audit_trails", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("audit_id");

            $table->string("object_table")->nullable();
            $table->string("object_id")->nullable();

            $table->text("before")->nullable();
            $table->text("after")->nullable();
            $table->text("tag")->nullable();
            $table->text("additional")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("audit_trails");
    }
};
