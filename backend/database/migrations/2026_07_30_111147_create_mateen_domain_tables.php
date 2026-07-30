<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id(); $table->string('slug')->unique(); $table->string('title');
            $table->string('subtitle')->nullable(); $table->text('description')->nullable();
            $table->json('axes')->nullable(); $table->integer('sort_order')->default(0); $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->unique(); $table->string('phone')->nullable();
            $table->string('role')->default('student')->index();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('theme_id')->nullable(); $table->string('ornament_id')->nullable();
            $table->boolean('is_active')->default(true); $table->boolean('must_reset_password')->default(false);
        });
        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id(); $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->string('type'); $table->text('body')->nullable();
            $table->text('url')->nullable(); $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->integer('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete(); $table->timestamp('enrolled_at');
            $table->timestamps(); $table->unique(['user_id', 'subject_id']);
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('interview_status')->default('not_done'); $table->string('status_class')->default('newcomer');
            $table->text('notes')->nullable(); $table->json('extra')->nullable(); $table->timestamps();
        });
        Schema::create('schedule_entries', function (Blueprint $table) {
            $table->id(); $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->dateTime('starts_at'); $table->dateTime('ends_at')->nullable();
            $table->unsignedTinyInteger('weekday')->nullable(); $table->json('audience')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); $table->timestamps();
        });
        Schema::create('assignments', function (Blueprint $table) {
            $table->id(); $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->text('description')->nullable(); $table->dateTime('due_at')->nullable();
            $table->string('status')->default('open'); $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id(); $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->text('content')->nullable();
            $table->text('attachment_url')->nullable(); $table->string('status')->default('submitted');
            $table->decimal('grade', 8, 2)->nullable(); $table->text('feedback')->nullable(); $table->timestamps();
            $table->unique(['assignment_id', 'user_id']);
        });
        Schema::create('library_items', function (Blueprint $table) {
            $table->id(); $table->string('section'); $table->string('title'); $table->text('description')->nullable();
            $table->text('media_url')->nullable(); $table->string('subject_filter')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->integer('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('news_items', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->text('body'); $table->string('status')->default('draft');
            $table->dateTime('published_at')->nullable(); $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('conversations', function (Blueprint $table) { $table->id(); $table->timestamps(); });
        Schema::create('conversation_user', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['conversation_id', 'user_id']);
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_display')->nullable(); $table->text('body')->nullable();
            $table->text('media_url')->nullable(); $table->string('media_type')->nullable(); $table->timestamps();
        });
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('fcm_token')->unique(); $table->string('platform')->nullable();
            $table->dateTime('last_seen_at')->nullable(); $table->timestamps();
        });
        foreach (['attendance_records', 'grade_records'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                if ($name === 'attendance_records') { $table->date('session_date'); $table->boolean('present'); }
                else { $table->decimal('score', 8, 2); $table->string('label')->nullable(); $table->dateTime('recorded_at'); }
                $table->timestamps();
            });
        }
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); $table->json('params')->nullable(); $table->string('status')->default('pending');
            $table->text('file_path')->nullable(); $table->dateTime('ready_at')->nullable(); $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['export_jobs','grade_records','attendance_records','user_devices','messages','conversation_user','conversations','news_items','library_items','assignment_submissions','assignments','schedule_entries','student_profiles','enrollments','learning_materials'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['firebase_uid','phone','role','subject_id','theme_id','ornament_id','is_active','must_reset_password']);
        });
        Schema::dropIfExists('subjects');
    }
};
