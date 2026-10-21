<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MySQL equivalent of the Firestore collections documented in
 * docs/migration/data-model.md. Status strings stay VARCHAR so the three
 * vocabularies the original UI writes (pending, inProgress, resolved, …)
 * round-trip without being renamed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('representatives', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('display_name');
            $table->string('role', 20);
            $table->string('department')->default('');
            $table->string('position')->default('');
            $table->string('phone', 32)->nullable();
            $table->string('photo_url', 2048)->nullable();
            $table->json('permissions')->nullable();
            $table->json('specializations')->nullable();
            $table->json('target_groups')->nullable();
            $table->json('working_hours')->nullable();
            $table->unsignedInteger('active_cases')->default(0);
            $table->unsignedInteger('resolved_cases')->default(0);
            $table->unsignedInteger('total_cases_handled')->default(0);
            $table->decimal('average_response_time', 10, 2)->default(0);
            $table->decimal('average_resolution_time', 10, 2)->default(0);
            $table->decimal('resolution_rate', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('online_status', 16)->default('offline');
            $table->boolean('can_assign_cases')->default(false);
            $table->boolean('can_escalate_cases')->default(false);
            $table->boolean('can_resolve_cases')->default(false);
            $table->text('bio')->nullable();
            $table->timestamp('last_active')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'display_name']);
            $table->index(['role', 'is_active']);
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->string('case_number', 32)->nullable()->unique();
            $table->foreignId('complainant_id')->constrained('users');
            $table->string('complainant_email')->nullable();
            $table->string('complainant_name')->nullable();
            $table->string('complainant_address')->nullable();
            $table->string('complainant_contact')->nullable();
            $table->string('complainant_type', 20)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('anonymity_level', 24)->nullable();
            $table->json('sealed_identity')->nullable();
            $table->foreignId('respondent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('respondent_name')->nullable();
            $table->string('respondent_address')->nullable();
            $table->string('respondent_department')->nullable();
            $table->string('respondent_position')->nullable();
            $table->string('respondent_type', 20)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('statement_of_facts')->nullable();
            $table->string('type', 48)->nullable();
            $table->string('harassment_degree', 16)->nullable();
            $table->string('severity', 16)->nullable();
            $table->date('incident_date')->nullable();
            $table->string('incident_time', 8)->nullable();
            $table->string('incident_location')->nullable();
            $table->string('location_vicinity', 16)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('map_address', 512)->nullable();
            $table->text('witnesses')->nullable();
            $table->text('additional_info')->nullable();
            $table->string('status', 32)->default('pending');
            $table->string('stage', 40)->default('filing');
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('confidentiality_level', 20)->default('confidential');
            $table->timestamp('filed_at')->nullable();
            $table->timestamp('response_due_at')->nullable();
            $table->string('upload_status', 24)->nullable();
            $table->string('storage_provider', 32)->nullable();
            $table->string('cloud_name', 64)->nullable();
            $table->unsignedInteger('evidence_count')->default(0);
            $table->json('evidence_external_links')->nullable();
            $table->foreignId('assigned_representative_id')->nullable()->constrained('representatives')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_to_name')->nullable();
            $table->string('assigned_to_role', 32)->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_by_name')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accepted_by_name')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->unsignedInteger('time_to_assignment_hours')->nullable();
            $table->mediumText('admin_notes')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('notes_count')->default(0);
            $table->boolean('follow_up_requested')->default(false);
            $table->timestamp('follow_up_requested_at')->nullable();
            $table->foreignId('follow_up_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('handling_feedback_rating')->nullable();
            $table->text('handling_feedback_comment')->nullable();
            $table->timestamp('handling_feedback_submitted_at')->nullable();
            $table->foreignId('handling_feedback_submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('date_resolved')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('closed_by_name')->nullable();
            $table->text('decision_summary')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('final_notes')->nullable();
            $table->string('closure_document_url', 2048)->nullable();
            $table->string('closure_document_name')->nullable();
            $table->unsignedTinyInteger('escalation_level')->default(0);
            $table->boolean('is_escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->boolean('auto_escalated')->default(false);
            $table->string('escalation_reason')->nullable();
            $table->text('escalation_notes')->nullable();
            $table->timestamp('sla_deadline')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->timestamp('sla_breached_at')->nullable();
            $table->timestamp('last_escalation_check')->nullable();
            $table->unsignedInteger('hours_unprocessed')->nullable();
            $table->unsignedInteger('days_unprocessed')->nullable();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('last_updated_by_name')->nullable();
            $table->timestamps();

            $table->index(['complainant_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['status', 'type']);
            $table->index(['is_deleted', 'status']);
            $table->index('escalation_level');
            $table->index('respondent_name');
        });

        Schema::create('complaint_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32);
            $table->string('previous_status', 32)->nullable();
            $table->string('stage', 40)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('updated_by_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_url', 2048)->nullable();
            $table->string('attachment_file_name')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('assigned_to_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['complaint_id', 'created_at']);
        });

        Schema::create('complaint_handler_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('handler_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('handler_representative_id')->nullable()->constrained('representatives')->nullOnDelete();
            $table->string('handler_name')->nullable();
            $table->string('handler_role', 32)->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_by_name')->nullable();
            $table->timestamp('unassigned_at')->nullable();
            $table->string('unassigned_reason')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('complaint_escalation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->unsignedTinyInteger('previous_level')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('escalated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('escalated_by_name')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('auto_escalated')->default(false);
        });

        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('complaint_title')->nullable();
            $table->foreignId('complainant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('complainant_name')->nullable();
            $table->foreignId('handler_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('handler_name')->nullable();
            $table->string('status', 16)->default('active');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->boolean('allow_attachments')->default(true);
            $table->unsignedInteger('max_attachment_size_mb')->default(10);
            $table->json('allowed_file_types')->nullable();
            $table->string('last_message_content')->nullable();
            $table->foreignId('last_message_sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('last_message_sender_name')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->index('updated_at');
        });

        Schema::create('chat_room_participants', function (Blueprint $table) {
            $table->foreignId('room_id')->constrained('chat_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('role', 20)->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->primary(['room_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->foreignId('chat_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('complaint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sender_id', 64)->nullable();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_name')->nullable();
            $table->string('sender_role', 20)->nullable();
            $table->string('type', 16)->default('text');
            $table->text('content');
            $table->string('status', 16)->default('sent');
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['chat_room_id', 'created_at']);
        });

        Schema::create('message_reads', function (Blueprint $table) {
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();
            $table->primary(['message_id', 'user_id']);
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('url', 2048);
            $table->string('public_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->index(['attachable_type', 'attachable_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 48);
            $table->string('priority', 16)->default('normal');
            $table->string('status', 16)->default('unread');
            $table->string('title');
            $table->text('message');
            $table->foreignId('complaint_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('message_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chat_room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_url', 512)->nullable();
            $table->string('action_label', 64)->nullable();
            $table->json('data')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['user_id', 'complaint_id', 'status']);
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('email_address')->nullable();
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('push_enabled')->default(false);
            $table->boolean('email_enabled')->default(true);
            $table->json('preferences')->nullable();
            $table->string('email_digest', 16)->default('immediate');
            $table->time('digest_time')->nullable();
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 512)->unique();
            $table->char('token_hash', 64)->unique();
            $table->string('user_agent', 512)->nullable();
            $table->string('platform', 64)->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('case_notes', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role', 20)->nullable();
            $table->string('user_email')->nullable();
            $table->text('message');
            $table->timestamps();
            $table->index(['complaint_id', 'created_at']);
        });

        Schema::create('case_activities', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type', 40);
            $table->text('description')->nullable();
            $table->text('findings')->nullable();
            $table->string('performed_by', 64)->nullable();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_name')->nullable();
            $table->string('performed_by_role', 20)->nullable();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_user_name')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->json('metadata')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['complaint_id', 'created_at']);
        });

        Schema::create('case_views', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->timestamp('viewed_at')->useCurrent();
            $table->primary(['user_id', 'complaint_id']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role', 32)->nullable();
            $table->foreignId('complaint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('complaint_title')->nullable();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_user_name')->nullable();
            $table->text('description')->nullable();
            $table->text('details')->nullable();
            $table->json('changes')->nullable();
            $table->string('severity', 16)->default('info');
            $table->string('category', 32)->nullable();
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('severity');
        });

        Schema::create('laya_conversations', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('preview')->nullable();
            $table->json('messages')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->string('name');
            $table->string('category', 64)->nullable();
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('mental_health_videos', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->string('title');
            $table->string('host')->nullable();
            $table->text('description')->nullable();
            $table->string('duration', 16)->nullable();
            $table->json('tags')->nullable();
            $table->string('video_id', 32)->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->json('value')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('updated_by_name')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('system_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 64);
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('changed_by_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 32)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name')->nullable();
            $table->unsignedInteger('member_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->string('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->foreignId('last_message_sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('last_message_sender_name')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'created_at']);
        });

        Schema::create('group_members', function (Blueprint $table) {
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->string('role', 20)->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->primary(['group_id', 'user_id']);
        });

        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_id', 28)->nullable()->unique();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_name')->nullable();
            $table->string('sender_role', 20)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('type', 16)->default('text');
            $table->text('content');
            $table->string('status', 16)->default('sent');
            $table->foreignId('reply_to_id')->nullable()->constrained('group_messages')->nullOnDelete();
            $table->string('reply_to_content')->nullable();
            $table->string('reply_to_sender_name')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['group_id', 'created_at']);
        });

        Schema::create('admin_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 64)->nullable();
            $table->boolean('success')->default(false);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach ([
            'admin_login_attempts', 'group_messages', 'group_members', 'groups',
            'system_audit_logs', 'system_settings', 'mental_health_videos', 'locations',
            'laya_conversations', 'activity_logs', 'case_views', 'case_activities', 'case_notes',
            'fcm_tokens', 'notification_preferences', 'notifications', 'attachments',
            'message_reads', 'messages', 'chat_room_participants', 'chat_rooms',
            'complaint_escalation_history', 'complaint_handler_history', 'complaint_status_history',
            'complaints', 'representatives',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
