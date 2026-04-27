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

**Versão:** `alpha v0.0.10`  
**Data:** 17/03/2026   
**Descrição da Versão:** - Lançamento da `Central de Promoções`, módulo final da gestão de estoque focado na configuração e aplicação de descontos e campanhas promocionais para a loja.

### Funcionalidades (UX/UI & Melhorias):
- **Motor de Desconto em Lote:** Nova barra de ferramentas superior com integração nativa via Alpine.js (`$dispatch` e eventos de janela). Permite a aplicação instantânea de um percentual de desconto e um período de validade (Início/Fim) para todos os itens renderizados na view, agilizando o fluxo de criação de campanhas.
- **Cálculo de Preço Reativo (Tempo Real):** Implementação de cálculos em tempo real diretamente na tabela de listagem. A interface processa e exibe o "Preço Final" dinamicamente, inserindo *strike-through* (riscado) no valor original de forma automática assim que o input de desconto é preenchido.
- **Layout Fluido e Truncamento Inteligente:** Aprimoramento na responsividade das tabelas de dados para lidar com nomenclaturas longas (ex: nomes de TCG). A coluna de descrição agora utiliza propriedades combinadas de flexbox e elipse (`w-full max-w-0` com `truncate`), adaptando-se perfeitamente à largura disponível na tela com base no estado (aberto/fechado) da Sidebar, eliminando a quebra de layout.

### Páginas Adicionadas:

**Central de Promoções (Gestão de Descontos):**
- **Integração Unificada de Inventário:** Nova interface dedicada à precificação promocional. O sistema reúne, em uma única visualização padronizada, cartas avulsas (Singles) e produtos oficiais (Selados/Acessórios), diferenciando-os visualmente por meio de símbolos vetoriais de edição ou miniaturas fotográficas de alta qualidade.
- **Tooltips de Previsão (`StockPreview`):** Implementação do motor de visualização flutuante na nova página, proporcionando aos lojistas a exibição em alta resolução da arte da carta ou da foto do produto (com as respectivas variações de moldura e design) ao simplesmente passar o mouse sobre o item na tabela.

---

**Versão:** `alpha v0.1.0`  
**Data:** 20/03/2026  
**Descrição da Versão:** - Lançamento do `Marco Zero (Storefront)`. Implementação da arquitetura base pública do e-commerce (SaaS), introduzindo o motor de temas dinâmicos e a primeira página da loja conectada em tempo real ao estoque do lojista.

### Funcionalidades (UX/UI & Melhorias):
- **Motor de Temas Dinâmicos (SaaS):** Criação de um sistema inteligente de variáveis CSS (`styles.blade.php`) injetado no layout principal. Permite a customização da identidade visual da loja (Cores Primária, Secundária, Destaque, Fundo do Header e Barra de Contatos) de forma isolada para cada tenant.
- **Cálculo de Contraste Automático:** Implementação de um helper PHP nativo que avalia a luminosidade das cores escolhidas pelo lojista (método YIQ) e define dinamicamente se os textos e ícones sobrepostos devem ser brancos ou escuros, garantindo acessibilidade.
- **Refatoração de Partials:** Componentes globais `Header` e `Footer` completamente reescritos. Foram removidas as cores estáticas do Tailwind, adotando classes utilitárias reativas às variáveis do tema para suportar qualquer paleta escolhida pelo lojista.

### Páginas Adicionadas:

**Storefront - Vitrine Principal (Home):**
- **Prateleiras Vivas (Livewire):** Nova interface (`Store\Home`) conectada ao banco de dados. A prateleira "Últimas Adições" renderiza as cartas reais do estoque, aplicando automaticamente *badges* de desconto promocional e etiquetas de tratamento (ex: Foil).
- **Estrutura Comercial e Empty State:** Construção do esqueleto visual contendo Hero Banner, Sidebar com "Ofertas do Dia", prateleiras por TCG, bloco de avaliações e captura de Newsletter. Inclui um *Empty State* (Estado Vazio) profissional na prateleira principal para lojas recém-criadas que ainda não possuem produtos cadastrados.

---

**Versão:** `alpha v0.1.1`  
**Data:** 21/03/2026  
**Descrição da Versão:** - Implementação do **Gerenciador Dinâmico de Categorias (TCG)**. Introdução do painel administrativo para controle da arquitetura de navegação da loja, permitindo que cada lojista personalize nomes, visibilidade e a hierarquia de produtos (Singles, Selados e Acessórios) de forma isolada e multitenant.

### Funcionalidades (UX/UI & Melhorias):
- **Gestão de Categorias por Jogo (Dashboard):** Criação da interface administrativa (`DashboardStoreMenus`) para ativação e configuração de Card Games. O sistema permite renomear abas padrão e alternar a visibilidade de categorias específicas, salvando as preferências no banco de dados por `store_id`.
- **Motor de Menu Inteligente (Stock-Driven):** O menu frontal agora é gerado automaticamente com base no estoque real. Implementação de filtros complexos que ocultam coleções vazias e organizam os sets por ordem cronológica de lançamento (`released_at`), garantindo que o cliente veja apenas o que está disponível para compra.
- **Isolamento de Dados (Multitenancy):** Blindagem da camada de dados no Livewire. Todas as operações de leitura e escrita de categorias utilizam o `current_store_id` do usuário autenticado, impedindo vazamento de configurações entre diferentes lojas da plataforma.
- **Botão de Atualizações de Estoque (WhatsApp Ready):** Adição de um campo dedicado para o "Menu do Robô" (`name_updates`). Este recurso permite ao lojista configurar o nome e a ativação da página que servirá de destino para os disparos automáticos de novidades no WhatsApp, com suporte total a variáveis de contraste visual.

### Páginas Adicionadas / Atualizadas:

