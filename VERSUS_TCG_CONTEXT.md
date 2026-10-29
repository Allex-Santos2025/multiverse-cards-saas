# VERSUS TCG — ARQUITETURA, REGRAS DE NEGÓCIO E CONTEXTO TÉCNICO

> Documento de referência de arquitetura para uso como contexto persistente em IDEs (VS Code, Cursor, Copilot, Windsurf) ou System Prompt de LLMs.

---

## 1. DIRETRIZES FUNDAMENTAIS & REGRAS DE OURO

1. **Escopo Estrito de Refatoração:**
   - NUNCA modificar ficheiros ou trechos não solicitados.
   - NUNCA executar "limpeza de código", otimizações arbitrárias de estilo ou remoção de métodos existentes, especialmente em componentes Livewire e views Blade.
   - Ficheiros enviados completos servem estritamente para contexto de navegação e dependências.

2. **Arquitetura Agnóstica a Card Games:**
   - A plataforma gere múltiplos Trading Card Games (Magic: The Gathering, Battle Scenes, Pokémon, Yu-Gi-Oh!, etc.).
   - NUNCA acoplar atributos específicos de um jogo (ex.: mana cost, CMC, formatos MTG como Commander/Modern) diretamente em views Blade ou componentes Livewire genéricos.
   - Utilize a camada de **Presenters** (`App\Presenters\*`) e **Enums** para abstrair particularidades mecânicas de cada franquia.

3. **Sistema Visual & Separação de Ambientes:**
   - **Área Pública / Fichário / Visualizador de Acervo:** Tema escuro imersivo (`#161b22`, `#0d1117`, bordas `#30363d`, acentos em laranja/ciano).
   - **Área do Jogador / Painel da Loja (`Minha Coleção`):** Layout claro (`bg-white` / cinzento suave), permitindo herança visual da marca (logótipo e paleta de cores) do lojista parceiro. Não escurecer ou aplicar temas dark na área interna do cliente.

---

## 2. STACK TECNOLÓGICA & INFRAESTRUTURA

- **Backend:** Laravel 12.x (PHP 8.3+)
- **Reatividade Frontend:** Livewire 3.x com Alpine.js
- **Estilização:** Tailwind CSS & Phosphor Icons (`ph-*`)
- **Mecanismo de Busca:** Meilisearch
- **Servidor & Deploy:** Linux (CyberPanel / OpenLiteSpeed)
- **Persistência & Mídias:** MySQL / Eloquent ORM + Storage Local/Público

---

## 3. MODELAGEM DE DADOS & ENTIDADES PRINCIPAIS

### 3.1. Catálogo Global (Agnóstico)
- **`CatalogConcept`:** Conceito/carta canónica abstrata (ex.: nome canónico, texto de regras oracle/universal).
- **`CatalogPrint`:** Versão física específica de um card:
  - `id`: Chave primária.
  - `concept_id`: FK para o conceito.
  - `set_id`: FK para a coleção (`CatalogSet`).
  - `collector_number`: Número na edição (ex.: "152").
  - `language_code`: Idioma da impressão física (ex.: "en", "pt").
  - `printed_name`: Nome conforme impresso no idioma específico.
  - `image_path`: Imagem oficial do catálogo.
  - `specific_type` / `specific_id`: Ligação polimórfica com o modelo do jogo específico (ex.: `App\Models\Games\Magic\MtgPrint`).

### 3.2. Acervo Pessoal do Jogador
- **`PlayerCollectionItem`:** Carta real pertencente a um utilizador:
  - `id`: Identificador da cópia física.
  - `player_user_id`: Utilizador proprietário.
  - `catalog_print_id`: FK para a impressão exata do catálogo.
  - `condition`: Qualidade da carta física (`NM`, `SP`, `MP`, `HP`, `D`).
  - `language`: Idioma do exemplar do utilizador.
  - `quantity`: Quantidade total desta cópia sob a mesma condição.
  - `acquisition_price`: **Preço real de compra/custo pago pelo jogador** (campo decimal, ex.: "30.00"). Não utilizar a chave `price` direta.
  - `extras`: Array/JSON com atributos adicionais (`foil`, `signed`, `altered`, `promo`, etc.).
  - `real_photos`: Array/JSON de paths de fotos digitalizadas/reais da carta do próprio jogador.

