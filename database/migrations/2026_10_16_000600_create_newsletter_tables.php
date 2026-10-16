<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Newsletter / offers mailing list and email campaigns sent from the admin panel.
 * Only people who opted in are on the list; every email carries a one-click unsubscribe link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 160)->unique();
            $table->string('name', 120)->nullable();
            $table->string('status')->default('pending');      // pending (not confirmed) | subscribed | unsubscribed | bounced
            $table->string('source')->default('popup');        // popup | footer | enquiry | landing | admin
            $table->foreignId('enquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('token', 64)->unique();             // confirm + unsubscribe links
            $table->timestamp('consent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('preheader')->nullable();           // preview text shown after the subject in inboxes
            $table->string('heading')->nullable();
            $table->longText('body');                          // Markdown
            $table->string('image')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete(); // related offer
            $table->string('status')->default('draft');        // draft | scheduled | sending | sent | cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();
        });

        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('queued');       // queued | sent | failed | skipped
            $table->string('error', 500)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unique(['email_campaign_id', 'subscriber_id']);
            $table->index(['email_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('subscribers');
    }
};