**Dashboard - Configurações de Menus:**
- **Interface de Gestão de TCGs:** Tela com sistema de modais para ativação de novos jogos. Inclui validação contra duplicidade de menus e suporte a múltiplos estados (*Ativo/Oculto*) para cada categoria de produto (Cartas Avulsas, Selados, Acessórios e Sets).
- **Persistência em Tempo Real:** Integração completa entre o formulário do Dashboard e o banco de dados via Livewire, garantindo que qualquer alteração de nome ou visibilidade reflita instantaneamente na vitrine pública.

**Storefront - Menu de Navegação Global:**
- **Refatoração do Dropdown de Jogos:** O menu superior foi convertido de estático para 100% dinâmico. Agora ele renderiza a hierarquia completa: Categoria Principal > Título de Lançamentos > Lista de Sets Recentes > Links de Apoio.
- **Blindagem de Contraste no Header:** Aplicação de `style` inline em componentes críticos (botão de atualizações) para forçar o respeito às variáveis de contraste (`cor-texto-btn-1`), evitando falhas de legibilidade independentemente da cor do tema.

---

**Versão:** `alpha v0.1.2`  
**Data:** 21/03/2026  
**Descrição da Versão:** - Início da implementação do **Motor de Customização de Layout e Identidade Visual**. Esta atualização estabelece a infraestrutura técnica (pipeline) que permite a transição definitiva de interfaces estáticas para uma vitrine dinâmica, onde as escolhas de design feitas no painel administrativo (Cores, Logos e Proporções) ditam a arquitetura visual da loja em tempo real.

### Funcionalidades (Engenharia de Layout & UX):
- **Pipeline de Ativos Dinâmicos (Multitenant):** Desenvolvimento da lógica de entrega de assets isolados. O sistema agora processa e renderiza imagens (Logos/Favicons) com base no contexto da loja ativa, garantindo que o upload no Dashboard reflita instantaneamente no Front-end, Dashboard e Footer.
- **Engenharia de Header Premium:** Refatoração estrutural da cabeçalho para suportar a nova escala visual. Implementação de variáveis de respiro (`h-24`) e deslocamento (`ml-10`) que garantem que a identidade da loja tenha destaque sem comprometer a usabilidade da navegação.
- **Motor de Adaptabilidade de Tema (Reactive Contrast):** Implementação de lógica de detecção de estado via CSS e Tailwind (`invert dark:invert-0`). O sistema agora ajusta a identidade visual monocromática automaticamente entre os modos Claro e Escuro, resolvendo problemas críticos de contraste e legibilidade sem necessidade de intervenção do usuário.
- **Sincronização de Branding Global:** Centralização do objeto de visual da loja, garantindo consistência absoluta entre o ambiente de gestão (Dashboard) e o ambiente de venda (Storefront).

### Páginas Adicionadas / Atualizadas:

**Ambiente de Gestão (Dashboard):**
- **Header Administrativo Reativo:** Atualização do topo do painel para exibir a marca proprietária com comportamento inteligente de troca de tema. O Header agora atua como um espelho da identidade configurada pelo lojista.

**Ambiente de Vitrine (Storefront):**
- **Bridge Visual (Header/Footer):** Integração total dos componentes de topo e rodapé com o banco de dados visual da loja. A vitrine agora é "alimentada" dinamicamente, abandonando placeholders e textos estáticos em favor da identidade visual real do lojista.
- **Proteção de Layout (Object-Contain):** Implementação de travas de segurança em componentes de imagem para assegurar que logotipos de diferentes proporções sejam exibidos sem distorções ou cortes, mantendo a integridade do design.

---

**Versão:** `alpha v0.1.3`  
**Data:** 22/03/2026  
**Descrição da Versão:** - Implementação da **Interface de Navegação por Expansões (Sets)**. Esta atualização introduz a camada de descoberta de produtos organizada por coleções, permitindo que o usuário navegue pelo catálogo através de uma arquitetura visual focada em TCG (Trading Card Games).

### Funcionalidades (Engenharia de Layout & UX):
- **Barra de Navegação Contextual (Location Bar):** Desenvolvimento de um componente de trilha dinâmico que identifica a localização do usuário no ecossistema da loja. Implementação de lógica de breadcrumbs que se adapta ao contexto da coleção visualizada, melhorando o fluxo de navegação (User Flow).
- **Arquitetura de Grid de Coleções:** Implementação de um sistema de grid responsivo projetado para exibir expansões e sets. O layout foi otimizado para suportar artes de coleções variadas, garantindo que a hierarquia visual priorize a identificação rápida das edições.
- **Motor de Ingestão de Dados de Sets:** Integração dos componentes de vitrine com a estrutura de dados de expansões, garantindo que nomes, ícones e metadados das coleções sejam renderizados de forma performática e organizada.

### Páginas Adicionadas / Atualizadas:

**Ambiente de Vitrine (Storefront):**
- **Página de Listagem de Expansões (Sets Index):** Criação da interface principal de coleções. A tela atua como o ponto de entrada para jogadores que buscam cards de edições específicas, utilizando a nova barra de localização como guia estrutural.
- **Componentização de Card de Set:** Desenvolvimento do componente atômico para representação de cada expansão, com suporte a hover states e transições suaves, mantendo o padrão visual premium da plataforma.

---

**Versão:** `alpha v0.1.4`  
**Data:** 22/03/2026  
**Descrição da Versão:** - Evolução do **Módulo de Autenticação Camaleão (Intelligent Login)**. Esta atualização implementa uma lógica de "Identidade Híbrida" na tela de acesso, permitindo que o portal de entrada do lojista se adapte integralmente ao branding configurado ou utilize o fallback proprietário do Versus TCG em caso de ausência de dados visuais.

