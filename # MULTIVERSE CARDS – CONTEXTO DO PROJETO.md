# MULTIVERSE CARDS – CONTEXTO DO PROJETO

## 📌 VISÃO GERAL

**Projeto:** Multiverse Cards SaaS (Laravel 12)  
**Objetivo:** Marketplace de cartas colecionáveis com suporte a múltiplos card games  
**MVP:** Magic: The Gathering + Pokémon TCG  
**Ambiente:** deve.themultiversenetwork.com.br  
**Admin Panel:** Filament  

---

## 🎮 JOGOS SUPORTADOS (Planejados)

### MVP (Prioridade 1)
- ✅ Magic: The Gathering (MTG)
- ✅ Pokémon TCG

### Futuro (Sem ingestores ainda)
- ⏳ Yu-Gi-Oh! TCG (tabelas criadas, mas MUITO incompletas)
- ⏳ One Piece
- ⏳ Dragon Ball Super
- ⏳ Lorcana
- ⏳ Flesh and Blood
- ⏳ Digimon

### Não Planejado
- ❌ Pokémon OCG (tabelas não existem)
- ❌ Yu-Gi-Oh! OCG (tabelas não existem)

**Decisão:** Focar APENAS em Magic + Pokémon TCG para o MVP. Outros jogos serão adicionados depois com a estrutura correta.

---

## Capitulo 1 - ARQUITETURA DE BANCO DE DADOS

### Estrutura Geral

games (id, name, slug, description, etc.) ├── sets (id, game_id, name, code, release_date, etc.) │ └── Catálogo Unificado ├── catalog_concepts (id, set_id, game_id, specific_type, specific_id) │ └── Aponta para: │ ├── mtg_concepts │ ├── pk_concepts │ └── ygo_concepts │ └── catalog_prints (id, concept_id, set_id, game_id, specific_type, specific_id) └── Aponta para: ├── mtg_prints ├── pk_prints └── ygo_prints

### Tabelas de Usuários
users (tabela base – LEGADA, não refatorada) ├── admin_users (id, user_id, is_active, created_at, updated_at) ├── store_users (id, user_id, store_id, is_active, created_at, updated_at) ├── store_admin_users (id, store_user_id, is_active, created_at, updated_at) └── player_users (id, user_id, mtgo_profile, is_active, created_at, updated_at)

**Regras:**
- Cada usuário tem um único papel (admin, store owner, employee, player)
- Store owners: 1 loja por vez (podem trocar depois)
- Store employees: criados pelo store owner, sem verificação de email
- Admin users: criados pelo super admin, sem verificação de email
- Player users: auto‑registro
- Soft deletes: não implementados ainda (considerar para histórico)

### Tabelas de Lojas
stores (id, store_user_id, name, slug, url_slug, is_active, created_at, updated_at)

**Regras:**
- `url_slug`: domínio standalone (ex.: loja1.themultiversenetwork.com.br)
- `is_active`: false quando em transferência ou não‑pagamento
- Campos de margem de lucro: existem mas não usados ainda

### Tabelas de Cards (Catálogo)

#### Magic: The Gathering

**mtg_concepts:**
- id, supertype, type, subtypes, rules_text, mana_cost, cmc, power, toughness, loyalty, keywords, etc.

**mtg_prints:**
- scryfall_id, rarity, artist, collector_number, language, flavor_text, finishes (JSON), frame, border_color
- Flags: full_art, textless, promo, reprint, variation, has_foil, nonfoil, etched, oversized, digital, highres_image
- security_stamp, watermark, card_back_id, image_status, released_at
- prices (JSON), related_uris (JSON), purchase_uris (JSON)
- multiverse_ids (JSON), mtgo_id, mtgo_foil_id, arena_id, tcgplayer_id, cardmarket_id, illustration_id
- ⚠️ **FALTA:** URLs de imagens como fallback

#### Pokémon TCG

**pk_concepts:**
- id, supertype, hp, level, types, subtypes, attacks, abilities, weaknesses, resistances, retreat_cost
- evolves_from, evolves_to, national_pokedex_numbers, legalities, regulation_mark, ancient_trait, rules_text

**pk_prints:**
- id, rarity, artist, number, flavor_text, level
- images (JSON), tcgplayer (JSON), cardmarket (JSON)
- language_code, created_at, updated_at
- ⚠️ **FALTA:** api_id (identificador único da API), preços consolidados

#### Yu-Gi-Oh! TCG

**ygo_concepts:**
- ✅ Estrutura básica existe

**ygo_prints:**
- ⚠️ **MUITO INCOMPLETO** (~30% completo)
- Campos existentes: id, set_code, rarity, language_code, created_at, updated_at
- ❌ **FALTAM:**
  - api_id (identificador da API)
  - set_name
  - set_rarity_code
  - price
  - card_images (JSON com URLs)
  - artist
  - card_number
  - released_at
  - E muitos outros campos críticos

### Tabela de Estoque

**stock_items:**
- id, store_id, card_id, condition, language, is_foil, quantity, price, created_at, updated_at
- UNIQUE: (store_id, card_id, condition, language, is_foil)

**⚠️ PROBLEMAS CRÍTICOS:**
- FK `card_id` aponta para tabela `cards` (LEGADA, não existe mais)
- Deveria apontar para `catalog_prints`
- Falta campo `game_id` para filtrar por jogo
- Falta campos de controle: `is_available`, `reserved_quantity`, `last_price_update`, `deleted_at`
- Falta índices de performance

---

## 🖼️ ARMAZENAMENTO DE IMAGENS

**Local:** `public/card_images/` (ou similar)  
**Campo:** `card_image` (caminho local armazenado no banco)  
**Estratégia:**
- Armazenar caminhos locais no banco
- URLs das APIs como fallback futuro
- Estrutura: `public/card_images/{game}/{set}/{card_id}.jpg`

---

## 🔴 PROBLEMAS IDENTIFICADOS

### CRÍTICO (MVP)

1. **`stock_items` FK incorreta**
   - Aponta para `cards` (tabela legada que não existe)
   - Deveria apontar para `catalog_prints`
   - Faltam campos de controle

2. **Yu-Gi-Oh! MUITO incompleto**
   - `ygo_prints` tem apenas 5 campos
   - Faltam campos essenciais: `api_id`, `price`, `images`, `artist`, etc.
   - Não deve ser prioridade no MVP

3. **Faltam tabelas OCG**
   - Pokémon OCG (não existe)
   - Yu-Gi-Oh! OCG (não existe)
   - Futuro: criar quando houver ingestores

4. **Campos de imagem faltando**
   - MTG: faltam URLs de fallback
   - Pokémon: faltam URLs de fallback
   - Yu-Gi-Oh!: faltam URLs e tudo mais

### IMPORTANTE (Antes do MVP)

5. **Tabelas legadas ainda referenciadas**
   - `cards` (tabela antiga)
   - `cardfunctionalities` (tabela antiga)
   - Precisam ser removidas do código

6. **IDs de API faltando**
   - MTG: `scryfall_id` ✅ (existe)
   - Pokémon: `api_id` ❌ (falta)
   - Yu-Gi-Oh!: `api_id` ❌ (falta)

7. **Soft deletes não implementados**
   - Considerar adicionar `deleted_at` em tabelas críticas
   - Útil para histórico de usuários e estoque

### BOM TER (Pós-MVP)

8. **Validação de MTGO profile**
   - Campo `mtgo_profile` em `player_users` existe
   - Implementar validação para bot de entrega futura

9. **Índices de performance**
   - Adicionar índices em `stock_items`
   - Otimizar queries de catálogo

---

## 📋 DECISÕES JÁ TOMADAS

✅ **Arquitetura de catálogo:** Usar `catalog_concepts` + `catalog_prints` com polimorfismo  
✅ **Separação de jogos:** Tabelas específicas por jogo (não tudo em uma tabela)  
✅ **MVP:** Apenas Magic + Pokémon TCG  
✅ **Armazenamento de imagens:** Local em `public/card_images/`  
✅ **Roles de usuários:** Cada papel em sua própria tabela  
✅ **Store owners:** 1 loja por vez  
✅ **Domínios de lojas:** Standalone (não sub-paths)  
✅ **Admin Filament:** Usar para gerenciamento de loja  

---

## 🚀 PRÓXIMOS PASSOS (PRIORIDADE)

### FASE 1: Refatoração de Banco (AGORA)

1. **Corrigir `stock_items`**
   - Mudar FK de `cards` para `catalog_prints`
   - Adicionar campo `game_id`
   - Adicionar campos de controle: `is_available`, `reserved_quantity`, `last_price_update`, `deleted_at`
   - Adicionar índices

2. **Completar `mtg_prints`**
   - Adicionar URLs de imagens como fallback

3. **Completar `pk_prints`**
   - Adicionar `api_id`
   - Adicionar URLs de imagens como fallback

4. **Remover referências a tabelas legadas**
   - Deletar ou arquivar `cards`
   - Deletar ou arquivar `cardfunctionalities`

### FASE 2: Models (PRÓXIMO)

1. Revisar/refatorar Models de catálogo
2. Revisar/refatorar Models de Magic
3. Revisar/refatorar Models de Pokémon
4. Revisar/refatorar Models de estoque

### FASE 3: Filament Resources

1. Resources de catálogo
2. Resources de Magic
3. Resources de Pokémon
4. Resources de estoque

### FASE 4: Ingestores

1. Scryfall Ingestor (Magic)
2. Pokémon TCG API Ingestor
3. Testes e validação

### FASE 5: Marketplace

1. Carrinho
2. Checkout
3. Pagamentos
4. Entrega

---

## 📊 STATUS ATUAL

| Componente | Status | Observações |
|-----------|--------|-------------|
| Banco de dados | ⚠️ 70% | Tabelas criadas, mas com problemas críticos |
| Magic | ⚠️ 85% | Prints completo, faltam URLs de fallback |
| Pokémon | ⚠️ 80% | Prints incompleto, falta `api_id` e URLs |
| Yu-Gi-Oh! | ⚠️ 30% | MUITO incompleto, não prioridade MVP |
| Estoque | ⚠️ 50% | FK incorreta, faltam campos de controle |
| Models | ❌ 0% | Não revisados ainda |
| Filament | ❌ 0% | Não revisados ainda |
| Ingestores | ⚠️ 50% | Precisam ser validados |
| Marketplace | ❌ 0% | Não iniciado |

---

## 🔗 RELACIONAMENTOS CRÍTICOS
catalog_concepts ──┬──> mtg_concepts ├──> pk_concepts └──> ygo_concepts

catalog_prints ────┬──> mtg_prints ├──> pk_prints └──> ygo_prints

stock_items ──────> catalog_prints (PRECISA SER CORRIGIDO)

stores ────────────> store_users

player_users ──────> users admin_users ───────> users store_users ───────> users store_admin_users ─> store_users

---

## 📝 NOTAS IMPORTANTES

- **Imagens:** Armazenadas localmente em `public/card_images/`, com URLs das APIs como fallback futuro
- **Legado:** Tabelas `cards` e `cardfunctionalities` ainda existem no banco, mas não devem ser usadas
- **OCG vs TCG:** Pokémon e Yu-Gi-Oh! têm versões OCG e TCG completamente diferentes; futuro: criar tabelas separadas
- **Ingestores:** Apenas Magic (Scryfall) e Pokémon (Pokémon TCG API) têm ingestores prontos
- **Soft deletes:** Não implementados, considerar para histórico

---

## 🎯 OBJETIVO FINAL DO MVP

✅ Magic + Pokémon TCG funcionando 100%  
✅ Marketplace básico com 2 lojas de teste  
✅ Estoque sincronizado  
✅ Admin Filament funcional  
✅ Ingestores validados  

---


## Capitulo 2 - ARQUITETURA DOS MODELS

### 2.2 CatalogConcept
- **Propósito:** Conceito abstrato de carta no catálogo unificado (ex.: "Lightning Bolt", "Pikachu").
- **Campos:** `game_id`, `name`, `slug`, `search_names` (JSON), `specific_type`, `specific_id`.
- **Relacionamentos:**
  - `specific()` (MorphTo): aponta para `MtgConcept`, `PkConcept`, etc.
  - `game()` (BelongsTo): referência ao jogo.
  - `prints()` (HasMany): múltiplos prints do mesmo conceito.
- **Pontos positivos:** Polimorfismo bem implementado, campo `search_names` útil para buscas.
- **Pontos de atenção:**
  - Falta validação de `specific_type` (aceita qualquer string).
  - Falta índice em `(game_id, slug)` para otimizar buscas.
  - `slug` pode não ser único por jogo (considerar constraint `UNIQUE(game_id, slug)`).
  - Falta validação de `search_names` (deve ser array de strings).
  - Considerar método helper `sets()` para acessar sets via prints.


### 2.3 CatalogPrint
- **Propósito:** Versão física específica de uma carta (print) no catálogo unificado.
- **Campos:** `concept_id`, `set_id`, `image_path` (⚠️ inconsistente com `card_image` do banco), `specific_type`, `specific_id`.
- **Relacionamentos:**
  - `specific()` (MorphTo): aponta para `MtgPrint`, `PkPrint`, etc.
  - `concept()` (BelongsTo): referência ao conceito abstrato.
  - `set()` (BelongsTo): referência ao set/coleção.
- **Pontos positivos:** Polimorfismo consistente, campo de imagem centralizado.
- **Problemas críticos:**
  - ⚠️ Inconsistência: model usa `image_path`, banco usa `card_image` → corrigir.
  - ⚠️ Falta campo `game_id` para otimização de queries.
  - ⚠️ Falta campos básicos: `language`, `rarity`, `artist`, `collector_number` (atualmente só nas tabelas específicas).
  - ⚠️ Falta relacionamento com `stock_items`.
  - Falta validação de `specific_type`.
  - Falta índices de performance (`concept_id`, `set_id`, `game_id`, `language`).
  - Falta método helper para URL completa da imagem.
  - Falta campo `remote_image_url` para fallback.

### 2.3 CatalogPrint (REVISADO COM DADOS REAIS)

**Tabela:** `catalog_prints`  
**Registros atuais:** ~534.552

#### Estrutura Confirmada

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint(20) unsigned | PK auto_increment |
| `concept_id` | bigint(20) unsigned | FK → `catalog_concepts` (CASCADE) |
| `set_id` | bigint(20) unsigned | FK → `sets` |
| **`image_path`** | varchar(255) | **Caminho local da imagem** (confirmado) |
| `specific_type` | varchar(255) | Polimorfismo (ex.: `App\Models\Magic\MtgPrint`) |
| `specific_id` | bigint(20) unsigned | FK para tabela específica (sem constraint) |
| `created_at` | timestamp | Laravel timestamp |
| `updated_at` | timestamp | Laravel timestamp |

#### Índices

- PRIMARY KEY (`id`)
- INDEX (`concept_id`) — FK para catalog_concepts
- INDEX (`set_id`) — FK para sets
- INDEX (`specific_type`, `specific_id`) — Polimorfismo

#### Relacionamentos (Model)

- `specific()` (MorphTo): aponta para `MtgPrint`, `PkPrint`, etc.
- `concept()` (BelongsTo): referência ao conceito abstrato
- `set()` (BelongsTo): referência ao set/coleção

#### ✅ Confirmações

- ✅ Campo `image_path` existe e está correto (não há inconsistência com o model)
- ✅ Funciona na view de Catalog Concepts (tela de visualização de carta)
- ✅ Índice composto em `(specific_type, specific_id)` otimiza queries polimórficas
- ✅ CASCADE em `concept_id` mantém integridade referencial

#### ⚠️ Melhorias Futuras (NÃO são erros)

1. **Adicionar campo `game_id`** para otimizar queries por jogo (evitar JOIN)
2. **Considerar duplicar campos básicos** (`language`, `rarity`, `collector_number`, `artist`) para reduzir JOINs
3. **Adicionar validação de `specific_type`** no model (garantir que classe existe)
4. **Adicionar campo `remote_image_url`** para fallback quando imagem local não existir
5. **Adicionar ON DELETE em `set_id`** (decidir comportamento: CASCADE, SET NULL, ou RESTRICT)
6. **Considerar validação de `specific_id`** em nível de aplicação (constraints polimórficas são impossíveis no MySQL)


### 2.4 MtgConcept
- **Propósito:** Dados específicos de conceito de Magic: The Gathering.
- **Campos:** `oracle_id`, `mana_cost`, `cmc`, `type_line`, `oracle_text`, `power`, `toughness`, `loyalty`, `produced_mana`, `color_indicator`, `edhrec_rank`, `penny_rank`, `max_copies`, `colors`, `color_identity`, `keywords`, `legalities`.
- **Casts:** Todos os campos JSON (`colors`, `color_identity`, `keywords`, `legalities`, `produced_mana`, `color_indicator`) são arrays.
- **Relacionamentos:**
  - `catalogConcept()` (MorphOne): relacionamento reverso para `CatalogConcept`.
- **Pontos positivos:** Cobertura completa dos campos de Magic, casts corretos, relacionamento reverso bem implementado.
- **Pontos de atenção:**
  - Falta relacionamento com `mtg_prints` (ou `catalogPrints` filtrado).
  - `oracle_id` deveria ser `UNIQUE`.
  - Falta índices de busca (`oracle_id`, `cmc`, `type_line`).
  - Verificar se `edhrec_rank`, `penny_rank`, `max_copies` são `int` no banco.
  - Considerar validação de `legalities` (valores permitidos: legal, not_legal, banned, restricted).
  - Campo `max_copies` pode ser redundante (maioria é 4).


### 2.5 MtgPrint
- **Propósito:** Dados específicos de print (impressão física) de Magic: The Gathering.
- **Campos:** `scryfall_id`, `rarity`, `artist`, `collector_number`, `language_code`, `flavor_text`, `frame`, `border_color`, `illustration_id`, `security_stamp`, `watermark`, `card_back_id`, `image_status`, `released_at`, flags booleanas (11 campos), JSONs (`finishes`, `prices`, `related_uris`, `purchase_uris`, `multiverse_ids`), IDs externos (6 campos).
- **Casts:** Todos os campos JSON são arrays, todas as flags são booleans.
- **Relacionamentos:**
  - `catalogPrint()` (MorphOne): relacionamento reverso para `CatalogPrint`.
- **Pontos positivos:** Cobertura completa dos campos do Scryfall, casts corretos, organização clara.
- **Problemas críticos:**
  - ⚠️ `scryfall_id` deveria ser `UNIQUE`.
  - ⚠️ Falta índices de busca (`scryfall_id`, `language_code`, `rarity`, `released_at`).
  - ⚠️ Falta relacionamento direto com `MtgConcept` e `Set` (atualmente indireto via `CatalogPrint`).
- **Pontos de atenção:**
  - Redundância entre `finishes` (JSON) e flags booleanas (`has_foil`, `nonfoil`, `etched`) → decidir se mantém ambos.
  - Campo `language_code` pode duplicar `language` de `CatalogPrint` → verificar.
  - Campo `released_at` → verificar tipo no banco (date vs string) e adicionar cast se necessário.
  - Campo `prices` (JSON) → considerar accessor para facilitar acesso.
  - Falta relacionamento com `stock_items` (aguardando refatoração de `stock_items`).


### 2.6 PkConcept
- **Propósito:** Dados específicos de conceito de Pokémon TCG.
- **Campos:** `supertype`, `hp`, `level`, `types`, `subtypes`, `attacks`, `abilities`, `weaknesses`, `resistances`, `retreat_cost`, `evolves_from`, `evolves_to`, `rules_text`, `national_pokedex_numbers`, `legalities`, `regulation_mark`, `ancient_trait`.
- **Casts:** Todos os campos JSON são arrays.
- **Relacionamentos:**
  - `catalogConcept()` (MorphOne): relacionamento reverso para `CatalogConcept`.
- **Pontos positivos:** Cobertura completa dos campos da API, casts corretos, suporte a mecânicas antigas e modernas.
- **PROBLEMA CRÍTICO:**
  - ⚠️⚠️⚠️ **FALTA CAMPO `api_id`** (ID único da API) — bloqueador para ingestor funcional.
- **Pontos de atenção:**
  - Falta relacionamento com `pk_prints` (indireto via `CatalogPrint`).
  - Campo `hp` → verificar se é `int` ou `string` (pode ter valores como "?", "X").
  - Campo `level` → uso limitado (apenas cartas antigas), manter como nullable.
  - Campos JSON (`attacks`, `abilities`, `weaknesses`, `resistances`) → considerar validação de estrutura.
  - Campo `regulation_mark` → considerar validação de valores permitidos.
  - Falta índices de busca (`api_id`, `supertype`, `regulation_mark`).

### 2.7 User (super admin absoluto)

**Propósito:** Representa o **super admin único** do sistema (você).

**Tabela:** `users`

**Campos:** `name`, `email`, `password`, `email_verified_at`, `remember_token`

**Características:**
- Único registro no sistema (não pode ser criado nem deletado via aplicação)
- Não usa Spatie Permission (HasRoles removido)
- Cast moderno: `password` => `hashed` (Laravel 12)
- Extende `Authenticatable` + `Notifiable`

**Situação atual:**
- ✅ Model enxuto e coerente para o propósito
- ✅ Proteção contra deleção já implementada (confirmado pelo assistente anterior)
- ⏳ Verificação de email: **desabilitada agora** (para não atrapalhar testes), **habilitar depois** junto com 2FA
- ⏳ 2FA: **importante para segurança**, aplicar a **TODOS os usuários** (todas as tabelas) após MVP estável

**Decisões para refatoração:**
1. **2FA obrigatório** para todos os papéis (super admin, admin_users, store_users, player_users) — implementar após testes iniciais
2. **Email verification** para super admin — habilitar após testes
3. **Guards específicos** — revisar `config/auth.php` após análise de todos os models de usuário
4. **Proteção contra deleção** — já implementada (validar durante refatoração)

**Próximo passo:** Analisar `config/auth.php` após fechar todos os models de usuário


### 2.8 AdminUser
- **Propósito:** Administradores do sistema (staff criado pelo super admin).
- **Tabela:** `admin_users`
- **Campos:** `name`, `surname`, `login`, `email`, `password`, `is_active`
- **Casts:** `is_active` (boolean)
- **Relacionamentos:** Nenhum definido ainda

#### ✅ Pontos positivos
- Extende `Authenticatable` + `Notifiable`
- Campo `is_active` permite desativar sem deletar
- Campo `login` separado de `email` (permite login por username)
- Campo `surname` separado de `name` (mais estruturado)

#### ⚠️ Problemas críticos
1. **FALTA cast de `password` como `hashed`** (Laravel 12)  
   → Sem isso, senhas podem ser salvas em texto puro
   → **Ação:** Adicionar `'password' => 'hashed'` no `$casts`

#### ⚠️ Pontos de atenção
1. **Campo `login` vs `email`**  
   → Não está claro qual é usado para autenticação  
   → Verificar em `config/auth.php` qual campo o guard `admin` usa

2. **Falta `email_verified_at`**  
   → Se o campo existir na tabela, adicionar no `$casts`  
   → Se não existir, confirmar que admins nunca verificam email

3. **Soft deletes não implementado**  
   → Considerar adicionar `SoftDeletes` para histórico/auditoria

4. **Falta relacionamentos**  
   → `createdBy()` (quem criou esse admin)  
   → `logs()` (ações realizadas)  
   → `createdUsers()` (outros admins criados por esse admin)

5. **Falta campo `created_by`**  
   → Para rastrear quem criou cada admin (auditoria)

6. **Falta campo `last_login_at`**  
   → Para segurança e auditoria

7. **Campo `is_active` — comportamento não definido**  
   → Se `false`, deve bloquear login? Ou apenas flag visual?  
   → Implementar middleware/listener se necessário

#### 📝 Sugestões para refatoração


# 2.8 Users (Arquitetura Completa)

## Visão Geral
O sistema usa tabelas separadas por papel, seguindo o padrão:

- prefixo = papel (`admin_`, `store_`, `player_`)
- sufixo = `users`
- cada papel tem seu próprio guard/provider em `auth.php`

Arquitetura final inclui cinco papéis:

1. Super Admin (tabela: users)
2. Admin Users (staff do sistema)
3. Store Users (donos de loja)
4. Store Admin Users (funcionários de loja)
5. Player Users (jogadores/clientes)

O model **SuperUser** é inválido e deve ser removido.

---

## 2.9.1 User (Super Admin)
**Tabela:** `users`  
**Model:** `App\Models\User`

**Função:**
- usuário raiz do sistema (único, absoluto)
- cria AdminUsers
- tem acesso ao painel global

**Campos Importantes:**
- name
- email
- password
- email_verified_at
- remember_token

**Configuração Correta:**
- `Authenticatable`
- `Notifiable`
- `password` => `hashed`
- sem HasRoles (não é necessário)

**Decisões:**
- manter como único super admin
- adicionar 2FA depois do MVP
- habilitar email verification depois
- impedir deleção (já existe proteção)

**Status:** Manter

---

## 2.9.2 SuperUser (DELETAR)
**Tabela:** `super_users`  
**Model:** `App\Models\SuperUser`

**Problemas:**
- duplicado do super admin
- não faz parte da arquitetura final
- possui campos incoerentes (`store_id`, `is_protected`)
- causa conflitos de providers/guards
- não é usado por nenhum fluxo oficial

**Status:** Remover model e tabela no refatoramento

---

## 2.9.3 AdminUser (Staff)
**Tabela:** `admin_users`  
**Model:** `App\Models\AdminUser`

**Função:**
- administradores secundários criados pelo super admin
- acesso ao painel global

**Campos:**
- name, surname
- login, email
- password
- is_active

**Pendências:**
- adicionar cast: `password => 'hashed'`
- verificar unicidade de login/email
- verificar se existe email_verified_at na tabela
- considerar SoftDeletes no futuro
- possível adicionar `created_by` para auditoria

**Status:** Manter, com refinamento

---

## 2.9.4 StoreUser (Dono de Loja)
**Tabela:** `store_users`  
**Model:** `App\Models\StoreUser`

**Função:**
- dono de loja
- pode ter apenas uma loja ativa (current_store_id)
- pode vender a loja (e fica sem loja temporariamente)
- pode ser funcionário em outra loja simultaneamente

**Campos:**
- current_store_id
- name, surname
- login, email, password
- document_number, id_document_number
- phone_number
- social_name, company_phone
- is_active

**Relacionamentos:**
- `store()` → BelongsTo(Store::class)

**Pendências:**
- adicionar cast: `password => 'hashed'`

**Status:** Manter, com refinamento

---

## 2.9.5 StoreAdminUser (Funcionário de Loja)
**Tabela:** `store_admin_users`  
**Model:** `App\Models\StoreAdminUser`

**Função:**
- funcionários da loja
- não exigem verificação de email
- podem existir sem loja (histórico)

**Campos:**
- store_id
- name, surname
- login, email, password
- is_active
- phone_number
- permissions_json
- hired_date

**Casts:**
- is_active => boolean
- permissions_json => array
- hired_date => date

**Pendências:**
- adicionar cast: `password => 'hashed'`

**Status:** Manter, com refinamento

---

## 2.9.6 PlayerUser (Jogadores/Clientes)
**Tabela:** `player_users`  
**Model:** `App\Models\PlayerUser`

**Função:**
- jogadores do sistema
- acesso ao marketplace
- precisam email verification

### Contexto histórico – Cards, Scryfall e refatorações

- Versão v1/v2/v3 do sistema foram fortemente acopladas ao Magic (Scryfall).
- A arquitetura inicial usava:
  - Tabela `cards` (genérica, mas pensada na prática só pra Magic).
  - Tabela `cardfunctionalities` e outras estruturas específicas.
- Ao tentar adicionar Battle Scenes (4º game na lista):
  - não havia API sólida;
  - ingestão dependia de scraper em site antigo;
  - muitos dados iam para `cards` / `cardfunctionalities`, mas a estrutura não generalizava bem.
- Problemas:
  - Tabela `cards` se tornou “monstruosa”, com campos que só faziam sentido para Magic;
  - Para outros jogos, a maioria dos campos ficava NULL;
  - `mtg_scryfall_id` e vários detalhes de Magic vazaram para tabelas agnósticas.
- Daí veio a decisão da v4 e v5:
  - Criar tabelas específicas por jogo (`mtg_*`, `pk_*`, `ygo_*`, etc.);
  - Manter `games`, `sets`, `catalog_concepts`, `catalog_prints` como núcleo agnóstico;
  - Deixar `cards` e `cardfunctionalities` vivos apenas para manter o **view antigo do Magic** funcionando enquanto o refactor não termina.
- Situação atual:
  - `Card` e tabelas legadas ainda existem e são usadas no front antigo;
  - `stock_items` e outras partes ainda apontam para `cards`;
  - O objetivo da v5 é desligar definitivamente essa camada legada, sem quebrar Magic.

# 2.10 Stores & Stock Items (Models)

## 2.10.1 Store (Lojas)

**Tabela:** `stores`  
**Model:** `App\Models\Store`

### Finalidade
Representa cada loja do sistema.  
Cada loja possui seu próprio painel Filament, estoque, domínio e configurações.  
Loja pertence a um **StoreUser** (dono) e pode ter vários **StoreAdminUsers** (funcionários).

### Campos principais (`fillable`)
- `name` – nome da loja  
- `url_slug` – domínio próprio da loja  
- `slogan`  
- `user_id` – **LEGADO**: deveria ser `store_user_id`  
- `purchase_margin_cash` – não utilizado atualmente  
- `purchase_margin_credit` – não utilizado atualmente  
- `max_loyalty_discount`  
- `pix_discount_rate`  
- `store_zip_code`  
- `store_state_code`  
- `is_active` – controle de ativação/transferência  
- `is_template` – loja modelo (para copiar configs)

### Problemas identificados
1. **`user_id` aponta para `User` (super admin)**  
   - Errado.  
   - Dono de loja está na tabela `store_users`.

2. Relacionamento `users()`  
   - Aponta para `App\Models\User` (super admin) — incorreto.  
   - Não existe relação real entre Store → User.

3. Campos de margem não são usados no MVP  
   - Podem ser mantidos para uso futuro.

4. Faltam campos de auditoria:
   - `created_by`
   - `transferred_at`
   - `deactivated_at`
   - `deactivation_reason`

### Relacionamentos corretos a serem usados na refatoração

## 2.11 Games & Sets (Arquitetura Completa)

**Contexto histórico (v1 → v5):**
- v1: sistema apenas para Magic, usando Scryfall; tabelas `cards` e `cardfunctionalities`.
- v2: tentativa de adicionar Battle Scenes reaproveitando essas tabelas; muitos campos nulos e específicos de Magic.
- v3: tentativa com prefixos por jogo; tabelas gigantes, difíceis de manter.
- v4: arquitetura atual de catálogo multi-jogo com tabelas específicas por jogo (`mtg_*`, `pk_*`, `ygo_*`, etc.) e camada agnóstica (`games`, `sets`, `catalog_concepts`, `catalog_prints`).
- v5 (em andamento): limpeza de legado (especialmente Magic), consolidação do catálogo novo e desligamento de `cards` e `cardfunctionalities`.

---
### 2.11.1 Game (Jogos Suportados)

**Tabela:** `games`
**Model:** `App\Models\Game`

**Função:**
- representar cada jogo suportado pelo sistema
- ser raiz da hierarquia: Game → Sets → Concepts/Prints
- centralizar ingestão de dados (ingestores, APIs, rate limit)

**Campos:**
- `name` – nome do jogo
- `publisher` – editora/fabricante
- `api_url` – URL base da API oficial
- `formats_list` – lista de formatos (JSON)
- `ingestor_class` – classe responsável pela ingestão
- `rate_limit_ms` – tempo mínimo entre requisições
- `is_active` – se o jogo está ativo
- `url_slug` – slug para URLs

**Casts:**
- `is_active` => boolean
- `rate_limit_ms` => integer
- `formats_list` => json

**Relacionamentos:**
- `sets()` => HasMany(Set::class)

**Pendências:**
- adicionar índices em `url_slug` e `is_active`
- adicionar relacionamento `catalogConcepts()` => HasMany(CatalogConcept::class)
- considerar campos futuros: `logo_path`, `api_credentials` (json)

**Status:** Manter, com melhoria de índices e conexão com o catálogo

---
### 2.11.2 Set (Coleções / Edições)

**Tabela:** `sets`
**Model:** `App\Models\Set`

**Função:**
- representar coleções/edições de um jogo
- ligar prints a um jogo e a uma coleção específica
- guardar metadados da coleção (data, tipo, card_count, ícone, etc.)

**Campos:**
- `game_id` – referência para o jogo em `games`
- `is_fanmade` – se é coleção fanmade
- `digital` – se é apenas digital
- `foil_only` – se só tem cartas foil
- `mtg_scryfall_id` – id na Scryfall (LEGADO, específico de Magic)
- `code` – código da coleção
- `name` – nome da coleção
- `released_at` – data de lançamento
- `set_type` – tipo da coleção
- `card_count` – número oficial de cartas
- `icon_svg_uri` – URL do ícone SVG

**Casts:**
- `is_fanmade` => boolean
- `digital` => boolean
- `foil_only` => boolean
- `released_at` => date
- `card_count` => integer

**Relacionamentos:**
- `game()` => BelongsTo(Game::class)
- `cards()` => HasMany(Card::class) – LEGADO (v1–v3)

**Problemas (Legado):**
- `mtg_scryfall_id`: só faz sentido para Magic/Scryfall; deixa `sets` menos agnóstica
- `cards()`: aponta para `Card` (tabela antiga); não conversa com o catálogo novo

**Pendências (v5):**
- renomear `mtg_scryfall_id` => `api_id` (id genérico de API por jogo)
- remover `cards()` após migração
- adicionar `catalogPrints()` => HasMany(CatalogPrint::class)
- criar índices em `game_id`, `code`, `api_id`, `released_at`

**Status:** Estrutura conceitual boa, com resíduos fortes de Magic que serão tratados no v5

---
### 2.11.3 Resumo (Games & Sets)

**Game:**
- model correto para representar jogos
- precisa de índices básicos e relacionamento com o catálogo

**Set:**
- model correto como conceito de coleção
- tem acoplamento histórico com Magic (`mtg_scryfall_id`, `cards()`)
- será alinhado com `api_id` genérico e `CatalogPrint`

**Legado a manter provisoriamente:**
- `cards` e `cardfunctionalities` – usados ainda por telas antigas; serão desligados após v5

---
## 2.12 Stores & Stock Items (Models)

