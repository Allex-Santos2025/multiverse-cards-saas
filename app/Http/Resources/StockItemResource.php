<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; // Necessário para gerar link das fotos reais

class StockItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Carrega o print base
        $print = $this->catalogPrint;

        return [
            'id' => $this->id,
            
            // DADOS DO CATÁLOGO (Imagens Locais e Infos Fixas)
            'name' => $print->name ?? 'Desconhecido',
            'name_pt' => $print->name_pt ?? null,
            'set_code' => $print->set_code ?? '---',
            'collector_number' => $print->collector_number ?? '---',
            
            // Lógica da Imagem do Sistema (Armazenada Localmente)
            // Se o campo image_path for 'cards/magic/123.jpg', o asset() gera a URL completa
            'imageUrl' => $print->image_path ? asset($print->image_path) : asset('assets/img/card-back.jpg'),

            // DADOS DO LOJISTA (Editáveis via Alpine)
            'price' => (float) $this->price,
            'quantity' => (int) $this->quantity,
            'condition' => $this->condition, // NM, SP...
            'lang' => $this->language,       // pt, en...
            
            // Extras Selecionados (JSON -> Array)
            'extras' => $this->extras ?? [], 
            
            // Fotos Reais (Uploads do Lojista)
            // Transforma o caminho relativo em URL completa
            'real_photos' => collect($this->real_photos ?? [])->map(fn($path) => Storage::url($path))->toArray(),
            'comments' => $this->comments,
        ];
    }
}