### Funcionalidades (Engenharia de Layout & UX):
- **Motor de Identidade Híbrida (White Label vs. Legacy):** Implementação de um interruptor lógico (`Conditional Rendering`) que detecta a presença de ativos da loja. O sistema alterna automaticamente entre o **Modo White Label** (Veste as cores e logo do lojista) e o **Modo Versus Original** (Layout Dark Premium), garantindo uma experiência profissional em qualquer cenário e evitando quebras estéticas em lojas sem identidade definida.
- **Engenharia de Contraste Preditivo (getSafeTextColor):** Integração total do login com o motor de contraste. O sistema agora calcula a luminosidade dos campos de input (Cor Terciária) e do card (Cor do Header) em tempo real, ajustando automaticamente a cor de placeholders, ícones e textos para garantir legibilidade absoluta (WCAG Compliant) tanto em temas claros quanto escuros.
- **Injeção Dinâmica de Favicon:** Desenvolvimento da lógica de sobrescrita de favicon via Blade Sections. A aba do navegador agora é atualizada dinamicamente com o ícone da loja ativa, fortalecendo a percepção de plataforma proprietária (Branded Experience), com fallback automático para o ícone oficial da Versus TCG.
- **UX Security Layer:** Reintrodução e refinamento de elementos críticos de interface, incluindo o alternador de visibilidade de senha (Password Toggle) e o rodapé de segurança com ícone de proteção (Shield Check) e certificação visual de ambiente criptografado.

### Páginas Adicionadas / Atualizadas:

**Ambiente de Acesso (Auth):**
- **Tela de Login 2.0 (Chameleon Core):** Refatoração completa da página de login. A interface agora utiliza o sistema de cores dinâmicas para fundo (`Cor Secundária`), card (`Header`) e botões (`CTA`), eliminando cores "hardcoded" e respeitando a sobriedade do design original.

---

**Versão:** `alpha v0.1.5`  
**Data:** 24/03/2026  
**Descrição da Versão:** Implementação do **Módulo de Recuperação de Ativos (Secure Recovery Flow)** e refatoração da arquitetura de estados da tela de autenticação. Esta atualização introduz a lógica de redefinição de senha por tokens criptográficos, integrada ao motor camaleão.

### Funcionalidades (Engenharia de Estados & Segurança):

* **Arquitetura de Estados Tripartida (Login/Forgot/Reset):** Evolução do componente de entrada para suportar três modos operacionais distintos em uma única interface. A alternância de telas (Login -> Recuperação -> Nova Senha) agora é gerida por um sistema híbrido de `Livewire @entangle` e `Alpine.js`, permitindo transições instantâneas sem refresh de página e mantendo o estado de "Identidade Híbrida".
* **Infraestrutura de Segurança via Tokens (DB-Backed Security):** Implementação da camada de persistência para recuperação de contas. Criação da tabela `password_reset_tokens` e integração com o sistema de notificações do Laravel, garantindo que o ciclo de vida do reset de senha seja seguro, rastreável e expire automaticamente após o uso.
* **Fluxo de Recuperação Imersivo (Branded Recovery):** Otimização do UX onde o link de recuperação (e-mail) transporta o `slug` da loja. Isso permite que, ao clicar no link, o lojista "aterrize" em uma tela de nova senha que já carrega automaticamente sua identidade visual (White Label) ou o layout Versus Dark, eliminando a quebra de imersão durante o processo de recuperação.
* **Refatoração de Elementos de Interação (Button Logic Fix):** Correção estrutural de elementos de navegação interna. Substituição de tags de âncora por botões de ação (`type="button"`) para evitar conflitos de rota no navegador e garantir a execução fiel dos scripts de troca de estado do Alpine.js.

### Páginas Adicionadas / Atualizadas:

**Ambiente de Acesso (Auth):**
* **Componente LoginLojista 2.1:** Atualização do cérebro da autenticação para processar validações de e-mail, geração de chaves de segurança e persistência de novas credenciais (Bcrypt hashing) com suporte a feedback em tempo real para o usuário.
* **Roteamento Inteligente (Dynamic Slug Routes):** Adição da rota de reset de senha dentro do escopo de lojas, permitindo a identificação dinâmica do lojista via URL sem comprometer a segurança dos dados.

---

**Versão:** `alpha v0.1.6`  
**Data:** 26/03/2026  
**Descrição da Versão:** Implementação da **Página de Listagem de Cards por Edição (Set Page)** e otimização da arquitetura de consultas ao banco de dados. Esta atualização introduz o motor de filtragem híbrida (Cor/Tipo) e a tecnologia de Tabela Virtual para performance em larga escala no catálogo.

### Funcionalidades (Engenharia de Dados & Performance):

* **Arquitetura de Consulta via Tabela Virtual (Derived Table Join):** Substituição de subqueries correlacionadas por uma lógica de `leftJoinSub`. O motor agora processa o cálculo de estoque total, menor preço ativo, detecção de itens *foil* e descontos em uma única operação de memória, reduzindo a carga de processamento de O(n) para O(1) por página.
* **Motor de Filtragem Híbrida (Mana & Type Logic):** Implementação de um sistema de busca inteligente que distingue cores nativas (W, U, B, R, G), cartas Multicolores e Incolores Reais. A lógica foi expandida para tratar "Artefatos" e "Terrenos" como filtros de tipo, garantindo que terrenos básicos não poluam a busca por cartas incolores.
* **Sistema de Identidade de Print (Unique Collector ID):** Refatoração da lógica de agrupamento para operar via `catalog_print_id` em vez de `concept_id`. Isso garante que variantes de arte, terrenos básicos com diferentes ilustrações e números de colecionador distintos sejam exibidos como produtos individuais, respeitando a integridade da coleção.
* **Persistência de Estado e Ordenação Dinâmica:** Implementação de resets de página automáticos ao alternar filtros (`updated` hook) e sistema de ordenação tripla: por Número da Coleção (Cast Unsigned), Ordem Alfabética e Menor/Maior Preço (considerando nulos para itens sem estoque).

### Páginas Adicionadas / Atualizadas:

**Catálogo da Loja (Template):**
* **Componente SetPage 1.0:** Lançamento do controlador Livewire responsável pela gestão do grid de cartas, integrando o motor de busca unificado à tabela de estoque específica do lojista.
* **Interface de Filtros Reativa (UI/UX):** Atualização da View com suporte a filtros "em lote". O usuário agora pode configurar múltiplos parâmetros (Cor + Raridade + Estoque) e disparar a atualização via gatilho oficial (Botão Filtrar), otimizando a navegação e a estética da barra de ferramentas.

