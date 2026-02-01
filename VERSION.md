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