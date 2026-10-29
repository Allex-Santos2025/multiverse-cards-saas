<?php

namespace App\Livewire\Lobby;

use App\Models\PlayerDeck;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeckSandbox extends Component
{
    public int $deckId;
    public ?PlayerDeck $deck = null;

    public array $library = [];
    public array $hand = [];
    public array $battlefieldFront = [];
    public array $battlefieldBack = [];
    public array $graveyard = [];
    public array $exile = [];

    // Fichas disponíveis no deck
    public array $availableTokens = [];

    public int $life = 20;
    public int $turn = 1;
    public int $instanceCounter = 1;

    public function mount(int $deckId): void
    {
        $this->deckId = $deckId;
        $this->loadDeck();
    }

    public function loadDeck(): void
    {
        $playerId = Auth::guard('player')->id();

        $this->deck = PlayerDeck::where('id', $this->deckId)
            ->where('player_user_id', $playerId)
            ->with(['cards.catalogPrint.concept', 'game'])
            ->firstOrFail();

        $this->life = strtolower($this->deck->format) === 'commander' ? 40 : 20;
        $this->restartGame();
    }

    public function restartGame(): void
    {
        $this->library = [];
        $this->hand = [];
        $this->battlefieldFront = [];
        $this->battlefieldBack = [];
        $this->graveyard = [];
        $this->exile = [];
        $this->availableTokens = [];
        $this->turn = 1;
        $this->instanceCounter = 1;

        foreach ($this->deck->cards as $deckCard) {
            $boardType = $deckCard->board_type ?? 'main';

            // Sideboard ignorado no jogo principal
            if ($boardType === 'side') {
                continue;
            }

            $print = $deckCard->catalogPrint;
            $concept = $print?->concept;
            $image = $print?->image_url ?? $print?->image_path ?? $this->deck->cover_image_path ?? '/images/placeholder-card.png';
            $name = $print?->name ?? $concept?->name ?? ('Carta #' . $deckCard->id);
            $typeLine = strtolower($concept?->type_line ?? $concept?->type ?? '');

            $isLandOrEnergy = str_contains($typeLine, 'land') 
                || str_contains($typeLine, 'terreno') 
                || str_contains($typeLine, 'energy') 
                || str_contains($typeLine, 'energia');

            // Se for do tipo token, armazena na gaveta de fichas
            if ($boardType === 'token') {
                $this->availableTokens[] = [
                    'card_id'        => $deckCard->id,
                    'name'           => $name,
                    'image'          => $image,
                    'default_target' => $isLandOrEnergy ? 'back' : 'front',
                ];
                continue;
            }

            // Cartas normais (main / commander)
            for ($i = 0; $i < $deckCard->quantity; $i++) {
                $this->library[] = [
                    'instance_id'    => $this->instanceCounter++,
                    'name'           => $name,
                    'image'          => $image,
                    'is_tapped'      => false,
                    'is_token'       => false,
                    'counters'       => 0,
                    'attached'       => [],
                    'default_target' => $isLandOrEnergy ? 'back' : 'front',
                ];
            }
        }

        $this->shuffleLibrary();
        $this->drawCards(7);
    }

    public function shuffleLibrary(): void
    {
        shuffle($this->library);
    }

    public function drawCards(int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            if (!empty($this->library)) {
                $this->hand[] = array_shift($this->library);
            }
        }
    }

    public function mulligan(): void
    {
        if (!empty($this->hand)) {
            $this->library = array_merge($this->library, $this->hand);
            $this->hand = [];
        }

        $this->shuffleLibrary();
        $this->drawCards(7);
    }

    // Cria um token cadastrado no deck e posiciona em campo
    public function spawnOfficialToken(int $tokenIndex, string $targetLine = 'front'): void
    {
        if (!isset($this->availableTokens[$tokenIndex])) {
            return;
        }

        $tokenData = $this->availableTokens[$tokenIndex];

        $tokenInstance = [
            'instance_id'    => $this->instanceCounter++,
            'name'           => $tokenData['name'],
            'image'          => $tokenData['image'],
            'is_tapped'      => false,
            'is_token'       => true,
            'counters'       => 0,
            'attached'       => [],
            'default_target' => $targetLine,
        ];

        if ($targetLine === 'back') {
            $this->battlefieldBack[] = $tokenInstance;
        } else {
            $this->battlefieldFront[] = $tokenInstance;
        }
    }

    // Cria um token customizado/genérico quando não há carta cadastrada
    public function spawnCustomToken(string $name, string $pt = '1/1', string $targetLine = 'front'): void
    {
        $tokenInstance = [
            'instance_id'    => $this->instanceCounter++,
            'name'           => trim($name) !== '' ? trim($name) . " ($pt)" : "Ficha ($pt)",
            'image'          => null,
            'is_tapped'      => false,
            'is_token'       => true,
            'custom_pt'      => $pt,
            'counters'       => 0,
            'attached'       => [],
            'default_target' => $targetLine,
        ];

        if ($targetLine === 'back') {
            $this->battlefieldBack[] = $tokenInstance;
        } else {
            $this->battlefieldFront[] = $tokenInstance;
        }
    }

    public function dropCard(int $instanceId, string $fromZone, string $toZone): void
    {
        if ($fromZone === $toZone) {
            return;
        }

        $card = $this->extractCardFromZone($instanceId, $fromZone);
        if (!$card) {
            return;
        }

        // Regra de Tokens: ao sair de campo para cemitério/exílio/mão/deck, o token é destruído
        if (!empty($card['is_token']) && in_array($toZone, ['hand', 'graveyard', 'exile', 'library_top', 'library_bottom'])) {
            return;
        }

        if (in_array($toZone, ['hand', 'graveyard', 'exile', 'library_top', 'library_bottom'])) {
            $card['is_tapped'] = false;
            $card['counters'] = 0;
            if (!empty($card['attached'])) {
                foreach ($card['attached'] as $att) {
                    $att['is_tapped'] = false;
                    $att['counters'] = 0;
                    if (empty($att['is_token'])) {
                        if ($toZone === 'graveyard' || $toZone === 'exile') {
                            $this->{$toZone}[] = $att;
                        } else {
                            $this->graveyard[] = $att;
                        }
                    }
                }
                $card['attached'] = [];
            }
        }

        match ($toZone) {
            'front'          => $this->battlefieldFront[] = $card,
            'back'           => $this->battlefieldBack[] = $card,
            'hand'           => $this->hand[] = $card,
            'graveyard'      => $this->graveyard[] = $card,
            'exile'          => $this->exile[] = $card,
            'library_top'    => array_unshift($this->library, $card),
            'library_bottom' => $this->library[] = $card,
            default          => null
        };
    }

    public function attachCard(int $sourceId, string $fromZone, int $targetId, string $targetZone): void
    {
        if ($sourceId === $targetId) {
            return;
        }

        $sourceCard = $this->extractCardFromZone($sourceId, $fromZone);
        if (!$sourceCard) {
            return;
        }

        $sourceCard['is_tapped'] = false;

        if ($targetZone === 'back') {
            $primaryList = &$this->battlefieldBack;
            $secondaryList = &$this->battlefieldFront;
        } else {
            $primaryList = &$this->battlefieldFront;
            $secondaryList = &$this->battlefieldBack;
        }

        foreach ($primaryList as &$card) {
            if ((int)$card['instance_id'] === $targetId) {
                if (!isset($card['attached'])) {
                    $card['attached'] = [];
                }
                $card['attached'][] = $sourceCard;
                return;
            }
        }

        foreach ($secondaryList as &$card) {
            if ((int)$card['instance_id'] === $targetId) {
                if (!isset($card['attached'])) {
                    $card['attached'] = [];
                }
                $card['attached'][] = $sourceCard;
                return;
            }
        }

        match ($fromZone) {
            'front' => $this->battlefieldFront[] = $sourceCard,
            'back'  => $this->battlefieldBack[] = $sourceCard,
            default => $this->hand[] = $sourceCard
        };
    }

    public function detachCard(int $hostId, string $hostZone, int $attachedIndex): void
    {
        if ($hostZone === 'back') {
            $targetList = &$this->battlefieldBack;
        } else {
            $targetList = &$this->battlefieldFront;
        }

        foreach ($targetList as &$card) {
            if ((int)$card['instance_id'] === $hostId && isset($card['attached'][$attachedIndex])) {
                $detached = $card['attached'][$attachedIndex];
                array_splice($card['attached'], $attachedIndex, 1);
                $targetList[] = $detached;
                return;
            }
        }
    }

    public function adjustCounters(int $instanceId, string $zone, int $amount): void
    {
        if ($zone === 'back') {
            $targetList = &$this->battlefieldBack;
        } else {
            $targetList = &$this->battlefieldFront;
        }

        foreach ($targetList as &$card) {
            if ((int)$card['instance_id'] === $instanceId) {
                $card['counters'] = max(0, ($card['counters'] ?? 0) + $amount);
                break;
            }
        }
    }

    public function playCard(int $instanceId, ?string $forceZone = null): void
    {
        $card = $this->extractCardFromZone($instanceId, 'hand');
        if (!$card) {
            return;
        }

        $target = $forceZone ?? $card['default_target'] ?? 'front';
        $card['is_tapped'] = false;

        if ($target === 'back') {
            $this->battlefieldBack[] = $card;
        } else {
            $this->battlefieldFront[] = $card;
        }
    }

    public function toggleTap(int $instanceId, string $zone): void
    {
        if ($zone === 'back') {
            $targetList = &$this->battlefieldBack;
        } else {
            $targetList = &$this->battlefieldFront;
        }

        foreach ($targetList as &$card) {
            if ((int)$card['instance_id'] === $instanceId) {
                $card['is_tapped'] = !$card['is_tapped'];
                break;
            }
        }
    }

    public function untapAll(): void
    {
        foreach ($this->battlefieldFront as &$card) {
            $card['is_tapped'] = false;
        }
        foreach ($this->battlefieldBack as &$card) {
            $card['is_tapped'] = false;
        }
    }

    public function returnToHand(int $instanceId, string $fromZone): void
    {
        $this->dropCard($instanceId, $fromZone, 'hand');
    }

    public function sendToGraveyard(int $instanceId, string $fromZone): void
    {
        $this->dropCard($instanceId, $fromZone, 'graveyard');
    }

    public function sendToExile(int $instanceId, string $fromZone): void
    {
        $this->dropCard($instanceId, $fromZone, 'exile');
    }

    public function moveBetweenLines(int $instanceId, string $currentZone): void
    {
        $target = $currentZone === 'front' ? 'back' : 'front';
        $this->dropCard($instanceId, $currentZone, $target);
    }

    private function extractCardFromZone(int $instanceId, string $zone): ?array
    {
        $mapping = [
            'hand'      => &$this->hand,
            'front'     => &$this->battlefieldFront,
            'back'      => &$this->battlefieldBack,
            'graveyard' => &$this->graveyard,
            'exile'     => &$this->exile,
        ];

        if (!isset($mapping[$zone])) {
            return null;
        }

        foreach ($mapping[$zone] as $key => $card) {
            if ((int)$card['instance_id'] === $instanceId) {
                $found = $card;
                unset($mapping[$zone][$key]);
                $mapping[$zone] = array_values($mapping[$zone]);
                return $found;
            }
        }

        return null;
    }

    public function nextTurn(): void
    {
        $this->turn++;
        $this->untapAll();
        $this->drawCards(1);
    }

    public function adjustLife(int $amount): void
    {
        $this->life += $amount;
    }

    public function closeSandbox(): void
    {
        $this->dispatch('closeSandboxMode');
    }

    public function render()
    {
        return view('livewire.lobby.deck-sandbox');
    }
}