---

**Versão:** `alpha v0.1.7`  
**Data:** 29/03/2026  
**Descrição da Versão:** Implementação da **Página de Detalhes do Produto (Product/Card Page)** com renderização dinâmica multi-edição. Esta atualização introduz uma arquitetura de segregação de dados (Conceito vs. Físico) e um motor reativo de estado focado na integridade da UI perante dados incompletos de APIs externas.

### Funcionalidades (Engenharia de Dados & Performance):

* **Arquitetura de Segregação de Entidades (Concept vs. Print):** Refatoração da lógica de injeção de dados no DOM, isolando estritamente a "Mecânica do Jogo" (Custo de Mana, Cores, Poder/Resistência carregados do `CatalogConcept`) dos "Atributos Físicos da Edição" (Texto Traduzido, Flavor Text, Artista e Tipo Localizado carregados diretamente da tabela `mtg_prints`). Isso impede o atropelamento de variáveis e garante precisão técnica absoluta.
* **Motor de Fallback Multilíngue (Safe Navigation):** Implementação de um sistema de segurança no carregamento de traduções. O sistema detecta dados ausentes (como `printed_text` ou `flavor_text` registrados como `null` ou vazios pela API do Scryfall em coleções antigas) e executa um recuo automático (fallback) para o `oracle_text` original, prevenindo quebras de layout e buracos visuais na interface.
* **Processador de Tags e Extras (JSON Parsing Engine):** Desenvolvimento de um parser lógico robusto para o array de `extras` do estoque. O motor normaliza a inconsistência de *casing* proveniente do banco de dados (aplicando `strtolower` seguido de `ucwords` e limpeza de *underscores*) e injeta estilização condicional semântica em tempo de renderização (Vermelho para *Foil*, Laranja para *Foil Etched*, Cinza para regulares), separados por vírgulas dinâmicas.
* **Reatividade de Estado Otimizada (Livewire `mouseenter`):** Aprimoramento do método `updateStats` para carregar fragmentos de dados da tabela de *prints* sob demanda. A troca de imagens, recálculo de preço médio e injeção de textos localizados ocorrem na camada do componente sem engatilhar recarregamentos completos (`render()`), estabilizando a grid CSS.

### Páginas Adicionadas / Atualizadas:

**Catálogo da Loja (Template):**
* **Componente ProductPage 1.0:** Lançamento do controlador Livewire responsável pela página de carta individual, integrando visualização de produto, cotação de mercado (Mínimo, Médio e Máximo) e tabela de ofertas da loja.
* **Interface de Ofertas e Tooltips (UI/UX):** Construção da tabela de estoque multi-estado, operando com variação de opacidade visual para itens "Esgotados" e "Nunca Cadastrados". Implementação de tooltips interativos para leitura de Idiomas e Condição, e inserção de controles administrativos dinâmicos acessíveis apenas para o lojista autenticado. Limpeza estratégica do quadro técnico (remoção de campos redundantes) para otimização da carga cognitiva do usuário.

---

**Versão:** `alpha v0.1.8`  
**Data:** 08/04/2026  
**Descrição da Versão:** Aprimoramento da **Página de Detalhes do Produto (Product/Card Page)** com foco em precificação isolada por tratamento (Foil/Normal), reestruturação do algoritmo de ordenação de ofertas e nova arquitetura visual flutuante para o grid de cartas associadas.

### Funcionalidades (Engenharia de Dados & Performance):

* **Motor de Precificação Isolada (Detetive de Tratamento & Fallback):** Refatoração profunda no método `updateStats` para segregar ecossistemas de preços (Normal vs. Premium). O sistema agora executa um *parsing* robusto e higienização das strings de `extras` para identificar itens *Foil* e *Etched*, isolando o cálculo de Maior/Menor preço da loja. No quadro de mercado, busca de forma dinâmica a chave exata da API (`usd_foil`, `usd_etched`, `usd`) baseada no tratamento. Implementado um *Fallback Interno* matemático que utiliza a média do próprio estoque da loja caso o *ingest* de dados externos retorne nulo ou zero, garantindo integridade visual e funcional.
* **Algoritmo de Ordenação Hierárquica:** Reescrita da lógica de construção da lista (`mount`) aplicando uma nova balança de pesos operacionais: 1º Disponibilidade (Em Estoque > Esgotado > Fantasma), 2º Preço Decrescente (forçando tratamento premium ao topo absoluto da edição), 3º Lançamento (mais recentes) e 4º Volume. Implementada conversão forçada de *string* para *float* direto no encapsulamento dos dados para assegurar precisão na avaliação matemática dos preços.
* **Restauração do Ciclo de Vida Histórico (Sistema de 3 Estágios):** Correção da regressão que causava a oclusão de edições sem estoque. A arquitetura agora mapeia os *prints* em estoque da loja e faz uma varredura complementar injetando os *prints* não listados (`stock_id => null`), restaurando a exibição da linha do tempo completa da carta ("Avise-me") de forma coesa na tabela.
* **Resolução de Conflito de Estado (Livewire Sync):** Solução do bug de "seleção fantasma" (onde o hover em um item engatilhava múltiplos itens da mesma estampa/idioma). Introdução do controle de estado duplo injetando o rastreio via `$activeStockId` em conjunto com o `$activePrintId`. O disparo do DOM (`wire:mouseenter`) agora trafega variáveis com tratamento seguro (blindagem com aspas simples contra retornos nulos), sincronizando a *row* exata na UI com os recálculos no backend.

### Páginas Adicionadas / Atualizadas:

**Catálogo da Loja (Template):**
* **Componente ProductPage 1.1:** * **Grid de Cartas Associadas (Arquitetura Flutuante UI/CSS):** Reconstrução do motor de interação visual dos *cards* associados (Zoom/Hover). Desacoplamento da carta do fluxo do Grid através do padrão "Âncora Fixa e Imagem Flutuante". Uso de posicionamento absoluto (`absolute`), controle de empilhamento dinâmico (`z-[999]`) e transição fluida de proporção geométrica (`aspect-[2.5/1.8]` para `aspect-[2.5/3.5]`) combinada com escala (`scale-[1.7]`). O resultado é um efeito expansivo limpo (Estilo Netflix) sem causar *Layout Shifts* ou quebra de bordas na página.

---

**Versão:** `alpha v0.1.9`  
**Data:** 10/04/2026  
**Descrição da Versão:** Entrega e Lançamento oficial do módulo de **Catálogo Global de Cartas Avulsas (Singles Page)**. Implementação de arquitetura de alta escala para navegação em massa de registros, com sistema híbrido de visualização e otimização radical de performance via *Thin-Paginate*.

### Funcionalidades (Engenharia de Dados & Nova Entrega):

* **Nova Arquitetura de Listagem Global (Singles Delivery):** Implementação completa da página de cartas avulsas, capaz de gerenciar e exibir +540k registros sem degradação de performance. O módulo foi concebido para ser o motor principal do marketplace, permitindo a transição fluida entre o catálogo geral e o estoque específico da loja.
* **Arquitetura de Busca em Duas Etapas (Thin-Paginate):** Desenvolvimento de um motor de recuperação de dados que isola a paginação (IDs) da carga pesada de modelos. Isso permite que o sistema "corra" sobre o índice do banco de dados, injetando detalhes (imagens, slugs, conceitos) apenas para os 30 itens visíveis na tela.
* **Sistema Híbrido de Visualização (Concept vs. Print):** Entrega da inteligência de agrupamento dinâmico. Por padrão, o sistema entrega a visão por **Conceito** (36k registros), garantindo agilidade. O usuário tem a liberdade de "Desagrupar" para a visão de **Prints** individuais, acionando o motor de performance para processar o volume total de meio milhão de cartas.
* **Contador Inteligente & Persistência de Estado (Cache v8):** Implementação de um sistema de contagem via Cache que rastreia as variações de filtros (cor, raridade, estoque) e o estado de agrupamento. Isso elimina a necessidade de requisições `COUNT(*)` pesadas e garante que a paginação seja 100% precisa, evitando o erro de páginas vazias.
* **UX Controlada (Deferred Filtering):** Estruturação do fluxo de filtros para operação sob demanda. O sistema aguarda a configuração completa do usuário para disparar a consulta ao backend via botão "Filtrar", otimizando o tráfego de dados e dando controle total à jornada de busca (removendo a ansiedade do processamento em tempo real).

### Páginas Adicionadas / Inauguradas:

**Módulo de Catálogo Versus TCG:**
* **Página de Singles (Inaugural):** Entrega da interface completa de listagem com suporte a rotas dinâmicas por jogo (`gameSlug`). 
* **Integração de Menu Global:** Implementação do link oficial no Header dinâmico, conectando a nova página ao ecossistema de todas as lojas da plataforma.
* **Componente de Grid Responsivo:** Interface otimizada com suporte a filtros condicionais que se adaptam ao modo de visualização escolhido (exibindo raridade e numeração apenas quando necessário).

---

**Versão:** `alpha v0.1.10`  
**Data:** 12/04/2026  
**Descrição da Versão:** Entrega do módulo de **Gerenciamento Individual de Estoque (Manage Single Card)**. Implementação de uma interface ultra-responsiva para controle cirúrgico de inventário, com suporte nativo à inteligência de terrenos básicos e normalização virtual de versões.

### Funcionalidades (Engenharia de Dados & Nova Entrega):

* **Arquitetura de Gerenciamento Modal (Inventory Control):** Lançamento do novo ambiente de cadastro individual via Modal Dinâmico. A solução permite a manipulação de dados de estoque sem a necessidade de recarregamento de página, utilizando estados reativos do Livewire e Alpine.js para uma experiência de "aplicativo nativo".
* **Normalização de Terrenos Básicos (Virtual Concept Splitting):** Implementação da inteligência que desmembra terrenos básicos (Planície, Ilha, etc.) por número de colecionador. O sistema agora isola as quantidades e preços de cada arte específica, corrigindo a "venda cruzada" indesejada entre edições diferentes.
* **Sistema de Precificação Inteligente (Market-Sync):** Integração visual com sugestões de preços de mercado (Mínimo, Médio e Máximo). O módulo permite que o lojista ajuste valores baseando-se em dados reais em tempo real, com suporte a campos de precisão decimal e descontos percentuais programados.
* **Motor de Busca Reativo por Edição (Quick-Print-Finder):** Desenvolvimento de um sub-motor de busca interno dentro do modal de cadastro. O lojista pode filtrar instantaneamente entre centenas de variações da mesma carta, selecionando a edição correta com um clique, otimizando o tempo de catalogação em até 70%.
* **Persistência de Estado e UI Ininterrupta:** Implementação da funcionalidade "Manter Aberto", permitindo o cadastro em massa de diferentes variações (idiomas, qualidades, foils) da mesma carta sem fechar o ambiente de trabalho, mantendo o foco do operador.

### Páginas Adicionadas / Inauguradas:

**Módulo Administrativo Versus TCG:**
* **Interface de Gestão de Card (Manage Single):** Página dedicada ao controle de cada conceito do catálogo, com listagem detalhada de todos os itens em estoque e ferramentas de edição rápida.
* **Modal de Cadastro Multidirecional:** Ambiente unificado para adição de novos itens com inteligência para detecção de variações Foil, Etched e idiomas globais.

---

**Versão:** `alpha v0.1.11`  
**Data:** 13/04/2026  
**Descrição da Versão:** Entrega e Integração do **Motor de Busca de Alta Performance (Meilisearch Core)**. Implementação do sistema de indexação ultra-rápida para busca global, com inteligência de processamento de linguagem natural e visualização premium de resultados.

### Funcionalidades (Engenharia de Dados & Nova Entrega):