**Visão Geral:**
- a loja (`Store`) é o centro operacional do marketplace
- cada loja pertence a um único dono (`StoreUser`)
- cada loja pode ter funcionários (`StoreAdminUser`)
- o estoque da loja é gerenciado por `StockItem`
- cada `StockItem` representa um print específico do catálogo (`catalog_print_id`)

---
### 2.12.1 Store (Lojas)

**Tabela:** `stores`
**Model:** `App\Models\Store`

**Função:**
- representar cada loja cadastrada no sistema
- controlar domínio próprio da loja (`url_slug`)
- armazenar configs de desconto, endereço e comportamento
- servir como raiz para funcionários e estoque
- permitir ativação, desativação ou transferência de propriedade

**Campos:**
- `name` – nome da loja
- `url_slug` – domínio próprio da loja
- `slogan`
- `user_id` – LEGADO: deveria ser `store_user_id`
- `purchase_margin_cash` – atualmente não usado
- `purchase_margin_credit` – atualmente não usado
- `max_loyalty_discount`
- `pix_discount_rate`
- `store_zip_code`
- `store_state_code`
- `is_active` – controle de ativação/transferência
- `is_template` – indica se é uma loja base para clonagem

**Relacionamentos:**
- `owner()` => deveria ser BelongsTo(StoreUser::class, 'store_user_id')
- `employees()` => HasMany(StoreAdminUser::class)
- `stockItems()` => HasMany(StockItem::class)

**Problemas (Legado):**
- FK `user_id` aponta para `User` (super admin), não para `StoreUser`
- relacionamento `users()` não representa dono real
- ausência de campos de auditoria (quem criou, quem transferiu, quem desativou)

**Pendências:**
- renomear `user_id` => `store_user_id`
- remover relacionamento antigo com `User`
- adicionar campos: `created_by`, `transferred_at`, `deactivated_at`, `deactivation_reason`
- criar índices em `url_slug` e `is_active`

**Status:** Model antigo (v1/v2); precisa de refatoração no v5

---
### 2.12.2 StockItem (Itens de Estoque)

**Tabela:** `stock_items`
**Model:** `App\Models\StockItem`

**Função:**
- representar um item de estoque vinculado a uma loja
- conectar Loja => Print => Condição => Idioma => Quantidade => Preço
- cada registro é específico para um `catalog_print_id`

**Campos:**
- `store_id` – loja proprietária
- `catalog_print_id` – print no catálogo unificado
- `condition` – condição (NM, LP, MP, HP, etc.)
- `language` – idioma da carta
- `is_foil` – indica se é foil
- `quantity` – quantidade disponível
- `price` – preço unitário

**Relacionamentos:**
- `store()` => BelongsTo(Store::class)
- `catalogPrint()` => BelongsTo(CatalogPrint::class, 'catalog_print_id')
- `concept()` => HasOneThrough(CatalogConcept::class, CatalogPrint::class)

**Pontos positivos:**
- já usa `catalog_print_id`, totalmente integrado ao catálogo novo
- permite buscar facilmente o conceito da carta
- estrutura simples e adequada para o v4/v5

**Pendências:**
- adicionar casts: `is_foil` => boolean, `quantity` => integer, `price` => decimal:2
- padronizar `condition` (enum ou tabela auxiliar)
- padronizar `language` (códigos ISO)
- criar índices em `store_id`, `catalog_print_id`, `condition`, `is_foil`
- considerar `SoftDeletes` para manter histórico de estoque
- decidir manutenção ou remoção do helper `concept()`

**Status:** Model moderno (v4); precisa apenas de ajustes leves

---
### 2.12.3 Resumo (Stores & Stock Items)

**Store:**
- modelo antigo e acoplado à tabela `users`
- precisa de correção de FK para `store_users`
- deve ganhar auditoria e índices no v5
- relacionamento com funcionários precisa ser formalizado

**StockItem:**
- modelo atual e compatível com o catálogo unificado
- necessita apenas de ajustes de domínio e índices
- base sólida para o marketplace

---
## 3.0 Legado (Contexto Geral)

**Visão Geral:**
- antes da arquitetura atual (catálogo unificado + tabelas por jogo), o sistema tinha apenas Magic
- os models `Card`, `CardFunctionality` e `Ruling` foram criados nessa época
- esses modelos evoluíram sem padrão, acumulando dados misturados e regras específicas
- com a chegada do catálogo multi-jogo e das tabelas específicas por jogo, esse legado perdeu função
- agora eles se tornam um obstáculo para o MVP e para o futuro do sistema

**Objetivo deste capítulo:**
- entender como esse legado nasceu
- entender por que virou um problema técnico
- preparar o terreno para a análise profunda das tabelas (3.1)
- preparar o terreno para revisar os models legados (3.2)
- decidir o que será removido, o que será migrado e o que será reestruturado

---
### 3.0.1 Como o Legado Nasceu

**Contexto original (v1):**
- o sistema começou suportando apenas Magic: The Gathering
- a referência de dados era exclusivamente a API da Scryfall
- o modelo `Card` concentrava conceito, print, regras e atributos em um só lugar
- `CardFunctionality` foi criado para “expandir” dados que não cabiam dentro do `Card`
- `Ruling` foi criado para armazenar decisões oficiais (WotC/Scryfall)

**Evolução problemática:**
- o modelo `Card` misturava dados conceituais e dados de edição
- `CardFunctionality` virou um depósito de informações desconexas
- `Ruling` ficou amarrado a `CardFunctionality`, tornando-se impossível de usar com outros jogos
- nenhuma dessas tabelas foi criada pensando em múltiplos jogos

**Resumo histórico:**
- o legado não é “ruim” por incompetência;  
- ele é “ruim” porque nasceu quando o sistema era outro.

---
### 3.0.2 Por Que o Legado Virou Problema

**Expansão do Multiverse para 8 jogos:**
- quando o projeto deixou de ser apenas de Magic, o legado ficou limitado
- cada jogo tem estruturas diferentes (tipos, prints, IDs, regras)
- o modelo antigo não conseguiria suportar isso

**Problemas técnicos principais:**
- tabelas antigas misturam papéis: conceito + print + regras
- estruturas rígidas (campos específicos de Magic)
- acoplamento forte com Scryfall
- relacionamento baseado em `card_functionality_id` impede generalização
- duplicidade com o catálogo novo (`catalog_concepts` e `catalog_prints`)

**Impactos no sistema:**
- telas antigas ainda dependem dos modelos legados
- performance e integridade dos dados ficam prejudicadas
- manutenção fica difícil (campos nulos, lixos e duplicados)
- impede a finalização do MVP moderno

---
### 3.0.4 Próximos Passos (Capítulos 3.2 e 3.3)

**Capítulo 3.2 – Models Legados:**
- analisar `Card`
- analisar `CardFunctionality`
- analisar `Ruling` (que deve virar `mtg_rulings`)
- entender relacionamentos antigos
- verificar trechos de código que dependem deles

**Capítulo 3.3 – Migração / Remoção:**
- decidir o futuro de `cards`
- decidir o futuro de `cardfunctionalities`
- decidir o futuro das `rulings`
- definir rotas de migração para o v5
- planejar remoção segura do legado

**Ponto chave:**
- só após a análise completa decidimos o destino final de cada tabela.

---
### 3.0.5 Resumo Geral

**O que é o legado:**
- resquício da era do “sistema só de Magic”
- estruturas improvisadas que não servem mais ao catálogo multi‑jogo

**Por que estamos analisando:**
- porque não podemos simplesmente apagar sem olhar
- porque precisamos preservar dados importantes (especialmente rulings)
- porque o MVP depende da limpeza dessa base

**O que vai acontecer:**
- análise das tabelas no capítulo seguinte
- análise dos models na sequência
- decisões finais de migração e remoção após o v5 estabilizar

---
### 3.1.0 Tabela `rulings`

**Tabela:** `rulings`  
**Model atual:** `App\Models\Ruling`

**Descrição técnica:**
- tabela criada na época em que o sistema era exclusivamente para Magic
- registra “rulings” oficiais (WotC / Scryfall) de cartas
- cada ruling pertence a um registro de `cardfunctionalities`, que também é legado
- possui controle de fonte e data, garantindo histórico por funcionalidade

**Campos:**
- `id` – chave primária
- `card_functionality_id` – FK para `cardfunctionalities`
- `source` – enum('wotc', 'scryfall')
- `published_at` – data da publicação da ruling
- `comment` – texto da ruling
- `created_at` / `updated_at` – timestamps

**Relacionamentos:**
- `card_functionality_id` → `cardfunctionalities.id`
- índice único:  
  (`card_functionality_id`, `source`, `published_at`)

**Problemas identificados:**
- totalmente acoplada ao modelo legado `cardfunctionalities`
- enum `source` limitado a Magic (wotc/scryfall)
- não contém `game_id` (não identifica o jogo)
- não possui estrutura multi‑jogo
- impossível de usar com Pokémon / Yu‑Gi‑Oh / One Piece sem quebrar padrão
- não está ligada ao catálogo unificado (`catalog_concepts` ou `catalog_prints`)

**Uso atual:**
- ainda utilizada por algumas telas antigas
- mantém histórico valioso de rulings do Magic
- não é usada por telas novas baseadas no catálogo v4/v5

**Implicações técnicas para o v5:**
- precisa ser preservada até entender como rulings serão usadas no futuro
- não deve ser apagada antes da finalização do catálogo multi-jogo
- deve ser separada do legado e renomeada para evitar risco de perda de dados
- ideal para ser convertida em `mtg_rulings` (tabela específica por jogo)
- jogos que não têm rulings não precisam de tabela nenhuma

**Status:** Necessário manter por enquanto; será renomeada e migrada futuramente

---
### 3.1.1 Tabela `card_functionalities`

**Tabela:** `card_functionalities`  
**Model atual:** `App\Models\CardFunctionality`

**Descrição técnica:**
- tabela criada originalmente para armazenar dados funcionais de cartas de Magic
- evoluiu de forma improvisada para tentar suportar os 8 jogos
- cada jogo ganhou campos específicos prefixados (mtg_, pk_, ygo_, op_, lor_, fab_, swu_, bs_)
- virou uma tabela gigante, com centenas de campos nulos por registro
- mistura conceito, regras, atributos e mecânicas de jogos completamente diferentes

**Campos gerais:**
- `id` – chave primária
- `game_id` – FK para `games`
- `tcg_name` – nome do jogo (padrão: 'Magic: The Gathering')
- `searchable_names` – nomes pesquisáveis (text)
- `created_at` / `updated_at` – timestamps

**Campos específicos por jogo:**

**Magic (mtg_):**
- `mtg_oracle_id` – ID único do conceito no Scryfall (UNIQUE)
- `mtg_name` – nome da carta
- `mtg_mana_cost` – custo de mana
- `mtg_cmc` – custo convertido de mana
- `mtg_type_line` – linha de tipo
- `mtg_rules_text` – texto de regras
- `mtg_max_copies` – cópias permitidas (padrão: 4)
- `mtg_legalities` – legalidades por formato (JSON)
- `mtg_power`, `mtg_toughness`, `mtg_loyalty` – atributos de criatura/planeswalker
- `mtg_produced_mana` – mana produzida (JSON)
- `mtg_edhrec_rank`, `mtg_penny_rank` – rankings
- `mtg_colors`, `mtg_color_identity`, `mtg_color_indicator` – cores (JSON)
- `mtg_keywords` – palavras-chave (JSON)

**Pokémon (pk_):**
- `pk_name` – nome da carta
- `pk_supertype` – supertipo (Pokémon, Trainer)
- `pk_subtypes` – subtipos (Basic, VMAX) (JSON)
- `pk_types` – tipos de energia (Fire, Water) (JSON)
- `pk_hp` – pontos de vida
- `pk_level` – nível
- `pk_retreatCost` – custo de recuo (JSON)
- `pk_convertedRetreatCost` – custo convertido
- `pk_attacks` – ataques (nome, custo, dano, texto) (JSON)
- `pk_abilities` – habilidades (nome, texto, tipo) (JSON)
- `pk_weaknesses` – fraquezas (tipo, valor) (JSON)
- `pk_resistances` – resistências (tipo, valor) (JSON)
- `pk_evolvesFrom`, `pk_evolvesTo` – evolução
- `pk_nationalPokedexNumbers` – números da Pokédex (JSON)
- `pk_rules` – regras (text)

**Yu-Gi-Oh (ygo_):**
- `ygo_name` – nome da carta
- `ygo_konami_id` – ID oficial Konami
- `ygo_type` – tipo (Effect Monster)
- `ygo_race` – raça (Spellcaster)
- `ygo_attribute` – atributo (LIGHT, DARK)
- `ygo_atk`, `ygo_def` – ataque e defesa
- `ygo_level` – nível/rank
- `ygo_scale` – escala pêndulo
- `ygo_linkval` – valor link
- `ygo_linkmarkers` – setas link (JSON)
- `ygo_archetype` – arquétipo
- `ygo_banlist_info` – informações de banlist (JSON)
- `ygo_desc` – descrição

**One Piece (op_):**
- `op_name` – nome da carta
- `op_color` – cor (Red)
- `op_type` – tipo (Leader, Character)
- `op_cost` – custo
- `op_power` – poder
- `op_life` – vida (líder)
- `op_counter` – counter
- `op_attribute` – atributo (Slash)
- `op_traits` – traits (Straw Hat Crew)
- `op_effect` – efeito
- `op_trigger_effect` – efeito de gatilho

**Lorcana (lor_):**
- `lor_name` – nome da carta
- `lor_title` – título (Wayward Sorcerer)
- `lor_type` – tipo (Character)
- `lor_cost` – custo
- `lor_inkable` – pode virar tinta (boolean)
- `lor_color` – cor da tinta (Amber)
- `lor_strength` – força (ataque)
- `lor_willpower` – determinação (vida)
- `lor_lore` – pontos de lore
- `lor_classifications` – keywords (Dreamborn) (JSON)
- `lor_abilities_and_effects` – habilidades e efeitos

**Flesh and Blood (fab_):**
- `fab_name` – nome da carta
- `fab_pitch` – pitch
- `fab_cost` – custo
- `fab_power` – poder
- `fab_defense` – defesa
- `fab_health` – vida (herói)
- `fab_type` – tipo (Attack Action)
- `fab_keywords` – keywords (JSON)
- `fab_class` – classe
- `fab_talent` – talento
- `fab_stats` – estatísticas (JSON)
- `fab_legality` – legalidade (JSON)
- `fab_text` – texto

**Star Wars Unlimited (swu_):**
- `swu_name` – nome da carta
- `swu_title` – título
- `swu_is_unique` – é única (boolean)
- `swu_type` – tipo (Unit, Leader)
- `swu_aspects` – aspectos/cores (JSON)
- `swu_cost` – custo
- `swu_power` – poder
- `swu_hp` – pontos de vida
- `swu_arena` – arena (Ground/Space)
- `swu_traits` – traits/subtipos (JSON)
- `swu_ability_text` – texto de habilidade
- `swu_keywords` – keywords

**Battle Scenes (bs_):**
- `bs_name` – nome da carta
- `bs_alter_ego` – alter ego (conceito)
- `bs_type_line` – tipo (Personagem)
- `bs_power` – poder
- `bs_toughness` – escudo
- `bs_cost` – energia
- `bs_affiliation` – afiliação
- `bs_alignment` – alinhamento
- `bs_powers` – poderes (Voo, Magia, etc.) (JSON)
- `bs_rules_text` – texto de regras

**Relacionamentos:**
- `game_id` → `games.id`
- índice único em `mtg_oracle_id`

**Problemas identificados (críticos):**
- tabela com mais de 100 campos, sendo que cada registro usa apenas ~15
- campos específicos de cada jogo ficam nulos para os outros 7 jogos
- estrutura impossível de manter e escalar
- mistura conceito (nome, regras) com atributos específicos de jogo
- não segue o padrão do catálogo unificado (catalog_concepts + tabelas por jogo)
- duplica informações que já existem nas tabelas específicas (pk_concepts, mtg_prints, etc.)
- FK `game_id` não garante que apenas campos do jogo correto sejam preenchidos
- índice único em `mtg_oracle_id` só faz sentido para Magic
- campos JSON sem validação de schema
- nomes de campos inconsistentes entre jogos

**Uso atual:**
- ainda utilizada por telas antigas
- algumas queries dependem dela para buscar dados de Magic
- não é usada pelas telas novas baseadas no catálogo v4/v5

**Implicações técnicas para o v5:**
- deve ser completamente removida após migração
- dados de Magic devem ser migrados para `mtg_concepts` (se necessário)
- dados de outros jogos já existem nas tabelas específicas (pk_concepts, ygo_prints, etc.)
- relacionamento com `rulings` precisa ser quebrado antes da remoção
- telas antigas precisam ser refatoradas para usar o catálogo unificado

**Status:** Legado crítico; será removido completamente no v5

---
### 3.1.2 Tabela `cards`

**Tabela:** `cards`  
**Model atual:** `App\Models\Card`

**Descrição técnica:**
- criada originalmente para armazenar "prints" de cartas de Magic
- expandida posteriormente para tentar suportar os 8 jogos
- mistura dados de print (edição, número, artista, imagem) com dados de jogo específico
- possui FK para `card_functionalities` e `sets`
- cada registro representa um "print específico" de uma carta em uma edição

**Campos principais (834.660 registros):**
- `id` – chave primária
- `card_functionality_id` – FK para `card_functionalities` (pode ser NULL)
- `set_id` – FK para `sets`
- `game_id` – FK para `games` (pode ser NULL)
- campos específicos de **Magic** (prefixo `mtg_`): scryfall_id, printed_name, printed_text, printed_type_line, collection_code, collection_number, rarity, artist, flavor_text, image_url_api, language_code, layout, frame, border_color, full_art, textless, promo, reprint, variation, illustration_id, has_foil, nonfoil, etched, oversized, digital, security_stamp, watermark, card_back_id, highres_image, image_status, released_at, image_uris, prices, related_uris, purchase_uris, multiverse_ids, mtgo_id, mtgo_foil_id, arena_id, tcgplayer_id, tcgplayer_etched_id, cardmarket_id
- campos específicos de **Pokémon** (prefixo `pk_`): flavorText, artist, images, tcgplayer_prices, cardmarket_prices, set_id, set_name, number, language_code
- campos específicos de **Yu-Gi-Oh** (prefixo `ygo_`): card_sets, card_images, card_prices, language_code
- campos específicos de **One Piece** (prefixo `op_`): artist, image_url, promo, card_id_name, language_code
- campos específicos de **Lorcana** (prefixo `lor_`): flavor_text, artist, image_url, collector_number, set_id, set_name, illustrators, prices, tcgplayer_id, language_code
- campos específicos de **Flesh and Blood** (prefixo `fab_`): flavor, image_urls, tcgplayer_url, identifier, set, printings, language_code
- campos específicos de **Star Wars Unlimited** (prefixo `swu_`): flavor, artist, image_url, set, card_number, foil, stamped, language_code
- campos específicos de **Battle Scenes** (prefixo `bs_`): flavor_text, artist, image_url, image_path, collection_number, set_name, rarity, language_code
- campos locais de imagem: `local_image_path_large`, `local_image_path_art_crop`, `custom_image_path`

**Relacionamentos:**
- `card_functionality_id` → `card_functionalities.id`
- `set_id` → `sets.id`
- índices únicos por jogo:
  - `uc_swu_card_number_per_set` (set_id, swu_card_number)
  - `uc_bs_collection_number_per_set` (set_id, bs_collection_number)
  - `uc_lor_collector_number_per_set` (set_id, lor_collector_number)

**Problemas identificados:**

1. **Mistura print com dados de jogo:**  
   - cada jogo tem estrutura de print diferente
   - campos nulos para jogos que não usam determinado atributo
   - tabela gigante e difícil de manter

2. **Prefixos por jogo criam poluição extrema:**  
   - 8 jogos × média de 10-30 campos cada = tabela monstruosa
   - campos JSON misturados com escalares
   - impossível de indexar corretamente

3. **Duplicidade com o catálogo novo:**  
   - `catalog_prints` já existe e faz o papel de "print unificado"
   - tabelas específicas (`pk_prints`, `mtg_prints`, `ygo_prints`, etc.) já fazem o papel de "print por jogo"
   - essa tabela virou redundância completa

4. **FK para `card_functionalities` (legado):**  
   - quando `card_functionalities` for removida, essa FK quebra
   - precisa ser desacoplada antes

5. **Campos de imagem misturados:**  
   - alguns jogos usam `image_url` (externo)
   - outros usam `local_image_path` (local)
   - outros usam `custom_image_path`
   - falta padrão

**Uso atual:**
- ainda utilizada por telas antigas
- dados de Magic ainda dependem dela
- não é usada pelas telas novas baseadas no catálogo v4/v5

**Implicações técnicas para o v5:**
- deve ser completamente removida após migração
- dados devem ser migrados para:
  - `catalog_prints` (print unificado)
  - tabelas específicas por jogo (`pk_prints`, `mtg_prints`, etc.)
- imagens devem ser padronizadas antes da migração

**Status:** Legado crítico; será removido completamente no v5

---
### 3.1.3 Resumo Geral do Legado

**O que essas três tabelas representam:**
- resquício da arquitetura v1/v2/v3 (sistema apenas de Magic)
- tentativa fracassada de expandir para múltiplos jogos usando prefixos
- mistura de responsabilidades (conceito + print + regras + jogo específico)
- estrutura rígida que não escala

**Por que não funcionam mais:**
- o catálogo unificado (v4/v5) resolve todos os problemas dessas tabelas
- tabelas específicas por jogo (`pk_concepts`, `mtg_prints`, etc.) são mais limpas
- `catalog_concepts` e `catalog_prints` fornecem camada agnóstica
- manutenção dessas tabelas legadas impede finalização do MVP

**Comparação com o catálogo novo:**

| Legado | Catálogo Novo |
|--------|---------------|
| `card_functionalities` | `catalog_concepts` + tabelas específicas (`pk_concepts`, etc.) |
| `cards` | `catalog_prints` + tabelas específicas (`pk_prints`, `mtg_prints`, etc.) |
| `rulings` | `mtg_rulings` (específico por jogo, quando necessário) |

**Destino final:**
- `card_functionalities` → remover após migração
- `cards` → remover após migração
- `rulings` → renomear para `mtg_rulings`, preservar dados

**Próximos passos:**
- capítulo 3.2: analisar os models que usam essas tabelas
- capítulo 3.3: planejar migração e remoção segura

---
## 4.0 Visão Geral dos Services de Ingestão

Os services existentes atualmente no sistema têm um papel exclusivo: realizar a ingestão de dados externos (APIs e scraping) para alimentar as tabelas do catálogo, especialmente `sets`. Ainda não existem services de regra de negócio, pois o sistema nunca passou da etapa de ingestão + visualização.

### 4.0.1 Papel dos Services no Sistema Atual
- responsáveis por sincronizar dados externos com o banco local
- fazem ingestão de sets (e potencialmente prints) para diferentes jogos
- lidam com APIs oficiais (Scryfall, Pokémon TCG) e scraping (Battle Scenes)
- são utilizados por comandos Artisan, não diretamente por controllers ou Filament
- não possuem lógica de negócio; apenas realizam integração de dados

### 4.0.2 Lista de Services Atuais
- BattleScenesIngestorService
- BattleScenesScraper
- PokemonTcgApiService
- ScryfallApi

### 4.0.3 Padrões Observados
- todos usam logs para acompanhamento da execução
- todos implementam algum tipo de rate limit
- todos escrevem prioritariamente na tabela `sets`
- alguns possuem lógicas de “data healing” para corrigir duplicidades ou inconsistências
- não utilizam injeção de dependências (services instanciados diretamente)

### 4.0.4 Relação com o Legado e com o v5
- compatíveis com o estado atual do banco (v4)
- precisam apenas de ajustes cosméticos para o v5 (renomear campos externos, padronização)
- nenhum impede o funcionamento do MVP
- serão eventualmente integrados ao novo pipeline de ingestão (commands do Capítulo 5)

### 4.0.5 Diretrizes para o Futuro
- não refatorar agressivamente antes do MVP
- garantir que sets estejam sempre atualizados
- revisar nomenclatura de campos externos no v5
- considerar isolamento do scraping de Battle Scenes como módulo opcional

---
## 4.1 Service: BattleScenesIngestorService

### 4.1.1 Descrição Geral
Responsável por sincronizar os sets de Battle Scenes utilizando dados fornecidos pelo `BattleScenesScraper`. Atua exclusivamente sobre a tabela `sets`.

### 4.1.2 Fluxo Principal
- usa o scraper para obter lista de sets
- procura sets existentes usando `(game_id, name)`
- se existir: atualiza apenas o timestamp
- se não existir: cria novo registro com código gerado automaticamente
- preserva códigos manuais já existentes

### 4.1.3 Métodos Principais
- `runIngestionJob()` → executa todo o fluxo de sincronização
- `generateShortCode($name)` → cria códigos de 2–4 letras para novos sets

### 4.1.4 Tabelas Impactadas
- leitura e escrita em `sets`

### 4.1.5 Pontos Fortes
- simples e direto
- respeita códigos manuais existentes
- não altera estruturas que o usuário configurou manualmente

### 4.1.6 Fragilidades
- totalmente dependente do scraper (layout frágil)
- parâmetros `apiUrl` e `rateLimit` não utilizados
- acoplamento direto `new BattleScenesScraper()`

### 4.1.7 Classificação v4/v5
- v4: adequado e funcional
- v5: reaproveitável com pequenas melhorias (injeção de dependência)

**Status:** Em uso ativo; frágil por depender de scraping; útil enquanto Battle Scenes estiver no catálogo.

---
## 4.2 Service: BattleScenesScraper

### 4.2.1 Descrição Geral
Realiza scraping do site MagicJebb para extrair sets e cartas de Battle Scenes. Retorna arrays e generators, sem gravar diretamente no banco.

### 4.2.2 Funções Implementadas
- `getSetsList()` → obtém lista de sets via `<select>`
- `scrapeCardsForSet()` → iteração paginada sobre cards do set
- `fetchCardDetailData()` → busca imagem ideal + texto bruto da página
- `parseTextData()` → extrai atributos (tipo, raridade, energia, escudo, etc.)

### 4.2.3 Heurística de Imagens
- favorece `.png`
- favorece caminhos conhecidos (`bs_cards/`, `scan/`)
- penaliza `.gif`, imagens pequenas e ícones de interface

### 4.2.4 Dados Extraídos
- nome da carta
- número de coleção
- atributos (energia, escudo, afiliação, tipo)
- texto de regras
- flavor text
- imagem principal

### 4.2.5 Pontos Fortes
- isolamento completo da lógica de scraping
- heurística robusta para determinar melhor imagem
- usa generator, evitando consumo excessivo de memória

### 4.2.6 Fragilidades
- HTML do MagicJebb pode mudar a qualquer momento
- regexes sensíveis ao idioma e formato atual
- ausência de tratamento avançado de falhas

### 4.2.7 Classificação v4/v5
- v4: essencial para manter Battle Scenes
- v5: classificar como módulo opcional ou plugin; manter apenas se BS continuar no projeto

**Status:** Em uso ativo; alta fragilidade por scraping; reaproveitável com cautela.

---
## 4.3 Service: PokemonTcgApiService

### 4.3.1 Descrição Geral
Integra com a API oficial do Pokémon TCG. Atualmente foca exclusivamente na ingestão de sets, não de cartas.

### 4.3.2 Fluxo Principal
- paginação de `/sets` com `page` e `pageSize`
- para cada página, chama `upsertSet()`
- evita duplicidade utilizando:
  - `mtg_scryfall_id` como “ID técnico” (reutilizado aqui)
  - `code` como “ID visual”
- executa rate-limit baseado em milissegundos

### 4.3.3 Campos Processados
- nome do set
- total de cartas
- tipo de set
- datas de lançamento
- logo (icon_svg_uri)
- código técnico e visual

### 4.3.4 Pontos Fortes
- integração sólida com API oficial
- logs detalhados
- estratégia clara de “data healing”

### 4.3.5 Fragilidades
- reutilização de `mtg_scryfall_id` para Pokémon
- ingestão focada apenas em sets; não cobre prints

### 4.3.6 Classificação v4/v5
- v4: ótimo; nenhum ajuste urgente
- v5: renomear campos externos (`external_set_id`)

**Status:** Em uso ativo; totalmente reaproveitável; apenas ajustes cosméticos futuros.

---
## 4.4 Service: ScryfallApi

### 4.4.1 Descrição Geral
Service oficial de integração com a Scryfall. É um dos pilares do ingestion de Magic. Atua em ingestão de sets e suporte à ingestão de cartas via commands.

### 4.4.2 Funções Implementadas
- rate limit robusto
- GET com headers e parâmetros de compatibilidade
- ingestão de sets via `/sets`
- modo de resgate via paginação de cartas
- upsert completo dos sets

### 4.4.3 Campos Mapeados
- scryfall_id
- code
- set_type
- icon_svg_uri
- released_at
- digital / foil_only
- card_count

### 4.4.4 Pontos Fortes
- robusto, estável, testado
- possui fallback inteligente via busca por cartas
- usa `upsert` de forma eficiente

### 4.4.5 Fragilidades
- constante mágica `1019` para quantidade mínima de sets
- campo `mtg_scryfall_id` usado como chave externa genérica para sets

### 4.4.6 Classificação v4/v5
- v4: essencial e maduro
- v5: requer apenas renomear campos externos e possível abstração de uma interface mais genérica

**Status:** Núcleo da ingestão do sistema; totalmente reaproveitável com ajustes mínimos.

---
## 5.0 Visão Geral dos Commands

### 5.0.1 Papel dos Commands no Sistema

Os commands Artisan implementados no projeto servem dois propósitos distintos:

1. **Commands de Ingestão** (frequentemente usados)
   - responsáveis por sincronizar dados externos com o banco local
   - executados regularmente via cron ou manualmente
   - orquestram os services de integração
   - populam tabelas de catálogo (sets, concepts, prints)

2. **Commands Utilitários** (raramente usados)
   - suportam operações pontuais (migração, seed, diagnóstico)
   - executados uma vez ou em situações específicas
   - não fazem parte do fluxo normal de operação

### 5.0.2 Lista de Commands Implementados

**Ingestão (Uso Frequente):**
- IngestionManagerCommand (orquestrador)
- IngestScryfallSets
- IngestScryfallCards
- IngestScryfallRulings
- IngestPokemonCards
- IngestBattleScenesSets
- IngestBattleScenesCards

**Utilitários (Uso Raro):**
- BuildSearchIndex
- ListScryfallSets
- MigrateLegacyToV4
- SeedStockItems
- SetListCommand

### 5.0.3 Padrões Observados

- **Instanciação Manual de Services**: todos os commands instanciam manualmente os services, evitando injeção de dependência automática
- **Checkpoint Support**: commands de ingestão de longa duração usam checkpoint para retomada
- **Rate Limiting**: respeito a limites de requisição de APIs externas
- **Batch Processing**: processamento em lotes para otimizar memória
- **Logging Detalhado**: rastreamento de progresso e erros
- **Fallback Strategies**: tentativas alternativas quando IDs primários falham

### 5.0.4 Relação com Services e Models

- commands chamam services (ScryfallApi, BattleScenesScraper, etc.)
- services retornam dados brutos ou já mapeados
- commands fazem upsert/create nos models (Set, Card, CardFunctionality, Ruling, etc.)
- alguns commands dependem de dados já existentes (ex: IngestPokemonCards precisa dos Sets já criados)

### 5.0.5 Classificação Geral (v4 vs v5)

**Essenciais para v4:**
- IngestionManagerCommand
- IngestScryfallSets
- IngestScryfallCards
- IngestPokemonCards
- IngestBattleScenesSets

**Importantes para v4:**
- IngestScryfallRulings
- BuildSearchIndex

**Legado / Descartável:**
- MigrateLegacyToV4 (usado uma única vez)
- SeedStockItems (pode ser substituído por factory)
- SetListCommand (apenas diagnóstico)
- ListScryfallSets (apenas diagnóstico)

---
## 5.1 Command: IngestionManagerCommand

### 5.1.1 Descrição Geral

**Arquivo:** `App\Console\Commands\IngestionManagerCommand`  
**Assinatura:** `ingest:run {--game= : Opcional. ID ou url_slug do game específico.}`  
**Descrição:** Orquestrador central que dispara a ingestão de dados para todos os TCGs ativos ou um específico.

### 5.1.2 Fluxo de Execução

1. Busca todos os games com `is_active = true` (ou filtra por ID/slug se opção `--game` for passada)
2. Para cada game encontrado:
   - valida se existe `ingestor_class` definida na tabela `games`
   - instancia a classe do service (ex: `ScryfallApi`, `PokemonTcgApiService`)
   - injeta configurações do game (`api_url`, `rate_limit_ms`, `id`)
   - chama `runIngestionJob()` no service
3. Loga sucesso ou erro para cada game
4. Retorna `SUCCESS` se todos forem processados

### 5.1.3 Dependências

- **Models:**
  - `Game` (lê `ingestor_class`, `api_url`, `rate_limit_ms`, `is_active`)
- **Services:**
  - qualquer service que implemente `runIngestionJob()` (ScryfallApi, PokemonTcgApiService, BattleScenesScraper, etc.)

### 5.1.4 Pontos Fortes

- centraliza toda a lógica de orquestração
- permite filtrar por game específico
- reutilizável para novos jogos (basta adicionar registro em `games` com `ingestor_class`)
- tratamento robusto de exceções por game

### 5.1.5 Fragilidades

- depende de `ingestor_class` estar corretamente preenchida na tabela `games`
- se um game falhar, continua com os próximos (sem rollback global)
- não há retry automático em caso de falha

### 5.1.6 Classificação v4/v5

- **v4:** essencial; é o ponto de entrada para toda ingestão
- **v5:** manter sem alterações; apenas adicionar novos games conforme necessário

**Status:** Núcleo do sistema de ingestão; totalmente reaproveitável.

---
## 5.2 Command: IngestScryfallSets

### 5.2.1 Descrição Geral

**Arquivo:** `App\Console\Commands\IngestScryfallSets`  
**Assinatura:** `scryfall:ingest`  
**Descrição:** Sincroniza todos os sets de Magic: The Gathering do Scryfall com o banco local.

### 5.2.2 Fluxo de Execução

