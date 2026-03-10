# VERSUS TCG — CONTROLE DE VERSÃO OFICIAL

Sistema anteriormente chamado **Multiverse Cards SaaS**.  
A partir de 21/12/2025, o nome oficial passa a ser:

# **VERSUS TCG**

Este arquivo documenta a versão atual do sistema, o estágio de desenvolvimento e o histórico de evolução.

---

## 🔰 Informações Gerais

- **Nome do Projeto:** Versus TCG  
- **Domínio Final (planejado):** https://versustcg.com  
- **Domínio de Desenvolvimento:** https://dev.*.com.br  (alterado para o dominio panejado)
- **Stack:** Laravel 12 + Filament v4.18 
- **Estado Atual:** Desenvolvimento Inicial  
- **Fase Atual:** Alpha  

---

## 📌 Versão Atual

**Versão:** `alpha v0.0.1`  
**Data:** 21/12/2025  
**Descrição da Versão:**  
- Renomeado oficialmente o projeto para **Versus TCG**  
- Criado Roadmap V5  
- Criado Arquivo de Sucessos  
- Definido sistema de versionamento semântico interno  
- Preparação inicial para implementação do novo front-end moderno  

---

**Versão:** `alpha v0.0.2`  
**Data:** 20/01/2026   
**Descrição da Versão:** 
**Branding & Domínio:** 
- Renomeado oficialmente para **Versus TCG** e migração para `versustcg.com.br`.
**Banco de Dados (Refatoração):** 
- Recriação completa da estrutura de dados com foco em escalabilidade e limpeza de arquitetura.
- Implementação da tabela `store_users` (Lojistas).
- Implementação da tabela `stores` (Dados da Loja).
**Funcionalidades:** 
- Criada rotina de cadastro de lojistas via Wizard Multi-step.
- Integração com Brevo para envio de e-mails transacionais.
- Sistema de verificação de conta com redirecionamento dinâmico.
**Páginas Adicionadas:**
- Home, Planos, Registro e Fallback de Aguarde (Placeholder).  
**Segurança:**
- Segurança: Implementada validação de e-mail via Signed URLs (URLs assinadas) e isolamento de autenticação por Guards.  

---

**Versão:** `alpha v0.0.3`  
**Data:** 26/01/2026   
**Descrição da Versão:** 
- Implementação de portais de autenticação centralizados, segregação de segurança via Multi-Guards e expansão da malha de redirecionamento (Fallbacks).
**Arquitetura de Autenticação:** 
- Implementação do Guard `player` e Provider `player_users` para isolamento total entre Clientes e Lojistas.
- Refatoração da lógica de login para suporte híbrido: identificação automática via E-mail ou Nickname (resolução do conflito de colunas do banco de dados).
**Funcionalidades (UX/UI):** 
**Modal Unificado de Cadastro (Domingo):**
- Consolidação do fluxo de registro de lojistas com o novo sistema de registro de jogadores em um único portal de entrada.
**Modal de Login Centralizado (Hoje):**
- Criação da interface de acesso para jogadores com suporte ao localizador de lojas (Slug) para lojistas.
**Trava de Centro UI:**
- Implementação de regras de posicionamento absoluto (translate-50%) para garantir que os modais permaneçam centralizados em qualquer resolução.
**Páginas Adicionadas:**
- Tela de destino pós-login para validação de dados e boas-vindas.
- Estrutura de destino para a trilha de torneios e competições
- Lógica implementada para conduzir lojistas ao seu ambiente de gestão pré-existente e jogadores à sua área logada  
**Segurança:**
- Finalização da estabilização de rede e conectividade SSH para desenvolvimento remoto seguro (Concluído no Sábado).
- Implementação de session()->regenerate() pós-autenticação para prevenção de ataques de fixação de sessão.  

---

