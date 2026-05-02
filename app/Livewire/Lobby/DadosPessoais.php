<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 

class DadosPessoais extends Component
{
    use WithFileUploads;

    // Propriedades vinculadas diretamente às colunas do banco
    public $name;
    public $surname;
    public $nickname;
    public $email;
    public $document_number;    // CPF
    public $id_document_number; // RG
    public $phone_number;
    public $birth_date;

    // Propriedades de Senha e Avatar
    public $avatar;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    
    // Propriedade nova para receber o arquivo físico da foto
    public $photo; 

    public function mount()
    {
        $player = Auth::guard('player')->user();

        $this->name = $player->name;
        $this->surname = $player->surname;
        $this->nickname = $player->nickname;
        $this->email = $player->email;
        $this->document_number = $player->document_number;
        $this->id_document_number = $player->id_document_number;
        $this->phone_number = $player->phone_number;
        
        // CORREÇÃO AQUI: Força o formato YYYY-MM-DD para o input HTML5 entender
        $this->birth_date = $player->birth_date ? \Carbon\Carbon::parse($player->birth_date)->format('Y-m-d') : null;
        
        $this->avatar = $player->avatar ?? null;
    }

    public function selecionarAvatar($url)
    {
        $this->avatar = $url;
        $this->photo = null; // Limpa o upload se escolher um avatar pronto
    }

    public function salvar()
    {
        $player = Auth::guard('player')->user();

        $this->validate([
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'nickname' => 'required|string|max:100|unique:player_users,nickname,' . $player->id,
            'email' => 'required|email|unique:player_users,email,' . $player->id,
            'document_number' => 'nullable|string|max:20',
            'id_document_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|same:new_password_confirmation',
            'photo' => 'nullable|image|max:2048', // Valida se é imagem e tem até 2MB
        ]);

        if (!empty($this->new_password)) {
            if (!Hash::check($this->current_password, $player->password)) {
                $this->addError('current_password', 'A senha atual está incorreta.');
                return;
            }
            $player->password = Hash::make($this->new_password);
        }

        // Lógica de upload ajustada para salvar diretamente na pasta public raiz
        if ($this->photo) {
            $fileName = uniqid() . '.' . $this->photo->getClientOriginalExtension();
            $directory = 'store_images/player/avatar';
            
            if (!file_exists(public_path($directory))) {
                mkdir(public_path($directory), 0755, true);
            }
            
            // Extrai o arquivo da pasta temporária do Livewire e joga na pasta correta
            file_put_contents(public_path($directory . '/' . $fileName), file_get_contents($this->photo->getRealPath()));
            
            $player->avatar = '/' . $directory . '/' . $fileName; 
        } elseif ($this->avatar) {
            $player->avatar = $this->avatar;
        }

        $player->update([
            'name' => $this->name,
            'surname' => $this->surname,
            'nickname' => $this->nickname,
            'email' => $this->email,
            'document_number' => $this->document_number,
            'id_document_number' => $this->id_document_number,
            'phone_number' => $this->phone_number,
            // CORREÇÃO AQUI: Garante que se o campo for apagado, ele envia null para o banco e não uma string vazia
            'birth_date' => empty($this->birth_date) ? null : $this->birth_date,
        ]);

        // DISPARAR O EVENTO PARA O MAESTRO OUVIR
        $this->dispatch('perfil-atualizado');
        
        $this->reset(['current_password', 'new_password', 'new_password_confirmation', 'photo']);
        session()->flash('message', 'Dados atualizados com sucesso!');
    }

    public function render()
    {
        return view('livewire.lobby.dados-pessoais');
    }
}