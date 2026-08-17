---
title: "Importação de NFC-e"
weight: 4
---

## Visão geral

O módulo de importação de **Nota Fiscal de Consumidor eletrônica (NFC-e)** transforma uma URL pública de consulta em um rascunho de transação financeira. A importação consulta o portal fiscal, extrai os dados da nota e deixa a confirmação da conta, cartão e status para a revisão do usuário.

Atualmente o provedor suportado é o **SVRS** (Sefaz Virtual do Rio Grande do Sul), com resolução preparada para receber novos portais no futuro.

## Fluxo de uso

1. Abra `Finanças > Transações > Nova Transação`.
2. Clique em **Importar NFC-e**.
3. Cole a URL pública do QR Code da nota ou use **Colar URL**.
4. Envie a solicitação.
5. O sistema redireciona imediatamente para a listagem e coloca a consulta na fila.
6. Após o processamento, uma notificação abre a edição do rascunho.
7. Revise os dados, escolha uma conta ou cartão e salve a transação.

O botão de importação bloqueia envios duplicados enquanto a solicitação atual está sendo enviada.

## Dados importados

O rascunho pode conter:

- emitente e documento do emitente (CPF/CNPJ);
- data de emissão;
- valor total e desconto;
- itens, quantidade, preço unitário e total;
- chave de acesso de 44 dígitos;
- URL completa normalizada do portal.

O documento do emitente é armazenado apenas com dígitos e formatado na apresentação. A URL completa normalizada, incluindo o parâmetro da NFC-e, é armazenada para permitir a consulta posterior da mesma nota; ela não é incluída em logs.

## Status e impacto financeiro

Toda NFC-e importada começa como `draft` (rascunho). Enquanto estiver nesse status, ela não participa de:

- saldo de contas;
- limite utilizado e totais de faturas de cartão;
- dashboard, relatórios e resumos;
- alertas de vencimento e rotinas automáticas.

Ao revisar a transação:

- vinculá-la a uma conta permite salvá-la como `posted` (efetivada) ou `pending` (pendente);
- vinculá-la a um cartão resolve a fatura correspondente e salva a compra como `pending`;
- o pagamento da fatura posteriormente promove as compras elegíveis para `posted`.

## Processamento assíncrono

O endpoint autenticado apenas valida a URL, verifica duplicidade pela chave de acesso e despacha `ScrapeNfceInvoiceJob`. O job:

- é único por chave fiscal;
- possui três tentativas e backoff de `5`, `15` e `30` segundos;
- usa timeout de 60 segundos;
- grava a transação e seus itens em uma única transação de banco;
- envia notificações pelos canais Database e Telegram.

Em produção, o worker da fila precisa estar ativo no Supervisor. Consulte o [Guia de Deploy](/james/docs/deploy/#7-schedulers-e-processos-em-background-supervisor) para a configuração de `james-worker` e do `schedule:work`.

## Segurança e limitações

O resolvedor aceita somente provedores configurados e hosts HTTPS autorizados, rejeitando formatos incompatíveis e destinos não permitidos. A implementação atual não inclui leitura por câmera, OCR, navegador automatizado ou histórico de tentativas.