* **Motor de Busca Baseado em Meilisearch:** Substituição das buscas tradicionais em banco de dados por um motor de indexação em memória. A nova arquitetura entrega resultados em milissegundos, processando erros de digitação (Typo-tolerance) e termos parciais de forma inteligente.
* **Busca por Precisão Numérica (Regex Number-Detection):** Implementação de uma camada de inteligência que identifica números de colecionador nos termos de busca (ex: "Planície 292", "#292", "(292)"). O sistema separa o nome da carta do seu identificador físico, entregando o resultado exato e eliminando a poluição visual.
* **Dropdown de Busca Premium (Estilo Marketplace):** Lançamento do componente de busca rápida com suporte a miniaturas dinâmicas (Thumbnails). O sistema pré-processa os resultados enquanto o usuário digita, exibindo a imagem da carta, nome localizado e nome original para uma identificação visual instantânea.
* **Lógica "Stock-First" (Filtro de Relevância):** Reestruturação do motor de busca para priorizar a realidade da loja. O sistema utiliza os conceitos do Meilisearch como guarda-chuva, mas filtra os resultados estritamente pelo que a loja possui em estoque ou histórico, blindando a plataforma contra links órfãos ou páginas sem conteúdo.
* **Unificação de Resultados (Smart-Grouping):** Implementação do agrupamento dinâmico na tela de busca. Cartas idênticas em diferentes idiomas ou estados de conservação são unificadas em um único bloco visual, exibindo o "Menor Preço" e a "Quantidade Total", mantendo a consistência visual com o catálogo de Singles.

### Páginas Adicionadas / Inauguradas:

**Módulo de Navegação Versus TCG:**
* **Página de Resultados de Busca (Search Results):** Nova interface global para exibição de correspondências exatas e sugestões relacionadas, com suporte ao padrão visual de cards de alta densidade de informação.
* **Componente GlobalSearch (Header):** Integração do motor de busca

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

**Versão:** `alpha v0.1.12`  
**Data:** 15/04/2026  
**Descrição da Versão:** Consolidação do Módulo de Inteligência de Negócios (BI) do Lojista. Implementação do Dashboard principal e da tela de Histórico Consolidado de Estoque, com arquitetura Multi-TCG dinâmica, gráficos avançados (Stock Market UI) e tabelas responsivas de alta densidade.

### Funcionalidades (Engenharia de Dados & Nova Entrega):

* **Gráfico de Evolução Contínua (Stock Market UI):** Implementação de análise gráfica avançada (ApexCharts) para o histórico de estoque. O motor agora projeta uma linha do tempo ininterrupta, preenchendo automaticamente os "buracos" de dias sem snapshot com o último valor conhecido, garantindo a integridade visual da evolução a longo prazo. Inclui ferramentas de Zoom e Pan.
* **Tabela de Alta Densidade Dinâmica:** Desenvolvimento de uma grid de dados responsiva ao comportamento do usuário. O sistema calcula o número de jogos selecionados no filtro e ajusta matematicamente o padding e a tipografia (text-sm para text-[9px]). Ultrapassando o limite visual, o sistema habilita automaticamente um scroll horizontal fluído (padrão Liga Magic).
* **Filtros Inteligentes Multi-TCG (Blind Search):** O motor de PHP agora realiza uma varredura completa ("busca cega") no JSON do histórico da loja para extrair as chaves de jogos. Botões de filtros são gerados e coloridos dinamicamente na interface à medida que novos jogos (`game_id`) são adicionados ao banco, sem necessidade de manutenção de código.
* **Sincronização Temporal do Servidor (UTC Fix):** Ajuste aprofundado no fuso horário do servidor Linux e na configuração `APP_TIMEZONE` do Laravel (`America/Sao_Paulo`). O sistema agora garante que os *Snapshots* automáticos e manuais registrem a data local precisa, evitando "saltos" temporais incorretos nos gráficos de evolução.
* **Smart-Theming JavaScript:** Gráficos foram interligados ao sistema de cores (Dark/Light mode) do Tailwind. Eixos, textos e linhas de grade reagem instantaneamente e invertem seu contraste ao trocar o tema do painel administrativo.

### Páginas Adicionadas / Inauguradas:

**Módulo Administrativo da Loja (Dashboard):**
* **Dashboard Principal (Store Index):** Layout inaugural do painel do lojista, contendo atalhos rápidos de entrada, cards de status de vendas (Buylist, Balcão, Aguardando Envio), gráfico de desempenho de 7 dias e distribuição de estoque em gráfico de Pizza (Pie Chart) centralizado.
* **Histórico Consolidado de Estoque (Stock History):** Nova página dedicada à análise profunda de capital alocado. Exibe detalhamento item/valor fragmentado pelos jogos que a loja opera, com capacidade total de expansão visual e filtros independentes.

### Correções e Melhorias (Patches):
* Correção de renderização no Livewire (`extends` e `section` aplicados em substituição ao layout de slot vazio), evitando a quebra de telas ("tela branca") nos componentes do Dashboard.
* Ajuste na extração de datas pelo JavaScript no gráfico detalhado (`substring(0, 10)`), blindando a renderização contra falhas de conversão de fuso do navegador.

---

**Versão:** `alpha v0.1.13`  
**Data:** 16/04/2026  
**Descrição da Versão:** Aprimoramento visual da vitrine e estabilização crítica do fluxo de cadastro B2B. Injeção de identificadores dinâmicos de tratamento e desconto em todo o front-end da loja, somados à recuperação estrutural do motor de busca para exibição de sugestões de catálogo (fantasmas) e sincronização do Dashboard.

### Funcionalidades (Engenharia de Dados & Nova Entrega):

* **Sistema Dinâmico de Badges (Vitrine):** Implementação de identificadores visuais premium em todas as telas de exibição de produtos da loja. O sistema agora mapeia os metadados do `StockItem` (`extras`, `specific`) e renderiza automaticamente selos de tratamento visual (**Foil**, **Foil Etched**) e promotores de conversão calculados dinamicamente (**Sale -X%**).

