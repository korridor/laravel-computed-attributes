<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('rating');
            $table->integer('post_id');
            $table->foreign('post_id')
                ->on('posts')
                ->references('id')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
