<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_purchases_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->unique();
            $table->string('plan_id'); // np. text-week, voice-1h
            $table->integer('amount'); // w groszach (np. 9900)
            $table->string('status')->default('pending'); // np. completed
            $table->timestamp('valid_until')->nullable(); // np. dla subskrypcji tygodniowej
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
