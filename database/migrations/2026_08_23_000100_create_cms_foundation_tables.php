<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('canonical_url')->nullable()->after('meta_description');
            $table->string('og_title')->nullable()->after('canonical_url');
            $table->string('og_description', 320)->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
            $table->string('robots', 60)->default('index,follow')->after('og_image');
        });

        foreach (['blog_posts', 'solutions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('canonical_url')->nullable()->after('meta_description');
                $table->string('og_title')->nullable()->after('canonical_url');
                $table->string('og_description', 320)->nullable()->after('og_title');
                $table->string('og_image')->nullable()->after('og_description');
                $table->string('robots', 60)->default('index,follow')->after('og_image');
            });
        }

        Schema::create('page_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('text');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->json('settings')->nullable();
            $table->string('display_mode', 20)->default('append');
            $table->string('animation', 30)->default('fade');
            $table->unsignedSmallInteger('duration')->default(500);
            $table->unsignedSmallInteger('delay')->default(0);
            $table->unsignedTinyInteger('intensity')->default(30);
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('location', 40)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('label');
            $table->string('url');
            $table->string('link_type', 20)->default('internal');
            $table->string('target', 20)->default('_self');
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->string('disk', 30)->default('public');
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size');
            $table->string('alt_text')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 40)->default('general')->index();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('role')->nullable();
            $table->text('content');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('media');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('page_sections');

        foreach (['pages', 'blog_posts', 'solutions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['canonical_url', 'og_title', 'og_description', 'og_image', 'robots']);
            });
        }
    }
};
