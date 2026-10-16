<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Editorial content, leads and site configuration. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // legacy slug kept at the root: /rhcsa-certification-guide/
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body');                  // Markdown
            $table->string('author')->default('IT Security Nepal team');
            $table->string('featured_image')->nullable();
            $table->json('related_course_ids')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->index('published_at');
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('group');                   // getting-started | training | certification | fees | corporate
            $table->string('question');
            $table->text('answer');
            $table->boolean('show_on_home')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('role')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->json('expertise')->nullable();
            $table->json('certifications')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(false); // publish only verified profiles
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->text('quote');
            $table->string('outcome')->nullable();
            $table->boolean('consent_given')->default(false); // must be true to publish
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // privacy-policy, terms-and-conditions
            $table->string('title');
            $table->longText('body')->nullable();      // Markdown; empty = show the structure template
            $table->date('last_updated')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('form')->default('course'); // course | group | corporate | contact
            $table->string('name');
            $table->string('phone', 40);
            $table->string('email');
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('interest')->nullable();    // free text / "not-sure" / corporate area
            $table->string('audience')->default('Individual'); // Individual | Group | Corporate
            $table->string('group_size')->nullable();
            $table->string('mode')->nullable();
            $table->string('organisation')->nullable();
            $table->text('message')->nullable();
            $table->boolean('consent')->default(false);
            $table->string('source_url', 500)->nullable();
            $table->string('status')->default('new');  // new | contacted | enrolled | closed | spam
            $table->text('staff_notes')->nullable();
            $table->string('ip_hash', 64)->nullable(); // hashed, for abuse control only
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();     // "/old-path/" (leading + trailing slash)
            $table->string('to_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'redirects', 'enquiries', 'pages', 'testimonials', 'instructors', 'faqs', 'posts'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
