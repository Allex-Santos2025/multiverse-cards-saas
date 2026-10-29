# Análise Completa do Sistema – Multiverse Cards / Versus TCG

Este documento consolida o diagnóstico técnico e arquitetural completo do sistema **Multiverse Cards / Versus TCG**, mapeando o estado atual do banco de dados, models, Filament resources, Livewire components, services de ingestão, camadas legadas e o plano de padronização agnóstica para as próximas fases.

---

## 📌 1. Visão Geral da Stack e Infraestrutura

- **Framework:** Laravel 12 / PHP 8.2+
- **Painel Administrativo:** Filament 4
- **Reatividade Front-End:** Livewire 3 + Alpine.js + Tailwind CSS + Axios
- **Asset Bundler:** Laravel Mix / PostCSS
- **Banco de Dados:** MySQL / MariaDB (com transição arquitetural de v1/v3 para v4/v5)
- **Busca:** Laravel Scout com suporte a Meilisearch
- **Objetivo Central:** Plataforma integrada para múltiplos Trading Card Games (TCGs), contemplando catálogo unificado, marketplace híbrido multiloja, painel de lojistas, lobby de jogadores (decks, fichários, carteira) e suporte ao jogo autoral **Versus TCG**.

---

## 🏛️ 2. Arquitetura do Catálogo (O Núcleo Agnóstico)

O sistema opera na transição do modelo fortemente acoplado ao Magic: The Gathering (v1–v3) para o modelo agnóstico polimórfico (v4/v5):

### 2.1 Camada Agnóstica (Catálogo Unificado)
- **`games` (`App\Models\Game`):** Tabela raiz com o cadastro dos jogos (Magic, Pokémon, One Piece, Yu-Gi-Oh!, Battle Scenes, Lorcana, etc.). Controla URLs, slugs, status ativo e classe do ingestor oficial.
- **`sets` (`App\Models\Set`):** Coleções e expansões vinculadas aos jogos (`game_id`). Armazena metadados de lançamento, contagem de cartas, ícones SVG (`icon_svg_uri`) e tipo de set.
- **`catalog_concepts` (`App\Models\Catalog\CatalogConcept`):** O conceito abstrato da carta (ex: *"Lightning Bolt"*, *"Pikachu"*), independente de edição física.
  - Relacionamentos polimórficos: `specific_type` e `specific_id` apontando para o concept específico do jogo (`MtgConcept`, `PkConcept`, etc.).
  - Campo `search_names` (JSON) para indexação multilíngue e busca otimizada.
- **`catalog_prints` (`App\Models\Catalog\CatalogPrint`):** Impressões físicas específicas de uma carta em uma edição (`set_id`).
  - Aponta para `concept_id` e possui polimorfismo (`specific_type`, `specific_id`) para o print do jogo (`MtgPrint`, `PkPrint`, etc.).
  - Controla o caminho local da arte em `image_path`.

### 2.2 Camada Específica por Jogo (Models Especializados)
- **Magic:** `App\Models\Games\Magic\MtgConcept` e `MtgPrint` (oracle text, mana cost, cores, scryfall IDs, acabamentos, molduras).
- **Pokémon:** `App\Models\Games\Pokemon\PkConcept` e `PkPrint` (HP, tipos, fraquezas, ataques, recuo, numeração de Pokédex).
- **One Piece:** `App\Models\Games\OnePiece\OpConcept` e `OpPrint` (líderes, personagens, counter, traits).
- **Battle Scenes:** `App\Models\Games\BattleScenes\BsConcept` e `BsPrint` (alter ego, energia, escudo, afiliações).
- **Yu-Gi-Oh!:** `App\Models\Catalog\Games\Yugioh\YgoConcept` e `YgoPrint` (ataque, defesa, nível, escala pêndulo, banlist).

### 2.3 Camada Legada a ser Desativada (v1–v3)
- **`cards` e `card_functionalities`:** Tabelas monolíticas históricas com centenas de colunas prefixadas (`mtg_*`, `pk_*`, `op_*`).
- **`rulings`:** Julgamentos oficiais acoplados à tabela antiga `card_functionalities`. Deve ser migrada para `mtg_rulings`.

---

## 👥 3. Arquitetura de Autenticação e Perfis (Multi-Guards)

O sistema utiliza tabelas independentes e guards específicos para cada tipo de ator, evitando mistura de permissões:

1. **Super Admin (`users` / `App\Models\User`):** Usuário raiz único e absoluto do sistema, com proteção de integridade contra deleção acidental.
2. **Staff Global (`admin_users` / `App\Models\AdminUser`):** Administradores secundários criados pelo super admin para suporte à plataforma.
3. **Donos de Loja (`store_users` / `App\Models\StoreUser`):** Usuários proprietários de lojas físicas/virtuais no marketplace.
4. **Funcionários de Loja (`store_admin_users` / `App\Models\StoreAdminUser`):** Operadores criados pelos donos de loja com controle granular via `permissions_json`.
5. **Jogadores / Clientes (`player_users` / `App\Models\PlayerUser`):** Usuários consumidores com perfil de jogador, fichários (`player_binders`), wantlists, histórico de pedidos e construtor de decks.