1. Busca o game "Magic: The Gathering" (url_slug = 'magic')
2. Instancia `ScryfallApi` manualmente com dados do game
3. Chama `getAllSets()` para buscar lista completa de sets
4. Se retorno for vazio ou < 1019 sets, ativa modo de resgate via paginação de cartas
5. Mapeia cada set para a estrutura de `Set` model
6. Executa `upsert` com chaves de conflito `[mtg_scryfall_id, game_id]`
7. Exibe progresso com barra
8. Loga mensagem final com total de sets processados

### 5.2.3 Campos Mapeados

- `game_id` → ID do jogo Magic
- `mtg_scryfall_id` → ID único do Scryfall
- `code` → código visual do set (ex: "DOM")
- `name` → nome completo
- `set_type` → tipo (core, expansion, etc.)
- `released_at` → data de lançamento
- `card_count` → total de cartas no set
- `digital` → booleano
- `foil_only` → booleano
- `mtg_icon_svg_uri` → ícone SVG

### 5.2.4 Dependências

- **Models:**
  - `Game`
  - `Set`
- **Services:**
  - `ScryfallApi`

### 5.2.5 Pontos Fortes

- usa `upsert` para evitar duplicatas
- modo de resgate inteligente se API falhar
- barra de progresso clara
- mapeamento completo de campos

### 5.2.6 Fragilidades

- constante mágica `1019` hardcoded (limite mínimo de sets)
- assume que game "Magic: The Gathering" com url_slug 'magic' existe
- sem checkpoint; sempre processa todos os sets

### 5.2.7 Classificação v4/v5

- **v4:** essencial; primeira etapa da ingestão de Magic
- **v5:** manter; apenas renomear campo `mtg_scryfall_id` para `external_set_id`

**Status:** Núcleo da ingestão de Magic; totalmente reaproveitável.

---
## 5.3 Command: IngestScryfallCards

### 5.3.1 Descrição Geral

**Arquivo:** `App\Console\Commands\IngestScryfallCards`  
**Assinatura:** `scryfall:ingest-cards {--set-code=} {--force} {--resume}`  
**Descrição:** Ingere cartas de Magic do Scryfall, criando conceitos (CardFunctionality) e prints (Card).

### 5.3.2 Fluxo de Execução

1. Busca game Magic (ID 1)
2. Instancia `ScryfallApi` manualmente
3. Busca sets a processar (todos ou filtrado por `--set-code`)
4. Para cada set:
   - constrói URL de busca Scryfall com filtro `set:{code}`
   - pagina através de todas as cartas
   - para cada carta:
     - mapeia `CardFunctionality` (conceito/oracle)
     - mapeia `Card` (print específico)
     - baixa imagem se URL disponível
   - faz upsert em batch
5. Mantém checkpoint para retomada
6. Suporta `--force` para ignorar checkpoint e `--resume` para forçar leitura

### 5.3.3 Campos Mapeados (CardFunctionality)

- `mtg_oracle_id` → ID único do conceito
- `mtg_name` → nome da carta
- `mtg_mana_cost`, `mtg_cmc` → custo de mana
- `mtg_type_line` → tipo/subtipo
- `mtg_rules_text` → texto de regras
- `mtg_power`, `mtg_toughness`, `mtg_loyalty` → atributos
- `mtg_colors`, `mtg_color_identity` → cores (JSON)
- `mtg_keywords`, `mtg_legalities` → keywords e legalidades (JSON)
- `mtg_edhrec_rank`, `mtg_penny_rank` → rankings

### 5.3.4 Campos Mapeados (Card)

- `mtg_scryfall_id` → ID único do print
- `mtg_collection_number` → número do colecionador
- `mtg_collection_code` → código do set
- `mtg_printed_name`, `mtg_printed_text`, `mtg_printed_type_line` → versão impressa
- `mtg_rarity` → raridade
- `mtg_artist` → artista
- `mtg_flavor_text` → texto de sabor
- `mtg_frame`, `mtg_border_color` → detalhes visuais
- `mtg_full_art`, `mtg_textless`, `mtg_promo`, `mtg_reprint`, `mtg_variation` → flags
- `mtg_image_uris`, `mtg_prices` → dados estruturados (JSON)
- `local_image_path_large` → caminho local da imagem

### 5.3.5 Tratamento de Erros Avançado

- **404 Not Found:** tenta fallback para código visual se ID técnico falhar
- **5xx Server Error:** reduz tamanho da página (250 → 50 → 10 cartas)
- **Timeout/Conexão:** retry automático até 3 vezes antes de trocar ID
- **Página Vazia:** tenta fallback antes de desistir

### 5.3.6 Dependências

- **Models:**
  - `Game`
  - `Set`
  - `CardFunctionality`
  - `Card`
- **Services:**
  - `ScryfallApi`
- **Filesystem:**
  - `public/card_images/Magic/{setCode}/{lang}/`

### 5.3.7 Pontos Fortes

- tratamento robusto de erros e fallbacks
- checkpoint permite retomada de ingestões longas
- batch processing eficiente (200 cartas por chunk)
- download de imagens integrado
- suporte a cartas multilíngues

### 5.3.8 Fragilidades

- campo `mtg_scryfall_id` reutilizado como chave genérica
- lógica de "data healing" complexa e frágil
- dependência de estrutura de pastas local
- sem limpeza de imagens órfãs

### 5.3.9 Classificação v4/v5

- **v4:** essencial; segunda etapa da ingestão de Magic
- **v5:** refatorar para usar modelo genérico de prints/concepts

**Status:** Núcleo da ingestão de Magic; precisa de refatoração no v5.

---
## 5.4 Command: IngestScryfallRulings

### 5.4.1 Descrição Geral

**Arquivo:** `App\Console\Commands\IngestScryfallRulings`  
**Assinatura:** `scryfall:ingest-rulings {--id=} {--force} {--resume}`  
**Descrição:** Ingere julgamentos (rulings) de Magic do Scryfall para cada conceito de carta.

### 5.4.2 Fluxo de Execução

1. Busca game Magic (ID 1)
2. Instancia `ScryfallApi` manualmente
3. Se `--id` for passado:
   - busca `CardFunctionality` específica por `mtg_oracle_id`
   - processa apenas seus julgamentos
   - retorna
4. Senão, modo geral:
   - busca todas as `CardFunctionality` ordenadas por ID
   - se checkpoint existe e sem `--force`, retoma a partir do último ID
   - para cada funcionalidade:
     - chama `fetchRulingsForFunctionality()`
     - acumula em batch de 20
     - faz upsert em batch
     - atualiza checkpoint
5. Loga contagem inicial e final de registros

### 5.4.3 Métodos Principais

**`fetchRulingsForFunctionality(CardFunctionality $f, ScryfallApi $api)`**
- busca rulings via endpoint `/cards/{oracle_id}/rulings`
- mapeia cada ruling para estrutura esperada
- retorna array de rulings

**`upsertRulingsBatch(array $rulings, int $count)`**
- executa `Ruling::upsert()` com batch
- chaves de conflito: `[card_functionality_id, source, published_at]`
- campos atualizados: `comment`, `updated_at`
- calcula "aumento real" (novos registros inseridos)

### 5.4.4 Campos Mapeados

- `card_functionality_id` → FK para a funcionalidade
- `source` → origem (wotc ou scryfall)
- `published_at` → data de publicação
- `comment` → texto do julgamento

### 5.4.5 Dependências

- **Models:**
  - `Game`
  - `CardFunctionality`
  - `Ruling`
- **Services:**
  - `ScryfallApi`

### 5.4.6 Pontos Fortes

- checkpoint permite retomada
- batch processing eficiente
- suporte a processamento de card único (debug)
- logging detalhado de progresso

### 5.4.7 Fragilidades

- sem validação se `CardFunctionality` realmente existe antes de buscar rulings
- sem tratamento de rate limit específico para rulings
- campo `card_functionality_id` acoplado a Magic

### 5.4.8 Classificação v4/v5

- **v4:** importante; enriquece dados de Magic
- **v5:** refatorar para tabela genérica de rulings (não acoplada a Magic)

**Status:** Importante para v4; precisa de refatoração no v5.

---
## 5.5 Command: IngestPokemonCards

### 5.5.1 Descrição Geral

**Arquivo:** `App\Console\Commands\IngestPokemonCards`  
**Assinatura:** `pokemon:ingest-cards {--set-id=} {--force} {--resume} {--page-size=250}`  
**Descrição:** Ingere cartas de Pokémon TCG via API oficial para a estrutura Catalog V4.

### 5.5.2 Fluxo de Execução

1. Define limites de memória e tempo (ilimitado)
2. Busca game Pokémon (ID 2)
3. Se `--set-id` for passado:
   - filtra apenas esse set
   - modo debug
4. Senão, modo geral:
   - busca checkpoint se existe
   - se `--force`, ignora checkpoint
   - se `--resume`, força leitura de checkpoint
5. Para cada set:
   - constrói URL de busca: `/cards?q=set.id:{apiSetCode}&page={page}&pageSize={pageSize}`
   - pagina através de todas as cartas
   - para cada carta:
     - mapeia `PkConcept` (conceito)
     - mapeia `PkPrint` (print específico)
     - baixa imagem se disponível
   - faz upsert em batch
   - atualiza checkpoint
6. Suporta fallback de ID (tenta `mtg_scryfall_id` depois `code`)

### 5.5.3 Tratamento de Erros Avançado

- **404 Not Found:** tenta fallback para código visual
- **5xx Server Error:** reduz tamanho de página (250 → 50 → 10)
- **Timeout/Conexão:** retry automático até 3 vezes
- **Página Vazia:** tenta fallback antes de desistir
- **Rate Limit:** respeita header `X-Api-Key` se configurado

### 5.5.4 Campos Mapeados (PkConcept)

- `pokemon_id` → ID da API
- `name` → nome
- `hp` → pontos de vida
- `types` → tipos (JSON)
- `abilities` → habilidades (JSON)
- `attacks` → ataques (JSON)
- `weaknesses`, `resistances` → fraquezas e resistências (JSON)
- `retreat_cost` → custo de recuo
- `legalities` → legalidades (JSON)

### 5.5.5 Campos Mapeados (PkPrint)

- `pokemon_id` → ID da API
- `rarity` → raridade
- `artist` → artista
- `number` → número do colecionador
- `language_code` → idioma
- `images` → URLs de imagens (JSON)
- `tcgplayer`, `cardmarket` → dados de marketplace (JSON)

### 5.5.6 Dependências

- **Models:**
  - `Game`
  - `Set`
  - `PkConcept`
  - `PkPrint`
