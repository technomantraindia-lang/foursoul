<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            // Stable machine key used by the event mapper, e.g. payment_success.
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('channels'); // allowed channels for this template
            $table->string('subject')->nullable(); // email subject
            $table->string('title')->nullable();   // push/in-app title
            $table->longText('body');              // {{variable}} placeholders
            $table->json('variables')->nullable(); // documented variables
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};