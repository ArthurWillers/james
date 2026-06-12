---
title: "Visão Geral"
weight: 1
date: 2026-06-09T12:41:14-03:00
draft: false
---

# Módulo CRM

O Módulo CRM do James atua como o diretório central e privado de todos os contactos do utilizador. É desenhado sob o princípio da simplicidade e da máxima privacidade.

## Propósito: A Fonte Única de Verdade

O CRM não é um sistema complexo de gestão de clientes tradicionais, mas sim a **"Fonte Única de Verdade"** (Single Source of Truth) para todas as pessoas e entidades com as quais o utilizador interage no seu dia a dia. Este módulo não possui lógica de negócio complexa isolada; pelo contrário, ele serve como uma fundação passiva e essencial para os módulos adjacentes, nomeadamente o Módulo de Acertos e o Módulo de Finanças. Qualquer transação, dívida partilhada ou registo financeiro estará sempre ancorado a um contacto gerido por este CRM.

## Entidades Passivas

De acordo com o princípio "Single User" do James, os contactos aqui registados são estritamente **entidades passivas** ou meras "etiquetas de relacionamento".
- **Sem Logins:** Nenhum contacto possui credenciais de acesso ao sistema.
- **Sem Painéis:** Não existe portal do cliente ou áreas partilhadas. O James é um cofre digital exclusivo do utilizador administrador.

## Gestão de Avatares Segura e Privacidade Total

A privacidade do círculo social do utilizador é um pilar inegociável da arquitetura do James. Para gerir as fotos de perfil (avatares) dos contactos, o módulo delega a gestão de ficheiros ao pacote `spatie/laravel-medialibrary`.

**A proteção de dados é garantida pelo design do sistema:**
- **Armazenamento Privado:** Todos os avatares são guardados de forma segura no disco privado do servidor local, nunca sendo expostos na pasta pública do servidor web.
- **Invisibilidade Web:** As imagens são 100% invisíveis para a web externa. Não existem URLs diretos para aceder aos ficheiros.
- **Acesso Autenticado:** A visualização de qualquer avatar ou documento no sistema é sempre mediada pelo Laravel através de rotas protegidas pelo *middleware* de autenticação, garantindo que apenas o utilizador dono do sistema consegue ver a face e os dados dos seus contactos.
