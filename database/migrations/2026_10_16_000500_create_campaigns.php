<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marketing campaigns: a pop-up shown on the website (poster or text + button) and an optional landing page
 * at /offer/{slug}/. Views, clicks and leads are counted per campaign; enquiries remember their campaign.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // internal name, e.g. "Dashain 2026"
            $table->string('slug')->unique();                 // landing page: /offer/{slug}/
            $table->boolean('is_active')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();          // also drives the countdown
            $table->unsignedSmallInteger('priority')->default(0); // highest wins when several are live

            // Pop-up content
            $table->string('style')->default('poster');       // poster (image only) | card (image + text)
            $table->string('image')->nullable();              // poster / card image (public disk)
            $table->string('mobile_image')->nullable();       // optional portrait version for phones
            $table->string('image_alt')->nullable();
            $table->string('badge')->nullable();              // e.g. "Dashain offer"
            $table->string('headline')->nullable();
            $table->text('text')->nullable();
            $table->string('offer_code', 40)->nullable();     // shown with a copy button
            $table->boolean('show_countdown')->default(false);
            $table->string('cta_label')->default('Claim offer');
            $table->string('cta_action')->default('enquiry'); // enquiry (opens the form) | landing | link
            $table->string('cta_url')->nullable();            // for cta_action = link
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); // pre-selected in the form

            // Targeting
            $table->string('show_on')->default('all');        // all | home | selected
            $table->json('paths')->nullable();                // for "selected": ["/redhat/", "/course/*"]
            $table->string('device')->default('all');         // all | desktop | mobile
            $table->string('trigger')->default('delay');      // delay | scroll | exit
            $table->unsignedSmallInteger('delay_seconds')->default(5);
            $table->unsignedTinyInteger('scroll_percent')->default(40);
            $table->string('frequency')->default('day');      // session | day | week | always

            // Landing page
            $table->boolean('has_landing_page')->default(true);
            $table->longText('landing_body')->nullable();     // Markdown
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();

            // Stats
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campaign_id');
        });
        Schema::dropIfExists('campaigns');
    }
};