*(Observação: O model duplicado `SuperUser` está obsoleto e marcado para remoção).*

---

## 🏪 4. Lojas, Estoque e Marketplace Híbrido

- **Lojas (`stores` / `App\Models\Store`):**
  - Identificador próprio (`url_slug`) permitindo domínios standalone e subdomínios.
  - Regras de negócio, descontos (PIX, fidelidade), configurações de frete (`store_shipping_settings`) e identidade visual customizável (`store_visuals`).
- **Estoque (`stock_items` / `App\Models\StockItem`):**
  - Conecta a loja ao print unificado: `store_id` + `catalog_print_id`.
  - Controle de qualidade e acabamento: `condition` (NM, SP, MP, HP, DM), `language`, `is_foil`, `quantity` e `price`.
- **Lobby do Jogador (Livewire):**
  - Módulos completos para: Gestão de Carteira (`Carteira`), Coleção Pessoal (`Colecao`), Fichários Virtuais (`FicharioViewer`), Decks e Testes (`DeckBuilder`, `DeckSandbox`), Pedidos (`MeusPedidos`) e Checkout (`Checkout`).
- **Painel do Lojista (Livewire / Filament):**
  - Gestão de catálogo local, importação/exportação de inventário em massa (`InventoryImport`, `InventoryExport`), ajuste de estoque por carta (`ManageSingleCard`), histórico de vendas e configurações operacionais.

---

## ⚙️ 5. Ingestão de Dados (Services & Commands)

Mecanismos automatizados para captura e sincronização de dados externos:

### Services:
- **`ScryfallApi`:** Comunicação oficial com Scryfall (Magic), rate limit, download de coleções completas e cartas.
- **`PokemonTcgApiService`:** Integração com a API oficial do Pokémon TCG para importação de sets e prints.
- **`BattleScenesScraper` & `BattleScenesIngestorService`:** Crawler dedicado para raspagem estruturada de coleções e cartas de Battle Scenes (via MagicJebb).

### Console Commands:
- `ingest:run` (`IngestionManagerCommand`): Orquestrador central de ingestão via CLI.
- `scryfall:ingest` / `scryfall:ingest-cards` / `scryfall:ingest-rulings`: Pipelines de Magic.
- `pokemon:ingest-cards`: Ingestor de Pokémon TCG para o catálogo V4.
- `battlescenes:ingest-sets` / `bs:ingest-cards`: Ingestão de Battle Scenes.
- `scryfall:build-search-index`: Indexação de nomes pesquisáveis.

---

## 🎛️ 6. Painel Administrativo (Filament)

Cobertura completa de resources administrativos em `app/Filament/Resources/`:
- **Catálogo:** `CatalogConcepts`, `CatalogPrints`, `CatalogProducts`, `Games`, `Sets`.
- **Usuários:** `Users`, `AdminUsers`, `StoreUsers`, `StoreAdminUsers`, `PlayerUsers`.
- **Lojas:** `Stores`, `StoreUsers`.
- **Operações:** `Changelogs`, relatórios e visualizações de conformidade.
- **Legados em transição:** `Cards`, `CardFunctionalities` (prontos para serem desligados após a migração definitiva).

---

## 🎯 7. Diretrizes de Padronização para as Próximas Etapas

Para garantir que o sistema permaneça **estritamente agnóstico a regras de jogos específicos**, mas com **padrão de código e arquitetura rigorosos**, os seguintes pontos foram definidos como prioritários:

1. **Padronização dos Contratos de Catálogo:**
   - Criar interfaces TypeScript / PHP para os conceitos e prints (`GameConceptInterface`, `GamePrintInterface`).
   - Padronizar métodos de resolução de imagem, numeração de colecionador e raridade.
2. **Eliminação de Vínculos com Tabelas Legadas:**
   - Garantir que `stock_items` utilize exclusivamente `catalog_print_id`, eliminando de vez colunas ou referências a `card_id` / `cards`.
   - Limpar FKs órfãs de `card_functionalities`.
3. **Padronização da Interface de Ingestores:**
   - Definir contrato comum (`IngestorServiceInterface`) com ciclo de vida homogêneo para fácil adição de novos TCGs (One Piece, Lorcana, Digimon).
4. **Padronização dos Componentes Visuais (Blade / Livewire):**
   - Unificar componentes de exibição de cartas, seletores de idioma/foil e badges de raridade sob componentes reutilizáveis agnósticos.
5. **Integração com o Motor de Regras do Versus TCG:**
   - Manter o Rules Engine do jogo autoral (definido em `VERSUS_TCG_CONTEXT.md`) desacoplado do marketplace, funcionando como um módulo de jogo isolado e determinístico.
