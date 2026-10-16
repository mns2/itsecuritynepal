<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Course catalogue: categories (tracks), courses, learning paths, batches, training methods.
 * List-type content (outcomes, modules, FAQs…) is stored as JSON and edited with Filament repeaters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // stable id used in filters, e.g. "redhat"
            $table->string('slug')->unique();         // public URL path, e.g. "redhat" → /redhat/
            $table->string('name');
            $table->string('short_name');
            $table->string('icon')->default('layers');
            $table->string('tagline')->nullable();
            $table->text('intro')->nullable();
            $table->json('objectives')->nullable();
            $table->json('faqs')->nullable();
            $table->string('learning_path_key')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('show_on_home')->default(true);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('slug')->unique();          // legacy slug kept: /red-hat-system-administration/
            $table->string('code');
            $table->boolean('official_code')->default(true);
            $table->string('name');
            $table->string('level')->default('Beginner'); // Beginner | Intermediate | Advanced
            $table->string('certification')->nullable();
            $table->string('duration')->nullable();
            $table->json('modes')->nullable();
            $table->text('summary')->nullable();
            $table->json('overview')->nullable();
            $table->json('outcomes')->nullable();
            $table->json('prerequisites')->nullable();
            $table->json('benefits')->nullable();
            $table->json('careers')->nullable();
            $table->json('modules')->nullable();       // [{title, topics:[]}]
            $table->json('faqs')->nullable();          // [{q, a}]
            $table->json('extra_sections')->nullable(); // [{heading, items:[]}]
            $table->boolean('featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->index(['is_published', 'featured']);
        });

        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('icon')->default('route');
            $table->string('goal')->nullable();
            $table->json('steps')->nullable();        // [{course_id, note}]
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on')->nullable();     // real start date when known
            $table->string('start_label');             // display text, e.g. "Every Monday"
            $table->string('schedule')->nullable();
            $table->string('mode')->nullable();
            $table->string('status')->default('open'); // open | few | soon | waitlist
            $table->boolean('is_illustrative')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('training_methods', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('icon')->default('presentation');
            $table->string('short')->nullable();
            $table->text('body')->nullable();
            $table->string('best_for')->nullable();
            $table->json('features')->nullable();
            $table->string('verification_note')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_methods');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('learning_paths');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('categories');
    }
};