### Correções e Melhorias (Patches):

* **Correção Crítica no Motor de Busca (Global Search & Search Results):** Reestruturação da lógica de *gatekeeper* de sessão (`isLojista`). O componente voltou a validar corretamente a autenticação através do `current_store_id` e `store_id`, restaurando a renderização da grade de "Fantasmas" (cartas e paginação exaustiva de terrenos básicos do catálogo global não presentes no estoque) para o fluxo de cadastro do dono da loja.
* **Sincronização em Tempo Real do Gráfico de Pizza (Dashboard Index):** Ajuste na manipulação da coleção de *Snapshots* no frontend (Blade). O componente Alpine.js/ApexCharts agora aponta estritamente para a extremidade correta do array invertido (`last()`), garantindo que o gráfico de distribuição espacial reflita imediatamente os novos volumes físicos de estoque após o recálculo do servidor.

---

**Versão:** `alpha v0.1.14`  
**Data:** 20/04/2026  
**Descrição da Versão:** Implementação estrutural do componente interativo de Carrinho Flutuante (Dropdown) no cabeçalho das lojas. Foco na criação de um fluxo de compra ágil, integrando o estado global do carrinho com o front-end através de Livewire e Alpine.js, permitindo a visualização reativa de itens e valores sem interrupção da navegação do usuário.

### Funcionalidades (Engenharia de Dados & Nova Entrega):

* **Componente Dinâmico de Carrinho (Dropdown Flutuante):** Criação e injeção do componente genérico de carrinho (`livewire:store.template.cart.dropdown`) no Header principal. O sistema agora mapeia os itens adicionados à sessão do usuário em tempo real, renderizando um *flyout* interativo (controlado via Alpine.js) que exibe a listagem de cartas, quantidades, subtotal e botões de *Call to Action* (CTA) diretamente na barra de navegação.

### Correções e Melhorias (Patches):

* **Adequação Dinâmica de Cores (White Label):** O dropdown do carrinho foi estruturado para herdar nativamente as variáveis CSS injetadas pelo motor de estilos da loja (`var(--cor-cta)`, `var(--cor-texto-header)`, etc). Isso garante que o componente respeite o contraste e a paleta exclusiva de cada lojista cliente, sem a necessidade de folhas de estilo estáticas ou duplicação de código.
* **Isolamento de Estado (Livewire vs Alpine):** Ajuste na arquitetura do componente para evitar conflitos de re-renderização (DOM Diffing). O botão de acionamento (ícone de sacola/carrinho) e a janela do dropdown foram blindados para abrir e fechar fluidamente via micro-interações do Alpine, enquanto o Livewire gerencia apenas o tráfego de dados (adição/remoção de produtos e cálculo financeiro).

---

**Versão:** `alpha v0.1.15`  
**Data:** 21/04/2026  
**Descrição da Versão:** Criação das interfaces de autenticação para as lojas clientes (White Label) e estruturação do componente de menu de usuário (Dropdown). O desenvolvimento foi espelhado na arquitetura base do modal de autenticação do sistema Versus, visando unificar e padronizar a experiência de acesso de ponta a ponta.

### Funcionalidades (Engenharia de Dados & Nova Entrega):

* **Formulários de Login (White Label):** Criação dos componentes visuais e lógicos de login e registro dedicados ao ambiente individual de cada loja. A estrutura do formulário foi clonada e adaptada a partir da base sólida do sistema central (Versus TCG).
* **Dropdown de Usuário (Header):** Estruturação inicial do menu flutuante de perfil (avatar, saudação e opções da conta) no cabeçalho das lojas, projetado para substituir o botão "Entrar" após a autenticação bem-sucedida do jogador.

### Errata & Problemas Conhecidos (Bugs Mapeados):

* **Falha de Redirecionamento (Fallback):** O fluxo de autenticação, ao ser concluído com sucesso, não está persistindo o usuário na tela de origem. O sistema executa um redirecionamento indesejado para uma rota de *fallback*, impedindo a ativação dinâmica e fluida do Dropdown no cabeçalho.
* **Inconsistência de Cores e Contraste:** Conflito de herança de CSS na tela de login. O formulário importado do Versus carrega estilos *inline* rígidos (focados no tema dark original), o que quebra a legibilidade de inputs, placeholders e ícones quando submetidos ao motor de variáveis dinâmicas (cores claras/brand) das lojas White Label.

------

**Versão:** `alpha v0.1.16`  
**Data:** 22/04/2026  
**Descrição da Versão:** Expansão do ecossistema de autenticação com a implementação do Wizard de Cadastro de Jogadores dedicado ao ambiente das lojas clientes (White Label). A interface foi inteiramente modelada a partir do fluxo padrão da Versus TCG, mantendo a estrutura de múltiplos passos (Termos, Dados Pessoais, Conclusão).

### Funcionalidades (Engenharia de Dados & Nova Entrega):

* **Wizard de Registro de Jogadores (White Label):** Construção do formulário em etapas para captação de novos usuários diretamente pela URL do lojista. O componente compartilha a mesma base lógica (`Livewire`) do marketplace, garantindo uniformidade na validação e na injeção de dados no banco (model `PlayerUser`).

### Errata & Problemas Conhecidos (Bugs Mapeados):

* **Falha na Reatividade da Barra de Progresso:** O rastreador visual de etapas (linha de conexão e bolinhas numeradas) não está sincronizando com o avanço do formulário. Conflito mapeado entre o estado do Alpine.js (`x-data="{ currentStepAlpine }"`) e o ciclo de renderização do Livewire (DOM Diffing).
* **Herança dos Bugs de Autenticação (Dia 21):** O componente recém-criado herdou os mesmos passivos técnicos da tela de login: redirecionamento incorreto para rota de *fallback* após o cadastro/login e quebra de legibilidade visual (conflito entre o CSS *inline* fixo e o motor dinâmico de cores da loja).

---

