<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use App\Models\PlayerAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class Enderecos extends Component
{
    public $enderecos = [];
    public $showForm = false;

    // Campos do Formulário
    public $endereco_id;
    public $title;
    public $receiver_name;
    public $zip_code;
    public $street;
    public $number;
    public $complement;
    public $neighborhood;
    public $city;
    public $state;
    public $is_official = false;

    public function mount()
    {
        $this->carregarEnderecos();
    }

    public function carregarEnderecos()
    {
        // Puxa os endereços e coloca o oficial sempre no topo
        $this->enderecos = Auth::guard('player')->user()->addresses()->orderByDesc('is_official')->get();
    }

    public function novoEndereco()
    {
        if (count($this->enderecos) >= 3) {
            session()->flash('error', 'Você já atingiu o limite de 3 endereços cadastrados.');
            return;
        }

        $this->resetForm();
        
        // Se não tiver nenhum endereço, esse obrigatoriamente será o oficial
        if (count($this->enderecos) === 0) {
            $this->is_official = true;
        }

        $this->showForm = true;
    }

    public function editarEndereco($id)
    {
        $endereco = PlayerAddress::where('player_user_id', Auth::guard('player')->id())->findOrFail($id);
        
        $this->endereco_id = $endereco->id;
        $this->title = $endereco->title;
        $this->receiver_name = $endereco->receiver_name;
        $this->zip_code = $endereco->zip_code;
        $this->street = $endereco->street;
        $this->number = $endereco->number;
        $this->complement = $endereco->complement;
        $this->neighborhood = $endereco->neighborhood;
        $this->city = $endereco->city;
        $this->state = $endereco->state;
        $this->is_official = $endereco->is_official;

        $this->showForm = true;
    }
    
    public function updatedZipCode($value)
    {
        $cepLimpo = preg_replace('/[^0-9]/', '', $value);
        
        if (strlen($cepLimpo) === 8) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->timeout(5)
                    ->get("https://brasilapi.com.br/api/cep/v1/{$cepLimpo}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    $this->street = $data['street'] ?? '';
                    $this->neighborhood = $data['neighborhood'] ?? '';
                    $this->city = $data['city'] ?? '';
                    $this->state = $data['state'] ?? '';
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Erro: ' . $e->getMessage());
            }
        }
    }

    public function salvarEndereco()
    {
        $this->validate([
            'title' => 'required|string|max:50',
            'receiver_name' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:50',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
        ]);

        $player_id = Auth::guard('player')->id();

        // Se marcou como oficial (ou é o primeiro), desmarca todos os outros deste player
        if ($this->is_official || count($this->enderecos) === 0) {
            PlayerAddress::where('player_user_id', $player_id)->update(['is_official' => false]);
            $this->is_official = true;
        }

        if ($this->endereco_id) {
            // Atualiza existente
            $endereco = PlayerAddress::where('player_user_id', $player_id)->findOrFail($this->endereco_id);
            $endereco->update([
                'title' => $this->title,
                'receiver_name' => $this->receiver_name,
                'zip_code' => $this->zip_code,
                'street' => $this->street,
                'number' => $this->number,
                'complement' => $this->complement,
                'neighborhood' => $this->neighborhood,
                'city' => $this->city,
                'state' => $this->state,
                'is_official' => $this->is_official,
            ]);
            session()->flash('message', 'Endereço atualizado com sucesso!');
        } else {
            // Cria novo
            PlayerAddress::create([
                'player_user_id' => $player_id,
                'title' => $this->title,
                'receiver_name' => $this->receiver_name,
                'zip_code' => $this->zip_code,
                'street' => $this->street,
                'number' => $this->number,
                'complement' => $this->complement,
                'neighborhood' => $this->neighborhood,
                'city' => $this->city,
                'state' => $this->state,
                'is_official' => $this->is_official,
            ]);
            session()->flash('message', 'Endereço cadastrado com sucesso!');
        }

        $this->showForm = false;
        $this->carregarEnderecos();
    }

    public function tornarOficial($id)
    {
        $player_id = Auth::guard('player')->id();
        PlayerAddress::where('player_user_id', $player_id)->update(['is_official' => false]);
        PlayerAddress::where('player_user_id', $player_id)->where('id', $id)->update(['is_official' => true]);
        
        $this->carregarEnderecos();
        session()->flash('message', 'Endereço principal atualizado!');
    }

    public function excluirEndereco($id)
    {
        PlayerAddress::where('player_user_id', Auth::guard('player')->id())->where('id', $id)->delete();
        $this->carregarEnderecos();

        // Se excluiu o oficial, promove o primeiro que sobrou a oficial
        if (count($this->enderecos) > 0 && !$this->enderecos->contains('is_official', true)) {
            $primeiro = $this->enderecos->first();
            $primeiro->update(['is_official' => true]);
            $this->carregarEnderecos();
        }

        session()->flash('message', 'Endereço removido com sucesso.');
    }

    public function cancelar()
    {
        $this->showForm = false;
    }

    private function resetForm()
    {
        $this->endereco_id = null;
        $this->title = '';
        $this->receiver_name = '';
        $this->zip_code = '';
        $this->street = '';
        $this->number = '';
        $this->complement = '';
        $this->neighborhood = '';
        $this->city = '';
        $this->state = '';
        $this->is_official = false;
    }

    public function render()
    {
        return view('livewire.lobby.enderecos');
    }
}