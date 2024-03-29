<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_outbounds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('warehouses_id')->nullable(false);
            $table->uuid('central_productions_id')->nullable(true);
            $table->string('code')->nullable(true)->unique();
            $table->bigInteger('increment')->nullable(true);
            $table->string('note', 255)->nullable(true);
            $table->uuid('created_by')->nullable(false);
            $table->uuid('updated_by')->nullable(true);
            $table->timestamps();

            $table->foreign('warehouses_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('central_productions_id')->references('id')->on('central_productions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_outbounds');
    }
};
