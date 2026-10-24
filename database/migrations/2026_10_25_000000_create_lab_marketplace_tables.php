<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('lab_categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lab_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('kind'); // desktop, web_app, mobile_app, website
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('icon_path')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('billing_type')->default('quote'); // free, one_time, subscription, quote
            $table->string('status')->default('draft'); // draft, published, archived
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_category_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('lab_products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('lab_categories')->cascadeOnDelete();
            $table->primary(['product_id', 'category_id']);
        });

        Schema::create('lab_product_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('lab_products')->cascadeOnDelete();
            $table->string('platform'); // web, android, ios, windows, macos, linux
            $table->string('purpose'); // demo, store, download, documentation
            $table->text('url');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('lab_products')->cascadeOnDelete();
            $table->string('type'); // screenshot, video, icon
            $table->text('path_or_url');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lab_software_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->string('request_type'); // website, ecommerce, web_system, mobile, desktop, integration
            $table->string('project_title');
            $table->text('problem_statement');
            $table->text('objectives')->nullable();
            $table->json('target_platforms')->nullable();
            $table->json('required_features')->nullable();
            $table->json('user_roles')->nullable();
            $table->json('integrations')->nullable();
            $table->string('estimated_users')->nullable();
            $table->string('budget_range')->nullable();
            $table->date('target_date')->nullable();
            $table->text('existing_system')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('status')->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lab_requirement_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('lab_software_requests')->cascadeOnDelete();
            $table->string('section');
            $table->string('question');
            $table->json('answer')->nullable();
            $table->boolean('internal_only')->default(false);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lab_request_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('lab_software_requests')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->longText('note');
            $table->boolean('internal_only')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->text('website_url')->nullable();
            $table->boolean('approved_for_display')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['lab_partners','lab_request_notes','lab_requirement_answers','lab_software_requests','lab_product_media','lab_product_links','lab_category_product','lab_products','lab_categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
