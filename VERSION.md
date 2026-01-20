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