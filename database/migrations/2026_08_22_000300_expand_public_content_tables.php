<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solutions', function (Blueprint $table): void {
            $table->string('category')->nullable()->index()->after('slug');
            $table->text('problem')->nullable()->after('description');
            $table->text('value_proposition')->nullable()->after('problem');
            $table->json('modules')->nullable()->after('value_proposition');
            $table->json('benefits')->nullable()->after('modules');
            $table->json('tags')->nullable()->after('benefits');
            $table->string('hero_image')->nullable()->after('tags');
            $table->string('meta_title')->nullable()->after('hero_image');
            $table->string('meta_description', 320)->nullable()->after('meta_title');
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->string('category')->nullable()->index()->after('slug');
            $table->json('tags')->nullable()->after('content');
            $table->string('hero_image')->nullable()->after('tags');
            $table->boolean('featured')->default(false)->after('hero_image');
            $table->unsignedInteger('sort_order')->default(0)->after('featured');
            $table->string('meta_title')->nullable()->after('sort_order');
            $table->string('meta_description', 320)->nullable()->after('meta_title');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropColumn(['category', 'tags', 'hero_image', 'featured', 'sort_order', 'meta_title', 'meta_description']);
        });

        Schema::table('solutions', function (Blueprint $table): void {
            $table->dropColumn(['category', 'problem', 'value_proposition', 'modules', 'benefits', 'tags', 'hero_image', 'meta_title', 'meta_description']);
        });
    }
};
