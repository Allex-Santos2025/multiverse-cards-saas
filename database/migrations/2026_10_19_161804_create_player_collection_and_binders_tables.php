<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Acervo Físico Central do Jogador (Espelho do StockItem)
        Schema::create('player_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_user_id')->constrained('player_users')->cascadeOnDelete();
            $table->foreignId('catalog_print_id')->constrained('catalog_prints')->cascadeOnDelete();

            $table->string('language', 10)->default('pt');
            $table->string('condition', 10)->default('NM');
            $table->json('extras')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('acquisition_price', 10, 2)->nullable();
            $table->text('comments')->nullable();
            $table->json('real_photos')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['player_user_id', 'catalog_print_id'], 'idx_pci_player_print');
        });

        // 2. Fichários Físicos Criados
        Schema::create('player_binders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_user_id')->constrained('player_users')->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();

            $table->enum('pocket_layout', ['2x2', '3x3', '4x4', 'bulk'])->default('3x3');
            $table->unsignedInteger('factory_sheets_count')->default(20);
            $table->unsignedInteger('current_sheets_count')->default(20);

            $table->string('cover_material')->default('leather_black');
            $table->string('cover_image_path')->nullable();
            $table->string('strap_color')->default('orange');

            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        // 3. Bolsos das Folhas do Fichário
        Schema::create('player_binder_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('binder_id')->constrained('player_binders')->cascadeOnDelete();
            $table->foreignId('player_collection_item_id')->constrained('player_collection_items')->cascadeOnDelete();

            $table->unsignedInteger('page_number');
            $table->unsignedTinyInteger('slot_position');
            $table->unsignedTinyInteger('quantity_in_slot')->default(1);

            $table->timestamps();

            $table->unique(['binder_id', 'page_number', 'slot_position'], 'uniq_binder_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_binder_slots');
        Schema::dropIfExists('player_binders');
        Schema::dropIfExists('player_collection_items');
    }
};
