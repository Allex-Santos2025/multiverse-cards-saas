<?php

namespace App\Livewire\Lobby;

use App\Models\Game;
use App\Models\PlayerDeck;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Decks extends Component
{
    public ?int $activeDeckId = null;
    public ?int $sandboxDeckId = null;

    public bool $modalCreateDeck = false;
    public string $newDeckName = '';
    public ?int $newDeckGameId = null;
    public string $newDeckFormat = 'constructed';

    protected $listeners = [
        'refreshDecks'     => '$refresh',
        'closeSandboxMode' => 'closeSandbox',
    ];

    public function openDeckBuilder(int $deckId): void
    {
        $this->sandboxDeckId = null;
        $this->dispatch('sandbox-mode-changed', false);
        $this->activeDeckId = $deckId;
    }

    public function closeDeckBuilder(): void
    {
        $this->activeDeckId = null;
    }

    public function openSandbox(int $deckId): void
    {
        $this->activeDeckId = null;
        $this->sandboxDeckId = $deckId;
        $this->dispatch('sandbox-mode-changed', true);
    }

    public function closeSandbox(): void
    {
        $this->sandboxDeckId = null;
        $this->dispatch('sandbox-mode-changed', false);
    }

    public function openCreateDeckModal(): void
    {
        $firstGame = Game::first();
        $this->newDeckGameId = $firstGame ? $firstGame->id : null;
        $this->newDeckName = '';
        $this->newDeckFormat = 'constructed';
        $this->modalCreateDeck = true;
    }

    public function saveNewDeck(): void
    {
        $this->validate([
            'newDeckName'   => 'required|min:2|max:60',
            'newDeckGameId' => 'required|exists:games,id',
        ]);

        $playerId = Auth::guard('player')->id();

        $deck = PlayerDeck::create([
            'player_user_id' => $playerId,
            'game_id'        => $this->newDeckGameId,
            'name'           => $this->newDeckName,
            'format'         => $this->newDeckFormat,
        ]);

        $this->modalCreateDeck = false;
        $this->activeDeckId = $deck->id;
    }

    public function deleteDeck(int $deckId): void
    {
        PlayerDeck::where('id', $deckId)
            ->where('player_user_id', Auth::guard('player')->id())
            ->delete();

        if ($this->activeDeckId === $deckId) {
            $this->activeDeckId = null;
        }
        if ($this->sandboxDeckId === $deckId) {
            $this->sandboxDeckId = null;
            $this->dispatch('sandbox-mode-changed', false);
        }
    }

    public function render()
    {
        $playerId = Auth::guard('player')->id();

        $decks = PlayerDeck::where('player_user_id', $playerId)
            ->with(['game', 'cards'])
            ->withSum('cards', 'quantity')
            ->latest()
            ->get();

        $games = Game::all();

        return view('livewire.lobby.decks', [
            'decks' => $decks,
            'games' => $games,
        ]);
    }
}
