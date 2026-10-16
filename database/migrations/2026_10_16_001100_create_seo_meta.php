<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Advanced SEO per page. Either attached to a record (course, blog post, track…) or to a fixed path ("/", "/about/").
 * `path` is always filled (kept in sync with the record's URL) so the layout can look it up for any request.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('seoable');
            $table->string('path')->index();                  // "/redhat/" (leading + trailing slash)
            $table->string('title')->nullable();               // overrides the page's <title>
            $table->string('description', 320)->nullable();
            $table->string('focus_keyword')->nullable();       // for the editor checklist only (not output)
            $table->string('og_title')->nullable();            // social share title
            $table->string('og_description', 320)->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical', 500)->nullable();      // absolute URL or path
            $table->string('robots')->nullable();              // null = default
            $table->json('schema')->nullable();                // [{label, json}] custom JSON-LD blocks
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
