<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('final_year_project_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_reference', 40)->unique();
            $table->string('customer_name', 120);
            $table->string('customer_email', 190);
            $table->string('customer_phone', 40);
            $table->string('institution', 160)->nullable();
            $table->string('project_title', 180);
            $table->string('package_key', 40);
            $table->string('package_label', 120);
            $table->string('domain_name', 120)->nullable();
            $table->text('notes')->nullable();
            $table->string('currency', 10)->default('UGX');
            $table->decimal('amount', 12, 2);
            $table->string('payment_status', 20)->default('NOT_STARTED');
            $table->string('payment_status_description', 120)->default('Not started');
            $table->string('order_tracking_id', 120)->nullable()->index();
            $table->string('pesapal_redirect_url', 2048)->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('gateway_status_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_year_project_orders');
    }
};
