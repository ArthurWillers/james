---
title: "Contatos"
weight: 2
date: 2026-06-09T12:41:14-03:00
draft: false
---

### Visão Geral

O módulo de Contatos é a fundação relacional do James. Ele atua como um registro pessoal de pessoas e entidades com as quais você se relaciona, servindo de Single Source of Truth (SSOT) passivo, ou seja, sem vínculo com a tabela de usuários autenticáveis.

O cadastro oferece funcionalidades de CRUD completo (Criar, Ler, Atualizar, Excluir), busca em tempo real, suporte para múltiplos telefones e e-mails por contato, além de gerenciamento avançado de avatares com recorte automático.

### Modelagem de Dados

Para maior flexibilidade e organização, dados secundários como `phones` e `emails` não necessitaram de tabelas auxiliares. Eles são armazenados de forma otimizada via colunas dinâmicas `JSONB` no PostgreSQL, garantindo que o banco permaneça com as consultas limpas.

```mermaid
erDiagram
    CONTACTS {
        bigint id PK
        varchar name "Obrigatório, max 255"
        varchar relationship_category "Opcional, max 255"
        date birthdate "Opcional, data de nascimento"
        jsonb phones "Opcional, array de objetos {number: '...', type: '...'}"
        jsonb emails "Opcional, array de objetos {value: '...', type: '...'}"
        text notes "Opcional, anotações ricas em Markdown"
        timestamp deleted_at "Soft Delete"
        timestamp created_at
        timestamp updated_at
    }
```

> **Nota sobre o Avatar**: O avatar do contato não é uma coluna física na tabela `contacts`. A gestão dos arquivos é feita de forma polimórfica pela biblioteca externa `Spatie MediaLibrary` na tabela `media`, vinculada através do `model_type` e `model_id`.

### Funcionalidades e Bibliotecas Implementadas

* **Campos Múltiplos via AlpineJS**: Inserção dinâmica no frontend de \(N\) telefones e \(N\) e-mails, gerenciados via estados reativos e submetidos numa estrutura de Array.
* **Busca e Pesquisa Inteligente**: Integração com a Trait [`Searchable`](/james/docs/traits-e-helpers/#searchable) no Laravel, usando consultas recursivas nas colunas textuais nativas e nas raízes dos atributos JSON via consultas nativas (ex: `phones::text ILIKE`).
* **Anotações em Markdown**: Integração com a biblioteca JS `EasyMDE`, que sobrepõe de forma invisível as `textarea`s, convertendo-as em blocos ricos de escrita com barra de ferramentas e syntax highlight, persistidos como raw-text no banco de dados.
* **Lixeira e Soft Deletes**: O módulo de contatos usa o trait de `SoftDeletes` impedindo que a exclusão exclua os arquivos ou os contatos de fato. Os contatos são expostos numa tela à parte da "Lixeira", permitindo restaurá-los ou executar o Force Delete (que aí sim purga as imagens e o registro).

### Gestão de Avatares

A foto de perfil (`avatar`) nunca usa um campo tradicional no banco. Toda a orquestração é liderada pelo pacote `Spatie MediaLibrary`.

1. A imagem é recebida via request.
2. É enviada pro pacote e manipulada localmente via integração com `Spatie Image` (`spatie/image`).
3. Ocorre o corte centralizado via método `fit(Fit::Crop, 200, 200)`.
4. Ocorre a otimização de cor e a conversão de formato obrigatória final para `.webp`.
5. O modelo não armazena strings. Para buscar a imagem, uma rota protegida `contacts.avatar` envia uma Stream HTTP com base no disco privado para evitar scraping público na internet.

### Telas e Interfaces

A interface foi construída priorizando a clareza e a usabilidade. Adotamos um visual limpo ("Design Clean"), utilizando TailwindCSS v4 em conjunto com componentes Blade.

Você pode conferir a anatomia exata das telas abaixo:

#### Listagem de Contatos (Busca ativa)
![Listagem de Contatos](../../../images/contatos/index.png)

#### Criação e Edição (Editor Markdown e JSONs Dinâmicos)
![Criação de Contato](../../../images/contatos/create.png)
![Edição de Contato](../../../images/contatos/edit.png)

#### Ações e Detalhes
![Detalhes Avançados do Contato](../../../images/contatos/show.png)

#### Lixeira de Registros (Soft Deletes)
![Lixeira de Contatos](../../../images/contatos/trashed.png)

### Referências
- [Roadmap — Módulo Contatos](/james/docs/roadmap/#fase-2-módulo-contatos)
- [Decisão 006 — Spatie Media Library](/james/docs/decisoes/#006--adoção-da-spatie-media-library-com-armazenamento-privado)
- [Decisão 009 — Editor Visual Markdown](/james/docs/decisoes/#009--editor-visual-markdown)
