<?php

namespace App\Livewire\Store\Dashboard\Layout;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\StoreVisual;
use App\Models\Store;

class VisualIdentity extends Component
{
    use WithFileUploads;

    // --- VARIÁVEIS DE CONTROLE ---
    public $slug;
    public $userStoreId;
    public $tab = 'basico';

    // --- PROPRIEDADES DE CORES ---
    public $color_primary;
    public $color_topbar_bg;
    public $color_header_bg;
    public $color_footer_bg;
    public $global_bg_color;
    public $color_menu_text;
    public $color_cta;
    public $color_menu_hover;

    // --- PROPRIEDADES DE IMAGENS (UPLOADS) ---
    public $upload_logo_main;
    public $upload_logo_footer;
    public $upload_logo_marketplace;
    public $upload_avatar_marketplace;
    public $upload_favicon;

    // --- IMAGENS ATUAIS (PREVIEW) E OPÇÕES ---
    public $current_logo_main;
    public $current_logo_footer;
    public $current_logo_marketplace;
    public $current_avatar_marketplace;
    public $current_favicon;
    public $use_logo_dashboard;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->userStoreId = auth('store_user')->user()->current_store_id;

        $visual = StoreVisual::where('store_id', $this->userStoreId)->firstOrCreate(
            ['store_id' => $this->userStoreId]
        );

        // Alimenta Cores
        $this->color_primary   = $visual->color_primary;
        $this->color_topbar_bg = $visual->color_topbar_bg;
        $this->color_header_bg = $visual->color_header_bg;
        $this->color_footer_bg = $visual->color_footer_bg;
        $this->global_bg_color = $visual->global_bg_color;
        $this->color_menu_text = $visual->color_menu_text;
        $this->color_cta        = $visual->color_cta ?? '#F59E0B';
        $this->color_menu_hover = $visual->color_menu_hover ?? '#2563EB';