**Versão:** `alpha v0.0.4`  
**Data:** 01/02/2026   
**Descrição da Versão:** 
- Expansão do Ecossistema Versus TCG com lançamento do núcleo operacional da Loja (Dashboard v0.1.3) e centralização de logs.
**Branding & Domínio:**
- Consolidação da identidade visual e estabilização do ambiente de desenvolvimento em `versustcg.com.br`
**Banco de Dados (Refatoração):**
- Implementação das tabelas de auditoria e comunicação: `changelogs` e `changelog_user_reads`
- Otimização de relacionamentos entre `store_users` e as novas entidades de notificações.
**Funcionalidades (UX/UI):** 
**Funcionalidades (Loja / Dashboard):**
- v0.1.0: Lançamento da interface base do Dashboard (Engine da Loja) com sistema de temas (Light/Dark).
- v0.1.1: Implementação da primeira funcionalidade operacional: `Central de Logs do Sistema`.
- v0.1.2: Implementação da segunda funcionalidade: `Hub de Novidades` e `Sininho de Notificações` com contador dinâmico.
- v0.1.3: Correção de bugs de interface (Z-Index), padronização de ícones e lançamento do sistema de Leitura Inteligente (Dedução automática).
**Páginas Adicionadas:**
- Hub de Novidades (Listagem).
- Detalhe da Novidade (Leitura em Markdown).
- Dashboard Index (Home do lojista).
- Painel de Logs.  
**Segurança:**
- Navegação: Implementação de camadas de profundidade (Z-Index) para evitar sobreposição de menus suspensos.
- Autenticação: Refatoração da lógica de Logout para redirecionamento inteligente baseado no Slug da loja.  

---

**Versão:** `alpha v0.0.5`  
**Data:** 08/02/2026   
**Descrição da Versão:** 
- Lançamento do módulo crítico de `Gestão de Estoque em Massa (Bulk Manager)` para Magic: The Gathering.
**Branding & Domínio:**
- Refatoração completa da performance de renderização de listas longas e implementação de arquitetura híbrida (Visualização/Importação).
**Banco de Dados (Refatoração):**
- Otimização de Queries: Implementação de Eager Loading `(with(['concept', 'set', 'stockItems']))` para reduzir o número de consultas ao carregar a lista de cartas.
- Model StockItem: Configuração de `casts => array` para a coluna `extras`, permitindo salvamento correto de dados JSON (Foil, Etched, Promo) no banco.
- Sanitização de Dados: Implementação de lógica no back-end para tratamento de decimais (conversão automática de `0,25` para `0.25`) e prevenção de "registros fantasmas" (não salva linhas vazias/zeradas).
**Funcionalidades (UX/UI):** 
- Edição em Lote (Batch Edit): Substituição do `wire:model` linha a linha por formulário nativo HTML, permitindo salvar 50+ itens simultaneamente sem travamentos no navegador.
**Páginas Adicionadas:**
**Dropdown de Extras "Pixel Perfect":**
- Implementação de Teleport via Alpine.js para menus flutuantes que ignoram as barreiras da tabela (`overflow`).
- Lógica de Posicionamento Inteligente: O menu detecta o fim da tela e abre para cima ou para baixo automaticamente.
- Hidden Scroll: Barra de rolagem invisível para manter a estética limpa ("Apple-like").
**Filtros Avançados:**
- Adição do filtro lógico "Minha Loja", que exibe apenas cartas com estoque positivo (> 0)
- Preservação de estado dos filtros (Busca, Edição, Cor) durante a navegação entre páginas.
**Funcionalidades (Loja / Dashboard):**
- v0.1.4: Lançamento do Menu `Estoque` onde esse Módulo de gestão de estoque se encontra.
- Lógica de salvamento inteligente (`updateOrCreate`) que preserva o idioma original da carta via `Input Hidden`.
- Sistema de paginação fluido integrado ao Livewire.
- Ordenação dinâmica por Preço, Quantidade, Nome (PT/EN) e Numeração (Collector Number).
**Correções e Melhorias:**
- Visual: Correção do "z-index" no menu de Extras para sobrepor o cabeçalho e rodapé.
- Usabilidade: Adição de "padding" (colchão de ar) no final da tabela para permitir que o último item seja editado confortavelmente sem ser cortado pelo rodapé.
**Segurança:**
- Validação no Back-end para garantir que apenas variantes existentes na loja sejam atualizadas ou criadas corretamente.  

