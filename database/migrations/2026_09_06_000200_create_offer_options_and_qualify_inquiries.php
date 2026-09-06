<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_options', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 14, 3)->default(0);
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $timestamp = now();
        DB::table('offer_options')->insert(collect([
            ['name' => 'WhatsApp Business', 'slug' => 'whatsapp-business', 'price' => 120, 'sort_order' => 10],
            ['name' => 'Pixel Meta & CAPI', 'slug' => 'pixel-meta-capi', 'price' => 150, 'sort_order' => 20],
            ['name' => 'Analytics & Search Console', 'slug' => 'analytics-search-console', 'price' => 90, 'sort_order' => 30],
            ['name' => 'SEO local', 'slug' => 'seo-local', 'price' => 180, 'sort_order' => 40],
            ['name' => 'Paiement en ligne', 'slug' => 'paiement-en-ligne', 'price' => 250, 'sort_order' => 50],
            ['name' => 'Module SMS', 'slug' => 'module-sms', 'price' => 80, 'sort_order' => 60],
            ['name' => 'Emails professionnels', 'slug' => 'emails-professionnels', 'price' => 60, 'sort_order' => 70],
            ['name' => 'Support prioritaire', 'slug' => 'support-prioritaire', 'price' => 240, 'sort_order' => 80],
        ])->map(fn (array $option): array => $option + [
            'description' => null,
            'status' => 'published',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->all());

        Schema::table('inquiries', function (Blueprint $table): void {
            $table->foreignId('lead_id')->nullable()->unique()->after('id')->constrained('leads')->nullOnDelete();
            $table->foreignId('pack_id')->nullable()->after('lead_id')->constrained('packs')->nullOnDelete();
            $table->string('activity')->nullable()->after('requested_solution');
            $table->string('company_size', 30)->nullable()->after('activity');
            $table->unsignedSmallInteger('user_count')->nullable()->after('company_size');
            $table->json('current_tools')->nullable()->after('user_count');
            $table->json('selected_options')->nullable()->after('current_tools');
            $table->string('hosting_preference', 30)->nullable()->after('selected_options');
            $table->string('desired_timeline', 30)->nullable()->after('hosting_preference');
            $table->string('budget_range', 30)->nullable()->after('desired_timeline');
            $table->decimal('estimated_total', 14, 3)->nullable()->after('budget_range');
            $table->timestamp('consent_accepted_at')->nullable()->after('estimated_total');
            $table->string('consent_version', 30)->nullable()->after('consent_accepted_at');
            $table->char('consent_ip_hash', 64)->nullable()->after('consent_version');
            $table->uuid('submission_uuid')->nullable()->unique()->after('consent_ip_hash');
            $table->text('source_url')->nullable()->after('submission_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('lead_id');
            $table->dropConstrainedForeignId('pack_id');
            $table->dropColumn([
                'activity',
                'company_size',
                'user_count',
                'current_tools',
                'selected_options',
                'hosting_preference',
                'desired_timeline',
                'budget_range',
                'estimated_total',
                'consent_accepted_at',
                'consent_version',
                'consent_ip_hash',
                'submission_uuid',
                'source_url',
            ]);
        });

        Schema::dropIfExists('offer_options');
    }
};