- **External:**
  - API Pokémon TCG (https://api.pokemontcg.io/v2)
  - API Key opcional (config `services.pokemon.api_key`)

### 5.5.7 Pontos Fortes

- tratamento robusto de erros e fallbacks
- checkpoint permite retomada
- suporte a múltiplos IDs de set (fallback)
- batch processing eficiente
- download de imagens integrado

### 5.5.8 Fragilidades

- reutilização de `mtg_scryfall_id` para ID de Pokémon API
- lógica de fallback complexa
- sem limpeza de imagens órfãs
- dependência de estrutura de pastas local

### 5.5.9 Classificação v4/v5

- **v4:** essencial para Pokémon
- **v5:** refatorar para usar modelo genérico de prints/concepts

**Status:** Essencial para v4; precisa de refatoração no v5.

---
## 5.6 Command: IngestBattleScenesSets

### 5.6.1 Descrição Geral

**Arquivo:** `App\Console\Commands\IngestBattleScenesSets`  
**Assinatura:** `battlescenes:ingest-sets`  
**Descrição:** Sincroniza sets de Battle Scenes via scraping do site MagicJebb.

### 5.6.2 Fluxo de Execução

1. Busca game "Battle Scenes"
2. Instancia `BattleScenesScraper` (injeção de dependência)
3. Chama `getSetsList()` para raspar lista de sets do MagicJebb
4. Para cada set:
   - gera código curto (2-4 letras) a partir do nome
   - busca set existente por `[game_id, code]`
   - se existe: apenas atualiza `updated_at`
   - se não existe: cria novo com:
     - `name` = nome completo
     - `code` = código gerado
     - `scryfall_id` = `'bs-' + code` (prefixo para identificar Battle Scenes)
     - `set_type` = `'scraped_set'`
     - `card_count` = 0
     - `released_at` = agora
5. Usa `withProgressBar` para exibir progresso
6. Loga total de sets salvos/atualizados

### 5.6.3 Lógica de Geração de Código

- remove "Battle Scenes" do começo do nome
- remove caracteres especiais
- pega primeira letra de cada palavra
- limita a 10 caracteres
- fallback: se resultado < 2 caracteres, usa slug do nome

Exemplo: "Battle Scenes - Universo Marvel" → "UM"

### 5.6.4 Dependências

- **Models:**
  - `Game`
  - `Set`
- **Services:**
  - `BattleScenesScraper`

### 5.6.5 Pontos Fortes

- preserva códigos manuais (não sobrescreve)
- geração automática de códigos curtos
- barra de progresso clara
- idempotente (pode rodar múltiplas vezes)

### 5.6.6 Fragilidades

- **altamente frágil**: depende de estrutura HTML do MagicJebb
- sem tratamento de erros de scraping
- sem retry em caso de falha
- sem checkpoint; sempre processa todos

### 5.6.7 Classificação v4/v5

- **v4:** essencial para Battle Scenes (se mantido no MVP)
- **v5:** considerar remover ou substituir por API se disponível

**Status:** Funcional mas frágil; candidato a remoção no futuro.

---
## 5.7 Command: IngestBattleScenesCards

### 5.7.1 Descrição Geral

**Arquivo:** `App\Console\Commands\IngestBattleScenesCards`  
**Assinatura:** `bs:ingest-cards {--set-code=} {--force}`  
**Descrição:** Ingere cartas de Battle Scenes via scraping do site MagicJebb.

### 5.7.2 Fluxo de Execução

1. Busca game "Battle Scenes"
2. Instancia `BattleScenesScraper` (injeção de dependência)
3. Busca sets a processar (todos ou filtrado por `--set-code`)
4. Para cada set:
   - constrói URL de busca no MagicJebb
   - chama `scrapeCardsForSet()` (generator)
   - para cada carta:
     - mapeia para estrutura de `Card`
     - baixa imagem se disponível
     - faz upsert individual
   - loga progresso
5. Suporta fallback de codificação ISO-8859-1 para nomes com acentos

### 5.7.3 Tratamento de Erros

- **Codificação:** tenta UTF-8, depois ISO-8859-1
- **Imagens:** continua mesmo se download falhar
- **Cartas:** pula cartas com erro, continua com próximas

### 5.7.4 Dependências

- **Models:**
  - `Game`
  - `Set`
  - `Card`
- **Services:**
  - `BattleScenesScraper`
- **Filesystem:**
  - `public/card_images/BattleScenes/`

### 5.7.5 Pontos Fortes

- suporta processamento de set único (debug)
- fallback de codificação para nomes com acentos
- download de imagens integrado

### 5.7.6 Fragilidades

- **extremamente frágil**: depende de estrutura HTML do MagicJebb
- sem checkpoint; sempre processa todos
- sem tratamento robusto de erros de scraping
- sem retry automático

### 5.7.7 Classificação v4/v5

- **v4:** funcional mas frágil
- **v5:** considerar remover ou substituir

**Status:** Funcional mas muito frágil; alto risco de quebra.

---
## 5.8 Command: BuildSearchIndex

### 5.8.1 Descrição Geral

**Arquivo:** `App\Console\Commands\BuildSearchIndex`  
**Assinatura:** `scryfall:build-search-index`  
**Descrição:** Popula coluna `searchable_names` de `CardFunctionality` com todos os nomes alternativos de uma carta.

### 5.8.2 Fluxo de Execução

1. Itera sobre todas as `CardFunctionality` em chunks de 200
2. Para cada funcionalidade:
   - busca todos os `Card` (prints) relacionados
   - pega `printed_name` de cada print
   - adiciona `name` da funcionalidade
   - remove duplicatas e valores vazios
   - junta com " / " como separador
   - salva em `searchable_names`
3. Exibe ponto (.) para cada chunk processado
4. Loga conclusão

### 5.8.3 Lógica de Busca

Exemplo:
- Funcionalidade: "Black Lotus"
- Prints: "Black Lotus" (EN), "Loto Negro" (ES), "Lotus Noir" (FR)
- Resultado: `"Black Lotus / Loto Negro / Lotus Noir"`

### 5.8.4 Dependências

- **Models:**
  - `CardFunctionality`
  - `Card`

### 5.8.5 Pontos Fortes

- simples e direto
- chunk processing eficiente
- idempotente (pode rodar múltiplas vezes)

### 5.8.6 Fragilidades

- sem checkpoint; sempre processa todos
- sem tratamento de erro

### 5.8.7 Classificação v4/v5

- **v4:** importante; necessário após ingestão de cartas
- **v5:** manter; considerar automatizar via evento no model

**Status:** Importante para v4; pode ser automatizado no v5.

---
## 5.9 Command: ListScryfallSets

### 5.9.1 Descrição Geral

**Arquivo:** `App\Console\Commands\ListScryfallSets`  
**Assinatura:** `debug:list-sets`  
**Descrição:** Lista todos os sets do Scryfall com código, nome, contagem de cartas e tipo.

### 5.9.2 Fluxo de Execução

1. Faz requisição GET para `https://api.scryfall.com/sets`
2. Extrai dados de cada set
3. Monta tabela com colunas: Code, Name, Card Count, Set Type
4. Exibe tabela formatada
5. Loga total de sets encontrados

### 5.9.3 Dependências

- **External:**
  - Scryfall API (`/sets`)

### 5.9.4 Pontos Fortes

- simples e direto
- útil para diagnóstico

### 5.9.5 Fragilidades

- apenas lista; não salva nada
- sem tratamento de erro de conexão

### 5.9.6 Classificação v4/v5

- **v4:** utilitário; apenas diagnóstico
- **v5:** pode ser removido

**Status:** Utilitário; candidato a remoção.

---
### 5.10.3 Dependências

- **Models legados:**
  - `CardFunctionality`
  - `Card`
- **Models novos (v4):**
  - `CatalogConcept`
  - `CatalogPrint`
  - `MtgConcept`
  - `MtgPrint`
- **Infra:**
  - `DB::transaction()` para garantir consistência
  - barras de progresso no console

### 5.10.4 Pontos Fortes

- migração **completa** de Magic do modelo antigo para o modelo v4
- idempotente:
  - checa se o conceito ou print já existe antes de criar
- uso de transações por registro:
  - reduz risco de registros quebrados no meio do caminho

### 5.10.5 Fragilidades

- completamente acoplado ao legado (`CardFunctionality` e `Card`)
- assume que `game_id = 1` é Magic (valor mágico)
- foi desenhado para ser usado **uma única vez** durante a migração
- não faz limpeza dos dados antigos

### 5.10.6 Classificação v4/v5

- **v4:** já cumpriu o seu papel; hoje é basicamente histórico
- **v5:** pode ser removido com segurança após confirmação de que a migração foi executada

**Status:** One-shot de migração; considerado legado a ser removido depois da estabilização do v4.

---
## 5.11 Command: SeedStockItems

### 5.11.1 Descrição Geral

**Arquivo:** `App\Console\Commands\SeedStockItems`  
**Assinatura:** `multiverse:seed-stock {--store= : ID da loja para seed}`  
**Descrição:** Cria, para uma loja específica, todas as combinações possíveis de estoque (`stock_items`) para cada card do catálogo, em todas as condições, idiomas e estados foil, com quantidade e preço inicial zero.

### 5.11.2 Fluxo de Execução

1. Lê a opção obrigatória `--store` (ID da loja).
2. Valida se a loja existe:
   - se não existir, exibe erro e aborta.
3. Busca todos os `Card` já ingeridos (via `Card::pluck('id')`).
4. Calcula o número total de combinações:
   - `totalCards * condições * idiomas * foilStates`.
5. Para cada `card_id`:
   - para cada `condition` em `['NM', 'SP', 'MP', 'HP', 'DM']`
   - para cada `language` em `['en', 'pt', 'es', 'fr', 'de', 'it', 'ja', 'ko', 'ru', 'zhs', 'zht']`
   - para cada `is_foil` em `[true, false]`
   - monta um registro em memória:
     - `store_id`, `card_id`, `condition`, `language`, `is_foil`
     - `quantity = 0`, `price = 0.00`
     - `created_at`, `updated_at = now()`
   - acumula em `$itemsToInsert`
   - quando atinge 1000 registros, chama `upsertStockBatch()` e limpa o buffer.
6. Após o loop, insere o lote final se houver.
7. Exibe progresso baseado no número de cards (não em todas as combinações).
8. Exibe mensagem final com o total de combinações geradas.

### 5.11.3 Método `upsertStockBatch(array $batch)`

- usa `StockItem::upsert` com:
  - chave única: `[store_id, card_id, condition, language, is_foil]`
  - campos atualizados: `quantity`, `price`, `updated_at`
- isso permite:
  - criar registros inexistentes
  - atualizar quantidade/preço se já existirem

### 5.11.4 Dependências

- **Models:**
  - `Card`
  - `Store`
  - `StockItem`
- **Infra:**
  - `DB` para operações em lote (implícito no Eloquent)
  - barra de progresso no console

### 5.11.5 Pontos Fortes

- garante que a loja tenha **todas as combinações possíveis** de estoque previamente criadas:
  - simplifica lógica de UI (não precisa “criar” linha, apenas editar quantidade/preço)
- usa `upsert`, portanto é:
  - idempotente (pode rodar mais de uma vez para a mesma loja)
  - seguro para re-seeding parcial

### 5.11.6 Fragilidades

- extremamente pesado em termos de volume:
  - combinações = `cards * 5 condições * 11 idiomas * 2 foilStates`
  - para 10.000 cards → 1.100.000 registros por loja
- pode se tornar inviável com crescimento do catálogo
- não considera preferências reais da loja:
  - gera combinações mesmo para idiomas/condições que ela nunca usará
- amarra a modelagem ao `Card` legado em vez de `CatalogPrint` / prints específicos por jogo

### 5.11.7 Classificação v4/v5

- **v4:** útil para testes e para um ambiente pequeno / controlado com poucas cartas e poucas lojas
- **v5:** provavelmente deve ser substituído por:
  - criação dinâmica de `stock_items` conforme o lojista adiciona um item de estoque
  - seeds mais controladas (por jogo, por idioma, por condição efetivamente usada)

**Status:** Utilitário pesado; útil em ambientes pequenos, candidato a revisão ou remoção no v5.

---
## 5.12 Command: SetListCommand

### 5.12.1 Descrição Geral

**Arquivo:** `App\Console\Commands\SetListCommand`  
**Assinatura:** `app:list-sets`  
**Descrição:** Lista todos os sets de Magic: The Gathering salvos no banco, com algumas colunas principais, para fins de diagnóstico.

### 5.12.2 Fluxo de Execução

1. Assume `game_id = 1` como Magic (ou tenta buscar `Game::find(1)` para obter o nome).
2. Busca todos os `Set` com `game_id = 1`, ordenados por `released_at` desc.
3. Se não houver sets:
   - exibe erro e retorna código de falha.
4. Monta uma tabela com os campos:
   - `mtg_code`
   - `name`
   - `set_type`
   - `card_count`
   - `released_at` (formatado `Y-m-d` ou `N/A`)
   - `mtg_scryfall_id`
5. Usa `$this->table()` para exibir a tabela formatada no console.
6. Exibe contagem total de sets.

### 5.12.3 Dependências

- **Models:**
  - `Set`
  - `Game` (opcional, apenas para mostrar nome do jogo)

### 5.12.4 Pontos Fortes

- comando simples para:
  - verificar se ingestão de sets foi bem-sucedida
  - conferir contagem e datas de lançamento
- não altera dados; apenas leitura

### 5.12.5 Fragilidades

- assume `game_id = 1` para Magic (valor mágico)
- usa colunas específicas do legado (`mtg_code`, `mtg_scryfall_id`)
- é puramente de diagnóstico; não faz parte do fluxo normal do sistema

### 5.12.6 Classificação v4/v5

- **v4:** útil como ferramenta de debug durante desenvolvimento e migração
- **v5:** candidato claro à remoção ou substituição por relatórios no painel admin

**Status:** Utilitário de diagnóstico; não essencial ao funcionamento do sistema.

---
## 6.0 Visão de Produto (Protótipos HTML)

Os arquivos HTML enviados representam a visão atual do produto final do sistema (Versus TCG), servindo como referência visual para o MVP e para a versão completa do marketplace. São protótipos estáticos: não possuem integração com banco de dados, não possuem lógica de negócio e não dependem de back-end para funcionar.

O propósito deste capítulo é:

- documentar cada tela como elemento da visão de produto;  
- identificar seus componentes visuais e objetivos funcionais;  
- mapear quais dados reais o sistema deverá fornecer no futuro;  
- sugerir melhorias, boas práticas e ajustes de UX/UI;  
- preparar terreno para o desenvolvimento da V5, que será construída com base na experiência pretendida.

**Importante:**  
Nenhuma ausência de funcionalidade nestes HTMLs é tratada como erro.  
Eles são mockups conceituais, feitos para capturar ideias, validar layout e visualizar fluxo.

A seguir, cada tela é documentada separadamente.

## 6.1 Página Inicial do Marketplace (Home.html)

### 6.1.1 Objetivo da Tela
A página inicial é o primeiro ponto de contato do usuário com o marketplace Versus TCG. Seu propósito é apresentar o produto, comunicar valor imediatamente e oferecer caminhos claros tanto para jogadores quanto para lojistas.

Esta tela funciona como uma *landing page* institucional e comercial ao mesmo tempo.

### 6.1.2 Estrutura Geral da Tela
A Home é dividida em seções distintas, cada uma com um objetivo claro:

1. **Hero / Cabeçalho inicial**
   - Exibe o nome “Versus TCG”
   - Frase de impacto: “Um login. Infinitos Universos.”
   - Links principais:
     - Marketplace
     - Eventos
     - Área do Lojista
   - Botões de ação:
     - Entrar
     - Criar Conta

2. **Banner Promocional**
   - Destaque visual chamativo
   - Texto: “Promoção de lançamento”
   - Oferta: “Ganhe uma Booster Box!”
   - Mecânica: R$100,00 = 1 número da sorte
   - CTA principal: “Quero Participar”

3. **Seletor de Card Games**
   - Cards individuais para cada jogo
   - Nome + editora + slogan temático
   - Jogos listados:
     - Magic, Pokémon, Yu-Gi-Oh!, Battle Scenes, One Piece, Lorcana, Flesh and Blood, Star Wars Unlimited, Pokémon OCG JP, Yu-Gi-Oh! OCG JP

4. **Área “Versus Partner”**
   - Seção dedicada aos lojistas
   - Mensagem motivacional: profissionalismo, automação, antifraude, envios integrados
   - Funcionalidades futuras destacadas:
     - Cadastro por ingestão automática
     - Venda segura
   - Botões:
     - Cadastrar Minha Loja
     - Ver Taxas e Planos

5. **Preview de Painel do Lojista**
   - Exemplo visual de dashboard interno:
     - Nome da loja
     - Vendas do mês
     - Pedidos pendentes
     - Gráfico semanal

6. **Rodapé Institucional**
   - Sobre nós, carreiras, imprensa
   - Políticas legais
   - Formas de pagamento
   - Certificado de segurança (SSL)
   - Avisos de direitos e disclaimers de marca

### 6.1.3 Identidade e Tom
A página usa um tom moderno, tecnológico e confiante — reforçando que o marketplace é:

- seguro  
- profissional  
- fácil para lojistas  
- apaixonado por card games  

O slogan “Um login. Infinitos universos.” funciona MUITO bem para diferenciar.

### 6.1.4 Dados que esta Tela Exigirá no Futuro
Ainda que hoje seja apenas um HTML estático, no futuro esta página usará dados reais, como:

- status do marketplace (número de lojas ativas, sets recentes, promoções)  
- promoções configuráveis pela administração  
- lista de jogos ativa no banco (`games` table)  
- URLs reais para:
  - marketplace
  - eventos
  - área do lojista
- módulos para:
  - login
  - cadastro
  - campanhas automáticas

### 6.1.5 Ações Futuras Esperadas do Usuário
- Entrar / Criar conta
- Navegar pelos jogos
- Visualizar marketplace por jogo
- Acessar área do lojista
- Cadastrar loja
- Entrar em promoções
- Ler páginas institucionais

### 6.1.6 Sugestões de UX / Melhorias
- **Adicionar animação leve** no hero ou card games (hover, fade-in, parallax)
- **Tornar o banner promocional dinâmico**, administrado via painel
- **Boosters ilustrados no fundo da seção promocional** (cria hype)
- Substituir os grids de card games por **cards clicáveis com ícones** (tipo Steam)
- Mostrar **estatísticas públicas**:
  - nº de cartas cadastradas
  - nº de lojas
  - nº de pedidos concluídos
- CTA duplo no hero:
  - “Comprar Cartas”
  - “Vender como Loja”
- Suporte a **tema escuro** (dark mode)
- Um painel “Novos Sets” dinâmico vindo da tabela `sets`
- Um painel “Cartas mais buscadas” (analíticos futuros)
- Gamificação futura:
  - selos de “Lojista Verificado”
  - ranking de lojas
  - conquistas do usuário/jogador

### 6.1.7 Impacto no Design da V5
A existência dessa tela determina:

- necessidade de um **módulo de campanhas promocionais**
- necessidade de **gestão avançada de jogos ativos**
- necessidade de **landing institucional** separada do marketplace real
- necessidade de **área do lojista** robusta
- modularidade do marketplace (cada jogo com seu catálogo e fluxo)
- suporte no backend para **conteúdos de marketing** (banner, slogan, cards de jogos)

### 6.1.8 Conclusão
A Home é uma landing forte: clara, moderna e segmentada. Ela apresenta com clareza o que o Versus TCG promete ser e abre espaço para evolução tanto de UX quanto de lógica real no futuro.

## 6.2 Tela: Universo Magic (Magic_Home)

### 6.2.1 Descrição Geral
Esta tela representa a **home do ecossistema Magic** dentro do Versus TCG. É uma página dedicada exclusivamente ao jogo Magic: The Gathering e funciona como um portal especializado, oferecendo conteúdo, notícias, decks, spoilers, rankings e referências ao mercado de cartas.

Seu papel principal é **reter o usuário dentro do universo Magic**, criando uma experiência completa que vai além da compra e venda de cartas.

Ela também serve como porta de entrada para:
- marketplace filtrado para Magic
- decks populares
- artigos e notícias
- metagame e torneios
- sistema de criação/gestão de decks
- sistema de venda individual voltado ao jogador de Magic

### 6.2.2 Elementos Visuais e Estruturais

- **Header com logotipo e navegação Magic:**
  - Meus Decks
  - Entrar
  - Vender Cartas
  - Home, Marketplace, Torneios & Meta, Artigos, Spoilers, Ranking

- **Bloco de Busca de Cartas:**
  - Título “Encontre sua carta”
  - Input de busca
  - Lista de populares (Orcish Bowmasters, Mana Crypt, etc.)

- **Seção: Últimas Notícias**
  - Blocos com categorias (Competitivo, Commander)
  - Títulos de artigos com pequenas descrições
  - CTA “Ver tudo”

- **Seção: Decks em Alta**
  - Tabs (Standard, Modern, Pioneer)
  - Cards de decks trending com autor, resultado e preço total estimado

- **Seção: Market Watch**
  - Filtro (Diário / Semanal)
  - Alta, Baixa, Most Viewed
  - Cards com porcentagem de variação de preço

- **Seção: Selados Mais Vendidos**
  - Produtos como Commander Decks
  - Booster Boxes com preço

- **Seção: Publicidade/Parceiros**
  - Espaço reservado para banners

- **Footer:**
  - Créditos Versus TCG
  - Links internos (institucional, suporte)
  - Copyright
  - Botão ou link para páginas internas futuras

### 6.2.3 O que esta tela representa no produto final

Esta página é o equivalente a criar **um mini-portal especializado de Magic dentro do Versus TCG**.

Ela funciona como um:
- Hub de conteúdo
- Hub de dados de mercado
- Hub de ferramentas do jogador
- Porta de entrada para o marketplace filtrado por Magic

É a “home oficial de Magic” do ecossistema Versus.

### 6.2.4 Requisitos futuros de dados (quando existirem)
*Nenhum desses precisa existir agora, mas esta tela revela o que será necessário no futuro.*

- Tabela de **artigos/notícias internas** (por jogo)
- Tabela de **categorias de conteúdo** (Competitivo, Commander etc.)
- Sistema de **decks montados** por usuários
- API interna para:
  - tracking de cartas mais vistas
  - variação de preço (precificação interna + lojas parceiras)
  - produtos selados cadastrados via catálogo
- Sistema de **ranking de jogadores**
- Sistema de **torneios**, com:
  - nome
  - data
  - formato
  - premiação
  - decklists
- Tabela de **spoilers** (extensível via ingestão da Scryfall)

### 6.2.5 Ações previstas do usuário
- buscar cartas pelo nome
- navegar por notícias e artigos
- visualizar decks populares
- ver preços que subiram/caíram
- acessar página de produto
- acessar marketplace filtrado de Magic
- criar/entrar no sistema de decks (futuro)
- vender cartas diretamente (acesso rápido ao fluxo de venda)

### 6.2.6 Funcionalidades implícitas (versão MVP futuro)
- página de “Marketplace Magic”
- página de “Decks”
- página de “Torneios & Metagame”
- página de “Artigos”
- página de “Spoilers”
- página de “Ranking”
- sistema de login persistente por jogo
- sistema de venda rápida (aparecendo “Vender Cartas” no topo)

### 6.2.7 Ideias de Expansão (Visão de Produto)
Estas não são exigências — são percepções naturais da identidade do produto:

- adicionar **perfil do jogador** específico por jogo
- permitir favoritar cartas, decks, artigos etc.
- criar sistema de **coleção virtual** (minha binder)
- sistema de **histórico de preço** da carta
- sistema de **alerta de preço** (notificação quando baixar)
- integração futura com API MTGO para logs de deck
- adicionar **vídeos** (YouTube, Twitch) embedados
- ranking semanal dos **decks mais vendidos** no marketplace
- possibilidade de **avaliar loja** dentro do ecossistema Magic

### 6.2.8 Observação Importante
Nesta página existe um botão “Vender Cartas”.  
No design final, este botão deve **redirecionar para o login do lojista**, ou para:
- fluxo de venda rápida (se permitido ao usuário jogador)
- fluxo de criação de loja (se não for lojista ainda)

---
## 6.3 Tela: Marketplace de Produto (Marketplace Híbrido)

### 6.3.1 Descrição Geral
Esta é a tela de **produto individual dentro do marketplace**. O usuário chegou aqui buscando uma carta específica (ex: Serra Angel) e encontra:

- **Dados completos da carta** (imagem, texto, atributos)
- **Seletores de configuração** (idioma, acabamento)
- **Tabela de ofertas de múltiplas lojas** vendendo essa mesma carta
- **Dois fluxos de compra possíveis**:
  - comprar direto pelo marketplace (centralizado)
  - ir para a loja e comprar no domínio dela

É o coração do marketplace híbrido: permite que o player compare preços entre lojas e escolha onde comprar.

### 6.3.2 Estrutura Visual

**Header:**
- Logo Versus TCG
- Breadcrumb: Home / Magic / Foundations (J25) / Serra Angel
- Carrinho (contador)
- Login

**Seção Principal (Esquerda):**
- Imagem grande da carta (clicável para ampliar)
- Link "Ver todas as versões (28)" → lista de outros prints da mesma carta
- Raridade, número, set

**Seção de Dados da Carta (Direita):**
- Nome da carta (EN + PT-BR)
- Custo de mana (ícones CSS)
- Tipo/Supertipo
- Texto de regras
- Flavor text

**Seletores de Configuração:**
- **Idioma:** PT, EN, JP (com indicador "Hot" para trending)
- **Acabamento:** Normal, Foil
- Ao mudar, **a imagem e os dados da carta atualizam**

**Referência de Preço:**
- Mínimo (Versus): R$ 0,25
- Médio (TCGPlayer via API): R$ 1,80
- Máximo (Versus): R$ 5,00
- Nota: "Importado via API Scryfall. Convertido na cotação do dia."

**CTA de Venda:**
- "Você tem essa carta? Venda por R$ 0,15"
- Redireciona para fluxo de venda rápida (futuro)

**Filtros Laterais (Reduzidos):**
- **Estado (Condition):** NM (12), SP (5)
- **Vendedores:** Lojas Verificadas [x], Jogadores P2P [ ]
- Checkboxes para refinar a listagem

**Tabela de Ofertas:**
- Colunas:
  - Loja / Vendedor (logo + nome + avaliação + localização)
  - Detalhes (idioma + condição)
  - Quantidade disponível
  - Preço unitário
  - Botão "Comprar"
- Ordenação padrão: "Menor Preço + Frete"
- Opções de ordenação: "Menor Preço", "Melhor Reputação"
- Exemplo: Dragon's Den (★★★★★ 1.2k reviews) vende por R$ 0,25

### 6.3.3 Dados que esta Tela Exigirá no Futuro

Quando o backend estiver pronto, essa tela precisará de:

- **Dados da carta (CatalogConcept + MtgConcept):**
  - nome, tipo, supertipo, texto de regras, flavor text
  - atributos específicos (mana cost, power/toughness, etc.)
  - ícones de mana (CSS/SVG)

- **Dados de prints (CatalogPrint + MtgPrint):**
  - imagem por idioma e acabamento
  - raridade, número do colecionador
  - set de origem

- **Dados de estoque (StockItem):**
  - quantidade por loja
  - condição (NM, SP, MP, HP, DM)
  - idioma
  - foil/non-foil
  - preço

- **Dados de loja (Store):**
  - nome, logo, avaliação
  - localização (estado)
  - URL do domínio próprio

- **Referência de preço (via API externa ou tabela interna):**
  - mínimo, médio, máximo
  - cotação do dia (se importado)

- **Histórico de preço (futuro):**
  - para gráficos de tendência

### 6.3.4 Ações Previstas do Usuário

- Visualizar dados completos da carta
- Trocar idioma (atualiza imagem + texto)
- Trocar acabamento (atualiza preço + imagem)
- Filtrar ofertas por condição
- Filtrar ofertas por tipo de vendedor
- Ordenar ofertas (preço, reputação, frete)
- Clicar "Comprar" → adiciona ao carrinho (marketplace) ou redireciona para loja
- Clicar "Ver todas as versões" → lista de outros prints da mesma carta
- Clicar "Venda por..." → fluxo de venda rápida

### 6.3.5 Dois Fluxos de Compra

**Fluxo 1: Comprar pelo Marketplace (Centralizado)**
- Clica "Comprar"
- Adiciona ao carrinho do Versus TCG
- Checkout único
- Pedido é distribuído para a loja correspondente
- Rastreamento centralizado

**Fluxo 2: Comprar na Loja (Domínio Próprio)**
- Clica no nome/logo da loja
- Redireciona para `loja.com.br` (domínio próprio)
- Compra acontece no site da loja
- Checkout e rastreamento na loja

### 6.3.6 Filtros Expandidos (Sugestão)

O protótipo mostra apenas "Estado" e "Vendedores", mas devem ser adicionados:

- **Faixa de Preço:** R$ 0,00 – R$ 1.000,00 (slider)
- **Idioma:** PT, EN, JP, FR, DE, IT, etc. (checkboxes)
- **Acabamento:** Normal, Foil (checkboxes)
- **Localização da Loja:** SP, RJ, MG, etc. (checkboxes)
- **Avaliação Mínima:** ★★★★☆ ou superior (radio)
- **Estoque Mínimo:** 1+, 5+, 10+ (radio)
- **Tipo de Vendedor:** Loja, Jogador, Ambos (radio)
- **Frete Incluído:** Sim / Não (checkbox)

### 6.3.7 Ideias de Expansão (UX/Funcionalidades)

- **Gráfico de histórico de preço** (últimos 30/90/365 dias)
- **Alerta de preço:** "Me avise quando cair para R$ X"
- **Comparador de lojas:** lado a lado, com frete calculado
- **Avaliação de loja** dentro dessa tela (mini-card com reviews)
- **Selo de "Loja Verificada"** com tooltip explicando critérios
- **Tempo de envio estimado** por loja
- **Foto do usuário que vendeu** (se P2P)
- **Botão "Favoritar"** para salvar na coleção de desejos
- **Compartilhar no WhatsApp/Twitter** (social sharing)
- **QR Code** para abrir no celular
- **Versão mobile** com layout adaptado (cards em coluna, filtros em drawer)
- **Integração com MTGO** (futuro): "Importar para MTGO" se player tiver perfil vinculado
- **Notificação de restock:** "Essa loja reabasteceu essa carta"
- **Recomendação de cartas relacionadas:** "Quem comprou isso também comprou..."

### 6.3.8 Impacto no Design da V5

Esta tela exige:

- **Sistema de StockItem robusto** (quantidade, condição, idioma, foil, preço)
- **API de busca de cartas** por jogo (Magic, Pokémon, etc.)
- **Sistema de carrinho híbrido** (marketplace + loja)
- **Sistema de pedidos** que distribui para lojas
- **Integração com gateway de pagamento** (Pix, Cartão)
- **Sistema de avaliação de loja**
- **Rastreamento de pedidos** centralizado
- **Gestão de frete** (integração com transportadoras)
- **Cache de referência de preço** (atualizado diariamente)
- **Suporte a múltiplos idiomas** (PT, EN, JP, FR, etc.)

### 6.3.9 Observação Importante

O protótipo mostra apenas **Magic**, mas essa tela será **multiplicada por 10 jogos**.

Cada jogo terá:
- mesmo esqueleto técnico
- design visual adaptado (cores, ícones, tipografia)
- dados específicos do jogo (ex: Pokémon terá HP, tipos; YGO terá ATK/DEF)

### 6.3.10 Conclusão

Esta é a tela mais crítica do marketplace: onde a compra realmente acontece. Ela precisa ser rápida, clara e confiável. O protótipo é simples, mas a implementação será complexa (múltiplos dados, filtros, dois fluxos de compra, integração com lojas).

## 6.4 Tela de Criação de Conta (Modal Pop‑up)

### 6.4.1 Descrição Geral
Este modal exibe o fluxo inicial de criação de conta no Versus TCG. Ele não substitui a página atual; aparece como um pop-up centralizado e elegante, mantendo o usuário no contexto onde estava.

A experiência é rápida, de baixa fricção, com foco em diferenciar claramente dois tipos de usuário:

- Jogador (player)
- Lojista (store owner)

O modal funciona como um “mini‑wizard”: o usuário escolhe uma trilha e o modal troca de conteúdo sem recarregar a página.

---

### 6.4.2 Estrutura do Modal

#### A) Tela inicial — Selecionar tipo de conta
- Título: “Crie sua conta Versus”
- Duas opções grandes:
  - **Sou Jogador**
  - **Sou Lojista**
- Texto curto explicando o propósito de cada tipo de conta
- Link: “Já tem uma conta? Fazer Login”
- Ação: “← Voltar”

A escolha determina qual bloco do modal será exibido.

---

#### B) Fluxo: Criar Conta de Jogador (Modal interno)
Campos:
- Nome  
- Sobrenome  
- Nick (público)  
- E-mail  
- Senha / Confirmar Senha  

Botões:
- “Criar Conta Grátis”
- “← Voltar”

Objetivo:
- Criar perfil do jogador
- Habilitar login normal no marketplace
- Alta velocidade no cadastro, sem validações externas

---

#### C) Fluxo: Criar Conta de Lojista (Modal interno)
Fase 1 (dados do usuário administrador da loja):
- CPF/CNPJ  
- Celular  
- Nome completo  
- E‑mail comercial  

Fase 2 (prévia automática da loja):
- Nome fantasia  
- URL (slug)  
  - Exemplo renderizado: `versus.com/slug-da-loja`

Botão:
- “Continuar para Validação”

Objetivo:
- Criar o administrador principal da loja
- Gerar prévia da loja
- Iniciar o processo que levará ao painel administrativo do lojista

---

### 6.4.3 Sobre Assumir Loja Desativada (caso especial)
O fluxo dessa tela deve suportar futuramente:

- Um lojista criando conta para assumir uma loja que foi vendida legalmente
- Loja precisa estar **desativada e marcada como transferível**
- O sistema exige um **token de transferência** (gerado pelo antigo dono)
- Sem token → **não pode assumir**
- Impede que alguém tome a loja de outro lojista que a desativou temporariamente

Esse comportamento não aparece visualmente no HTML atual, mas é **requisito de backend** para evitar fraude.

---

### 6.4.4 Modalidade Modal (pop‑up)
Este fluxo acontece dentro de um modal overlay:

- fundo escurecido  
- janela central  
- sem recarregar a página  
- efeito suave ao trocar entre “Jogador” e “Lojista”

Isso permite:
- login/cadastro em qualquer parte do site  
- experiência fluida  
- onboarding mais elegante  

---

### 6.4.5 Ações previstas do usuário
- escolher tipo de conta  
- criar conta de jogador  
- criar conta de lojista  
- iniciar processo de ativação da loja  
- no futuro: usar token de transferência  

---

### 6.4.6 Sugestões / Melhorias futuras
- Verificar disponibilidade de slug em tempo real  
- Mostrar aviso “Você está criando a conta de administrador da loja”  
- Suporte a 2FA nesse mesmo modal no futuro  
- Animação suave ao trocar de player para lojista  
- Passo “Revisar dados antes de criar conta”  

---

### 6.4.7 Conclusão
O modal é limpo, direto e poderoso. Ele respeita a estrutura dual do sistema (players e lojistas), não exige telas separadas e mantém a experiência dentro do fluxo do site sem recarregar páginas.  
É uma base muito sólida para o onboarding completo do Versus TCG.

## 6.5 Tela de Login (Modal Pop‑up)

### 6.5.1 Descrição Geral
O modal de login é o ponto de entrada para **jogadores**.  
Ele aparece como um pop‑up flutuante, acima da tela em que o usuário estava, reforçando que:

**A experiência de login no marketplace é exclusivamente para players.**

Lojistas possuem um ecossistema separado, acessado apenas através do domínio da loja.

---

### 6.5.2 Estrutura do Modal

#### Bloco principal — Login de Jogador
- Título: “Bem‑vindo de volta”
- Subtítulo: “Acesse sua conta de Jogador”
- Campos:
  - E‑mail ou Nick
  - Senha
- Link: “Esqueceu?”
- Botão: “ENTRAR”
- Login social:
  - Discord  
  - Google

Esse é o fluxo padrão de acesso ao universo Versus TCG como player.

---

### 6.5.3 A sacada essencial: “E é um lojista parceiro?”
No final do modal:

- Texto: **“É um lojista parceiro?”**
- Link: **“Acesse o Painel da Loja”**
- → Ao clicar, o modal se transforma em outra tela interna (sem sair da página).

Essa funcionalidade evita que o lojista fique perdido tentando logar pelo lugar errado.

---

### 6.5.4 Modal interno — Login de Lojista (Redirecionamento)
Após clicar no link de lojista, o modal exibe:

**Título:** “Painel da Loja”  
**Subtítulo:** “Para gerenciar, acesse o endereço da sua loja.”

Campos:
- “Endereço da Loja (URL)”
  - placeholder: `Digite seu domínio personalizado ou o endereço Versus da sua loja`

Botão:
- “IR PARA MEU PAINEL”

Links:
- “Esqueci o endereço da minha loja”
- “← Voltar para Jogador”

### 6.5.5 Como funciona o fluxo (importante)
**O lojista NÃO loga pelo marketplace.**  
O marketplace serve aos players.

Esse modal:
- ajuda o lojista a chegar onde deve
- sem misturar as experiências
- sem confusão

Fluxo real:

1. Lojista digita:  
   - `minhaloja.com.br` **ou**  
   - `versus.com/minhaloja`

2. O sistema valida o domínio  
3. Redireciona para **/login** no domínio da loja  
4. O lojista faz login lá  
5. Acessa o painel completo da loja (estoque, pedidos, relatórios etc.)

---

### 6.5.6 Benefícios UX da solução
- evita frustração (“onde faço login da minha loja??”)
- mantém o marketplace limpo e focado no jogador
- dá acolhimento ao lojista que veio parar no lugar errado
- redirecionamento suave e profissional

---

### 6.5.7 Modal como overlay
- não impede navegação  
- permite logar de qualquer ponto do site  
- animações suaves entre “Player Login” e “Lojista (URL)”  
- retorno fácil com botão “Voltar”  

---

### 6.5.8 Melhorias futuras possíveis
- Auto-sugerir lojas associadas ao CPF/e-mail
- Histórico: “lojas que você acessou recentemente”
- Verificação se a loja está ativa/desativada antes do redirecionamento
- Modal de confirmação se o domínio estiver incorreto

---

### 6.5.9 Conclusão
O modal de login separa perfeitamente:
- o **fluxo do jogador** (marketplace)  
- o **fluxo do lojista** (domínio próprio)  

A solução de redirecionamento por URL é simples, elegante, intuitiva e evita erros comuns.  
Excelente decisão de design — e totalmente alinhada com a arquitetura do Versus TCG.

## 6.6 Tela: Carrinho do Marketplace (cart.html)

### 6.6.1 Descrição Geral
Esta é a tela de **carrinho de compras do marketplace**, onde o usuário revisa os produtos selecionados antes de prosseguir para identificação e pagamento.  
Diferente do carrinho de uma loja individual, o carrinho do marketplace pode reunir itens de **diversas lojas simultaneamente**, refletindo o modelo híbrido da plataforma.

Cada loja mantém:
- seus próprios produtos  
- seu próprio frete  
- seu próprio tempo de envio  
- seu subtotal  

E o marketplace consolida tudo em um pedido dividido internamente.

---

### 6.6.2 Estrutura Visual e Componentes

#### A) Cabeçalho do fluxo
Um topo em forma de **linha do tempo**:

1. Carrinho  
2. Identificação  
3. Pagamento  

Indica ao usuário onde ele está.

---

#### B) Saudação
- “Olá, Allex”
- Indica que o usuário está logado como **player** (não lojista)

---

### C) Lista de Lojas no Carrinho
Os itens aparecem agrupados por loja, cada bloco contendo:

- Logo ou código da loja (ex: DRAGON, MANA)
- Nome da loja (ex: Dragon’s Den Oficial)
- Cidade / Estado
- Tempo estimado de envio (ex: “Enviando em 24h”)
- Links:
  - **Visitar Loja**
  - **Aproveitar Frete**  
    (incentiva o usuário a comprar mais itens daquela loja para economizar no envio)

---

### D) Produtos da Loja
Cada produto exibe:

- Imagem do card  
- Nome (ex: Serra Angel)  
- Set / Condição / Idioma  
- Preço unitário  
- Botão Remover  
- Controle de quantidade (– / 1 / +)

---

### E) Cálculo de Frete Por Loja
Cada loja calcula **seu próprio frete**, independentemente das outras:

- Exemplo:  
  - PAC: R$ 12,00  
  - Sedex: R$ 22,00  
  - JadLog: R$ 18,00

A entrega é sempre **por loja**, nunca unificada — porque cada loja envia de um local e com suas próprias políticas.

---

### F) Subtotal da Loja
Exemplo:

- Dragon's Den: R$ 12,25  
- Mana Leak Store: R$ 268,00

---

### G) Resumo Final do Carrinho
O resumo consolida:

- Total de produtos (ex: R$ 250,25)  
- Soma dos fretes de cada loja (ex: R$ 30,00)  
- Descontos (se houver)  
- **Total Geral (ex: R$ 280,25)**  
- Métodos aceitos: Pix ou Cartão  
- Botão: “IR PARA PAGAMENTO”

Importante: O cliente paga **um único checkout**, mesmo comprando de várias lojas.

---

### 6.6.3 Funcionamento Financeiro (Futuro)
A tela não mostra explicitamente, mas representa o fluxo financeiro real do sistema:

1. O pagamento **entra na conta do marketplace** (PIX ou Cartão).  
2. Quando a transação é compensada:  
   - a plataforma **deduz a taxa** (porcentagem da venda)  
   - e **repassa automaticamente** o valor líquido para cada loja  
3. O repasse é **automatizado**, sem retenções além da taxa.  
4. Cada loja recebe seu valor em conta imediatamente após liquidação.

Este processo será totalmente transparente no futuro painel da loja.

---

### 6.6.4 Papel do Marketplace no Pagamento
O marketplace funciona como:

- intermediador pago  
- responsável por checkout e split pós‑compensação  
- repassando valores líquidos para as lojas  
- sem tocar nos dados de pagamento da loja individual

Essa arquitetura reduz chargebacks, fraudes e problemas de captura.

---

### 6.6.5 Carrinho da Loja vs Carrinho do Marketplace  
*(Observação essencial que deve constar na documentação.)*

Este arquivo (**cart.html**) representa apenas o **carrinho do marketplace**.

### Carrinho do Marketplace (este arquivo)
- Itens de várias lojas  
- Fretes separados  
- Checkout central  
- Pagamento Pix/Cartão **do marketplace**  
- Split financeiro automático

### Carrinho da Loja Individual (futuro)
- Apenas itens daquela loja  
- Frete único  
- Checkout **exclusivamente com meios de pagamento da loja**  
- Pagamento **vai direto para a loja**, sem intermediação do marketplace  
- Sem split (não faz sentido nesse ambiente)

Essa distinção é crucial para manter a lógica do ecossistema:

- Marketplace = ambiente unificado de compra  
- Loja = ecossistema isolado do lojista

---

### 6.6.6 Ações do Usuário nesta Tela
- Remover produto  
- Alterar quantidade  
- Alterar método de entrega por loja  
- Visitar loja  
- Aproveitar frete  
- Conferir resumo  
- Avançar para pagamento  

---

### 6.6.7 Dados Necessários no Futuro (Backend)
- Estoque por loja  
- Cálculo de frete individual  
- Dados de endereço do cliente  
- Métodos de entrega por loja  
- Ferramenta de split financeiro interno  
- Regras de cálculo de taxa do marketplace  
- Módulo de checkout unificado  

---

### 6.6.8 Sugestões e Melhorias Futuras
- Mostrar aviso “Você está comprando de 2 lojas diferentes”  
- Cálculo de prazo total por loja  
- Alertas de estoque baixo  
- Cupom por loja  
- Animação suave no modificar quantidade  
- Mensagem clara se a loja tiver envio grátis acima de determinado valor  
- Lista de recomendações “Da mesma loja”  

---

### 6.6.9 Conclusão
O carrinho do marketplace é uma solução elegante que respeita a arquitetura híbrida do Versus TCG. Ele permite pedidos com múltiplas lojas, fretes separados e pagamento unificado, de forma clara, intuitiva e tecnicamente escalável.

## 6.7 Perfil do Player (Hub Universal do Usuário)

### 6.7.1 Visão Geral
O Perfil do Player é o núcleo da experiência do usuário no Versus TCG. Ele acompanha o jogador em **qualquer parte do marketplace** e em **qualquer loja do ecossistema**, funcionando como um hub unificado que centraliza todas as informações pessoais, compras, dados de jogo e histórico.

Diferente das plataformas concorrentes, o jogador não precisa acessar um painel separado para cada loja onde comprou. O Versus TCG oferece um **sistema unificado**, no qual:

- todos os pedidos de todas as lojas aparecem no mesmo lugar  
- todos os status são sincronizados automaticamente  
- o jogador tem uma única identidade universal  
- o player interage com qualquer loja a partir de um único centro de controle  

Este perfil representa a identidade do usuário em **TODO o multiverso** do Versus TCG.

---

### 6.7.2 Estrutura Geral do Perfil do Player
O perfil é composto por seções que aparecem como abas, cards ou blocos no lado esquerdo ou superior do painel (dependendo do design final). As seções esperadas incluem:

- **Meus Pedidos** *(6.7.1 – já detalhado em seguida)*
- **Mensagens**
- **Endereços**
- **Minha Coleção (Binder Digital)**
- **Decks**
- **Wishlist / Favoritos**
- **Minha Conta**
- **Configurações**
- **Notificações**
- **Preferências de Jogos**
- **Histórico de Preços (futuro)**
- **Validação MTGO (futuro)**
- **Conquistas e Progressão (Nível do Colecionador)**

Nem todas estão nos protótipos enviados, mas todas nasceram da intenção e da estrutura da tela de perfil e serão adicionadas conforme você enviar as telas.

---

### 6.7.3 Elementos fixos na interface do Perfil
Independente da aba, sempre aparecem:

- **Foto / avatar do player**
- **Nick público**
- **Nível / Rank de Colecionador**
- Badges ou conquistas (futuro)
- Ações rápidas:
  - editar perfil  
  - verificar e-mail (se existir)  
  - 2FA (futuro)  
  - trocar senha  
  - sair  

---

### 6.7.4 Comportamento Universal
O Perfil do Player é acessível de:

- qualquer página do marketplace  
- qualquer página de qualquer loja do ecossistema  
- header global (ao clicar no nome / avatar do usuário)
- modais de “ver detalhes do pedido”
- páginas de decks
- área de mensagens com lojistas

Essa universalidade reforça a ideia central do sistema:

> “O player nunca pertence a uma loja — ele pertence ao Versus TCG.”

---

### 6.7.5 Impacto no Design Geral
A existência deste perfil universal determina:

- sistema de login único  
- sincronização de pedidos de múltiplas lojas  
- centralização de mensagens entre player ↔ lojas  
- API universal de pedidos  
- identificação de player em 10 marketplaces diferentes e em todas as lojas  
- consistência visual entre áreas distintas do ecossistema  

---

### 6.7.6 Ideias Futuras para Evolução
- Perfil público para outros players visualizarem (decklists, conquistas)  
- Modo streamer (compartilhar deck/rede social)  
- Ranking global de colecionadores  
- Cards de resumo: total gasto, lojas favoritas, cartas favoritas  
- Estatísticas de coleção  
- Desafios / missões (gamificação leve)  
- Integração com MTGO e Arena (automático, opcional)

---

### 6.7.7 Conclusão
O Perfil do Player é a “casa” do usuário no ecossistema inteiro.  
É a peça que diferencia o Versus TCG do padrão do mercado e dá início à visão do multiverso de card games: **um jogador, um login, infinitos universos.**

## 6.7.2 Minha Coleção (Visão Geral)

### 6.7.2.1 Propósito
A “Minha Coleção” é o hub central de cartas do jogador — um sistema totalmente automatizado que organiza tudo o que o player possui, sem exigir esforço manual.

O objetivo é ser:
- rápido  
- automático  
- intuitivo  
- impossível de bagunçar  

E funcionando igualmente bem para jogadores iniciantes e colecionadores hardcore.

---

### 6.7.2.2 Criação Automática de Pastas e Álbuns (Regra Absoluta)
O jogador **nunca cria pastas de jogo nem álbuns de set manualmente**.

A criação é 100% automática com base em duas situações:

#### A) Quando uma compra é entregue com sucesso
O sistema detecta:
- o jogo (Magic, Pokémon TCG, Pokémon OCG, YGO TCG, YGO OCG…)  
- o set (Foundations J25, Obsidian Flames, Duelist Nexus…)  

E faz automaticamente:
1. Criar o **Fichário do Jogo** (se não existir).  
2. Criar o **Álbum do Set** (se não existir).  
3. Adicionar as cartas no álbum correto.  

O jogador só precisa responder “Sim, adicionar à coleção”.

#### B) Quando adiciona cartas manualmente
Ao adicionar uma carta manualmente:
- o sistema detecta o jogo da carta  
- cria o fichário  
- detecta o set  
- cria o álbum  
- adiciona a carta  

Zero esforço. Zero atrito.

---

### 6.7.2.3 Organização Visual
A tela mostra:

- **Patrimônio Estimado** (baseado no preço mínimo do marketplace)  
- **Total de Itens**  
- **Lista de Fichários (por jogo)**  
- **Pastas temáticas criadas pelo usuário**, como:  
  - Trade Binder  
  - Commander Staples  
  - Pokémon 151  
  - etc.  

Essas pastas são opcionais e totalmente diferentes dos álbuns automáticos de set.

---

### 6.7.2.4 Joias da Coroa
Uma tabela com as cartas mais valiosas, contendo:

- Imagem  
- Edição  
- Condição  
- Preço médio  
- Botões “Vender” ou “Editar”  

Serve como um painel rápido da coleção premium.

---

### 6.7.2.5 Venda Direta P2B (Player → Loja)
A coleção permite que o player coloque pastas, álbuns ou decks à venda para lojas do sistema.

Fluxo:
1. O player seleciona uma pasta/álbum/deck.  
2. Clica “Oferecer para Lojas”.  
3. O sistema monta automaticamente uma lista baseada nas cartas possuídas.  
4. O player pode remover cartas da lista.  
5. Lojas fazem ofertas com base em preços de referência do marketplace.  
6. Player escolhe entre:  
   - receber em **créditos da loja**  
   - receber em **PIX direto**  

**Marketplace NÃO cobra taxa, NÃO processa pagamento e NÃO media a venda**, exceto em disputas.

#### Regras de remoção:
- Se o player vender um álbum inteiro → o álbum desaparece da interface.  
- Se vender um fichário inteiro → o fichário desaparece.  
- Cartas vendidas são removidas da coleção automaticamente.  

---
### 6.7.2.6 Regras de Deck
Para evitar duplicidade, confusão e erros:

- Uma carta pode estar em **somente UM dos seguintes estados**:
  - **Em um Deck**  
  - **Na Coleção (Álbum/Fichário)**  
  - **Em negociação**  
  - **Vendida (removida)**  

Se a carta estiver em um deck:
- ela **não aparece como “livre” no álbum**  
- mas aparece colorida com um **ícone indicando alocação**  
- e com o texto:  
  - “3 em Decks / 0 livres”, por exemplo

Isso permite ao jogador saber que possui a carta **mas ela já está em uso**.

Se vender um deck inteiro:
- o deck some  
- as cartas também  
- e são removidas dos álbuns automaticamente  

---

### 6.7.2.7 Retenção de Dados
- Pedidos permanecem disponíveis por **1 ano**.  
- Cartas permanecem na coleção para sempre (até venda/remoção).  
- PDF de pedidos pode ficar disponível sem prazo.  

---

### 6.7.2.8 Ações do Usuário
- adicionar carta  
- organizar pastas temáticas  
- vender fichários/álbuns/decks  
- navegar por jogos e sets  
- acessar valores, quantidades e estados  

---

### 6.7.2.9 Conclusão
É um sistema robusto, simples e elegante, que combina o que existe de melhor no mundo físico (álbuns, sets, decks) com automatização digital de ponta.  

A experiência é fluida e sem atrito — o jogador foca em colecionar e jogar, não em “gerenciar planilhas”.

## 6.7.3 Álbum / Pasta de Set (Visão tipo Álbum de Figurinhas)

### 6.7.3.1 Propósito
Esta é a visão tipo álbum de figurinhas, onde o player vê **todas as cartas de um set específico**, indicando:

- o que possui  
- o que falta  
- em que idioma  
- em que estado  
- quantas cópias  
- e quais estão alocadas em decks  

É a visão mais dedicada ao colecionador.

---

### 6.7.3.2 Comportamento Visual
Cada card possui 3 estados possíveis:

#### A) O player não possui a carta →  
- imagem em **grayscale**  
- label “Faltante”  
- zero opções de hover  

#### B) O player possui e está “livre” →  
- imagem **colorida**  
- contadores:  
  - 2x NM  
  - 1x SP  
  - etc.  
- contador total  
- indicações de idioma  
- hover com ações:  
  - mover  
  - editar  
  - vender  

#### C) O player possui, mas ela está em um Deck →  
- imagem **colorida**  
- **ícone visual no canto superior** indicando “alocada em deck”  
- texto:  
  - “3 em Decks / 1 Livre”  
  - ou “2 em Decks / 0 Livres”  
- hover continua ativo apenas para cartas possuídas

O ícone resolve a leitura rápida:  
O texto resolve o detalhe exato.

É a combinação perfeita que você descreveu.

---

### 6.7.3.3 Marcação de Alocação
O álbum precisa mostrar claramente:

- que o player **possui** a carta  
- que ela está **em uso em deck(s)**  
- que o player **não precisa comprar outra**  

Essa clareza visual evita erro de compra.

---

### 6.7.3.4 Detalhamento de Carta
Ao passar o mouse (somente cartas possuídas), o usuário vê:

- quantidade total  
- condições (NM, SP, HP…)  
- idiomas  
- quantas estão em cada deck  
- opções:
  - mover para outra pasta  
  - mover para deck  
  - vender  
  - remover manualmente  

Cartas não possuídas **não** ativam hover.

---

### 6.7.3.5 Regras de Criação Automática
Como definido:

- Ao adicionar a primeira carta de um set → o álbum é criado  
- Se o álbum esvaziar completamente (após vendas) → ele desaparece da interface  

O sistema se mantém limpo e organizado sem intervenção humana.

---

### 6.7.3.6 Conexão com Decks
A visão de álbum se integra 100% com os decks:

- “0 livres / 4 em decks”  
- ícone de deck  
- clique abre lista dos decks onde a carta está incluída  
- sistema impede vender carta que está num deck sem desbloquear antes  

---

### 6.7.3.7 Estatísticas do Set
Topo da página exibe:

- Progresso (142/271)  
- Comuns, Incomuns, Raras, Míticas  
- Data de lançamento  
- Cód. do Set  

---

### 6.7.3.8 Conclusão
O álbum é a visão mais visual e intuitiva da coleção, usando o esquema de “álbum de figurinhas” onde o progresso é claro e gratificante.  

Você transformou o que seria um inventário seco em uma experiência de colecionador real.

## 6.7.4 Deck Builder (Modelo A)

### 6.7.4.1 Propósito
Este é o primeiro modelo de deckbuilder do Versus TCG — uma ferramenta profissional que permite ao jogador:

- importar listas de sites externos  
- adicionar cartas do inventário pessoal  
- validar deck conforme as regras do jogo  
- visualizar estatísticas em tempo real  
- cruzar com a coleção pessoal  
- receber sugestões de compra/troca  
- marcar decks como completos ou incompletos  
- exportar em múltiplos formatos  

O Modelo A prioriza uma **layout tradicional**, com lista de cartas à esquerda e painel de informações à direita.

---

### 6.7.4.2 Estrutura Visual (Modelo A)

#### Topo
- Nome do deck (editável)  
- Formato (Standard, Modern, Commander, Expanded, etc.)  
- Jogo (Magic, Pokémon TCG, etc.)  
- Botões:
  - Importar Lista  
  - Adicionar do Inventário  
  - Salvar Deck  
  - Exportar  
  - Duplicar  
  - Deletar  

#### Lado Esquerdo — Decklist
- Mainboard (com contador total)  
- Sideboard (se aplicável ao jogo)  
- Cada linha mostra:
  - Quantidade  
  - Nome da carta  
  - Set/Edição  
  - Custo (mana/energia/etc.)  
  - Ícone de status (✓ possui, ⚠ faltando, ⟳ em outro deck)  

#### Lado Direito — Painel de Análise
- Status da Coleção:
  - X/Y cartas possuídas  
  - Z faltando  
  - W em uso (transferir)  

- Estatísticas Rápidas:
  - Total de cartas  
  - Custo médio  
  - % de terrenos (Magic)  
  - % de energias (Pokémon)  

- Botão: "Adicionar Faltantes ao Carrinho"  

---

### 6.7.4.3 Fluxo de Importação de Lista

#### Passo 1: Cole a lista
O jogador cola uma lista de texto de qualquer site (MTGGoldfish, Moxfield, Limitless TCG, etc.).

#### Passo 2: Sistema detecta formato automaticamente
- Identifica jogo (Magic, Pokémon, etc.)  
- Identifica formato (Standard, Modern, etc.)  
- Separa mainboard de sideboard (se houver)  
- Valida quantidade de cartas  

#### Passo 3: Cruzamento com coleção
O sistema verifica cada carta:

**Caso A: Possui em quantidade suficiente**
- ✓ Adicionada automaticamente  
- Marcada como "Possuída"  

**Caso B: Possui, mas em quantidade insuficiente**
- ⚠ Adicionada parcialmente  
- Marcada como "Faltando X cópias"  
- Sugestão de compra com link direto  

**Caso C: Possui, mas alocada em outro deck**
- ⟳ Sistema oferece duas opções:
  1. Transferir do deck anterior (marcará aquele como incompleto)  
  2. Comprar novas cópias  

**Caso D: Não possui**
- ❌ Marcada como "Faltando"  
- Sugestão de compra  

#### Passo 4: Resultado
O deck é criado com status:
- **Completo** (todas as cartas possuídas)  
- **Incompleto** (faltam cartas ou estão em outro deck)  

---

### 6.7.4.4 Adicionar Cartas Manualmente

O jogador pode:
- clicar "+ Add Carta"  
- buscar pelo nome  
- selecionar versão (set, idioma, condição)  
- definir quantidade  
- adicionar ao mainboard ou sideboard  

A carta é adicionada e o deck é revalidado em tempo real.

---

### 6.7.4.5 Validação por Jogo

#### Magic: The Gathering
- Mainboard: 60+ cartas  
- Sideboard: 15 cartas (exato)  
- Máximo 4 cópias de cada (exceto terrenos básicos)  
- Validação de legalidade por formato  

#### Pokémon TCG
- Exato: 60 cartas  
- Sem sideboard  
- Máximo 4 cópias (exceto energia básica)  
- Validação de formato (Standard, Expanded)  

#### Yu-Gi-Oh! (futuro)
- Main Deck: 40-60 cartas  
- Extra Deck: 0-15 cartas  
- Side Deck: 0-15 cartas  
- Máximo 3 cópias  

---

### 6.7.4.6 Exportação

O jogador pode exportar o deck em formatos:
- TXT (texto puro)  
- MTGO (Magic Online)  
- Arena (Magic Arena)  
- Moxfield  
- Archidekt  
- Cockatrice  
- Formato do jogo (quando aplicável)  

---

### 6.7.4.7 Salvar Deck

O jogador pode:
- nomear o deck  
- adicionar descrição  
- adicionar tags (ex: "Standard", "Budget", "Casual")  
- definir como favorito  
- compartilhar via link (futuro)  

O deck fica salvo no perfil e acessível de qualquer lugar do sistema.

---

### 6.7.4.8 Diferença entre Modelo A e B

O Modelo A prioriza:
- **layout tradicional** (lista esquerda, análise direita)  
- **foco em decklist**  
- **painel compacto de estatísticas**  

O Modelo B (6.7.5) prioriza:
- **layout visual** (pool em destaque, análise em painel lateral)  
- **foco em coleção**  
- **estatísticas mais expandidas**  

Ambos fazem exatamente a mesma coisa; a diferença é UX/layout.

---

### 6.7.4.9 Conclusão

O Modelo A é uma ferramenta robusta e tradicional, ideal para jogadores que já conhecem deckbuilding e querem velocidade e eficiência.

## 6.7.5 Deck Builder (Modelo B)

### 6.7.5.1 Propósito
Este é o segundo modelo de deckbuilder — uma ferramenta mais visual e intuitiva, que prioriza a experiência do colecionador enquanto mantém toda a funcionalidade competitiva.

O Modelo B exibe a **pool de cartas disponíveis** em destaque, com análise de coleção em painel lateral, permitindo uma experiência mais fluida e visual.

---

### 6.7.5.2 Estrutura Visual (Modelo B)

#### Topo
- Nome do deck (editável)  
- Formato  
- Jogo  
- Botões (Importar, Adicionar, Salvar, Exportar, etc.)  

#### Centro — Pool de Cartas Disponíveis
- Grid visual com cartas do inventário  
- Cada carta mostra:
  - Imagem  
  - Nome  
  - Quantidade disponível  
  - Ícone de "adicionar ao deck"  
- Filtros:
  - Por tipo  
  - Por custo  
  - Por cor (Magic)  
  - Por tipo de energia (Pokémon)  

#### Lado Direito — Painel Lateral (Modular)
- Decklist atual (compacta)  
- Status da coleção  
- Estatísticas  
- Curva de mana  

---

### 6.7.5.3 Fluxo de Adição de Cartas

#### Via Pool Visual
- Jogador vê cartas disponíveis no grid  
- Clica na carta → adiciona ao deck  
- Deck é revalidado em tempo real  

#### Via Importação
- Mesmo fluxo do Modelo A  
- Resultado é exibido no grid com ícones de status  

---

### 6.7.5.4 Painel Lateral (Modular no Futuro)

O painel exibe:
- Decklist compacta  
- Status por carta  
- Estatísticas rápidas  
- Curva de mana (selecionável entre gráficos)  

**Para MVP:** layout fixo.  
**Para futuro:** painel redimensionável e arrastável.

---

### 6.7.5.5 Validação e Cruzamento com Coleção

Idêntico ao Modelo A:
- Validação por jogo  
- Cruzamento com coleção  
- Sugestões de compra/troca  
- Marcação de decks incompletos  

---

### 6.7.5.6 Diferença entre Modelo A e B

**Modelo A:**
- Layout tradicional (lista esquerda)  
- Foco em texto/dados  
- Painel compacto  

**Modelo B:**
- Layout visual (grid central)  
- Foco em imagens  
- Painel lateral modular  

**Ambos têm as mesmas funcionalidades.**  
A diferença é puramente UX/design.

---

### 6.7.5.7 Conclusão

O Modelo B é ideal para colecionadores que querem uma experiência mais visual e intuitiva, sem perder a profundidade competitiva.

## 6.7.6 Estatísticas, Curva de Mana e Modos (Pilar do Deckbuilding)

### 6.7.6.1 Propósito
As estatísticas são o coração do deckbuilding profissional. Elas transformam o deckbuilder de um "editor de listas" em uma **ferramenta de análise estratégica**.

O Versus TCG oferece múltiplas visualizações, múltiplos modos e total personalização — porque o deck é do player, e ele decide como analisá-lo.

---

### 6.7.6.2 Curva de Mana (Magic) — Obrigatória no MVP

A curva de mana responde a pergunta fundamental:

> "Meu deck é rápido, lento ou equilibrado?"

#### Visualizações Disponíveis

**A) Gráfico de Barras Vertical (padrão Magic)**
- Eixo X: custo de mana (0, 1, 2, 3, 4, 5, 6+)  
- Eixo Y: quantidade de cartas  
- Mostra distribuição clara  

