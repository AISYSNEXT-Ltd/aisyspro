<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_values', function (Blueprint $table): void {
            $table->id();
            $table->string('group_key', 80)->index();
            $table->string('code', 100);
            $table->string('label');
            $table->string('color', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['group_key', 'code']);
        });

        $now = now();
        $groups = [
            'lead_status' => [
                ['new', 'Nouveau', '#7c3aed'], ['contacted', 'Contacté', '#2563eb'],
                ['qualified', 'Qualifié', '#0891b2'], ['converted', 'Converti', '#16a34a'],
                ['lost', 'Perdu', '#dc2626'],
            ],
            'lead_source' => [
                ['manual', 'Saisie manuelle', '#64748b'], ['portal_contact', 'Formulaire contact', '#7c3aed'],
                ['portal_quote', 'Demande de devis', '#d97706'], ['whatsapp', 'WhatsApp', '#16a34a'],
                ['facebook', 'Facebook', '#2563eb'],
            ],
            'quote_status' => [
                ['draft', 'Brouillon', '#64748b'], ['sent', 'Envoyé', '#2563eb'],
                ['accepted', 'Accepté', '#16a34a'], ['rejected', 'Refusé', '#dc2626'],
            ],
            'client_status' => [['active', 'Actif', '#16a34a'], ['inactive', 'Inactif', '#64748b']],
            'task_status' => [
                ['todo', 'À faire', '#64748b'], ['in_progress', 'En cours', '#2563eb'],
                ['done', 'Terminée', '#16a34a'], ['cancelled', 'Annulée', '#dc2626'],
            ],
            'task_priority' => [
                ['low', 'Basse', '#64748b'], ['normal', 'Normale', '#2563eb'],
                ['high', 'Haute', '#d97706'], ['urgent', 'Urgente', '#dc2626'],
            ],
            'inquiry_status' => [
                ['new', 'Nouvelle', '#7c3aed'], ['in_progress', 'En traitement', '#2563eb'],
                ['closed', 'Clôturée', '#16a34a'],
            ],
        ];

        foreach ($groups as $group => $values) {
            foreach ($values as $index => [$code, $label, $color]) {
                DB::table('reference_values')->insert([
                    'group_key' => $group,
                    'code' => $code,
                    'label' => $label,
                    'color' => $color,
                    'sort_order' => ($index + 1) * 10,
                    'is_default' => $index === 0,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('leads', function (Blueprint $table): void {
            $table->uuid('submission_uuid')->nullable()->unique()->after('id');
        });

        Schema::table('quotes', function (Blueprint $table): void {
            $table->foreignId('lead_id')->nullable()->after('id')->constrained('leads')->nullOnDelete();
            $table->boolean('is_initial')->default(false)->after('lead_id');
            $table->foreignId('client_id')->nullable()->change();
        });

        Schema::create('lead_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 80);
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        DB::table('quotes')->whereNull('client_id')->delete();
        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('lead_id');
            $table->dropColumn('is_initial');
            $table->foreignId('client_id')->nullable(false)->change();
        });
        Schema::table('leads', fn (Blueprint $table) => $table->dropColumn('submission_uuid'));
        Schema::dropIfExists('reference_values');
    }
};
