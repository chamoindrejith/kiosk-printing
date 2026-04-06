<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('paper_size')->default('A4');
            $table->string('color_mode')->default('color');
            $table->string('duplex_mode')->default('simplex');
            $table->decimal('price_per_sheet', 10, 2);
            $table->foreignId('printer_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printer_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('status')->default('draft');
            $table->string('original_filename');
            $table->string('file_path');
            $table->integer('original_page_count')->nullable();
            $table->integer('effective_page_count')->nullable();
            $table->integer('sheet_count')->nullable();
            $table->integer('copies')->default(1);
            $table->boolean('color')->default(true);
            $table->boolean('duplex')->default(false);
            $table->string('paper_size')->default('A4');
            $table->string('page_range')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->integer('last_confirmed_page')->default(0);
            $table->string('external_job_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('print_job_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_job_id')->constrained()->onDelete('cascade');
            $table->integer('page_number');
            $table->integer('copy_number')->default(1);
            $table->integer('sequence_order');
            $table->string('status')->default('pending');
            $table->string('external_page_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_job_id')->constrained()->onDelete('cascade');
            $table->string('gateway');
            $table->string('gateway_payment_id')->nullable();
            $table->string('reference');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('initiated');
            $table->string('qr_code')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->string('event_type');
            $table->json('payload');
            $table->timestamps();
        });

        Schema::create('print_job_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_job_id')->constrained()->onDelete('cascade');
            $table->string('event_type');
            $table->json('payload');
            $table->timestamps();
        });

        Schema::table('print_jobs', function (Blueprint $table) {
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_job_events');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('print_job_pages');
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('pricing_rules');
    }
};