**B) Gráfico de Barras Horizontal**
- Mesma informação, rotacionada  
- Útil em telas menores  

**C) Gráfico de Linha (Curva Contínua)**
- Mostra tendência visual  
- Identifica "picos" e "vales" rapidamente  

**D) Gráfico de Área (Preenchido)**
- Visão mais visual  
- Mostra volume total de cartas por faixa  

**E) Scatter Plot (Avançado)**
- Cada ponto = uma carta  
- Eixo X: custo  
- Eixo Y: tipo (criatura, spell, etc.)  
- Identifica outliers  

#### Estatísticas Calculadas

- **Custo Médio (Average CMC):** soma de custos / total de cartas  
- **Custo Mediano:** valor do meio (mais útil que média em decks bimodais)  
- **Custo Mínimo e Máximo:** extremos  
- **Desvio Padrão:** variação da curva  
- **Moda:** custo mais frequente  

---

### 6.7.6.3 Estatísticas Adicionais (Magic)

- **Distribuição por Tipo:**
  - % Criaturas  
  - % Magias Instantâneas  
  - % Feitiços  
  - % Terrenos  
  - % Artefatos  
  - % Encantamentos  

- **Distribuição por Cor:**
  - % Branco, Azul, Preto, Vermelho, Verde  
  - % Multicolorido  
  - % Incolor  

- **Análise de Remoção:**
  - Quantidade de remoções  
  - Quantidade de card draw  
  - Quantidade de ramp  
  - Quantidade de board wipes  

- **Sinergia:**
  - Cartas que se complementam (futuro)  
  - Combos detectados (futuro)  

---

### 6.7.6.4 Estatísticas para Pokémon TCG

- **Distribuição de Pokémon:**
  - Básicos  
  - Evolução 1  
  - Evolução 2  

- **Distribuição de Treinadores:**
  - Suporte  
  - Item  
  - Estádio  

- **Distribuição de Energias:**
  - Básicas  
  - Especiais  

- **Curva de Custos de Energia:**
  - Quantas cartas custam 1, 2, 3, 4+ energias para atacar  

- **Consistência:**
  - Probabilidade de abrir um start consistente  
  - Probabilidade de "brick" no turno 1  

---

### 6.7.6.5 Modos de Visualização: Collection vs Competitive

#### COLLECTION MODE
- Foco visual  
- Artes grandes das cartas  
- Mostra raridades (R, RR, RRR, etc.)  
- Mostra foil vs normal  
- Mostra versões alternativas  
- Mostra condições (NM, SP, HP)  
- Mostra idiomas  
- Mostra se está em outro deck  
- Estatísticas em segundo plano  
- **Ideal para:** colecionadores que querem VER o que estão montando  

#### COMPETITIVE MODE
- Foco em dados  
- Lista compacta  
- Custo bem visível  
- Habilidades e regras da carta  
- Tipo, subtipo, raridade em texto  
- Curva de mana em primeiro plano  
- Estatísticas em destaque  
- Modo "dark" para reduzir fadiga ocular  
- **Ideal para:** grinders, pros, competitivos que CONHECEM as cartas pelo nome  

#### Seletor de Modo
- Botão no topo: "Collection Mode" | "Competitive Mode"  
- Preferência salva por usuário  
- Pode alternar a qualquer momento  

---

### 6.7.6.6 Personalização de Visualização da Curva

O player pode escolher:

- **Tipo de gráfico:** barras vertical, horizontal, linha, área, scatter  
- **Cor do gráfico:** automática, tema escuro, tema claro, customizado  
- **Incluir/excluir sideboard** na curva  
- **Incluir/excluir terrenos** na curva  
- **Mostrar/ocultar custo médio e mediano**  
- **Mostrar/ocultar distribuição por tipo**  
- **Mostrar/ocultar distribuição por cor**  

Todas essas opções são salvas por usuário.

---

### 6.7.6.7 Layout Modular (MVP vs Futuro)

#### MVP
- Layout fixo  
- Curva em local padrão  
- Estatísticas em painel lateral  
- Decklist em local fixo  

#### Futuro (Versão 2.0+)
- Blocos arrastáveis  
- Redimensionáveis  
- Mostrar/ocultar seções  
- Salvar múltiplas configurações de layout  
- Sincronizar entre dispositivos  

---

### 6.7.6.8 Sugestões Automáticas

Com base na curva e estatísticas, o sistema pode sugerir:

- "Sua curva está pesada demais; considere reduzir cartas de custo 5+"  
- "Custo médio do deck está acima da média do formato"  
- "Seu deck está com poucos terrenos para a curva atual"  
- "Você tem muita remoção e pouco board presence"  
- "Considere adicionar mais card draw"  

Essas sugestões são **informativas, não obrigatórias**.

---

### 6.7.6.9 Integração com Coleção

As estatísticas levam em conta:

- Cartas alocadas em deck  
- Cartas faltantes  
- Cartas em outro deck  
- Múltiplas cópias  
- Sideboard separado  

A curva recalcula em tempo real conforme o jogador:
- Adiciona cartas  
- Remove cartas  
- Transfere entre decks  
- Compra cartas faltantes  

---

### 6.7.6.10 Exportação de Estatísticas

O player pode exportar:
- Imagem da curva (PNG/SVG)  
- Relatório em PDF  
- Dados brutos (JSON/CSV)  

Útil para compartilhar análises em comunidades ou redes sociais.

---

### 6.7.6.11 Conclusão

As estatísticas transformam o deckbuilder em uma ferramenta profissional de análise. Combinadas com a integração perfeita com a coleção pessoal, elas permitem que qualquer jogador — do iniciante ao pro — construa decks com confiança e conhecimento.

O Versus TCG oferece o máximo de informação e personalização, deixando o controle total nas mãos do player.

## 7.1 Storefront da Loja (Página Pública)

(Baseado em `tenant_store_template.html`)

### 7.1.1 Propósito

A Storefront representa a página pública da loja, acessada por players e visitantes. Ela utiliza um **layout base neutro e modular**, permitindo personalização visual sem alterar a estrutura técnica. Todos os elementos são organizados em blocos independentes.

Elementos principais:

- Header
- Menu de navegação
- Banner / Hero
- Categorias
- Vitrine "Chegaram Agora"
- Rodapé

---

### 7.1.2 Header

**Elementos-chave do Header:**

- Telefone e e-mail
- Ícones de redes sociais (Instagram, Facebook, WhatsApp)
- Logo da loja
- Nome da loja
- Botão "Entrar" (login universal)
- Ícone do carrinho com contador

**Características:**

- Sempre fixo na estrutura técnica
- No Premium pode trocar layout dentro de limites seguros
- No Pro pode escolher entre modelos
- No Básico muda apenas logo e cores

---

### 7.1.3 Menu de Navegação

Itens padrões incluídos:

- Home
- Magic: The Gathering
- Pokémon
- Acessórios
- Promoções

**Regras:**

- Jogos oficiais não podem ser removidos
- Lojista pode ocultar jogos que não vende
- Subcategorias podem ser adicionadas livremente

---

### 7.1.4 Banner Principal (Hero)

Bloco visual de destaque com:

- Tag ("Novidade")
- Título
- Texto explicativo
- Botão de ação ("Ver Lançamentos")
- Imagem definida pelo lojista

**Personalização por Plano:**

- **Básico:** troca imagem e texto
- **Pro:** 3 modelos + carrossel simples
- **Premium:** vídeo, parallax, banners animados

---

### 7.1.5 Bloco de Categorias

Categorias padrão:

- Singles de Magic
- Pokémon TCG
- Acessórios

**Propriedades do bloco:**

- Imagens customizáveis
- Descrições editáveis
- Pode ser reorganizado (Pro e Premium)
- Pode ser expandido (Premium)

---

### 7.1.6 Vitrine "Chegaram Agora"

Exibe itens recentes ou populares.

**Cada card contém:**

- Imagem
- Condição
- Nome do jogo
- Nome da carta
- Preço
- Botão de adicionar ao carrinho

**Planos:**

- **Básico:** vitrine fixa
- **Pro:** 3 estilos (grid, lista, compacto)
- **Premium:** múltiplas vitrines, carrosséis, filtros avançados

---

### 7.1.7 Rodapé (Footer)

Inclui:

- Sobre a loja
- Links úteis
- Atendimento
- Formas de pagamento
- Assinatura "Tecnologia VS"

**Planos:**

- **Básico:** editar textos
- **Pro:** escolher layout
- **Premium:** blocos livres dentro da área segura

---

### 7.1.8 Sistema de Blocos (Base de Personalização)

A página é composta por blocos modulares:

- Cada bloco pode ter múltiplas variações visuais
- A ordem pode mudar (Pro e Premium)
- Blocos podem ser ativados/desativados
- Premium pode duplicar blocos e criar páginas adicionais

A estrutura técnica permanece intocada para garantir consistência, desempenho e integração futura.

---

## 7.2 Tela de Pedido do Lojista (Pedido Pago / Em Separação)

Esta tela exibe um pedido individual para o lojista separar, coletar e enviar.  
Ela deve apresentar **sempre visíveis** os dados críticos do cliente e do pagamento.

### Elementos permanentes (lado direito fixo da tela)
- Nome do cliente
- Endereço completo
- CEP
- Telefone (quando permitido)
- Valor total do pedido
- Forma de pagamento
- Status do pagamento
- Botão para imprimir etiqueta
- Código de rastreio (se houver)
- Botão para copiar o código

Esse painel lateral **não rola** com a página — permanece fixo.

---

### Elementos principais (lista de itens do pedido)

Cada item deve exibir:

- Checkbox para marcar a coleta
- Quantidade + nome da carta + set
- Qualidade e idioma
- Local físico na loja (ex.: A-12 / COFRE / CAIXA 05)
- Indicação visual se é carta com imagem personalizada (danificada, assinada, etc.)
- Ao passar o mouse → mostrar a **imagem exata** da carta cadastrada pelo lojista
- Status individual:
  - “OK”
  - “FALHA”
  - “VERIFICAR IMAGEM”
  - “ASSINADA” (quando aplicável)

### Ações do lojista
- “Concluir Separação”
- “Marcar como Enviado”
- “Imprimir”
- “Gerar Etiqueta” (quando integrações estiverem ativas)

---

### Comportamento esperado

- A lista deve aceitar muitos itens (mais de 100) sem prejudicar o painel lateral.
- Rolagem somente no corpo da lista.
- O painel lateral de dados do cliente permanece sempre visível.
- Ao marcar todas as cartas → o botão “Concluir Separação” habilita.
- Ao concluir → status altera para “Aguardando Envio”.
- Ao marcar como enviado → status muda para “Enviado”.

---

### Melhorias sugeridas (não obrigatórias)

- Filtros internos:
  - por localização física
  - por idioma
  - por qualidade
- Visual para destacar itens caros ou raros
- Botão “resolver inconsistência” quando estoque físico ≠ estoque do sistema
- Opção para abrir a página da carta direto da tabela

---
## 7.3 Dashboard do Lojista

O Dashboard do lojista é a página inicial da área administrativa.  
Ele apresenta um panorama completo da operação da loja, oferecendo métricas rápidas, atalhos para ações essenciais e indicação de pendências.  
É projetado para ser prático, direto e funcional — permitindo ao lojista tomar decisões imediatas.

A versão atual já contém praticamente tudo que é necessário.  
As sugestões abaixo focam em refinamento, clareza e pequenas melhorias que ampliam a eficiência sem alterar o conceito original.

---

## 7.3.1 Estrutura Geral

Elementos presentes:

- Foto/Avatar do lojista  
- Nome da loja  
- Nome do usuário logado  
- Cargo (ex.: Proprietário)

Menu lateral:

- Dashboard  
- Pedidos  
  - Todos os Pedidos  
  - Aguardando Envio  
  - Problemas / Disputas  
- Catálogo & Estoque  
- Buylist (Compras)  
- Clientes  
- Eventos  
- Financeiro  
- Configurações  

O menu lateral permanece fixo e oferece acesso direto às principais áreas de gestão.

---

## 7.3.2 Indicadores Rápidos (Cards Principais)

### Aguardando Envio
- Exibe o total de pedidos pendentes
- Indica origem: Marketplace vs Loja Própria

### Retirada no Balcão
- Quantos clientes estão aguardando retirada física

### Buylist (Aprovar)
- Quantidade de entradas pendentes
- Valor total aproximado em compras

### Pré-vendas
- Quantidade de itens em pré-venda
- Próximos lançamentos

Esses cards funcionam como “gatilhos de ação rápida”.

---

## 7.3.3 Métricas de Vendas

Gráfico de barras ou linha, comparando:

- Marketplace  
- Loja Própria  

Período padrão: últimos 7 dias.

Essa área serve para indicar desempenho recente, quedas ou picos.

---

## 7.3.4 Últimos Pedidos

Lista simplificada com:

- Número do pedido  
- Tempo desde a compra  
- Valor  
- Status (Pago, Processando, Enviado)

Ação recomendada:
- Botão “Ver Todos” levando à lista completa de pedidos

---

## 7.3.5 Indicadores de Estoque

### Evolução do Estoque
Gráfico global mostrando variação de itens registrados na loja.

### Estoque por Jogo
Distribuição do estoque entre:

- Magic  
- Pokémon  
- Yu‑Gi‑Oh  
- Battle Scenes  
- Outros  

---

## 7.3.6 Ação Rápida: Cadastrar Estoque

Blocos com botões grandes para seleção rápida do jogo:

- MTG  
- PKM  
- YGO  
- BS  
- Outros Games  

Atalho importante para entrada de produtos, agilizando a rotina do lojista.

---

## 7.3.7 Melhorias Sugeridas (Refinamentos, sem remover nada)

- Permitir que cada card abra um modal com mais detalhes (ex.: “Aguardando Envio → pedidos listados”)
- Botão “Ver Buylist Hoje” diretamente no card de buylist
- Badge de alerta caso exista disputa pendente
- Área “Próximos Eventos da Loja” (opcional)
- Pequena timeline com atividades recentes (estoque cadastrado, pedido enviado, etc.)
- Campo de busca global (cartas, clientes, pedidos)
- Card “Produtos com Baixo Estoque”
- Exportação rápida de relatórios (CSV)

Nenhuma dessas mudanças altera o projeto atual — apenas complementam.

---

## 7.3.8 Comportamento e Responsividade

- Layout dividido em colunas adaptáveis  
- Cards se reorganizam em dispositivos menores  
- Menu lateral se recolhe automaticamente no mobile  
- Gráficos mantêm proporção mínima legível  
- Filtros permanecem acessíveis em todas as resoluções  

---

## 7.3.9 Objetivo da Tela

O Dashboard funciona como centro de operações da loja:

- Mostra tudo que exige atenção imediata  
- Resume vendas  
- Resume estoque  
- Exibe pendências  
- Mostra pedidos urgentes  
- Oferece atalhos para as áreas mais usadas  

Seu foco é **rapidez**, **clareza** e **contexto operacional**.

---

## 7.4 Tela de Cadastro e Gestão de Estoque (Lojista)

Esta tela permite ao lojista cadastrar, editar e gerenciar variações de cartas no estoque da loja.  
Ela combina um modal rápido de entrada com uma lista completa de variações já cadastradas, permitindo ajustes ágeis de preço, quantidade e imagens reais.

---

## 7.4.1 Estrutura Geral

A tela é dividida em duas partes principais:

- Modal de cadastro/edição (pop-up)  
- Lista de variações cadastradas (tabela abaixo)

O modal abre ao clicar em "Adicionar Novo" ou ao editar uma variação existente.

---

## 7.4.2 Modal de Cadastro/Edição

O modal exibe os dados essenciais da carta e permite cadastrar ou editar uma variação específica.

Elementos obrigatórios:

- Nome da carta  
- Set / Edição  
- Imagem da carta (padrão do servidor, no idioma selecionado)  
- Qualidade (NM, SP, MP, HP)  
- Idioma (PT, EN, JP, etc.)  
- Quantidade  
- Preço unitário  
- Extras (foil, etched, assinada, alterada)  

Elementos opcionais:

- Custo de aquisição (privado)  
- Observação interna  
- Prioridade de venda (baixa, média, alta, liquidar)  

---

## 7.4.3 Radar de Preços (Top 5 do Marketplace)

O modal exibe uma tabela compacta com os 5 primeiros preços do marketplace para a mesma carta, mesma qualidade ou classe de qualidade.

Formato da tabela:

- Posição (1º, 2º, 3º, 4º, 5º)  
- Nome da loja  
- Preço  
- Quantidade disponível  
- Idioma  
- Condição  

Botões rápidos:

- "Posicionar no 1º Lugar" (venda rápida)  
- "Posicionar no 5º Lugar" (estratégia conservadora)  
- "Posicionar no Preço Médio"  

Esses botões preenchem automaticamente o campo de preço com o valor correspondente.

---

## 7.4.4 Sistema de Imagens Reais (Upload e Gestão)

O lojista pode adicionar imagens reais para produtos específicos, substituindo temporariamente a imagem padrão do servidor.

Regras:

- A imagem padrão vem do banco de dados do servidor (ex.: Scryfall, Pokémon TCG API).  
- O lojista pode fazer upload de até 3 imagens reais por produto.  
- A primeira imagem enviada torna-se a imagem principal.  
- As demais imagens servem como suporte (detalhes, verso, dano visível).  
- Se o lojista quiser trocar a ordem, basta reenviar na sequência desejada.  

Comportamento no sistema:

- Ao adicionar imagem real → ela substitui a imagem padrão na lista e no hover.  
- Ao apagar o produto com estoque zero → a imagem real é removida do banco.  
- A imagem padrão do servidor nunca é apagada.  

Casos de uso:

- Carta assinada → foto da assinatura  
- Carta danificada → foto do dano  
- Carta alterada → foto da alteração  

---

## 7.4.5 Lista de Variações Cadastradas

Abaixo do modal (ou em aba separada dentro dele), o lojista visualiza todas as variações já cadastradas daquela carta.

Colunas da tabela:

- Estoque (quantidade)  
- Desconto (se houver)  
- Início / Término (datas de promoção, quando aplicável)  
- Preço  
- Idioma (bandeira)  
- Qualidade (NM, SP, etc.)  
- Extras (foil, promo, assinada)  
- Edição / Set  
- Ações:
  - editar  
  - apagar  
  - adicionar foto real  
  - duplicar  
  - colocar em promoção  
  - mover entre sets (raro)  

Funcionalidades adicionais:

- Filtro por idioma  
- Filtro por qualidade  
- Filtro por edição  
- Opção "Ver somente com estoque"  
- Agrupamento automático por set  

---

## 7.4.6 Validações e Alertas

Ao salvar uma variação, o sistema valida:

- Preço muito acima da média → alerta "Preço acima do mercado"  
- Preço muito abaixo do custo → alerta "Preço abaixo do sugerido"  
- Estoque negativo → bloqueio  
- Imagem real obrigatória para cartas assinadas ou alteradas → alerta  

---

## 7.4.7 Atalhos de Teclado

- [F2] → Salvar e Novo  
- [Enter] → Salvar e Adicionar  
- [Esc] → Fechar modal  

---

## 7.4.8 Comportamento ao Apagar Produto com Estoque Zero

Quando o lojista apaga um produto com estoque zero:

- A variação é removida da lista.  
- A imagem real associada é apagada do banco de dados.  
- A imagem padrão do servidor permanece intacta.  
- Outros produtos da mesma carta não são afetados.  

---

## 7.4.9 Melhorias Sugeridas (Não Obrigatórias)

- Campo "Localização Física" (ex.: A-12, COFRE, CX-05)  
- Botão "Resolver Inconsistência" quando estoque físico ≠ estoque do sistema  
- Opção de importar CSV com múltiplas variações de uma vez  
- Histórico de alterações de preço  
- Gráfico de evolução de preço da carta no marketplace  
- Notificação quando o preço do lojista fica muito acima ou abaixo da média  

---

## 7.4.10 Objetivo da Tela

Permitir ao lojista:

- Cadastrar produtos rapidamente  
- Ajustar preços com base no mercado  
- Gerenciar variações de forma visual e ágil  
- Adicionar imagens reais para produtos específicos  
- Manter o estoque organizado e atualizado  

---

## 7.5 Telas Faltantes do Storefront (A Serem Implementadas)

As seguintes telas ainda precisam ser adicionadas ao layout principal do Storefront. Elas são essenciais para navegação, descoberta de produtos, organização por sets e visualização detalhada de cartas.

---

## 7.5.1 Tela de Listagem de Sets por Jogo

Tela que exibe todos os sets de um jogo específico (ex.: Magic, Pokémon) em ordem de lançamento ou ordem alfabética.

Requisitos:

- Seleção do jogo (Magic, Pokémon para o MVP)
- Exibição agrupada por ano
- Para cada set:
  - sigla do set
  - nome do set
  - indicação se é coleção ou deck
- Ordenações:
  - Data de lançamento
  - Nome A–Z
- Filtro por idioma
- Ao clicar no set → ir para a lista de cards do set

---

## 7.5.2 Tela de Listagem de Cartas (“Todos os Cards”)

Lista todas as cartas de um jogo ou de um set, inclusive cartas com estoque zero.

Requisitos:

- Miniatura da carta
- Nome da carta
- Estoque (ex.: “0 unid.”)
- Variações disponíveis
- Filtros:
  - Nome A–Z
  - Número do set
  - Raridade
  - Tipo (ex.: criatura, mágica, trainer…)
  - “Somente com estoque”
- Paginação
- Ao clicar na carta → abrir a tela de detalhes

---

## 7.5.3 Tela de Detalhes da Carta (Página de Produto)

Tela para visualizar variações e dados completos da carta.

Parte esquerda:

- Imagem grande da carta
- Navegação entre imagens
- Placeholder caso não exista imagem

Parte direita (tabela de variações):

- Set / edição
- Idioma (bandeira)
- Qualidade (NM, SP etc.)
- Extras (foil, etched)
- Estoque
- Preço
- Seletor de quantidade
- Botão “Comprar”

Parte inferior (dados enriquecidos):

- Tipo da carta
- Texto da carta
- Texto de sabor
- Atributos particulares do jogo
- Legalidade em formatos
- Erratas e rulings
- Cartas associadas

---

## 7.5.4 Fluxo de Navegação

- Sets → Lista de Cartas
- Lista de Cartas → Detalhes da Carta
- Detalhes da Carta → Carrinho
- Breadcrumb automático

---

## 7.5.5 Telas Relacionadas (A Serem Documentadas Depois)

- Tela de Cadastrar Produto
- Tela de Carrinho
- Tela de Checkout
- Tela de Confirmação de Compra
- Lista de Pedidos do Usuário

---
## 7.5.6 Tela de Listagem de Sets por Jogo

Tela que exibe todos os sets de um jogo específico, permitindo que o usuário navegue pelas coleções de Magic, Pokémon (MVP) e outros jogos no futuro.

Elementos obrigatórios:

- Cabeçalho do jogo selecionado
- Agrupamento de sets por ano
- Para cada set:
  - ícone / sigla do set
  - nome do set
  - indicação se é deck, coleção especial ou edição normal
- Controles de ordenação:
  - Ordem de lançamento
  - Ordem alfabética
- Filtro opcional de idioma
- Ao clicar no set → abrir a tela 7.x.2 (lista de cartas do set)

---

## 7.5.7 Tela de Listagem de Cartas (“Todos os Cards”)

Lista todas as cartas do jogo ou do set selecionado, incluindo cartas com estoque zero (importante para o lojista saber o que tem e o que falta).

Elementos obrigatórios:

- Lista de cartas com miniatura
- Nome da carta
- Estoque (exibir “0 unid.” quando faltar)
- Quantidade total de resultados
- Paginação
- Filtros:
  - Nome (A–Z / Z–A)
  - Número do set
  - Raridade
  - Tipo (criatura, feitiço, trainer, etc.)
  - Filtro “Somente com estoque”
- Ao clicar na carta → abrir a tela 7.x.3 (detalhes da carta)

---

## 7.5.8 Tela de Detalhes da Carta (Página de Produto)

Tela dedicada a exibir a carta individual e suas variações disponíveis para compra.

Layout sugerido:

### Parte esquerda
- Imagem grande da carta
- Botão de ampliar / zoom
- Placeholder caso não exista imagem

### Parte direita (tabela de variações)
Para cada variação:
- Edição (ícone do set)
- Idioma (bandeira)
- Qualidade (NM, SP, MP, HP…)
- Extras (foil, etched, stamped)
- Estoque
- Preço
- Seletor de quantidade
- Botão **Comprar**

### Parte inferior (informações ricas da carta)
- Tipo da carta
- Texto de regras
- Flavor text
- Subtipos / atributos específicos do jogo
- Legalidade por formato
- Erratas / atualizações
- Rulings relevantes
- Cartas associadas

---

## 7.5.9 Fluxo de Navegação Entre Telas

- Sets → Lista de Cartas  
- Lista de Cartas → Detalhes da Carta  
- Detalhes da Carta → Carrinho  
- Breadcrumb automático em todas as telas  
- Botão para voltar ao set ou à listagem filtrada  

---

## 7.5.10 Telas Relacionadas Dependentes

A implementação das telas acima libera as seguintes telas adicionais:

- Tela de Cadastrar Produto do Lojista
- Tela de Carrinho (da loja)
- Tela de Checkout
- Tela de Confirmação do Pedido
- Tela de Histórico de Pedidos do Usuário (universal)

## 7.6 Funcionalidades e Telas Não Documentadas (Complemento do Ecossistema da Loja)

Este capítulo reúne todas as telas, ferramentas, configurações e recursos que ainda não foram documentados formalmente no Capítulo 7.  
Inclui funcionalidades obrigatórias, recomendadas e opcionais que completam a experiência da loja dentro do ecossistema Versus TCG.

---

## 7.6.1 Configurações Gerais da Loja

- Nome da loja  
- Logo principal da loja  
- Slogan (opcional)  
- Favicon personalizado (Pro/Premium)  
- Descrição da loja  
- Horário de funcionamento  
- Informações de retirada presencial  
- Links sociais (Instagram, Facebook, WhatsApp)  
- E‑mails da loja (atendimento, vendas, administração)  
- Configuração de domínio próprio (url_slug)  
- Ativar/Desativar loja (Modo Férias)  
- Políticas personalizadas:
  - Trocas e devoluções  
  - Envio  
  - Termos da loja  
  - Privacidade  

---

## 7.6.2 Configurações do Catálogo e Estoque

Regras e funções gerais:

- Permitir ou bloquear estoque negativo  
- Bloquear vendas acima do estoque disponível  
- Reserva automática ao adicionar ao carrinho  
- Tempo da reserva (ex.: 15 minutos)  
- Regras de qualidade editáveis (NM, SP, MP, HP, etc.)  
- Aviso para qualidades mais baixas  
- Sinalização automática de produtos raros  
- Detecção de estoque inconsistente  
- Sugerir reposição automática para produtos de alta rotatividade  

---

## 7.6.3 Sistema de Imagens Reais por Produto

Regras principais:

- Cada variação pode ter **múltiplas imagens reais**  
- A **primeira imagem enviada sempre será a imagem principal**  
- Imagens adicionais aparecem em **carrossel de miniaturas**  
- Ao clicar nas miniaturas, a imagem principal troca  
- Se a variação tem imagem real, ela substitui:
  - a imagem da lista  
  - a imagem do hover  
  - a imagem da página de produto  
- Ao apagar a variação com estoque zero:
  - apagar somente as imagens reais daquela variação  
  - **não** apagar imagens oficiais do banco  
  - **não** manter histórico de imagens antigas  
- Não armazenar imagens de cartas já vendidas  

---

## 7.6.4 Equipe da Loja e Permissões Internas

- Cadastro de funcionários  
- Permissões por função:
  - alterar catálogo  
  - ver financeiro  
  - ajustar preços  
  - editar imagens  
  - cancelar pedidos  
  - editar pedidos  
  - criar promoções  
  - gerenciar estoque  
- Trilhas de ação (auditoria interna):
  - quem alterou preço  
  - quem apagou variação  
  - quem editou estoque  
  - quem fez upload de imagem real  

---

## 7.6.5 Gestão de Clientes (CRM da Loja)

- Histórico de compras por cliente  
- Anotações internas do lojista sobre clientes  
- Marcação: VIP, prioridade, risco, etc.  
- Histórico de problemas ou devoluções  
- Possibilidade de **banir cliente específico** (fundamental)  
- Regras de restrição:
  - impedir compras  
  - impedir contato  
  - impedir visualização da loja  
- Lista de clientes banidos  

---

## 7.6.6 Área Financeira da Loja

- Resumo financeiro por período  
- Vendas por jogo  
- Relatório de taxas  
- Conciliação financeira  
- Transferências realizadas  
- Estornos e cancelamentos  
- Exportação CSV ou Excel  
- Relatórios para contabilidade  

---

## 7.6.7 Promoções, Cupons e Descontos

Tipos de promoções:

- Desconto percentual ou fixo  
- Promoção por categoria  
- Promoção por jogo  
- Promoção por set específico  
- Promoção por carta específica  
- Combo de cartas (kits)  
- Desconto progressivo por quantidade  
- Cupons de desconto (valor ou percentual)  
- Datas de início e término  
- Promoções globais (Black Friday)  
- Preços temporários especiais  

---

## 7.6.8 Ferramentas Avançadas de Precificação

- Radar com os **5 menores preços do marketplace** para a mesma carta / variação  
- Preço mínimo, médio e máximo  
- Botões rápidos:
  - Posicionar no 1º lugar  
  - Posicionar no 5º lugar  
  - Posicionar na média  
- Histórico de preço da carta (quando disponível)  
- Comparação entre idiomas  
- Comparação entre qualidades  
- Regras automáticas:
  - preço reduzido quando estoque alto  
  - preço aumentado quando estoque baixo  
  - aviso para preços abaixo do custo  

---

## 7.6.9 Página da Loja e Conteúdo

- Página “Sobre a Loja”  
- Página de eventos (calendário)  
- Blog da loja (conteúdos e anúncios)  
- Publicação de fotos e galerias  
- Editor de texto (markdown básico)  
- Gerenciamento de banners internos  

---

## 7.6.10 Logística, Envio e Coleta

- Configurar métodos de envio (PAC, SEDEX, Jadlog)  
- Configurar “Retirar na Loja”  
- Definir tamanhos e pesos padrões  
- Cálculo automático de frete  
- Geração de etiqueta (futuro)  
- Atualização automática de rastreio (futuro)  
- Histórico de rastreamento por pedido  

---

## 7.6.11 Histórico de Ações e Auditoria Técnica

- Registro de todas as ações relevantes:
  - alteração de preço  
  - alteração de estoque  
  - alteração de qualidade  
  - upload de imagens  
  - criação e exclusão de produtos  
- Log técnico:
  - IP  
  - horário  
  - usuário interno  
  - ação realizada  

---

## 7.6.12 Notificações e Alertas

Para o lojista:

- Pedido criado  
- Pedido pago  
- Pedido com problema  
- Produto vendido → estoque reduzido  
- Produto chegando a zero  
- Buylist recebida  
- Cliente problemático tentando comprar (quando banido)  

Para o cliente:

- Confirmação de pedido  
- Pedido enviado  
- Atualização de rastreio  
- Carta favorita voltou ao estoque  
- Promoções personalizadas  

---

## 7.6.13 Ferramentas de Pré-venda

- Cadastro de pré-vendas por set  
- Controle de limite por cliente  
- Regras de envio somente após data oficial  
- Estoque automático após release  

---

## 7.6.14 Produtos Selados (Futuro)

- Booster  
- Booster box  
- Decks  
- Kits  
- Produtos colecionáveis  
- Estado da embalagem: lacrado, amassado, danificado  

---

## 7.6.15 Integrações Externas (Futuras)

- Correios API  
- MelhorEnvio  
- WhatsApp API  
- Gateways alternativos  
- Importar estoque via CSV  
- Exportar catálogo completo  

---

## 7.6.16 Funcionalidades Futuras (Opcional / Expansão)

- Modo Venda Rápida (PDV / loja física)  
- Impressão de etiquetas internas  
- Câmera do celular → upload automático  
- Reconhecimento de carta via IA  
- Sugestões inteligentes de preço  
- Aviso automático de valorização  
- Agrupamento automático de playsets  
- Metas e desempenho da loja


## 7.7 Configurações da Loja

Esta seção compreende todas as configurações gerais e avançadas que afetam identidade, operação, regras e comportamento da loja no ecossistema Versus TCG.

---

### 7.7.1 Identidade e Branding

- Nome da loja  
- Logo principal  
- Cores do tema (quando o plano permitir)  
- Banner principal  
- Favicon personalizado  
- Descrição da loja  
- Modo escuro (Premium)  

---

### 7.7.2 Políticas da Loja

- Política de envio  
- Política de troca e devolução  
- Política para cartas danificadas  
- Política sobre cartas assinadas / alteradas  
- Política de privacidade  
- Termos de uso da loja  
- Campo “Observação pública da loja”  

---

### 7.7.3 Modo Férias (com vendas ativas)

A loja pode entrar em um modo especial que mantém vendas funcionando, mas desloca prazos de envio:

Regras:

- A loja permanece **visível**.  
- A loja continua **aceitando pedidos normalmente**.  
- O checkout é permitido sem restrições.  
- Antes da compra, o cliente visualiza aviso automático:  
  “Esta loja está em período de férias. Envio programado para após a data X.”  
- O lojista define a data de retorno.  
- O prazo real de envio começa **D+2** após o retorno programado.  
- Garante ao lojista tempo realista para separar pedidos acumulados.  
- O prazo exibido ao cliente já considera esse deslocamento.  

---

### 7.7.4 Contatos e Redes Sociais

- WhatsApp  
- E‑mail de atendimento  
- Instagram  
- Facebook  
- Twitch / YouTube (opcional)  
- Link externo da loja física  

---

### 7.7.5 Domínio e URL

- Configuração de domínio próprio  
- Configuração de url_slug  
- Redirecionamentos internos para campanhas futuras  

---

### 7.7.6 Horários e Operação

- Horário de funcionamento  
- Horário de retirada presencial  
- Instruções especiais de retirada (“Balcão 3”, “Campainha ao lado”)  

---

## 7.8 Gestão de Clientes

Ferramentas para administrar compradores, seu comportamento, preferências e histórico.

---

### 7.8.1 Perfil do Cliente

- Nome completo  
- Histórico de compras  
- Histórico de problemas  
- Endereços salvos  
- Preferência de idioma  
- Status interno (VIP, confiável, problemático etc.)  
- Notas internas do lojista  

---

### 7.8.2 Banição de Cliente (Essencial)

Funcionalidade obrigatória:

- Botão “Banir Cliente”  
- Registrar motivo da punição  
- Cliente banido não pode:
  - finalizar compras  
  - adicionar itens ao carrinho  
  - enviar buylist  
- A loja ainda é visível, mas não interativa para o cliente banido  
- Tentativa de compra → aciona aviso interno ao lojista  
- Reversível: “Desbanir Cliente”  

---

### 7.8.3 Wantlist (Futuro)

- Lista de cartas desejadas pelo cliente  
- Notificar cliente quando a loja cadastrar carta desejada  
- Lojista pode ofertar manualmente  

---

### 7.8.4 Histórico de Suporte (Futuro)

- Registro de mensagens internas  
- Reclamações e devoluções  
- Soluções aplicadas  

---

## 7.9 Financeiro da Loja

Ferramentas que permitem ao lojista acompanhar vendas, taxas, repasses e extratos.

---

### 7.9.1 Resumo Financeiro

- Total vendido no marketplace  
- Total vendido na loja própria  
- Gráfico de vendas por período  
- Comparação entre jogos  
- Resumo de taxas pagas e taxas economizadas (PIX próprio)  

---

### 7.9.2 Extratos e Relatórios

- Extrato mensal detalhado  
- Extrato por período customizado  
- Exportar CSV  
- Exportar PDF  

---

### 7.9.3 Previsão de Recebíveis

- Lista de pedidos com valores a receber  
- Datas estimadas de repasse  
- Agrupamento por período (semanal, mensal)  

---

### 7.9.4 Estornos e Cancelamentos

- Registro completo de estornos  
- Motivos  
- Valores  
- Data  
- Usuário que executou a ação  

---

## 7.10 Logística, Envio e Coleta

Ferramentas para organização interna de envio, cálculo de frete, rastreamento e retirada.

---

### 7.10.1 Métodos de Envio

- Correios PAC  
- Correios SEDEX  
- Jadlog  
- “Retirar na Loja”  
- Previsão de entrega ajustada ao Modo Férias  

---

### 7.10.2 Regras de Embalagem e Peso

- Peso padrão de carta (ex.: 4g)  
- Tabela de pesos para múltiplas unidades  
- Tipos de embalagem pré-cadastrados  
- Dimensões mínimas e máximas  

---

### 7.10.3 Cálculo de Frete

- Integração com API de cálculo (Correios/MelhorEnvio – futuro)  
- Frete em tempo real no checkout  
- Frete por item especial (produtos selados – futuro)  

---

### 7.10.4 Rastreamento

- Campo de código de rastreio  
- Notificações automáticas quando houver movimentação  
- Histórico de mudanças de status  

---

### 7.10.5 Separação de Pedido (ligação com tela 7.2)

- Itens com imagem real no hover  
- Localização física (A-12, Cofre, Caixa 05)  
- Verificação dupla (dois funcionários – futuro)  

---

## 7.11 Funcionalidades Futuras e Recursos Avançados

Recursos planejados para versões posteriores ao MVP, mantidos aqui para referência e organização.

---

### 7.11.1 Automação de Preços

- Regras automáticas:
  - preço mínimo  
  - preço máximo  
  - preço baseado na média  
- Ajuste inteligente:
  - estoque alto → reduções sugeridas  
  - estoque baixo → aumento sugerido  
  - staples com faixa ideal de valor  
- Radar de preços:
  - 1º ao 5º menor preço do marketplace  
  - botões de ajuste rápido  

---

### 7.11.2 Reconhecimento de Carta (IA)

- Identificação de carta por foto  
- Sugestão de variações  
- Sugestão de preço  
- Detecção de dano  

---

### 7.11.3 Compra Estratégica Automática

- Sistema identifica cartas em wantlists  
- Sugere ao lojista comprar do marketplace para completar estoque  
- Cálculo de margem e viabilidade  

---

### 7.11.4 Ferramentas de Pré-Venda

- Cadastro de pré-venda por set  
- Controle por cliente  
- Limite de compra  
- Disponível apenas após DOR (Data Oficial de Release)  

---

### 7.11.5 Produtos Selados

- Booster  
- Booster box  
- Decks pré-construídos  
- Kits colecionáveis  
- Estado da embalagem (lacrado, amassado, danificado)  

---

### 7.11.6 Blog e Eventos da Loja

- Blog interno da loja  
- Calendário de torneios  
- Postagem de decklists  
- Resultado de campeonatos  

---

### 7.11.7 Logística Avançada

- Impressão de etiqueta interna  
- Suporte a múltiplos endereços de envio (futuro)  

---

### 7.11.8 Histórico de Preço e Estatísticas

- Histórico por idioma  
- Histórico por qualidade  
- Histórico por variação foil / não foil  
- Gráficos semanais e mensais  

---

### 7.11.9 Confiabilidade e Auditoria

- Registro de todas as ações internas  
- IP  
- horário  
- dispositivo  
- funcionário responsável

## 8.0 Regras de Negócio (Planos, Taxas e Pagamentos)

Este capítulo documenta todas as regras comerciais do Versus TCG.
Inclui planos de assinatura, valores, taxas por pedido, regras de isenção, funcionamento de pedidos multi-loja e detalhes sobre pagamentos.
Nada aqui afeta a estrutura técnica — são apenas regras de operação do serviço.

---

## 8.1 Planos de Assinatura (Mensal)

- **Básico:** R$ 79,90  
- **Intermediário (Pro):** R$ 119,90  
- **Premium:** R$ 169,90  

Os preços foram definidos para serem acessíveis e permitir crescimento gradual das lojas conforme suas necessidades.

---

## 8.2 Planos Anuais

Planos anuais oferecem o equivalente a **1 mês grátis**:

- **Básico Anual:** R$ 799  
- **Intermediário Anual:** R$ 1199  
- **Premium Anual:** R$ 1699  

Parcelamento disponível.

---

## 8.3 Níveis de Personalização Visual

### 8.3.1 Básico
- troca de logo  
- troca de cores  
- banner editável  
- textos das seções fixas  
- estrutura não pode ser reorganizada  

### 8.3.2 Pro
- tudo do Básico  
- mover blocos dentro de limites  
- 3 modelos de header  
- 3 modelos de vitrine  
- fontes dentro de biblioteca controlada  
- subcategorias personalizadas  

### 8.3.3 Premium
- tudo do Pro  
- duplicar blocos  
- criar páginas adicionais  
- múltiplas vitrines  
- blocos avançados (vídeo, parallax, carrosséis)  
- layout livre dentro das zonas seguras  
- backgrounds customizados  

---

## 8.4 Taxas do Marketplace

A taxa só existe quando o lojista usa **o pagamento do marketplace**.

### 8.4.1 Padrão
- **5%** por pedido processado no marketplace

### 8.4.2 Reduzidas por Plano
- **4,5%** → Intermediário (Pro)  
- **4,0%** → Premium  

---

## 8.5 Quando a Taxa é ZERO

As taxas do marketplace são anuladas quando o lojista usa:

- PIX próprio  
- Link de pagamento próprio  
- QR Code próprio  

Nenhuma taxa é aplicada nesses casos.

---

## 8.6 Pedidos Conjugados (Multi-Loja)

Quando o cliente compra de várias lojas ao mesmo tempo:

- o checkout é único para o cliente  
- internamente são criados **vários pedidos**, um por loja  
- **cada loja** recebe apenas o seu pedido  
- **cada loja** tem sua taxa individual aplicada (se houver)  

### 8.6.1 Regras de Transparência
- o cliente nunca vê taxas  
- o lojista vê a taxa do seu pedido  
- frete é separado por loja  
- valores são repassados automaticamente  

---

## 8.7 Antecipação de Pagamentos (Opcional)

- antecipação padrão: +1%  
- antecipação expressa: +2%  

Somente para pagamentos processados via marketplace.

---

## 8.8 Regras Gerais de Pagamento

- repasse automático ao lojista  
- conciliação automática  
- chargebacks tratados pelo gateway  
- pagamentos externos não geram taxa  

---

## 8.9 Diretrizes do Modelo de Negócio

- crescimento progressivo e sustentável  
- atraente para lojas pequenas  
- vantajoso para lojas grandes  
- marketplace nunca concorre com as lojas  
- foco em autonomia e identidade visual do lojista  

---

## 8.10 Transparência para o Lojista

- cada pedido mostra a taxa (quando houver)  
- o marketplace não exibe taxas ao cliente  
- relatórios mensais por loja  

---

## 8.11 Incentivos Suaves

- sorteios  
- créditos de compra  
- promoções não agressivas  
- benefícios que não reduzem margem do lojista  

## 9.0 Finalizando a Análise do Sistema Versus TCG

Este capítulo encerra a documentação técnica do código-fonte real do projeto Versus TCG, analisando exclusivamente os elementos que existem no sistema atual, escritos em Laravel 12 e Filament Admin. Diferente dos capítulos 7.x e 8.x, que abordaram telas planejadas e fluxos conceituais, aqui documentamos apenas o que está implementado na aplicação hoje.

O objetivo deste capítulo é:

- Mapear e documentar todos os módulos existentes no backend do sistema
- Registrar a estrutura e comportamento dos Resources do Filament
- Analisar os Controllers reais responsáveis pelas telas atuais
- Documentar as Views existentes em /resources/views
- Registrar rotas, assets e providers ativos
- Identificar trechos legados ainda necessários para operar
- Preparar terreno para o Capítulo 3.0 (refatoração do domínio de cartas e funcionalidades)

A documentação seguirá a seguinte ordem:

### 9.1 Resources (Filament)
Cada pasta será documentada individualmente:
- 9.1.1 AdminUsers  
- 9.1.2 CardFunctionalities  
- 9.1.3 Cards  
- 9.1.4 CatalogConcepts  
- 9.1.5 CatalogPrints  
- 9.1.6 Games  
- 9.1.7 PlayerUsers  
- 9.1.8 Sets  
- 9.1.9 StoreAdminUsers  
- 9.1.10 Stores  
- 9.1.11 StoreUsers  
- 9.1.12 Users  

#### 9.1.1 AdminUsers

O Resource `AdminUsers` gerencia os usuários administrativos internos do sistema (staff do marketplace Versus TCG). Esses usuários têm acesso ao painel Filament e podem executar tarefas administrativas, mas não são o SuperUser principal.

##### Estrutura de Arquivos

- AdminUsers/
  - Pages/
    - CreateAdminUser.php
    - EditAdminUser.php
    - ListAdminUsers.php
  - Schemas/
    - AdminUserForm.php (não utilizado)
  - Tables/
    - AdminUsersTable.php (não utilizado)
  - AdminUserResource.php

##### Model Associado

`App\Models\AdminUser`

##### Campos do Formulário

- **name** (obrigatório, máximo 100 caracteres)
- **email** (obrigatório, único, validado como email)
- **password** (obrigatório apenas na criação, oculto na edição)
- **is_active** (toggle, padrão true)

##### Colunas da Tabela

- Nome (pesquisável, ordenável)
- Email (pesquisável)
- Ativo (ícone boolean, ordenável)

##### Ações Disponíveis

- Editar registro
- Deletar registro (individual e em massa)

##### Navegação no Painel

- **Ícone:** heroicon-o-briefcase
- **Label:** "Staff do Sistema"
- **Grupo:** "Gestão de Clientes e Lojas"

##### Regras de Permissão

- Apenas o **SuperUser** (model `User`) pode criar, editar ou deletar AdminUsers
- O SuperUser logado **não aparece** na listagem de AdminUsers (filtrado via `getEloquentQuery`)
- AdminUsers **não podem se deletar**

##### Observações Técnicas

1. **Import incorreto:** O arquivo `AdminUserResource.php` contém um import errado que referencia `SAdminUsers\Pages\EditUser` em vez de `AdminUsers\Pages\EditAdminUser`.

2. **Formulário inline:** O formulário está definido diretamente no Resource, não utiliza o arquivo `Schemas/AdminUserForm.php`.

3. **Tabela inline:** A tabela está definida diretamente no Resource, não utiliza o arquivo `Tables/AdminUsersTable.php`.

4. **Lógica de exclusão do SuperUser:** O método `getEloquentQuery()` assume que o ID do SuperUser na tabela `users` é o mesmo que o ID na tabela `admin_users`, o que pode causar inconsistências se as tabelas tiverem IDs diferentes.

##### Recomendações

- Corrigir o import errado
- Mover a lógica do formulário para `Schemas/AdminUserForm.php`
- Mover a lógica da tabela para `Tables/AdminUsersTable.php`
- Revisar a lógica de exclusão do SuperUser na query
- Adicionar validação para impedir que AdminUsers editem suas próprias permissões

#### 9.1.2 CardFunctionalities

O Resource `CardFunctionalities` gerencia as funcionalidades das cartas de forma agnóstica, permitindo a criação, edição, visualização e listagem de diferentes funcionalidades que podem ser aplicadas a cartas de 8 TCGs diferentes: Magic: The Gathering, Pokémon TCG, Battle Scenes, Yu-Gi-Oh!, One Piece Card Game, Lorcana TCG, Flesh and Blood e Star Wars: Unlimited.

Este é um dos Resources mais complexos do sistema, pois implementa lógica dinâmica baseada no TCG selecionado, além de gerenciar relacionamentos com Cards (impressões) e StockItems (estoque de lojas).

##### Estrutura de Arquivos

- CardFunctionalities/
  - Pages/
    - CreateCardFunctionality.php
    - EditCardFunctionality.php
    - ListCardFunctionalities.php
    - ViewCardFunctionality.php
  - RelationManagers/
    - CardsRelationManager.php
    - StockItemsRelationManager.php
  - Schemas/
    - CardFunctionalityForm.php (não utilizado)
  - Tables/
    - CardFunctionalitiesTable.php (não utilizado)
  - CardFunctionalityResource.php

##### Model Associado

`App\Models\CardFunctionality`

##### Campos do Formulário (Dinâmicos por TCG)

- **tcg_name** (obrigatório, select de Games)

Seções dinâmicas que aparecem conforme o TCG selecionado:

- **Magic: The Gathering:** mtg_name (Oracle)
- **Battle Scenes:** bs_name, bs_alter_ego
- **Pokémon TCG:** pk_name
- **Yu-Gi-Oh!:** ygo_name
- **One Piece Card Game:** op_name
- **Lorcana TCG:** lor_name, lor_title
- **Flesh and Blood:** fab_name
- **Star Wars: Unlimited:** swu_name, swu_title

##### Colunas da Tabela

- Nome (usa accessor agnóstico)
- Jogo (badge)
- Tipo (usa accessor)
- Custo (com conversão de símbolos de mana para Magic)
- Coluna oculta `searchable_names` (para busca real)

##### Ações Disponíveis

- Visualizar registro (abre página customizada)
- Editar registro
- Deletar registro (individual e em massa)

##### Navegação no Painel

- **Ícone:** heroicon-o-rectangle-stack
- **Label:** "Funcionalidades de Cartas"
- **Grupo:** "Gestão de Cartas"

##### Relacionamentos

- **CardsRelationManager** — gerencia as impressões (prints) associadas a esta funcionalidade
- **StockItemsRelationManager** — gerencia o estoque de cada loja para as impressões desta funcionalidade

##### Funcionalidades Avançadas

###### Busca Global Agnóstica

O Resource implementa busca global que:
- Busca no `generic_name` (nome em inglês)
- OU busca nas impressões em Português (`cards.language_code = 'pt'` + `cards.name`)

Resultado da busca mostra:
- Nome da funcionalidade
- Jogo associado
- Tipo de carta
- Custo (com símbolos convertidos para Magic)

###### Página de Visualização Customizada (ViewCardFunctionality)

A página de visualização é uma página customizada que oferece:

- **Carrossel de Impressões:** Exibe todas as impressões (prints) da funcionalidade com paginação (20 por página)
- **Seletor de Idioma:** Permite trocar o idioma da impressão exibida, com fallback automático para inglês se o idioma selecionado não existir
- **Priorização de Imagem:**
  1. Caminho local do Battle Scenes (`local_image_path`)
  2. Caminho local do Magic (`local_image_path_large`)
  3. URLs remotas (fallback)
  4. Placeholder se nenhuma existir
- **Exibição de Dados Específicos do TCG:**
  - Nome, tipo, custo, poder/resistência, lealdade (Planeswalkers)
  - Texto de regras com conversão de símbolos de mana
  - Texto de ambientação
  - Artista
  - Legalidades (em view customizada)

###### RelationManager: CardsRelationManager

Gerencia as impressões (prints) associadas a uma funcionalidade de carta.

**Formulário dinâmico:**
- Seletor de `set_id` (coleção)
- Seções dinâmicas para cada TCG, mostrando apenas os campos relevantes:
  - Magic: mtg_printed_name, mtg_language_code, mtg_rarity, mtg_collection_number, mtg_artist
  - Battle Scenes: bs_language_code, bs_rarity, bs_collection_number, bs_artist

**Mutação de dados:**
- `mutateFormDataBeforeCreate()` injeta automaticamente `tcg_name` do CardFunctionality pai no Card filho

**Tabela:**
- Imagem (usa accessor `image_url`)
- Coleção (set.name)
- Raridade (badge com cores: common=gray, uncommon=info, rare=warning, mythic=danger)
- Número de coleção
- Idioma (badge)

###### RelationManager: StockItemsRelationManager

Gerencia o estoque de cada loja para as impressões desta funcionalidade.

**Formulário avançado:**
- Seletor de Card (exibe edição, idioma, número)
- Preço unitário (em R$)
- Quantidade (mínimo 1)
- Qualidade (NM, SP, MP, HP, D)
- Idioma (override)
- Toggle de Foil (inteligente — só mostra se a carta pode ser Foil)

**Toggle Inteligente de Foil:**
- Para Magic: mostra apenas se `mtg_has_foil = true`
- Para Star Wars: mostra apenas se `swu_foil = true`
- Para outros jogos: mostra sempre

**Tabela:**
- Imagem do Card (usa accessor `card.imageUrl`)
- Edição (set.name)
- Idioma (badge colorida: pt=success, en=info, ja=danger)
- Qualidade (badge com cores de semáforo: NM=success, SP=info, MP=warning, HP/D=danger)
- Foil (ícone brilhante se true)
- Quantidade
- Preço (em BRL, negrito)

**Filtro:**
- Permite visualizar apenas itens Foil

##### Observações Técnicas

1. **Sistema agnóstico bem implementado:** O Resource usa `tcg_name` para diferenciar entre jogos e mostra campos dinâmicos conforme o TCG selecionado.

2. **Conversão de símbolos de mana:** O método `convertManaSymbolsToHtml()` converte `{1}{W}` em ícones da fonte Mana — funciona apenas para Magic, o que é correto.

3. **Busca personalizada na listagem:** O método `getEloquentQuery()` em `ListCardFunctionalities` implementa busca que procura no nome genérico e nas impressões em português.

4. **TODO importante:** O `store_id` no `StockItemsRelationManager` está hardcoded como `1`. Quando a autenticação de lojista estiver pronta, isso deve ser `auth()->user()->store_id`.

5. **Views customizadas faltantes:** O sistema referencia 3 views customizadas que precisam ser criadas:
   - `filament.infolists.components.legalities-view`
   - `filament.infolists.components.language-switcher-view`
   - `filament.infolists.components.print-list-view`

##### Recomendações

- Mover a lógica do formulário para `Schemas/CardFunctionalityForm.php`
- Mover a lógica da tabela para `Tables/CardFunctionalitiesTable.php`
- Corrigir o `store_id` hardcoded no `StockItemsRelationManager`
- Criar as views customizadas que faltam
- Adicionar permissões se necessário

#### 9.1.3 Cards

O Resource `Cards` representa a camada de cadastro e manutenção das impressões (prints) de cartas do sistema legado. Ele foi construído originalmente para Magic: The Gathering e, embora ainda funcional, não utiliza as tabelas modernas agnósticas (CatalogConcepts e CatalogPrints). Atualmente, ele serve como o núcleo das prints usadas pelo estoque, layouts legados e venda no marketplace.

##### Estrutura de Arquivos

- Cards/
  - Pages/
    - CreateCard.php
    - EditCard.php
    - ListCards.php
  - Schemas/
    - CardForm.php (não utilizado)
  - Tables/
    - CardsTable.php (não utilizado)
  - CardResource.php

##### Model Associado

`App\Models\Card`

##### Propósito Geral do Resource

O CardResource é responsável por cadastrar e manter **prints individuais de cartas**, contendo:
- Nome impresso
- Idioma
- Número da coleção
- Raridade
- Imagens (locais, de API ou fanmade)
- Atributos específicos ou genéricos
- Associação com uma funcionalidade (CardFunctionality)
- Associação com um Set
- Integração direta com o estoque (StockItems)

##### Campos do Formulário

O formulário do Resource é dividido em seções e inclui:

- **set_id** (obrigatório, define o jogo/TGC via relação com Set)
- **tcg_name** (preenchido automaticamente com base no Set)
- **name** (nome conceitual, usado para relação com CardFunctionality)
- **printed_name**
- **language_code**
- **collection_number**
- **rarity**
- **custom_image_path** (upload manual de imagens)
- **type_main**, **type_sub**
- **card_cost**
- Atributos genéricos:
  - stat_attack
  - stat_defense
  - stat_life_hp
  - stat_level_link_pitch
- Textos:
  - rules_text
  - flavor_text

##### Colunas da Tabela

- Imagem principal (custom_image_path ou fallback para local_image_path_large)
- Nome da funcionalidade associada
- Nome impresso
- Jogo (tcg_name)
- Set (set.name)

##### Ações Disponíveis

- Visualizar (direciona para ViewCardFunctionality quando existe card_functionality_id)
- Editar registro
- Deletar registro (individual e em massa)

##### Navegação no Painel

- **Ícone:** heroicon-o-identification
- **Label:** herdado do Resource
- **Agrupamento:** padrão do Filament

##### Relacionamentos

- Cada Card pertence a:
  - um Set  
  - uma CardFunctionality  
- O Resource integra indiretamente com:
  - StockItemsRelationManager (estoque)
  - CardsRelationManager (prints por funcionalidade)

##### Observações Técnicas

1. O CardResource opera sobre a tabela `cards`, que é uma estrutura **legacy**, limitada e não agnóstica.
2. Sua lógica se mistura parcialmente com CardFunctionalities, mas de forma incompleta.
3. Campos genéricos como `type_main`, `stat_attack`, `stat_defense` são inadequados para múltiplos TCGs.
4. A tabela usada por esse resource não acompanha a arquitetura moderna do catálogo.
5. O campo `tcg_name` é derivado corretamente do Set e não deve ser manualmente editado.
6. A estrutura atual permite upload de imagens customizadas, o que deve ser preservado na refatoração.

##### Recomendações

- Migrar gradualmente a lógica de prints para `CatalogPrints`, mantendo CardResource funcional até a substituição completa.
- Transferir a lógica de imagens personalizadas para uma nova entidade vinculada ao StockItem.
- Separar atributos genéricos e específicos por TCG para evitar poluição da tabela.
- Remover ou migrar `CardForm.php` e `CardsTable.php` que estão vazios e não utilizados.
- Introduzir suporte a múltiplas imagens por print com ordenação para fotos reais do item.
- Após o Capítulo 3.0, descontinuar o CardResource, preservando apenas partes necessárias para migração de dados.

#### 9.1.4 CatalogConcepts

O Resource CatalogConcepts é a versão moderna e agnóstica do sistema de funcionalidades de cartas. Ele substitui o modelo legado CardFunctionalities e funciona como um catálogo unificado capaz de representar conceitos de cartas para todos os oito TCGs suportados. Cada conceito é armazenado na tabela catalog_concepts e possui ligação polimórfica com tabelas específicas de cada jogo, como mtg_concepts, pk_concepts, ygo_concepts, bs_concepts e outras. O CatalogConcept concentra informações gerais como nome, atributos principais, tipo, regras e características específicas do jogo correspondente.

##### Estrutura de Arquivos

- CatalogConcepts/
  - Pages/
    - CreateCatalogConcept.php
    - EditCatalogConcept.php
    - ListCatalogConcepts.php
    - ViewCatalogConcept.php
  - RelationManagers/
    - PrintsRelationManager.php
    - StockItemsRelationManager.php
  - Schemas/
    - CatalogConceptForm.php
  - Tables/
    - CatalogConceptsTable.php
  - CatalogConceptResource.php

##### Model Associado

App\Models\Catalog\CatalogConcept

##### Propósito Geral do Resource

O CatalogConceptResource permite:
- Criar, editar e listar conceitos de cartas.
- Expor atributos específicos de cada TCG de forma dinâmica.
- Conectar cada conceito às suas impressões (CatalogPrints).
- Exibir informações completas na página ViewCatalogConcept.php.
- Servir de base para navegação e visualização de cartas no sistema moderno.

##### Campos do Formulário

O formulário é dinâmico e exibe apenas os campos relevantes para o TCG selecionado.

Campos gerais:
- game_id (desabilitado após criação)
- name

Exemplos de campos específicos:
- Magic: mtg_mana_cost, mtg_type_line, mtg_rules_text
- Pokémon: hp, supertype, level, types, subtypes, rules_text
- Outros TCGs seguem o mesmo padrão de especialização.

##### Colunas da Tabela

- ID
- Jogo (badge)
- Nome do conceito
- Tipo específico do TCG
- Custo, HP ou outro atributo primário dependendo do jogo

##### Ações Disponíveis

- Visualizar (abre ViewCatalogConcept.php)
- Editar
- Deletar
- Deleção em massa

##### Navegação no Painel

- Ícone: heroicon-o-rectangle-stack
- Grupo: Catálogo V4

##### Relacionamentos

- PrintsRelationManager: gerencia os CatalogPrints ligados ao conceito.
- StockItemsRelationManager: controla itens de estoque ligados às impressões do conceito.

##### Página de Visualização: ViewCatalogConcept.php

A ViewCatalogConcept é a página central de visualização do sistema moderno. Ela funciona como painel unificado de informações de uma carta e possui:
- Exibição do conceito principal.
- Lista clicável de prints relacionados.
- Troca dinâmica de print selecionado.
- Troca dinâmica de idioma.
- Atualização instantânea de imagem, texto e atributos.
- Seções específicas baseadas no TCG.

A página utiliza:
- Métodos Livewire (changePrint, changeLanguage, getSelectedPrintProperty).
- Views Blade customizadas (print-list-view, language-switcher-view, legalities-view).
- Priorização automática de imagens: local → remota → placeholder.

##### Observações Técnicas

- A arquitetura é totalmente agnóstica.
- A view moderna já substitui a view de prints individuais.
- A experiência é equivalente ao legado, porém expandida para todos os TCGs.
- Formulário e tabela podem futuramente ser movidos para Schemas e Tables para maior organização.

##### Recomendações

- Refinar a ViewCatalogConcept com CSS, grid e componentes visuais aprimorados.
- Adicionar ícones específicos dos TCGs.
- Melhorar o carrossel de prints.
- Implementar fallback mais completo de idiomas.

#### 9.1.5 CatalogPrints

O Resource CatalogPrints representa a camada moderna de impressões (prints) de cartas. Ele substitui o recurso legado Cards, oferecendo uma estrutura polimórfica e agnóstica onde cada impressão pertence a um conceito (CatalogConcept) e é relacionada a uma tabela específica do TCG, como mtg_prints, pk_prints ou ygo_prints. O CatalogPrint não possui página de visualização própria; todas as impressões são mostradas dentro da página ViewCatalogConcept.php.

##### Estrutura de Arquivos

- CatalogPrints/
  - Pages/
    - CreateCatalogPrints.php
    - EditCatalogPrint.php
    - ListCatalogPrints.php
  - Schemas/
    - CatalogPrintForm.php
  - Tables/
    - CatalogPrintsTable.php
  - CatalogPrintResource.php

##### Model Associado

App\Models\Catalog\CatalogPrint

##### Propósito Geral do Resource

O CatalogPrintResource permite:
- Criar, editar e listar impressões de cartas.
- Associar cada impressão a seu conceito e ao set correspondente.
- Utilizar campos específicos do TCG por meio de relações polimórficas.
- Fornecer dados para a ViewCatalogConcept.php, onde as impressões são exibidas.

##### Relacionamentos Polimórficos

Cada print pertence a:
- Um CatalogConcept (concept_id)
- Um Set (set_id)
- Uma tabela específica do TCG (specific_type e specific_id)

##### Campos do Formulário

- concept_id (select, desabilitado após criação)
- set_id (select, desabilitado após criação)
- printed_name
- language_code
- rules_text (override)
- flavor_text (override)
- custom_image_path (upload)

Campos provenientes do modelo específico (read-only):
- specific.number
- specific.rarity

##### Colunas da Tabela

- Imagem (local ou remota)
- Nome do conceito
- Set
- Número do print
- Raridade
- Idioma

##### Ações Disponíveis