---

**Versão:** `alpha v0.0.6`  
**Data:** 22/02/2026   
**Descrição da Versão:** 
- Correção crítica de vazamento de dados (Multi-tenant) e otimização de performance no carregamento do Inventário Geral (Magic: The Gathering).
**Branding & Domínio:**
- Refinamento da arquitetura Multi-tenant do sistema, assegurando o isolamento absoluto das operações de banco de dados entre diferentes lojistas ativos no marketplace.
**Banco de Dados (Refatoração):**
- Otimização de Queries (Filtros): Remoção do aninhamento profundo (`whereHas('concept.game')`) e substituição por resolução prévia do ID do jogo (`$gameId`). Isso eliminou múltiplos `EXISTS` no banco, cortando o Full Table Scan em cascata na tabela de 530+ mil registros.
- Otimização de Queries (Ordenação): Remoção de cálculos pesados de Expressão Regular (`regexp_replace`) na coluna de numeração do colecionador (`collector_number`), substituindo pela ordenação nativa para devolver a velocidade à tela.
**Funcionalidades (UX/UI):** 
- Estabilidade de Interface: Manutenção da contagem exata do catálogo em tempo real para o lojista (`$items->total()`), revertendo tentativas de paginação simples que quebravam o contrato com a view Blade.
**Correções e Melhorias:**
- Bugfix Crítico (Erro 500): Resolução da exceção `BadMethodCallException` causada pela incompatibilidade de métodos de paginação com o contador visual do layout.
**Segurança:**
- Isolamento de Lojas (Tenant Context): Correção de vulnerabilidade lógica na função `mount()` do Livewire. O identificador da loja logada foi alterado de `store_id` (que permitia fallbacks perigosos para o ID 1) para a coluna correta de operação `current_store_id`. Isso garante que nenhuma loja tenha acesso de leitura ou gravação ao estoque de terceiros.  

---

**Versão:** `alpha v0.0.7`  
**Data:** 22/02/2026   
**Descrição da Versão:** 
- Lançamento do módulo de `Importação de Inventário via Arquivo (.txt / .csv)` com suporte a Drag & Drop e processamento agnóstico.
**Branding & Domínio:**
- Implementação de arquitetura de leitura local via `FileReader` (JavaScript/Alpine.js), eliminando a necessidade de uploads temporários no servidor e aumentando a privacidade dos dados do lojista.
**Banco de Dados (Refatoração):**
- Integração de Catálogo Agnóstico: Refatoração da lógica de busca para conectar arquivos externos ao `CatalogPrint` através do `set_code` e `printed_name`, garantindo que o estoque seja vinculado corretamente à identidade global da carta.
- Persistência Segura: Implementação de `DB::beginTransaction` e `Rollback` no processo de importação em lote para garantir integridade total do banco em caso de falha em linhas específicas.
**Funcionalidades (UX/UI):** 
- Drag & Drop Inteligente: Área de upload receptiva que identifica o arraste de arquivos, com feedback visual de estado (borda dashed/highlight) via Alpine.js.
- Processamento Instantâneo: Leitura automática do conteúdo do arquivo para o campo de edição (`textarea`), permitindo que o lojista revise ou corrija dados antes da gravação final.
**Páginas Adicionadas:**
**Importação de Estoque:**
- Aba de Importação de Estoque: Interface dedicada com dicionário de termos (Qualidade/Idioma) e exemplo de formatação integrada ao Dashboard.
**Funcionalidades (Loja / Dashboard):**
- Regex Universal: Motor de extração de dados configurado para aceitar siglas de edições de 2 a 5 caracteres (`[A-Z0-9]{2,5}`), preparando o sistema para expansão de múltiplos TCGs.
- Regras de Negócio em Lote: Integração do seletor de "Extras do Lote" (Foil, Etched, etc.) e limitador de quantidade (Regra de 4 unidades) aplicados automaticamente durante o processamento da lista.
- Sincronização de Abas: Implementação de `dispatch` para alternância automática entre a aba de importação e a lista de estoque após o sucesso da operação.
**Segurança:**
- Tenant Isolation: Validação rigorosa do `current_store_id` em cada inserção do `updateOrCreate`, impedindo que importações massivas afetem ou visualizem estoques de outras lojas do marketplace.  

