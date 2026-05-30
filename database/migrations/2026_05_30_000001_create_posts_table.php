<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();       // unique index; slug [a-z0-9-] (D-04)
            $table->text('body');                   // raw markdown — feeds <x-markdown>
            $table->text('excerpt')->nullable();
            $table->string('status', 16)->default('draft');  // 'draft'|'published'; NOT ->enum() (project convention D-03)
            $table->string('author')->default('Pierre ADAM');
            $table->date('date')->nullable();       // datePublished/dateModified for SEO JSON-LD
            $table->boolean('show_date')->default(true);
            $table->timestamps();

            $table->index(['status', 'date']); // scopePublished() + orderByDesc('date')
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