- Visualizar (redireciona para ViewCatalogConcept via o conceito)
- Editar
- Deletar
- Deleção em massa

##### Navegação no Painel

- Ícone: heroicon-o-camera
- Grupo: Catálogo V4

##### Integração com StockItems

O sistema moderno usa:
- catalog_print_id
em vez de card_id do legado.  
Isso integra o estoque diretamente às impressões modernas.

##### Como os Prints São Exibidos

Os prints não têm view própria.  
Eles são exibidos dentro da ViewCatalogConcept.php, que oferece:
- Lista de prints clicável
- Troca dinâmica de print
- Troca dinâmica de idioma
- Atualização instantânea de imagem, texto e atributos

##### Prioridade de Imagens

A lógica segue a ordem:
1. custom_image_path (se houver)
2. imagem remota da tabela específica
3. placeholder padrão

##### Observações Técnicas

- A lógica é semelhante ao Cards legado, porém completamente agnóstica.
- O Resource serve principalmente como backend para ViewCatalogConcept.php.
- Formulário e tabela podem futuramente ir para Schemas e Tables para maior organização.

##### Recomendações

- Centralizar estilização das imagens.
- Unificar exibição das especificidades dos TCGs na view inteligente.
- Refinar a integração com o estoque para permitir previews visuais do print.

#### 9.1.6 Games

O Resource Games gerencia os oito TCGs suportados pelo sistema: Magic: The Gathering, Pokémon TCG, Battle Scenes, Yu-Gi-Oh!, One Piece Card Game, Lorcana TCG, Flesh and Blood e Star Wars: Unlimited. Cada jogo é a base da arquitetura agnóstica do projeto, servindo como referência para conceitos, impressões, sets, legalidades e formatos específicos.

##### Estrutura de Arquivos

- Games/
  - Pages/
    - CreateGame.php
    - EditGame.php
    - ListGames.php
  - RelationManagers/
    - SetsRelationManager.php
  - Schemas/
    - GameForm.php
  - Tables/
    - GamesTable.php
  - GameResource.php

##### Model Associado

App\Models\Game

##### Propósito Geral do Resource

O GameResource permite:
- Criar, editar e listar os TCGs suportados.
- Configurar URL de API para ingestão de dados.
- Definir formatos válidos para cada jogo.
- Ativar ou desativar um jogo sem deletá-lo.
- Gerenciar sets (coleções) de cada jogo via RelationManager.

##### Campos do Formulário

- name (obrigatório, único, máximo 255 caracteres)
- publisher (opcional, máximo 255 caracteres)
- api_url (opcional, URL válida, máximo 255 caracteres)
- formats_list (opcional, JSON, textarea)
- is_active (toggle, default true)

##### Colunas da Tabela

- ID (oculta)
- Nome do Jogo (pesquisável, ordenável)
- Editora (pesquisável)
- URL da API (link clicável)
- Formatos (truncado a 20 caracteres, com tooltip)
- Ativo (ícone booleano)

##### Ações Disponíveis

- Editar
- Sem deletar (recurso crítico)

##### Navegação no Painel

- Ícone: heroicon-o-rectangle-stack

##### Relacionamentos

- SetsRelationManager: gerencia os sets (coleções) de cada jogo.

##### Observações Técnicas

- O Game é a base da arquitetura agnóstica.
- Cada Game tem tabelas específicas para conceitos (mtg_concepts, pk_concepts, etc.) e impressões (mtg_prints, pk_prints, etc.).
- O campo api_url permite integração com APIs externas.
- O campo formats_list em JSON é flexível e escalável.
- O campo is_active permite desativar um jogo sem deletá-lo.
- Não há ação de deletar para evitar quebra de integridade.

##### Recomendações

- Adicionar filtro para is_active.
- Validar formats_list como JSON válido.
- Adicionar documentação sobre o formato esperado de api_url para cada jogo.
- Mover formulário e tabela para Schemas e Tables.

#### 9.1.7 PlayerUsers

O Resource PlayerUsers gerencia os jogadores e clientes do sistema. Cada jogador pode comprar cartas no marketplace, criar álbuns e decks, acumular pontos de fidelidade e fazer pedidos nas lojas. O PlayerUserResource oferece funcionalidades básicas de CRUD com campos para autenticação, documentos, dados pessoais e fidelidade.

##### Estrutura de Arquivos

- PlayerUsers/
  - Pages/
    - CreatePlayerUser.php
    - EditPlayerUser.php
    - ListPlayerUsers.php
  - Schemas/
    - PlayerUserForm.php
  - Tables/
    - PlayerUsersTable.php
  - PlayerUserResource.php

##### Model Associado

App\Models\PlayerUser

##### Propósito Geral do Resource

O PlayerUserResource permite:
- Criar, editar e listar jogadores.
- Gerenciar autenticação e documentos.
- Rastrear pontos de fidelidade.
- Ativar ou desativar um jogador sem deletá-lo.
- Servir de base para pedidos, álbuns e decks.

##### Campos do Formulário

Dados básicos (obrigatórios):
- name (máximo 100 caracteres)
- surname (máximo 100 caracteres)
- login (único, máximo 100 caracteres)
- email (único, email válido, máximo 100 caracteres)

Autenticação:
- password (obrigatório apenas na criação, oculto na edição)

Documentos (opcionais):
- document_number (CPF/CNPJ, único, máximo 20 caracteres)
- id_document_number (RG/ID, único, máximo 20 caracteres)

Dados pessoais (opcionais):
- phone_number (máximo 20 caracteres)
- birth_date (data de nascimento)

Fidelidade:
- loyalty_points (numérico, default 0)

Status:
- is_active (toggle, default true)

##### Colunas da Tabela

- Nome (pesquisável, ordenável)
- Email (pesquisável)
- Pontos (ordenável)
- CPF/CNPJ (toggle, oculto por padrão)
- Ativo (ícone booleano, ordenável)

##### Ações Disponíveis

- Editar
- Deletar em massa

##### Navegação no Painel

- Ícone: heroicon-o-users
- Grupo: Gestão de Clientes e Lojas

##### Observações Técnicas

- O PlayerUser é a base para pedidos, álbuns e decks.
- Documentos são únicos para evitar duplicação.
- Pontos de fidelidade servem para programa de recompensas.
- O campo is_active permite desativar um jogador sem deletá-lo.
- Senha é obrigatória apenas na criação e oculta na edição por segurança.
- Não há filtros definidos.
- Não há RelationManagers para pedidos, álbuns ou decks.
- Não há validação de CPF/CNPJ.
- Não há página de visualização customizada.

##### Recomendações

- Adicionar filtros para is_active, loyalty_points e birth_date.
- Adicionar RelationManagers para Orders, Albums, Decks e Addresses.
- Validar CPF/CNPJ usando biblioteca apropriada.
- Criar página de visualização ViewPlayerUser.php com detalhes completos.
- Implementar busca global com getGlobalSearchResultTitle e getGlobalSearchResultDetails.
- Mover formulário e tabela para Schemas e Tables.

#### 9.1.8 Sets (Coleções)

O Resource Sets é responsável por gerenciar todas as coleções (edições) de todos os TCGs suportados pelo sistema. Cada Set pertence a um Game específico e funciona como a unidade básica que organiza prints (impressões) legadas e modernas. Ele é fundamental para a ingestão de dados, construção de catálogo, organização de estoque e exibição no marketplace.

##### Estrutura de Arquivos

- Sets/
  - Pages/
    - CreateSet.php
    - EditSet.php
    - ListSets.php
  - RelationManagers/
    - CardsRelationManager.php
  - Schemas/
    - SetForm.php
  - Tables/
    - SetsTable.php
  - SetResource.php

##### Model Associado

App\Models\Set

##### Finalidade do Resource

O SetResource permite:
- Criar, editar e listar coleções de todos os TCGs.
- Associar cada Set ao seu jogo (Magic, Pokémon, YuGi, BS, etc.).
- Controlar metadados como data de lançamento, códigos externos e imagem.
- Integrar coleções ao catálogo moderno e ao sistema legado.
- Organizar impressões (prints) por coleção usando RelationManagers.

##### Campos Principais

- game_id: identifica o jogo ao qual a coleção pertence.
- name: nome oficial da coleção.
- code: código de identificação (ex.: DMU, AQ, SV1).
- release_date: data de lançamento.
- total_cards: quantidade oficial de cartas.
- image_url: imagem representativa da coleção.
- api_code, tcgplayer_code, cardmarket_code: integração com APIs externas.
- is_active: determina se a coleção está ativa.

##### Colunas da Tabela

- Nome da coleção
- Jogo associado
- Código
- Data de lançamento
- Status ativo/inativo

##### RelationManagers

- CardsRelationManager: lista e gerencia prints legadas associadas ao Set.
  Usado principalmente para Magic no sistema legado.

##### Observações Técnicas

- O SetResource é híbrido: suporta tanto prints legadas quanto prints modernas (CatalogPrints).
- A arquitetura atual ainda utiliza CardsRelationManager devido ao legado.
- Campos como block_name e series_name existem por compatibilidade com ingestões antigas.
- É um dos Resources centrais do catálogo, pois todo Concept e Print depende de um Set.

##### Recomendações

- Reagrupar o formulário em seções menores.
- Remover campos defasados em ingestões futuras.
- Criar no futuro um CatalogSetsResource para substituir parcialmente o legado.
- Manter CardsRelationManager até a migração completa para V4.

#### 9.1.9 StoreAdminUsers

O Resource StoreAdminUsers gerencia o staff (funcionários) de cada loja. Cada StoreAdminUser é um funcionário que trabalha para uma loja específica, tem acesso restrito ao painel Filament e pode gerenciar estoque, preços e pedidos da sua loja. O Resource implementa isolamento por loja via getEloquentQuery e controle de criação via canCreate, garantindo segurança e segregação de dados.

##### Estrutura de Arquivos

- StoreAdminUsers/
  - Pages/
    - CreateStoreAdminUser.php
    - EditStoreAdminUser.php
    - ListStoreAdminUsers.php
  - Schemas/
    - StoreAdminUserForm.php
  - Tables/
    - StoreAdminUsersTable.php
  - StoreAdminUserResource.php

##### Model Associado

App\Models\StoreAdminUser

##### Propósito Geral do Resource

O StoreAdminUserResource permite:
- Criar, editar e listar funcionários de lojas.
- Vincular cada funcionário a uma loja específica.
- Gerenciar dados pessoais, autenticação e permissões.
- Rastrear data de contratação e status ativo/inativo.
- Implementar isolamento por loja via getEloquentQuery.
- Controlar quem pode criar novo staff via canCreate.

##### Campos do Formulário

Vínculo com loja:
- store_id (select, nullable) — FK para a loja

Identificação básica:
- name (obrigatório, máximo 100 caracteres)
- surname (obrigatório, máximo 100 caracteres)
- login (obrigatório, único, máximo 100 caracteres)
- email (obrigatório, email, único, máximo 100 caracteres)

Autenticação:
- password (obrigatório apenas na criação, oculto na edição)

Gestão interna:
- permissions_json (opcional, textarea) — permissões customizadas em JSON

Dados pessoais:
- hired_date (opcional, data de contratação)

Status:
- is_active (toggle, default true)

##### Colunas da Tabela

- Nome (pesquisável, ordenável)
- Loja (via store.name, ordenável)
- Contratação (data, ordenável)
- Ativo (ícone booleano, ordenável)

##### Ações Disponíveis

- Editar
- Deletar em massa

##### Navegação no Painel

- Ícone: heroicon-o-briefcase
- Label: Staff da Loja
- Grupo: Gestão de Clientes e Lojas

##### Isolamento por Loja (getEloquentQuery)

O método getEloquentQuery implementa isolamento por loja:

- SuperUser (User) e AdminUser veem todos os registros.
- StoreUser (lojista) vê apenas staff da sua loja (store_id).
- StoreAdminUser (staff) vê apenas staff da sua loja (store_id).
- Se o usuário não tem loja vinculada, a query retorna nenhum registro.

Isso garante que cada loja vê apenas seu próprio staff e impede acesso cruzado.

##### Controle de Criação (canCreate)

O método canCreate controla quem pode criar novo staff:

- SuperUser (User) e AdminUser podem criar livremente.
- StoreUser (lojista) pode criar apenas se estiver associado a uma loja.
- StoreAdminUser (staff) não pode criar novo staff (segurança).
- PlayerUser (cliente) não pode criar.

Isso impede que staff crie outro staff e garante que apenas lojistas e admins podem contratar.

##### Observações Técnicas

- O campo password é obrigatório apenas na criação e fica oculto na edição.
- Os campos login, email são únicos no banco.
- O campo store_id é a chave para isolamento por loja.
- O campo permissions_json permite customização granular de permissões.
- O campo is_active permite desativar um funcionário sem deletá-lo.
- getEloquentQuery garante isolamento por loja.
- canCreate impede que staff crie outro staff.
- Não há filtros implementados.
- Não há RelationManagers para gerenciar pedidos ou atividades.
- Não há validação de permissions_json.

##### Recomendações

- Adicionar filtros para is_active, store_id e hired_date.
- Validar permissions_json como JSON válido.
- Adicionar busca global.
- Permitir deletar um funcionário individualmente.
- Adicionar RelationManagers para pedidos e atividades.

#### 9.1.10 Stores

O Resource Stores gerencia as lojas (lojistas) do sistema. Cada Store é um lojista que vende cartas no marketplace, tem seu próprio catálogo de estoque e define suas próprias margens de lucro, descontos e limites de fidelidade. O StoreResource é central para o modelo de negócio SaaS, pois cada loja é uma unidade independente com seu próprio proprietário, URL e configurações financeiras.

##### Estrutura de Arquivos

- Stores/
  - Pages/
    - CreateStore.php
    - EditStore.php
    - ListStores.php
  - RelationManagers/
    - StockItemsRelationManager.php
  - Schemas/
    - StoreForm.php
  - Tables/
    - StoresTable.php
  - StoreResource.php

##### Model Associado

App\Models\Store

##### Propósito Geral do Resource

O StoreResource permite:
- Criar, editar e listar lojas.
- Vincular cada loja a um proprietário (user_id).
- Configurar margens de lucro por método de pagamento.
- Definir descontos PIX e limites de fidelidade.
- Gerenciar estoque via StockItemsRelationManager.
- Criar URLs personalizadas via slug.

##### Campos do Formulário

Propriedade (hidden):
- user_id (hidden, default Auth::id()) — vincula a loja ao usuário logado

Identidade da loja:
- name (obrigatório, máximo 255 caracteres) — nome da marca/loja
- url_slug (obrigatório, único, máximo 50 caracteres) — identificador para URL própria
- slogan (opcional, textarea, máximo 500 caracteres) — breve descrição

Margens financeiras (crítico):
- purchase_margin_cash (obrigatório, numérico, 0.05 a 1.0, default 0.400) — margem para dinheiro/PIX
- purchase_margin_credit (obrigatório, numérico, 0.05 a 1.0, default 0.300) — margem para crédito na loja

Limites de fidelidade e desconto:
- max_loyalty_discount (obrigatório, numérico, 0.0 a 0.5, default 0.200) — máximo desconto por fidelidade
- pix_discount_rate (obrigatório, numérico, 0.0 a 0.10, default 0.050) — taxa de desconto para PIX

##### Colunas da Tabela

- Nome da Loja (pesquisável, ordenável)
- Slug da URL (pesquisável, ordenável)
- Margem (Dinheiro) (com símbolo %, ordenável)
- Status (badge com cores: ativo=verde, inativo=vermelho)

##### Ações Disponíveis

- Editar
- Deletar em massa

##### Navegação no Painel

- Ícone: heroicon-o-building-storefront
- Label: Lojas
- Grupo: Gestão de Clientes e Lojas

##### RelationManagers

- StockItemsRelationManager: gerencia o estoque da loja.

##### Observações Técnicas

- O campo user_id (hidden) vincula a loja ao proprietário e é obrigatório.
- Os campos de margem (purchase_margin_cash, purchase_margin_credit) são críticos para o modelo de negócio.
- O campo url_slug é único e permite URLs personalizadas para cada loja.
- O campo pix_discount_rate incentiva pagamentos diretos.
- O campo max_loyalty_discount permite customização do programa de fidelidade.
- O campo is_active permite desativar uma loja sem deletá-la.
- Não há filtros implementados.
- Não há validação de lógica entre margens.
- Não há busca global implementada.

##### Recomendações

- Adicionar filtros para is_active, user_id e created_at.
- Validar lógica de margens (ex: margin_cash deve ser maior que margin_credit).
- Adicionar busca global.
- Permitir deletar uma loja individualmente.
- Criar página de visualização com estatísticas de vendas e estoque.
- Adicionar RelationManagers para staff, pedidos e análises.

#### 9.1.11 StoreUsers

O Resource StoreUsers gerencia os lojistas (proprietários de lojas) do sistema. Cada StoreUser é um lojista que possui uma loja, vende cartas no marketplace e tem acesso restrito ao painel Filament. O Resource implementa isolamento por loja via getEloquentQuery, garantindo que cada lojista vê apenas registros da sua própria loja.

##### Estrutura de Arquivos

- StoreUsers/
  - Pages/
    - CreateStoreUser.php
    - EditStoreUser.php
    - ListStoreUsers.php
  - Schemas/
    - StoreUserForm.php
  - Tables/
    - StoreUsersTable.php
  - StoreUserResource.php

##### Model Associado

App\Models\StoreUser

##### Propósito Geral do Resource

O StoreUserResource permite:
- Criar, editar e listar lojistas.
- Vincular cada lojista a uma loja específica.
- Gerenciar dados pessoais, autenticação e documentos.
- Ativar ou desativar um lojista sem deletá-lo.
- Implementar isolamento por loja via getEloquentQuery.

##### Campos do Formulário

Vínculo com loja:
- store_id (select, nullable) — FK para a loja

Identificação básica:
- name (obrigatório, máximo 100 caracteres)
- surname (obrigatório, máximo 100 caracteres)
- login (obrigatório, único, máximo 100 caracteres)
- email (obrigatório, email, único, máximo 100 caracteres)

Autenticação:
- password (obrigatório apenas na criação, oculto na edição)

Documentos:
- document_number (opcional, único, máximo 20 caracteres) — CPF ou CNPJ

Dados pessoais:
- phone_number (opcional, máximo 20 caracteres)

Status:
- is_active (toggle, default true)

##### Colunas da Tabela

- Nome (pesquisável, ordenável)
- Email (pesquisável)
- Loja (via store.name, ordenável)
- Ativo (ícone booleano, ordenável)

##### Ações Disponíveis

- Editar
- Deletar em massa

##### Navegação no Painel

- Ícone: heroicon-o-building-storefront
- Label: Lojista
- Grupo: Gestão de Clientes e Lojas

##### Isolamento por Loja (getEloquentQuery)

O método getEloquentQuery implementa isolamento por loja:

- SuperUser (User) e AdminUser veem todos os registros.
- StoreUser (lojista) vê apenas lojistas da sua loja (store_id).
- StoreAdminUser (staff) vê apenas lojistas da sua loja (store_id).
- Se o usuário não tem loja vinculada, a query retorna nenhum registro.

Isso garante que cada loja vê apenas seus lojistas e impede acesso cruzado.

##### Observações Técnicas

- O campo password é obrigatório apenas na criação e fica oculto na edição.
- Os campos login, email, document_number são únicos no banco.
- O campo store_id é a chave para isolamento por loja.
- O campo is_active permite desativar um lojista sem deletá-lo.
- getEloquentQuery garante isolamento por loja.
- Não há filtros implementados.
- Não há RelationManagers para gerenciar pedidos ou atividades.
- Não há validação de CPF/CNPJ.

##### Recomendações

- Adicionar filtros para is_active, store_id e created_at.
- Validar CPF/CNPJ como formato válido.
- Adicionar busca global.
- Permitir deletar um lojista individualmente.
- Adicionar RelationManagers para pedidos e atividades.

#### 9.1.12 Users (SuperAdmin)

O Resource Users gerencia os SuperAdmins (administradores supremos) do sistema. Cada User é um SuperAdmin que tem acesso total ao painel Filament e pode gerenciar todos os Resources. O UserResource implementa um sistema simplificado sem Spatie Permissions, com controle de acesso via shouldRegisterNavigation e proteção contra exclusão via is_protected.

##### Estrutura de Arquivos

- Users/
  - Pages/
    - CreateUser.php
    - EditUser.php
    - ListUsers.php
  - UserResource.php

##### Model Associado

App\Models\User

##### Propósito Geral do Resource

O UserResource permite:
- Criar, editar e listar SuperAdmins.
- Gerenciar dados básicos (nome, email).
- Controlar autenticação (senha).
- Proteger SuperAdmins críticos contra exclusão (is_protected).
- Controlar acesso ao menu via shouldRegisterNavigation.

##### Campos do Formulário

Dados básicos:
- name (obrigatório, máximo 255 caracteres) — nome completo do SuperAdmin
- email (obrigatório, email, único, máximo 255 caracteres) — email do SuperAdmin

Segurança (senha):
- password (obrigatório apenas na criação, hash automático via Hash::make, confirmação obrigatória)
- password_confirmation (obrigatório apenas na criação, não salvo no banco)

Status (proteção):
- is_protected (toggle, disabled, visível apenas na edição) — define se o usuário é Root Supremo (não pode ser deletado)

##### Colunas da Tabela

- Nome (pesquisável, ordenável)
- Email (pesquisável)
- Protegido (ícone booleano, com tooltip)

##### Ações Disponíveis

- Editar
- Deletar (visível apenas se is_protected for false)
- Deletar em massa (com validação para impedir exclusão de usuários protegidos)

##### Navegação no Painel

- Ícone: heroicon-o-users
- Label: Administrador do Sistema
- Grupo: Configurações de Plataforma

##### Controle de Acesso (shouldRegisterNavigation)

O método shouldRegisterNavigation controla quem vê o menu:

- User (SuperAdmin) vê o menu (está logado na tabela users).
- AdminUser, StoreUser, StoreAdminUser, PlayerUser não veem o menu (não estão logados na tabela users).

Isso garante que apenas SuperAdmins tenham acesso ao UserResource.

##### Proteção contra Exclusão (is_protected)

O Resource implementa duas camadas de proteção contra exclusão:

1. DeleteAction (individual): se is_protected for true, o botão de deletar não aparece.
2. DeleteBulkAction (em massa): se qualquer registro selecionado tiver is_protected true, a ação é cancelada com notificação.

Isso impede que SuperAdmins críticos sejam deletados acidentalmente.

##### Observações Técnicas

- O campo password é obrigatório apenas na criação e fica oculto na edição.
- O campo email é único no banco.
- O campo is_protected é disabled para evitar alterações acidentais.
- O Resource não usa Spatie Permissions (sistema simplificado).
- shouldRegisterNavigation garante que apenas SuperAdmins vejam o menu.
- Proteção contra exclusão funciona perfeitamente via is_protected.
- Não há filtros implementados.
- Não há RelationManagers para gerenciar atividades ou logs.
- Não há busca global implementada.

##### Recomendações

- Adicionar filtros para is_protected e created_at.
- Adicionar busca global.
- Validar email com formato específico.
- Adicionar RelationManagers para atividades e logs.
- Permitir deletar individualmente usuários não protegidos.

### 9.2 Controllers

A camada de Controllers é responsável por receber requisições HTTP e coordenar as ações necessárias para entregar respostas ao usuário final, seja renderizando páginas, executando lógica de negócios ou integrando diferentes partes do sistema. Embora grande parte da administração interna utilize o Filament (que funciona sem controllers tradicionais), a aplicação Multiverse Cards mantém controllers para fluxos públicos, páginas de navegação, operações de marketplace e utilidades específicas.

Os controllers atuam como ponte entre:

- rotas acessadas pelos jogadores (pages públicas do marketplace),
- rotas internas utilizadas por lojas,
- exibição de catálogos e detalhes de prints,
- carrinho, pedidos e checkout,
- páginas informativas ou auxiliares,
- endpoints de interação entre módulos.

Este capítulo documenta todos os controllers da pasta `app/Http/Controllers`.  
Cada controller receberá seu próprio subcapítulo, no formato:

- 9.2.1 NomeDoController  
- 9.2.2 NomeDoController  
- 9.2.3 NomeDoController  
- (e assim por diante)

Cada subcapítulo terá:
- visão geral do controller,
- rotas atendidas,
- métodos públicos e suas responsabilidades,
- dependências de modelos ou serviços,
- observações técnicas,
- recomendações de melhoria,
- e o markdown final para documentação.

#### 9.2.1 Controller (Classe Base)

Classe base abstrata que todos os controllers do Laravel estendem. Serve como ponto central para adicionar métodos compartilhados entre controllers, mas atualmente está vazia, seguindo o padrão do Laravel 11+.

##### Arquivo

app/Http/Controllers/Controller.php

##### Namespace

App\Http\Controllers

##### Finalidade

Servir como classe pai para todos os controllers do projeto, permitindo adicionar métodos compartilhados de validação, autorização ou lógica comum.

##### Código Atual

<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}

##### Observações Técnicas

- Classe abstrata, não pode ser instanciada diretamente.
- No Laravel 10 e anteriores, essa classe incluía traits como AuthorizesRequests e ValidatesRequests.
- No Laravel 11+, esses traits foram removidos e devem ser adicionados individualmente nos controllers que precisam deles.
- Atualmente está vazia, seguindo o padrão do Laravel 11+.

##### Recomendações

- Manter como está.
- Se precisar de métodos compartilhados entre controllers, adicionar aqui.
- Não adicionar lógica de negócio nesta classe.

#### 9.2.2 MarketplaceController

Controller responsável pelas rotas públicas do marketplace. Gerencia a página inicial (lista de jogos) e o catálogo de cartas por jogo.

##### Arquivo

app/Http/Controllers/MarketplaceController.php

##### Namespace

App\Http\Controllers

##### Finalidade

Controlar as rotas públicas do marketplace:
- Exibir a página inicial com todos os jogos ativos.
- Exibir o catálogo de cartas de um jogo específico, incluindo apenas itens com estoque disponível.

##### Métodos

**index()**

Carrega todos os jogos ativos e retorna a view da página inicial do marketplace.

- Rota: GET /
- View: resources/views/marketplace/index.blade.php
- Retorna: lista de jogos ativos ($games)

Código:
public function index()
{
    $games = Game::where('is_active', true)->get();
    return view('marketplace.index', compact('games'));
}

**showCatalog(string $game_slug)**

Carrega o catálogo de cartas de um jogo específico, filtrando apenas cartas com estoque (quantity > 0).

- Rota: GET /catalog/{game_slug}
- View: resources/views/marketplace/catalog.blade.php
- Retorna: $game (modelo do jogo), $cardFunctionalities (coleção de funcionalidades com estoque)

Código:
public function showCatalog(string $game_slug)
{
    $game = Game::where('slug', $game_slug)->firstOrFail();

    $cardFunctionalities = CardFunctionality::where('game_id', $game->id)
        ->with(['stockItems' => function ($query) {
            $query->where('quantity', '>', 0);
        }])
        ->get();

    return view('marketplace.catalog', compact('game', 'cardFunctionalities'));
}

##### Observações Técnicas

- Usa CardFunctionality do sistema legado, não o sistema moderno (CatalogConcept/CatalogPrint).
- Eager loading de stockItems evita problema N+1.
- Filtro adequado por estoque: mostra apenas cartas realmente disponíveis.
- firstOrFail() retorna 404 automaticamente caso o jogo não exista.
- Atualmente não utiliza paginação.
- Sem sistema de filtros por raridade, preço, tipo etc.
- Sem ordenação de resultados.
- Sem cache para a lista de jogos ativos.

##### Recomendações

- Adicionar paginação (ex.: paginate(50)).
- Adicionar cache (Cache::remember) na listagem de jogos ativos.
- Permitir filtros no catálogo (raridade, tipo, preço).
- Permitir ordenação (nome, preço, raridade).
- Migrar futuramente para CatalogConcepts/CatalogPrints quando o legado for substituído.

#### 9.2.3 RegisterController (Auth)

Controller responsável pelo fluxo completo de registro de usuários (jogadores e lojistas). Ele coordena a exibição dos formulários, valida os dados enviados, cria os registros nos respectivos modelos e efetua o login automático após a criação da conta.

##### Arquivo

app/Http/Controllers/Auth/RegisterController.php

##### Namespace

App\Http\Controllers\Auth

##### Finalidade

Gerenciar o processo de registro dos dois tipos de usuário da plataforma:
- Jogadores (PlayerUser)
- Lojistas (StoreUser + Store)

O controller contém telas separadas para cada tipo de registro e processamentos específicos para cada fluxo.

##### Métodos

**showRegistrationTypeForm()**

Exibe a tela onde o usuário escolhe o tipo de conta que deseja criar.

- Rota: GET /register
- View: resources/views/auth/register-type.blade.php

Código:
public function showRegistrationTypeForm()
{
    return view('auth.register-type');
}

---

**showPlayerRegistrationForm()**

Exibe o formulário para cadastro de jogadores.

- Rota: GET /register/player
- View: resources/views/auth/register-player.blade.php

Código:
public function showPlayerRegistrationForm()
{
    return view('auth.register-player');
}

---

**showStoreRegistrationForm()**

Exibe o formulário para cadastro de lojistas.

- Rota: GET /register/store
- View: resources/views/auth/register-store.blade.php

Código:
public function showStoreRegistrationForm()
{
    return view('auth.register-store');
}

---

**registerPlayer(Request $request)**

Processa o cadastro de um jogador.

Passos principais:
1. Valida os dados enviados.
2. Cria um novo PlayerUser com senha criptografada.
3. Ativa automaticamente o jogador.
4. Faz login utilizando o guard "player".
5. Redireciona para o dashboard do jogador.

Validação:
- name: required, max 100
- surname: required, max 100
- login: required, unique em player_users
- email: required, unique em player_users
- password: required, min 8, confirmado

Código:
public function registerPlayer(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'surname' => 'required|string|max:100',
        'login' => 'required|string|max:100|unique:player_users',
        'email' => 'required|email|max:100|unique:player_users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $player = PlayerUser::create([
        'name' => $validated['name'],
        'surname' => $validated['surname'],
        'login' => $validated['login'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'is_active' => true,
    ]);

    Auth::guard('player')->login($player);

    return redirect()->route('player.dashboard');
}

---

**registerStore(Request $request)**

Processa o cadastro de um lojista e sua loja.

Passos principais:
1. Valida dados do lojista e da loja.
2. Cria a loja (Store).
3. Cria o usuário lojista (StoreUser).
4. Associa a loja ao StoreUser criado.
5. Tudo ocorre dentro de uma transação para garantir integridade.
6. Faz login usando o guard "store_user".
7. Redireciona para o dashboard da loja.

Validação:
- store_name: obrigatório
- url_slug: obrigatório, único em stores
- name, surname: obrigatórios
- login: único em store_users
- email: único em store_users
- password: min 8, confirmado