---

**Versão:** `alpha v0.0.8`  
**Data:** 06/03/2026  
**Descrição da Versão:** - Lançamento do módulo de `Exportação em Massa .TXT` para inventário de loja, complementado por estabilização estrutural do motor de importação e performance de banco de dados.
**Funcionalidades (Loja / Dashboard):**
- Módulo de Exportação (Export Manager): Implementação de rotina de extração de dados que converte o estoque da loja autenticada (`current_store_id`) para o formato padrão universal de TCGs (`Qtd Name [Set] Cond Lang (Extras) Price`).
- Download Dinâmico: Geração de arquivos `.txt` construídos em memória (sem gravar arquivos temporários no servidor), forçando o download direto no navegador do lojista.
- Isolamento de Exportação: A query de exportação respeita rigidamente o escopo Multi-tenant, garantindo que o arquivo gerado contenha estritamente os itens com quantidade maior que zero pertencentes à loja logada.
**Banco de Dados (Refatoração):**
- Preparação para Carga Pesada (Export/Import): Criação de índices no MariaDB (`idx_catalog_prints_name`, `idx_catalog_prints_set_number`, `idx_stock_items_price`, `idx_stock_items_qty`) para garantir que a varredura do banco na hora de exportar milhares de cartas seja executada em milissegundos.
- Paginação Global e Joins: Refatoração da query principal da lista de estoque para ordenar os itens globais do catálogo via SQL antes da paginação, permitindo que a visualização da tela seja idêntica à ordem do arquivo exportado.
**Correções e Melhorias:**
- Estabilização do Interpretador Híbrido (`saveForm`): Atualização da lógica de salvamento para decodificar chaves de array mistas (prefixos `p` para novos e `s` para itens já em estoque), evitando erros de colisão e permitindo edições limpas pós-importação.
- Tratamento Silencioso no Importador: Remoção de interrupções de debug (dumps residuais) e adição de *Graceful Degradation* — linhas com formatos inválidos ou cartas inexistentes no upload não quebram mais o processamento em lote, apenas geram log de erro visual na tela.

**Filtros Avançados:**
- Restauração de Integridade ("Minha Loja"): Correção do filtro isolado usando o relacionamento `whereHas('stockItems')` para garantir que a visualização em tela espelhe perfeitamente a base que será enviada para o arquivo de exportação.

---

**Versão:** `alpha v0.0.9`  
**Data:** 10/03/2026   
**Descrição da Versão:** - Lançamento do módulo de `Gestão de Produtos Oficiais` (Selados e Acessórios) integrado ao Dashboard e otimização profunda do motor de processamento e salvamento em lote para alta performance.

### Banco de Dados (Refatoração & Performance):
- **Escudo de Persistência (Preço > 0):** Nova regra de negócio estrutural que exige um valor monetário válido para novas inserções. Isso impede o salvamento acidental de dezenas de itens não preenchidos do catálogo global diretamente para o estoque da loja durante salvamentos em lote.
- **Otimização de Queries em Lote (`isDirty`):** Implementação de checagem inteligente de estado no motor de salvamento. Operações de `UPDATE` no banco de dados agora só são disparadas se houver alterações reais nos campos (preço, quantidade, etc.), economizando processamento massivo.
- **Leitura Transparente (`withoutGlobalScopes`):** Refatoração nas queries de listagem para contornar filtros restritivos do ORM (Laravel), garantindo a recuperação e visualização exata da base de dados sem filtros ocultos.