        // Alimenta Imagens e Opções (TUDO NA STORE_VISUALS)
        $this->current_logo_main          = $visual->logo_main;
        $this->current_logo_footer        = $visual->logo_footer;
        $this->current_logo_marketplace   = $visual->logo_marketplace;
        $this->current_avatar_marketplace = $visual->avatar_marketplace;
        $this->current_favicon            = $visual->favicon;
        $this->use_logo_dashboard         = $visual->use_logo_dashboard ?? true;
    }

    public function save()
    {
        $this->validate([
            'upload_logo_main'          => 'nullable|image|max:2048',
            'upload_logo_footer'        => 'nullable|image|max:2048',
            'upload_logo_marketplace'   => 'nullable|image|max:2048',
            'upload_avatar_marketplace' => 'nullable|image|max:1024',
            'upload_favicon'            => 'nullable|image|mimes:ico,png|max:1024',
        ]);

        DB::beginTransaction();

        try {
            // Buscamos o registro atual ou criamos um novo
            $visual = StoreVisual::where('store_id', $this->userStoreId)->first();
            $loja = Store::find($this->userStoreId);
            
            $destinationPath = public_path('store_images/' . $loja->url_slug);
            \Illuminate\Support\Facades\File::ensureDirectoryExists($destinationPath, 0755, true);

            if ($visual) {
                // 1. LOGO PRINCIPAL
                if ($this->upload_logo_main) {
                    $nameMain = 'logo_main_' . time() . '.' . $this->upload_logo_main->extension();
                    \Illuminate\Support\Facades\File::copy($this->upload_logo_main->getRealPath(), $destinationPath . '/' . $nameMain);
                    $visual->logo_main = $nameMain;
                    $this->current_logo_main = $nameMain;
                }

                // 2. LOGO FOOTER
                if ($this->upload_logo_footer) {
                    $nameFooter = 'logo_footer_' . time() . '.' . $this->upload_logo_footer->extension();
                    \Illuminate\Support\Facades\File::copy($this->upload_logo_footer->getRealPath(), $destinationPath . '/' . $nameFooter);
                    $visual->logo_footer = $nameFooter;
                    $this->current_logo_footer = $nameFooter;
                }

                // 3. LOGO MARKETPLACE
                if ($this->upload_logo_marketplace) {
                    $nameMkp = 'logo_mkp_' . time() . '.' . $this->upload_logo_marketplace->extension();
                    \Illuminate\Support\Facades\File::copy($this->upload_logo_marketplace->getRealPath(), $destinationPath . '/' . $nameMkp);
                    $visual->logo_marketplace = $nameMkp;
                    $this->current_logo_marketplace = $nameMkp;
                }

                // 4. AVATAR MARKETPLACE
                if ($this->upload_avatar_marketplace) {
                    $nameAvatar = 'avatar_mkp_' . time() . '.' . $this->upload_avatar_marketplace->extension();
                    \Illuminate\Support\Facades\File::copy($this->upload_avatar_marketplace->getRealPath(), $destinationPath . '/' . $nameAvatar);
                    $visual->avatar_marketplace = $nameAvatar;
                    $this->current_avatar_marketplace = $nameAvatar;
                }

                // 5. FAVICON (Garantindo que a extensão seja pega corretamente)
                if ($this->upload_favicon) {
                    // Pega a extensão real (importante para .ico)
                    $extension = $this->upload_favicon->getClientOriginalExtension() ?: $this->upload_favicon->extension();
                    $nameFav = 'favicon_' . time() . '.' . $extension;
                    
                    \Illuminate\Support\Facades\File::copy(
                        $this->upload_favicon->getRealPath(), 
                        $destinationPath . '/' . $nameFav
                    );
                    
                    $visual->favicon = $nameFav; // Nome exato da sua coluna no DB
                    $this->current_favicon = $nameFav;
                }

                // ATUALIZAÇÃO DAS CORES E BOOLEANS
                $visual->color_primary      = $this->color_primary;
                $visual->color_topbar_bg    = $this->color_topbar_bg;
                $visual->color_header_bg    = $this->color_header_bg;
                $visual->color_footer_bg    = $this->color_footer_bg;
                $visual->global_bg_color    = $this->global_bg_color;
                $visual->color_menu_text    = $this->color_menu_text ?: null;
                $visual->color_cta          = $this->color_cta;
                $visual->color_menu_hover   = $this->color_menu_hover;
                $visual->use_logo_dashboard = $this->use_logo_dashboard;
                
                // SALVAMENTO ÚNICO COM TODAS AS ALTERAÇÕES ACUMULADAS
                $visual->save();
            }

            DB::commit();

            // Limpa os campos de upload para o próximo uso
            $this->reset(['upload_logo_main', 'upload_logo_footer', 'upload_logo_marketplace', 'upload_avatar_marketplace', 'upload_favicon']);

            $this->dispatch('notify', type: 'success', message: 'Toda a identidade visual foi salva!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Erro ao salvar: ' . $e->getMessage());
        }
    }
    
    public function resetToDefault()
    {
        DB::beginTransaction();

        try {
            $visual = StoreVisual::where('store_id', $this->userStoreId)->first();

            $defaults = [
                'color_primary'   => '#2563eb',
                'color_topbar_bg' => '#1e293b',
                'color_header_bg' => '#ffffff',
                'color_footer_bg' => '#0f172a',
                'global_bg_color' => '#ffffff', // Atualizado para bater com seu DB
                'color_menu_text' => null, 
                'color_cta'        => null, // Atualizado para bater com seu DB
                'color_menu_hover' => null, // Atualizado para bater com seu DB
            ];

            if ($visual) {
                $visual->update($defaults);
            }

            $this->color_primary   = $defaults['color_primary'];
            $this->color_topbar_bg = $defaults['color_topbar_bg'];
            $this->color_header_bg = $defaults['color_header_bg'];
            $this->color_footer_bg = $defaults['color_footer_bg'];
            $this->global_bg_color = $defaults['global_bg_color'];
            $this->color_menu_text = $defaults['color_menu_text'];
            $this->color_cta        = $defaults['color_cta'] ?? '#F59E0B';
            $this->color_menu_hover = $defaults['color_menu_hover'] ?? '#2563EB';

            DB::commit();
            $this->dispatch('notify', type: 'success', message: 'Cores resetadas para o padrão de fábrica!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Erro ao resetar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.store.dashboard.layout.visual-identity')
            ->extends('layouts.dashboard');
    }
}