Código:
public function registerStore(Request $request)
{
    $validated = $request->validate([
        'store_name' => 'required|string|max:255',
        'url_slug' => 'required|string|max:50|unique:stores',
        'name' => 'required|string|max:100',
        'surname' => 'required|string|max:100',
        'login' => 'required|string|max:100|unique:store_users',
        'email' => 'required|email|max:100|unique:store_users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    DB::transaction(function () use ($validated, &$store, &$storeUser) {
        $store = Store::create([
            'name' => $validated['store_name'],
            'url_slug' => $validated['url_slug'],
            'is_active' => true,
        ]);

        $storeUser = StoreUser::create([
            'store_id' => $store->id,
            'name' => $validated['name'],
            'surname' => $validated['surname'],
            'login' => $validated['login'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $store->update(['user_id' => $storeUser->id]);
    });

    Auth::guard('store_user')->login($storeUser);

    return redirect()->route('store.dashboard');
}

---

##### Observações Técnicas

- O fluxo de cadastro de lojista usa transação, garantindo que Store e StoreUser sejam criados juntos.
- Login automático após registro melhora experiência do usuário.
- Há validações completas para evitar duplicidade de login, email ou url_slug.
- registerStore() utiliza variáveis por referência (&$store), funcional mas pouco elegante.
- Nenhum dos fluxos utiliza verificação de email.
- Não há tratamento de exceções no fluxo de transação.
- Não existe rate limiting, o que permitiria atacar o endpoint com múltiplos registros.
- Nenhum fluxo envia email de boas-vindas ou confirmação.

##### Recomendações

- Adicionar verificação de email (Laravel Verification).
- Adicionar rate limiting aos endpoints de registro.
- Melhorar o uso da transação retornando valores em vez de usar referências.
- Adicionar try/catch com mensagens amigáveis ao usuário.
- Implementar email de confirmação ou boas‑vindas.
- Validar CPF/CNPJ caso necessário em versões futuras.

### 9.3 Providers

Os Providers são classes essenciais do Laravel responsáveis por registrar serviços, bindings, eventos, configurações e extensões que precisam ser carregadas no ciclo de inicialização da aplicação. Eles representam pontos de entrada importantes onde comportamentos globais são definidos e onde integrações externas ou internas são vinculadas.

No projeto, existem dois providers principais:

- Providers gerais do Laravel e da aplicação, que ficam na raiz de `app/Providers`.
- Providers específicos do Filament, que ficam dentro de `app/Providers/Filament`.

Cada provider será documentado no subcapítulo correspondente.

- 9.3.1 AppServiceProvider  
- 9.3.2 Filament\AdminPanelProvider

Cada item inclui:
- Localização no projeto  
- Responsabilidade principal  
- Funções executadas durante o boot e o register  
- Observações técnicas  
- Recomendações

#### 9.3.1 AppServiceProvider

##### Localização

app/Providers/AppServiceProvider.php

##### Namespace

App\Providers

##### Finalidade

Provider principal da aplicação Laravel. Responsável por registrar serviços, bindings e inicializações globais que são necessárias para o funcionamento correto da aplicação.

##### Métodos

**boot()**

Executado durante o bootstrap da aplicação. Atualmente vazio, mas serve como ponto de entrada para inicializações que precisam rodar antes da aplicação estar completamente carregada.

Código:
public function boot(): void
{
    //
}

**register()**

Executado durante o registro de serviços. Atualmente vazio, mas é o local ideal para registrar bindings no container de serviços.

Código:
public function register(): void
{
    //
}

##### Observações Técnicas

- Classe padrão do Laravel 12, sem customizações adicionadas.
- Ambos os métodos estão vazios, seguindo o padrão minimalista do Laravel moderno.
- No Laravel 11+, traits como AuthorizesRequests e ValidatesRequests foram removidos e devem ser adicionados individualmente nos controllers.
- Atualmente não há bindings, macros, ou inicializações globais registradas aqui.

##### Recomendações

- Manter como está enquanto não houver necessidade de configurações globais.
- Se precisar adicionar macros, validações customizadas ou bindings, adicionar aqui.
- Não adicionar lógica de negócio neste provider.
- Considerar criar providers adicionais caso este arquivo cresça significativamente.

#### 9.3.2 Filament\AdminPanelProvider

##### Localização

app/Providers/Filament/AdminPanelProvider.php

##### Namespace

App\Providers\Filament

##### Finalidade

Provider responsável por registrar e configurar o painel administrativo do Filament. Define recursos, páginas, widgets, temas, navegação e comportamentos específicos do painel admin.

##### Métodos

**panel()**

Método que retorna a configuração completa do painel Filament.

Responsabilidades:
- Define o ID e o path do painel.
- Registra todos os Resources do painel.
- Configura o tema visual (cores, fontes, ícones).
- Define a navegação e a estrutura do menu.
- Configura autenticação e autorização.
- Registra widgets e páginas customizadas.

Código:
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->colors([
            'primary' => Color::Amber,
        ])
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
        ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
        ->pages([
            Pages\Dashboard::class,
        ])
        ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
        ->widgets([
            Widgets\AccountWidget::class,
            Widgets\FilamentInfoWidget::class,
        ])
        ->middleware([
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisablePrefetchingMiddleware::class,
        ])
        ->authMiddleware([
            Authenticate::class,
        ]);
}

##### Observações Técnicas

- Usa o método `discoverResources()` para carregar automaticamente todos os Resources da pasta app/Filament/Resources.
- Usa o método `discoverPages()` para carregar automaticamente todas as Pages customizadas.
- Usa o método `discoverWidgets()` para carregar automaticamente todos os Widgets.
- Define a cor primária como Amber (padrão do Filament).
- Registra o Dashboard padrão como página inicial.
- Inclui widgets padrão: AccountWidget e FilamentInfoWidget.
- Middleware padrão do Laravel para sessão, CSRF, autenticação.
- Sem customizações adicionais de navegação, permissões ou recursos específicos do projeto.

##### Recomendações

- Adicionar Resources específicos do projeto conforme necessário (AdminUsers, Games, Stores, etc.).
- Customizar cores e tema conforme identidade visual do projeto.
- Adicionar Pages customizadas para dashboards ou relatórios específicos.
- Implementar políticas de autorização (Policies) para controlar acesso aos Resources.
- Considerar adicionar navegação customizada se o painel crescer.
- Adicionar widgets customizados para métricas importantes do projeto.

#### 9.3.2 Filament\AdminPanelProvider

##### Localização

app/Providers/Filament/AdminPanelProvider.php

##### Namespace

App\Providers\Filament

##### Finalidade

Provider responsável por registrar e configurar o painel administrativo do Filament. Define recursos, páginas, widgets, temas, navegação e comportamentos específicos do painel admin.

##### Métodos

**panel(Panel $panel): Panel**

Método que retorna a configuração completa do painel Filament.

Configurações aplicadas:

- **ID e Path**: painel identificado como 'admin', acessível via /admin
- **Autenticação**: usa tela de login padrão do Filament
- **Cor primária**: Amber (Color::Amber)
- **Descoberta automática**: Resources, Pages e Widgets são carregados automaticamente via discover*
- **Dashboard**: registra a página Dashboard padrão
- **Widgets padrão**: AccountWidget e FilamentInfoWidget
- **Grupos de navegação customizados**:
  - "Gestão de Clientes e Lojas"
  - "Configurações de Plataforma"
- **Middleware**: stack completo incluindo sessão, CSRF, autenticação e middlewares específicos do Filament
- **AuthMiddleware**: Authenticate::class

Código:
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->colors([
            'primary' => Color::Amber,
        ])
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
        ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
        ->pages([
            Dashboard::class,
        ])
        ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
        ->widgets([
            AccountWidget::class,
            FilamentInfoWidget::class,
        ])
        ->navigationGroups([
            NavigationGroup::make()
                ->label('Gestão de Clientes e Lojas'),
            NavigationGroup::make()
                ->label('Configurações de Plataforma'),
        ])
        ->middleware([
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ])
        ->authMiddleware([
            Authenticate::class,
        ]);
}

**boot(): void**

Método executado durante o bootstrap do provider. Registra um render hook customizado no Filament para injetar estilos CSS personalizados no head do painel.

Responsabilidades:
- Registra hook 'panels::head.end'
- Renderiza a view 'partials.custom-styles'
- Permite customização visual do painel sem alterar arquivos do Filament

Código:
public function boot(): void
{
    FilamentView::registerRenderHook(
        'panels::head.end',
        fn (): string => View::make('partials.custom-styles')->render()
    );
}

##### Observações Técnicas

- Usa descoberta automática (discover*) para carregar Resources, Pages e Widgets, evitando registro manual.
- Define dois grupos de navegação customizados para organizar o menu lateral do painel.
- Inclui AuthenticateSession no middleware, garantindo invalidação de sessão em caso de logout em outra aba.
- Registra render hook para injetar CSS customizado via view Blade (resources/views/partials/custom-styles.blade.php).
- Não define guard customizado, usando o padrão do Filament (provavelmente 'web' ou 'admin').
- Não define políticas de autorização ou permissões específicas.
- Não customiza a página de login (usa a padrão do Filament).
- Não define tema customizado (usa o tema padrão com cor primária Amber).

##### Recomendações

- Verificar se a view 'partials.custom-styles' existe e está corretamente configurada.
- Considerar adicionar guard customizado caso o painel use tabela de usuários específica (ex.: admin_users).
- Adicionar políticas de autorização (Policies) para controlar acesso aos Resources.
- Considerar criar tema customizado caso a identidade visual precise de mais ajustes além da cor primária.
- Adicionar widgets customizados para métricas importantes do projeto (ex.: total de lojas ativas, vendas do dia).
- Considerar adicionar mais grupos de navegação conforme o painel crescer (ex.: "Catálogo de Jogos", "Relatórios").
- Validar se todos os middlewares são necessários ou se algum pode ser removido para otimizar performance.

## 9.3 — Views (Blades) do Sistema

As Views são arquivos Blade (`.blade.php`) responsáveis pela interface pública do sistema — diferente das *Pages* do Filament, que pertencem ao painel administrativo.

Elas são usadas para:

- telas de registro e login
- páginas do marketplace
- fluxos públicos de interação
- layouts customizados
- páginas que servem usuários (jogadores e lojistas)

Elas não interagem com o Filament, e sim com Controllers e rotas do Laravel tradicional.

A seguir estão documentadas as views relacionadas ao fluxo de registro.

### 9.3.1 — Views de Registro (Cadastro de Usuários)

As views de registro ficam em `resources/views/auth` e implementam o fluxo público de criação de contas:

- escolha do tipo de conta
- cadastro de jogador
- cadastro de lojista

Todas são **customizadas**, não fazem parte do Laravel padrão e integram-se ao `RegisterController`.

A seguir, cada arquivo é documentado individualmente.

#### 9.3.1.1 — register-type.blade.php

**Localização:**  
`resources/views/auth/register-type.blade.php`

**Finalidade:**  
Página inicial do fluxo de cadastro. O usuário escolhe entre:

- Criar conta de Jogador
- Criar conta de Lojista

**Características:**

- HTML simples
- Sem formulários
- Apenas redireciona para `register.player` e `register.store`
- Faz parte do fluxo público

**Código Completo:**

@extends('layouts.app')

@section('content')
<div class="register-type-container">
    <h1>Crie sua Conta Multiverse Cards</h1>

    <p>Para continuar, escolha o tipo de conta:</p>

    <div class="options">
        <a href="{{ route('register.player') }}" class="option">
            <div class="icon">👤</div>
            <h2>Sou um Jogador</h2>
            <p>Compre, colecione e gerencie suas cartas.</p>
        </a>

        <a href="{{ route('register.store') }}" class="option">
            <div class="icon">🛍️</div>
            <h2>Sou um Lojista</h2>
            <p>Cadastre sua loja e venda para o Brasil inteiro.</p>
        </a>
    </div>
</div>
@endsection

#### 9.3.1.2 — register-player.blade.php

**Localização:**  
resources/views/auth/register-player.blade.php

**Finalidade:**  
Tela de cadastro completo do jogador (PlayerUser).  
Envia os dados para route('register.player') e cria um PlayerUser no banco.

**Principais Campos:**

- Nome / Sobrenome
- Login (nick)
- Email
- CPF/CNPJ (opcional)
- RG/ID (opcional)
- Data de nascimento
- Telefone
- Senha + confirmação

**Características Técnicas:**

- HTML/CSS puro
- Validação com $errors
- Formulário tradicional (sem Livewire ou Filament)
- Após criação, faz login automático no guard 'player'
- Redireciona para marketplace.home

**Código Completo:**

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Jogador - Multiverse Cards</title>
    <style>
        body { font-family: sans-serif; background-color: #f0f0f0; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); max-width: 700px; width: 90%; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .form-grid-full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9em; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;
        }
        .btn-submit { background-color: #ff9900; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 1em; margin-top: 20px; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #cc7a00; }
        .error { color: #ff3333; font-size: 0.8em; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center; color: #ff9900; font-size: 1.8em; margin-bottom: 20px;">Cadastro de Jogador</h1>

        @if ($errors->any())
            <div style="background-color: #fdd; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                Por favor, corrija os seguintes erros:
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.player') }}">
            @csrf

            <div class="form-grid">
                <div>
                    <label for="name">Nome</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="surname">Sobrenome</label>
                    <input type="text" id="surname" name="surname" value="{{ old('surname') }}" required>
                    @error('surname')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="login">Login (Nick)</label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required>
                    @error('login')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="document_number">CPF/CNPJ</label>
                    <input type="text" id="document_number" name="document_number" value="{{ old('document_number') }}">
                    @error('document_number')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="id_document_number">RG/ID (Opcional)</label>
                    <input type="text" id="id_document_number" name="id_document_number" value="{{ old('id_document_number') }}">
                    @error('id_document_number')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="birth_date">Data de Nascimento</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                    @error('birth_date')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="phone_number">Telefone (Celular)</label>
                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}">
                    @error('phone_number')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="password_confirmation">Confirmar Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">Criar Minha Conta</button>
        </form>
    </div>
</body>
</html>

#### 9.3.1.3 — register-store.blade.php

**Localização:**  
resources/views/auth/register-store.blade.php

**Tipo:**  
View Blade pública (frontend), fora do Filament.

---

### 📌 Propósito da View  
É a página que permite que um lojista crie:

1. **sua conta pessoal** (StoreUser)  
2. **a loja associada** (Store)

Tudo em um único formulário.

---

### 📌 Como funciona hoje  
O formulário é dividido em duas seções:

1. **Dados do proprietário**
   - nome, sobrenome  
   - login  
   - email  
   - documento  
   - telefone  
   - senha  

2. **Dados da loja**
   - nome fantasia  
   - slug  
   - CEP  
   - estado  

Quando o formulário é enviado:

- Vai para `RegisterController@registerStore`
- O controller executa uma **DB::transaction()**:
  - Cria a loja  
  - Cria o usuário da loja  
  - Conecta os dois (`owner_user_id` ↔ `current_store_id`)  
- Faz login no guard `store_user`
- Redireciona para `marketplace.home`

---

### 📌 Problemas existentes (legado)  
- Lógica pesada e duplicada no controller  
- Nenhum *Form Request* para validação  
- Nenhum *Service* para isolar a transação  
- Slug da loja pode ser conflitante  
- Falta de verificação de email  
- Falta de confirmação de identidade do lojista  
- Não suporta internacionalização  
- Falta integração com planos de assinatura  

---

### 📌 O que precisa mudar na v5  
#### ✔️ 1. **Separar criação do lojista em Services**  
Criar:

- RegisterStoreRequest  
- StoreRegistrationService  

Assim limpamos o controller e padronizamos.

---

#### ✔️ 2. **Adicionar lógica de planos**  
Na v5, a loja será criada com:

- plano básico / intermediário / premium  
- permissões conforme o plano  
- limites (ex.: estoque, prints customizados, layout premium)

A view deverá exibir isso.

---

#### ✔️ 3. **Adicionar onboarding após cadastro**  
Exemplo:

1. Escolher nome da loja  
2. Escolher tema / layout  
3. Upload do logo  
4. Configurar métodos de envio  
5. Configurar meios de pagamento  

A view atual é “vazia” nesse sentido.

---

#### ✔️ 4. **Slug mais seguro e automático**  
Hoje o lojista digita manualmente o slug.  
Na v5, será:

- gerado automaticamente  
- validado em tempo real  
- único no marketplace  

---

#### ✔️ 5. **Validação mais forte de documentos**  
Com suporte eventual a:

- CNPJ validado  
- CPF validado  
- Informação opcional de inscrição estadual  

---

#### ✔️ 6. **Possível redesign completo da view**  
A view atual é HTML puro.

Na v5, podemos usar:

- Tailwind  
- Blade Components  
- Steps (wizard)  
- Cards visuais  
- Componente de seleção de planos  

---

### 📌 O que pode ser mantido  
- Estrutura de “Criar lojista + loja juntos”  
- Autenticação automática após cadastro  
- Redirecionamento para dashboard da loja  

---

### 📌 O que deve desaparecer  
- CSS inline  
- layout fixo  
- slug manual  
- lógica pesada no controller  
- partes duplicadas de validação  

---

### 📌 Decisão v5  
**A view deve ser REFEITA**, mas mantendo o fluxo e propósito.  
O backend deve ser extraído para Services e Requests.  
O cadastro continuará existindo, mas mais inteligente e modular.

## 9.4 — Componentes de Visualização (Views) do Sistema

As visualizações detalhadas de cartas e conceitos utilizam um conjunto de componentes Blade customizados localizados em:

resources/views/filament/infolists/components

Esses componentes são responsáveis por:
- exibir lista de prints/edições por set;
- trocar a imagem exibida;
- controlar seleção de idioma;
- exibir legalidades;
- apresentar wrappers de largura total para melhorar o layout;
- compor o Infolist que forma a página “view” do Filament.

Essa estrutura foi criada inicialmente para o modelo legado (CardFunctionalities), mas passou a ser compatível com a implementação moderna (CatalogConcepts), permitindo que jogos diferentes exibam prints de maneira consistente.

### 9.4.1 — Controlador da View (ViewCardFunctionality / ViewCatalogConcept)

Essas classes são responsáveis por "dar vida" à experiência de visualização.

Elas implementam:

- seleção e troca de prints (`changePrint`)
- troca de idioma (`changeLanguage`)
- controle de paginação (`printPage`, `perPage`)
- recálculo das línguas disponíveis baseado no print selecionado
- carregamento da imagem correta (local ou remota)
- atualização do Infolist sem recarregar a página (Livewire)

Também agrupam prints por set, setcode, idioma e coleção, garantindo que a interface seja ordenada e intuitiva.

Essas páginas conectam todos os componentes Blade documentados abaixo.

Importante para a v5:
- Essas classes continuarão existindo como "controladores de experiência".
- A lógica deve ser extraída para Services mais enxutos.
- O padrão de estado interno (print selecionado, idioma, paginação) será mantido.

### 9.4.2 — Componente print-list-view

Responsável por exibir todas as impressões (prints) agrupadas por set, seguindo uma estrutura:

- nome do set
- código do set
- número da carta no set
- raridade
- preços (USD, EUR, TIX, BRL se houver)
- badge visual do set (Keyrune nos jogos que suportam)

Possui `wire:click="changePrint(id)"`, o que permite ao usuário trocar a imagem da carta instantaneamente.

Também inclui paginação manual, garantindo performance mesmo com centenas de edições.

Na v5:
- O agrupamento será reaproveitado.
- A UI será modernizada.
- Preços serão opcionais (depender do catálogo por jogo).
- Permitir esconder preços por permissão de usuário.

### 9.4.3 — Componente language-switcher-view

Renderiza uma lista de idiomas disponíveis para a carta/print.

Cada idioma aparece como um badge:
- Ativo: cor "primary"
- Inativo: cor "gray" ou neutra

Utiliza `wire:click="changeLanguage('xx')"` para atualizar dinamicamente o idioma exibido.

A lógica de idiomas válidos vem do controlador, que recalcula com base nas impressões disponíveis.

Na v5:
- Componente permanece.
- Pode receber customização por jogo (ex.: Pokémon tem idiomas diferentes de Magic).
- Pode suportar exibidores como bandeirinhas (opcional por loja).

### 9.4.5 — Componente full-width-wrapper

Simples wrapper que envolve seções do Infolist para:

- remover limites de largura
- permitir que a carta exibida fique em destaque
- manter proporção e responsividade em telas grandes

Ele resolve um problema do Filament, que por padrão estreita seções demais.

Na v5:
- Continuará existindo.
- Pode ser substituído por uma solução Tailwind custom se migrarmos layouts.

## 9.5 — Views do Marketplace

As views do marketplace são fundamentais para a experiência do usuário, permitindo que jogadores e lojistas interajam com o sistema de forma intuitiva. As duas principais views são:

- **home.blade.php**: página inicial do marketplace, onde os usuários podem navegar entre as lojas e jogos.
- **catalog.blade.php**: página que exibe a lista de cartas disponíveis, permitindo que os usuários filtrem e busquem itens específicos.

Essas views foram projetadas para serem responsivas e funcionais, utilizando componentes Blade para otimizar a experiência do usuário.

### 9.5.1 — home.blade.php

**Função principal:**  
Página inicial do marketplace, servindo como ponto de entrada para os usuários. 

**Como funciona hoje:**  
- Exibe uma lista de lojas disponíveis.
- Permite acesso rápido a diferentes jogos.
- Contém elementos de navegação e busca.

**O que está correto:**  
- A estrutura é clara e intuitiva.
- A navegação entre lojas e jogos é fluida.

**Pequenas melhorias recomendadas:**  
- Adicionar banners promocionais para destacar eventos ou novas coleções.
- Melhorar a responsividade em dispositivos móveis.
- Incluir uma seção de "Novidades" ou "Mais Vendidos" para engajar usuários.

**Na v5:**  
A view deve ser mantida, mas com melhorias visuais e funcionais para aumentar a conversão e a experiência do usuário.

### 9.5.2 — catalog.blade.php

**Função principal:**  
Exibe a lista de cartas disponíveis para compra, permitindo que os usuários filtrem e busquem itens específicos.

**Como funciona hoje:**  
- Apresenta cartas em um layout de grade.
- Permite filtragem por atributos como raridade, set e preço.
- Integra com o sistema de busca para facilitar a localização de cartas.

**O que está correto:**  
- A filtragem é eficiente e melhora a experiência do usuário.
- O layout é visualmente atraente e organizado.

**Pequenas melhorias recomendadas:**  
- Adicionar opções de ordenação (por preço, por popularidade, etc.).
- Incluir imagens de alta qualidade para cada carta.
- Implementar um sistema de comparação de cartas para ajudar na decisão de compra.

**Na v5:**  
A view deve ser otimizada para incluir novas funcionalidades, como comparação e ordenação, além de melhorar a apresentação visual das cartas.

### 9.5.3 — custom-styles.blade.php (Partials)

**Localização:**  
resources/views/partials/custom-styles.blade.php

**Tipo:**  
Partial Blade (incluída em layouts principais)

---

#### 📌 Propósito da View  
Carrega os arquivos CSS customizados necessários para exibir ícones de mana (Magic) e símbolos de sets (Keyrune) em todo o sistema.

Também aplica ajustes finos de estilo inline para garantir que os ícones de custo de mana sejam renderizados corretamente com sombras e espaçamento adequados.

---

#### 📌 Como funciona hoje  
A partial é incluída no `<head>` dos layouts principais (provavelmente `app.blade.php` ou similar).

Ela carrega:

1. **keyrune.css** → biblioteca de ícones de sets (ex.: símbolos de Foundations, Dominaria United, etc.)  
2. **mana.css** → biblioteca de ícones de custo de mana do Magic (ex.: {W}, {U}, {B}, {R}, {G}, {X}, etc.)  

E aplica um estilo inline customizado:

css .ms.ms-cost.ms-shadow { margin: 1px 0.7px !important; display: inline-block !important; }

Esse ajuste garante que os símbolos de mana apareçam alinhados e com sombra adequada.

---

#### 📌 Onde é usada  
- Páginas do marketplace (home, catalog)  
- Views de cartas (ViewCardFunctionality, ViewCatalogConcept)  
- Qualquer lugar que exiba custos de mana ou símbolos de sets  

---

#### 📌 Problemas existentes (legado)  
- **Hardcoded para Magic:** os arquivos CSS carregados são específicos de MTG.  
- **Não suporta outros jogos:** Pokémon, Yu-Gi-Oh! e outros não possuem bibliotecas equivalentes carregadas.  
- **Falta de condicional:** sempre carrega os arquivos, mesmo em páginas que não exibem cartas de Magic.  
- **CSS inline misturado:** o ajuste de sombra poderia estar em um arquivo `.css` separado.  

---

#### 📌 O que precisa mudar na v5  

##### ✔️ 1. **Carregar CSS condicionalmente por jogo**  
Exemplo:

<div class="widget code-container remove-before-copy"><div class="code-header non-draggable"><span class="iaf s13 w700 code-language-placeholder">blade</span><div class="code-copy-button"><span class="iaf s13 w500 code-copy-placeholder">Copiar</span><img class="code-copy-icon" src="data:image/svg+xml;utf8,%0A%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2216%22%20height%3D%2216%22%20viewBox%3D%220%200%2016%2016%22%20fill%3D%22none%22%3E%0A%20%20%3Cpath%20d%3D%22M10.8%208.63V11.57C10.8%2014.02%209.82%2015%207.37%2015H4.43C1.98%2015%201%2014.02%201%2011.57V8.63C1%206.18%201.98%205.2%204.43%205.2H7.37C9.82%205.2%2010.8%206.18%2010.8%208.63Z%22%20stroke%3D%22%23717C92%22%20stroke-width%3D%221.05%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%0A%20%20%3Cpath%20d%3D%22M15%204.42999V7.36999C15%209.81999%2014.02%2010.8%2011.57%2010.8H10.8V8.62999C10.8%206.17999%209.81995%205.19999%207.36995%205.19999H5.19995V4.42999C5.19995%201.97999%206.17995%200.999992%208.62995%200.999992H11.57C14.02%200.999992%2015%201.97999%2015%204.42999Z%22%20stroke%3D%22%23717C92%22%20stroke-width%3D%221.05%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%0A%3C%2Fsvg%3E%0A" /></div></div><pre id="code-bf9c02tq9" style="color:#111b27;background:#e3eaf2;font-family:Consolas, Monaco, &quot;Andale Mono&quot;, &quot;Ubuntu Mono&quot;, monospace;text-align:left;white-space:pre;word-spacing:normal;word-break:normal;word-wrap:normal;line-height:1.5;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-hyphens:none;-moz-hyphens:none;-ms-hyphens:none;hyphens:none;padding:8px;margin:8px;overflow:auto;width:calc(100% - 8px);border-radius:8px;box-shadow:0px 8px 18px 0px rgba(120, 120, 143, 0.10), 2px 2px 10px 0px rgba(255, 255, 255, 0.30) inset"><code class="language-blade" style="white-space:pre;color:#111b27;background:none;font-family:Consolas, Monaco, &quot;Andale Mono&quot;, &quot;Ubuntu Mono&quot;, monospace;text-align:left;word-spacing:normal;word-break:normal;word-wrap:normal;line-height:1.5;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-hyphens:none;-moz-hyphens:none;-ms-hyphens:none;hyphens:none"><span>@if($game-&gt;slug === &#x27;magic-the-gathering&#x27;)
</span>    &lt;link href=&quot;{{ asset(&#x27;css/keyrune.css&#x27;) }}&quot; rel=&quot;stylesheet&quot; /&gt;
<!-- -->    &lt;link href=&quot;{{ asset(&#x27;css/mana.css&#x27;) }}&quot; rel=&quot;stylesheet&quot; /&gt;
<!-- -->@endif
<!-- -->
<!-- -->@if($game-&gt;slug === &#x27;pokemon-tcg&#x27;)
<!-- -->    &lt;link href=&quot;{{ asset(&#x27;css/pokemon-symbols.css&#x27;) }}&quot; rel=&quot;stylesheet&quot; /&gt;
<!-- -->@endif
</code></pre></div>

Isso evita carregar CSS desnecessário.

---

##### ✔️ 2. **Mover estilos inline para arquivo CSS**  
O ajuste de `.ms.ms-cost.ms-shadow` deveria estar em:

public/css/custom-game-styles.css

E ser carregado junto com os demais.

---

##### ✔️ 3. **Criar bibliotecas de ícones para outros jogos**  
Atualmente só temos:

- Keyrune (sets de Magic)  
- Mana (custos de Magic)  

Na v5, precisamos de:

- **Pokémon:** símbolos de tipos (Fire, Water, Grass, etc.)  
- **Yu-Gi-Oh!:** símbolos de atributos (DARK, LIGHT, EARTH, etc.)  
- **Lorcana, Flesh and Blood, etc.:** símbolos específicos de cada jogo  

Essas bibliotecas podem ser criadas como webfonts customizadas ou SVGs inline.

---

##### ✔️ 4. **Lazy loading de CSS**  
Para melhorar performance, podemos carregar esses arquivos apenas quando necessário:

<div class="widget code-container remove-before-copy"><div class="code-header non-draggable"><span class="iaf s13 w700 code-language-placeholder">blade</span><div class="code-copy-button"><span class="iaf s13 w500 code-copy-placeholder">Copiar</span><img class="code-copy-icon" src="data:image/svg+xml;utf8,%0A%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2216%22%20height%3D%2216%22%20viewBox%3D%220%200%2016%2016%22%20fill%3D%22none%22%3E%0A%20%20%3Cpath%20d%3D%22M10.8%208.63V11.57C10.8%2014.02%209.82%2015%207.37%2015H4.43C1.98%2015%201%2014.02%201%2011.57V8.63C1%206.18%201.98%205.2%204.43%205.2H7.37C9.82%205.2%2010.8%206.18%2010.8%208.63Z%22%20stroke%3D%22%23717C92%22%20stroke-width%3D%221.05%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%0A%20%20%3Cpath%20d%3D%22M15%204.42999V7.36999C15%209.81999%2014.02%2010.8%2011.57%2010.8H10.8V8.62999C10.8%206.17999%209.81995%205.19999%207.36995%205.19999H5.19995V4.42999C5.19995%201.97999%206.17995%200.999992%208.62995%200.999992H11.57C14.02%200.999992%2015%201.97999%2015%204.42999Z%22%20stroke%3D%22%23717C92%22%20stroke-width%3D%221.05%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%0A%3C%2Fsvg%3E%0A" /></div></div><pre id="code-nzcdqm8r1" style="color:#111b27;background:#e3eaf2;font-family:Consolas, Monaco, &quot;Andale Mono&quot;, &quot;Ubuntu Mono&quot;, monospace;text-align:left;white-space:pre;word-spacing:normal;word-break:normal;word-wrap:normal;line-height:1.5;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-hyphens:none;-moz-hyphens:none;-ms-hyphens:none;hyphens:none;padding:8px;margin:8px;overflow:auto;width:calc(100% - 8px);border-radius:8px;box-shadow:0px 8px 18px 0px rgba(120, 120, 143, 0.10), 2px 2px 10px 0px rgba(255, 255, 255, 0.30) inset"><code class="language-blade" style="white-space:pre;color:#111b27;background:none;font-family:Consolas, Monaco, &quot;Andale Mono&quot;, &quot;Ubuntu Mono&quot;, monospace;text-align:left;word-spacing:normal;word-break:normal;word-wrap:normal;line-height:1.5;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-hyphens:none;-moz-hyphens:none;-ms-hyphens:none;hyphens:none"><span>@push(&#x27;styles&#x27;)
</span>    &lt;link href=&quot;{{ asset(&#x27;css/keyrune.css&#x27;) }}&quot; rel=&quot;stylesheet&quot; /&gt;
<!-- -->@endpush
</code></pre></div>

Assim, apenas páginas que realmente exibem cartas carregam os ícones.

---

#### 📌 O que pode ser mantido  
- A estrutura de partial (incluída no layout principal)  
- O carregamento de Keyrune e Mana para Magic  
- O ajuste de sombra (mas movido para arquivo CSS)  

---

#### 📌 O que deve desaparecer  
- CSS inline dentro da blade  
- Carregamento incondicional (sempre carregar, independente do jogo)  
- Falta de suporte a outros jogos  

---

#### 📌 Decisão v5  
**A partial deve ser REFATORADA** para:

1. Carregar CSS condicionalmente por jogo  
2. Mover estilos inline para arquivo CSS separado  
3. Adicionar suporte a bibliotecas de ícones de outros jogos  
4. Implementar lazy loading onde possível  

Mas a estrutura de "partial incluída no layout" permanece.

## 9.6 — Rotas do Sistema (Routes)

As rotas do sistema definem os pontos de entrada da aplicação, conectando URLs a controllers e actions específicas.

No Multiverse Cards, as rotas estão divididas em:

- **web.php**: rotas públicas (marketplace, catálogo, registro) e autenticadas (dashboard)
- **console.php**: comandos Artisan customizados (atualmente apenas o padrão "inspire")

O Filament gerencia suas próprias rotas internamente, então não há necessidade de declará-las manualmente em `web.php` (exceto customizações específicas).

A seguir, cada arquivo de rotas é documentado individualmente.

### 9.6.1 — web.php

**Localização:**  
routes/web.php

**Tipo:**  
Arquivo de rotas HTTP do Laravel

---

#### 📌 Propósito do Arquivo  
Define todas as rotas públicas e autenticadas do sistema, incluindo:

- fluxo de registro (jogador e lojista)
- home do marketplace
- catálogo de cartas por jogo

---

#### 📌 Grupos de Rotas Existentes

##### **1. Rotas de Registro**

Fluxo personalizado que separa Jogador e Lojista:

- `/register` → escolha do tipo de conta  
- `/register/player` → formulário e POST para criar PlayerUser  
- `/register/store` → formulário e POST para criar StoreUser + Loja  

**Pontos positivos:**  
- Named routes consistentes  
- GET e POST bem separados  
- Fluxo claro para dois tipos de usuário  

**Pequenos ajustes recomendados:**  
- Colocar tudo dentro de `Route::prefix('register')`  
- Adicionar `middleware: guest`  
- Mover validações para *Form Requests*  

---

##### **2. Rota Home (Marketplace)**

- `/` → Página inicial de jogos do marketplace (`marketplace.home`)

**Correto:**  
- Bom para SEO  
- Padrão simples e direto  

**Recomendação:**  
- Cache leve para lista de jogos ativos  

---

##### **3. Rota do Catálogo (Por Jogo)**

- `/{game:url_slug}/cards` → Catálogo público de cartas  

**Pontos positivos:**  
- Usa Route Model Binding via `url_slug`  
- Nome claro (`marketplace.catalog`)  
- Estrutura limpa e extensível  

**Pequenas melhorias:**  
- Garantir que o jogo esteja ativo  
- Cachear catálogo por jogo  
- Adicionar middleware para analytics  

---

#### 📌 O que Ainda Não Existe (Mas É Necessário para a v5)

##### **1. Rotas de Login / Logout**
Suporte a múltiplos guards: player, store_user, admin.

##### **2. Dashboards**
Rotas autenticadas para jogador e lojista.

##### **3. Carrinho e Checkout**
Fluxo completo de compra.

##### **4. Perfil de Usuário**
Editar nome, email, senha, preferências.

##### **5. Rotas de Lojas**
Acessar loja via `/{store:url_slug}` e catálogo filtrado por loja.

---

#### 📌 O que Deve Permanecer  
- Estrutura atual do marketplace  
- Named routes  
- Rota home → catálogo  

---

#### 📌 O que Seria Bom Ajustar  
- Agrupar rotas por área  
- Adicionar middlewares específicos  
- Separar web.php em múltiplos arquivos (`marketplace.php`, `auth.php`, etc.)  

---

#### 📌 Decisão para a v5  
O arquivo **não será reescrito**, apenas **expandido** e organizado.  
Ele já serve como base sólida.

### 9.6.2 — console.php

**Localização:**  
routes/console.php

**Tipo:**  
Arquivo de rotas para comandos Artisan (console)

---

#### 📌 Propósito do Arquivo  
O arquivo `console.php` registra comandos Artisan personalizados.  
Ele é carregado somente no contexto de linha de comando e não afeta rotas HTTP.

---

#### 📌 Conteúdo Atual  
O arquivo contém o comando padrão do Laravel:

- **`php artisan inspire`** → imprime uma frase motivacional aleatória.

Esse comando é apenas um exemplo deixado pelo framework.

---

#### 📌 O que está correto  
- A estrutura do arquivo está limpa e segue o padrão recomendado.
- Não há lógica desnecessária.
- O comando existente não causa nenhum problema.

---

#### 📌 Limitações do estado atual  
Apesar de correto, o arquivo ainda **não registra nenhum comando real do Multiverse Cards**, como:

- sincronização de catálogos,
- limpeza de imagens,
- manutenção periódica,
- relatórios,
- preenchimento de banco,
- integração com APIs externas.

Ele está funcional, mas vazio em termos de utilidade prática.

---

#### 📌 Pequenas melhorias sugeridas (para a v5)

##### ✔️ Remover o comando `inspire`
Não agrega nada ao projeto e só ocupa espaço.

##### ✔️ Registrar comandos úteis como:
- `cards:sync {game}` — sincronização de cartas por jogo  
- `prints:cleanup` — limpeza de imagens órfãs  
- `system:maintenance` — tarefas automáticas  
- `reports:daily` — geração de relatórios  

**Exemplo sugerido:**  
(Este exemplo é apenas ilustrativo — não será colado no projeto aqui.)

php Artisan::command('cards:sync {game}', function ($game) { // Lógica de sincronização })->purpose('Sincroniza cartas de um jogo');

---

#### 📌 O que deve permanecer  
- O arquivo em si  
- O modelo básico `Artisan::command()`  
- O uso desse arquivo para registrar comandos que não precisam de classes dedicadas

---

#### 📌 Decisão final  
O `console.php` **não deve ser removido**, apenas **expandido** no futuro conforme a necessidade do projeto.  
Hoje ele está correto, apenas minimalista.
