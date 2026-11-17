<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A price in pence (299 = £2.99). Empty means the entry is free.
        Schema::table('entries', function (Blueprint $table) {
            $table->unsignedInteger('price')->nullable()->after('link');
        });

        // One order per trip through the checkout.
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40)->unique();          // our own order number, sent to Stripe
            $table->string('status', 16)->default('pending');   // pending | paid
            $table->unsignedInteger('total');                    // pence
            $table->string('currency', 3)->default('gbp');
            $table->string('stripe_session')->nullable()->index();
            $table->string('stripe_payment')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('sandbox', 32)->nullable()->index();
            $table->timestamps();
        });

        // What was bought, at the price it cost on the day.
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('price');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