**Versão:** `alpha v0.1.17`  
**Data:** 23/04/2026  
**Descrição da Versão:** Estabilização crítica e refatoração completa dos fluxos de registro, login e navegação autenticada. Resolução definitiva do acúmulo de *bugs* visuais e lógicos herdados das versões anteriores, garantindo integridade de sessão e fluidez na interface (UI/UX) tanto no marketplace quanto nas lojas White Label.

### Correções e Melhorias (Patches):

* **Correção Crítica de Sessão e Redirecionamento (Guards):** O sistema agora mantém o usuário na tela de origem após o login/cadastro. Implementação do redirecionamento via cabeçalho `Referer` e ajuste global nas diretivas do Blade (`@auth('player')`), forçando os componentes do cabeçalho a validarem estritamente o porteiro correto de jogadores, ativando as áreas restritas sem ejetar o usuário.
* **Sincronização Definitiva da Barra de Progresso (Wizards):** Remoção da dependência do Alpine.js na barra de etapas dos formulários de registro (Lojista e Jogador). A renderização das cores e opacidades agora é controlada diretamente pelo Blade (`$currentStep`), com o uso rigoroso de `wire:key` para blindar o componente contra o DOM Diffing do Livewire.
* **Harmonização de Cores e Contraste Dinâmico:** Limpeza da injeção de CSS *inline* estático nos *inputs*. O sistema agora combina as variáveis dinâmicas do *backend* (`var(--cor-terciaria)`) com classes utilitárias do Tailwind (`placeholder-white/50`), garantindo legibilidade perfeita do texto digitado e do texto fantasma em qualquer paleta de loja.
* **Restauração da Máscara SVG (Logo Apple):** Correção da renderização do ícone de login da Apple. A lógica de máscara CSS (`-webkit-mask-image`) foi revertida para o formato *inline*, eliminando a quebra de compilação da URL do *asset* pelo Blade.
* **Ativação dos Ícones de Senha (Toggle de Visibilidade):** Injeção da biblioteca oficial Phosphor Icons no layout base (`app.blade.php`), reativando a renderização visual e a funcionalidade interativa (Alpine `@click`) do botão de visualizar/ocultar senha nos formulários.
* **Implementação do Menu Flutuante (Dropdown) por Hover:** O cabeçalho foi refatorado para exibir o perfil do usuário (Avatar dinâmico, Saudação e Opções) via interação fluida de *hover* (`group-hover`), extinguindo a necessidade de cliques. O *layout* foi ramificado para respeitar a identidade visual de cada ambiente (Dropdown *Dark* com detalhes laranjas na Versus TCG e Dropdown *Clean* com cores dinâmicas nas Lojas).

---

**Versão:** `alpha v0.1.18`  
**Data:** 27/04/2026  
**Descrição da Versão:** Refatoração da inteligência de persistência de dados e consolidação da herança de contexto visual entre Marketplace e Lojas White Label. Esta versão foca na eliminação de atritos na jornada do usuário, garantindo que a identidade visual e o carrinho de compras permaneçam íntegros durante transições críticas de estado (Login, Logoff e Registro).

### Correções e Melhorias (Patches):

* **Migração Inteligente de Carrinho (Anti-Session Fixation):** Implementação de lógica de transferência de estado no ato da autenticação. Ao realizar o login, o sistema agora captura o `session_id` de visitante e migra automaticamente todos os `CartItems` para a nova sessão autenticada do `player`, vinculando o `user_id` simultaneamente. Isso elimina a "amnésia de carrinho" causada pela regeneração nativa de sessão do Laravel.
* **Logoff com Preservação de Contexto:** Refatoração do método de saída no `PlayerDropdown`. O sistema abandonou o redirecionamento genérico para a home. Agora, ao deslogar, o usuário permanece na vitrine onde estava (Marketplace ou Loja específica). Foi implementada uma lógica de "expulsão seletiva": se o logoff ocorrer em áreas protegidas, o sistema identifica a origem e o redireciona para a home do marketplace ou para a raiz da loja correspondente, mantendo o contexto visual.
* **Herança Visual no Wizard de Registro:** O formulário de cadastro de jogadores agora é totalmente sensível ao contexto de origem. Através da captura de query strings (`game_slug` ou `loja`), o layout base e o cabeçalho identificam se o usuário veio de um universo específico (ex: Magic, Pokémon). Isso ativa automaticamente o menu de jogo, a barra de busca temática e a paleta de cores correta, eliminando a sensação de "teletransporte" para fora da loja durante o registro.
* **Inteligência de Rota no Header (Query String Awareness):** Atualização do cérebro lógico do `header.blade.php`. O componente foi ensinado a ler parâmetros de URL além das rotas nomeadas. Isso permitiu que o modo Marketplace (com busca e ícones de acesso rápido) seja ativado em qualquer página, incluindo o Wizard de Registro, desde que um contexto de jogo ou loja seja detectado.
* **Navegação de Retorno Preditiva (Smart BackLink):** O botão "Voltar" do Modo Funil foi desvinculado de rotas estáticas. O sistema agora calcula dinamicamente o link de retorno: se o usuário está se registrando a partir do Marketplace de Magic, o botão o devolve para o Marketplace de Magic; se está em uma loja, devolve para a URL slug da loja.
* **Blindagem de Erros de Variáveis (Context Sync):** Unificação da detecção de `activeSlug` entre o Modal de Login e o Header. A resolução eliminou o erro `Undefined variable` ao garantir que todos os componentes utilizem a mesma lógica nativa de inspeção de rota (`request()->route`) e query string para identificar o jogo ativo, garantindo que o link de "Criar Conta" nunca aponte para um destino nulo ou quebrado.
* **Refatoração do Login Modal (UX Cleanup):** O modal de autenticação agora oculta automaticamente atalhos administrativos (como "Acesse o Painel da Loja") quando o usuário já está navegando dentro de um contexto de jogo específico, limpando a interface e focando a jornada estritamente na conversão do jogador.

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

p3duhEVKfBpFWUXOlW7iGnPeLNSRHSEf668t3v/C+yI=