### Funcionalidades (UX/UI & Melhorias):
- **Retenção de Estoque Zero:** Alteração na regra de ciclo de vida do inventário. Itens que chegam a 0 de quantidade não são mais deletados do banco de dados, preservando a identidade visual, histórico de preços e facilitando a reposição futura na interface da "Minha Loja".
- **Auto-Filtro Instantâneo:** A seleção da origem de busca (ex: alternar entre "Catálogo Global" e "Minha Loja") agora atualiza a tabela e aplica as regras de negócio de forma instantânea, eliminando a fricção de ter que clicar manualmente no botão "Buscar".
- **Transições de Interface (Alpine.js):** Navegação entre os módulos (Cartas, Selados, Acessórios, Importação) otimizada para ocorrer puramente no lado do cliente (DOM), mantendo o estado de filtros abertos/fechados intacto sem recarregamentos desnecessários do servidor.

### Páginas Adicionadas:

**Gestão de Produtos (Selados e Acessórios):**
- **Integração do componente `ProductInventory`:** Nova interface dedicada ao gerenciamento de produtos fechados (Booster Boxes, Decks, Bundles) e acessórios (Sleeves, Playmats), seguindo a mesma arquitetura de edição em massa das cartas avulsas.
- **Dicionário de Dados Específico:** Colunas e seletores adaptados para a realidade de produtos (ex: listagem de idiomas para caixas e métricas de qualidade focadas na embalagem: *Selado / Novo*, *Caixa Danificada*, *Aberto*).
- **Modal de Detalhes Dinâmico:** Suporte total à anexação de fotos reais e observações de avarias de embalagem diretamente na linha do produto oficial.

### Segurança & Isolamento de Estado:
- **Prevenção de Colisão de URL (Query String Aliases):** Implementação de isolamento nas chaves de busca (`search` vs `p_search`) para garantir que os filtros da listagem de cartas e da listagem de produtos funcionem de maneira totalmente independente, mesmo coexistindo no mesmo ambiente de Dashboard. 

---

## 📈 Próxima Versão Planejada

**Próxima versão:** `alpha v0.1.0`  
**Objetivo:**  
- Implementar o layout público inicial: `storefront.blade.php`  
- Iniciar a transição visual para o design moderno  
- Criar a base do novo marketplace público  

---

## 🧩 Estrutura de Versionamento

O projeto usa um modelo adaptado do Semantic Versioning:

STAGE vMAJOR.MINOR.PATCH

### Componentes:
- **STAGE** → `alpha`, `beta`, `rc`, `stable`
- **MAJOR** → fases grandes do sistema
- **MINOR** → funcionalidades novas visíveis
- **PATCH** → correções e ajustes pequenos

### Regras:
- Qualquer alteração em arquivo → **+patch**  
- Nova funcionalidade visível → **+minor**  
- Refatoração grande / mudança estrutural → **+major**

---

## 📜 Histórico de Versões

### `alpha v0.0.1` — 21/12/2025  
- Nome oficial alterado para **Versus TCG**  
- Estrutura de versionamento criada  
- Roadmap da V5 adicionado  
- Arquivo de Sucessos adicionado  
- Planejamento do novo front iniciado  

---

## 🗂 Instruções de Atualização

Ao implementar algo novo:

1. Atualizar este arquivo com a nova versão  
2. Atualizar o `MULTIVERSE-SUCESSOS.md` com o novo sucesso  
3. Atualizar o `ROADMAP.md` se necessário  
4. Criar commit Git com a versão:

git commit -m "alpha vX.X.X — descrição da mudança"

5. Push normalmente:

git push origin main

Tags do GitHub são opcionais nesta fase.

---

**Última Atualização:** 21/12/2025  
**Mantido por:** Alexandro & Inner AI Fusion 