### 3.3. Fichários Virtuais (Binders)
- **`PlayerBinder`:** Pasta temática criada pelo jogador (`pocket_layout`: "3x3", "2x2", "4x4"; `current_sheets_count`, `cover_material`, `strap_color`, `is_locked_sheets`).
- **`PlayerBinderSlot`:** Posição ocupada dentro do fichário:
  - `binder_id`: FK para o fichário.
  - `page_number`: Número da folha física/página (1-indexed).
  - `slot_position`: Posição na grelha daquela folha (0 a 8 para 3x3).
  - `player_collection_item_id`: FK para a cópia física no acervo.
  - `quantity_in_slot`: Quantidade de cópias físicas empilhadas no mesmo bolso (1 a 4).

---

## 4. COMPONENTES E FLUXOS CHAVE

### 4.1. `FicharioViewer.php` (Lobby / Partilha Pública)
- **Cena 1 (Capa Fechada):** Renderiza o fichário fechado com textura, cantoneiras e botão de abertura flutuante não-cortado (`aspect-[1/1.35]`, sem `overflow-hidden` externo).
- **Cena 2 (Miolo Aberto):**
  - Modos: Livro (`book`) e Foco (`zoom`).
  - Central Rings (Lombada): Mantido em `relative z-0` para não sobrepor cartas ampliadas.
  - Páginas e Slots: `hover:z-30` no contentor da página e `hover:z-50` no slot ativo, projetando zoom (`scale-[2.3]`) legível sobre a lombada e slots adjacentes.
  - Métodos interativos:
    - `openCover()` / `closeCover()`
    - `closeModals()`: Limpa seleções e fecha gaveta do acervo e modal de ajuste.
    - `handleSlotClick($slotKey)`: Abre a gaveta de busca ou o ajuste de bolso.
    - `assignCardToSlot($itemId)`: Vincula carta do acervo ao slot.
    - `adjustCopies($delta)`: Ajusta cópias físicas no bolso (máx. 4).
    - `removeCardFromSlot()`: Retira o card e devolve ao acervo geral.

### 4.2. Dossiê de Detalhes da Carta (`CardDossier`)
- **Identificação Estrita:** Focado exclusivamente no `CatalogPrint` ativo (set + collector number + idioma), sem agrupar versões de outras línguas/edições na mesma janela.
- **Divisão em 3 Blocos:**
  1. *Visual & Digitalização Física:* Exibição da foto real digitalizada (`real_photos`) ou ilustração oficial do catálogo, com upload de imagem.
  2. *Dossiê Técnico:* Nome, tipo, atributos mecânicos e legalidade em formatos via Presenter dinâmico.
  3. *Mercado vs. Meu Acervo:*
     - Tabela de mercado por qualidade (`NM`, `SP`, `MP`, `HP`, `D`) com fallback: Loja Local -> Última Cotação -> Menor entre Cardmarket e TCGPlayer.
     - Lista das cópias do jogador com comparativo financeiro: **Valor Pago** (`acquisition_price`) vs. **Cotação Atual**, com tag de rendimento percentual (+/-%) e gestão de extras do enum.

---

## 5. REGRAS DE PRECIFICAÇÃO & AUDITORIA

- Ao renderizar valores de cartas do acervo do jogador, verificar prioritariamente:
  `$item->acquisition_price ?? $item->price ?? 0`
- A foto real (`real_photos`) é opcional. Se não cadastrada, o sistema faz fallback gracioso para o `image_path` do catálogo oficial.
