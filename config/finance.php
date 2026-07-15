<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Financial Tags
    |--------------------------------------------------------------------------
    |
    | These tags are suggested to the user as defaults that they can quickly
    | add to their workspace via the interface.
    |
    | Note: The 'icon' keys correspond to Heroicons names (e.g. heroicon-o-*).
    | If you add another icon library in the future, you may need to map these
    | or store the icon prefix.
    |
    */
    'default_tags' => [
        // === RECEITAS ===
        ['name' => 'Salário', 'icon' => 'heroicon-o-banknotes', 'color_hex' => '#22c55e'],
        ['name' => 'Investimentos', 'icon' => 'heroicon-o-arrow-trending-up', 'color_hex' => '#0ea5e9'],
        ['name' => 'Rendimentos', 'icon' => 'phosphor-piggy-bank', 'color_hex' => '#14b8a6'],
        ['name' => 'Auxílio', 'icon' => 'heroicon-o-hand-raised', 'color_hex' => '#10b981'],

        // === CONTAS DA CASA ===
        ['name' => 'Moradia', 'icon' => 'heroicon-o-home', 'color_hex' => '#6366f1'],
        ['name' => 'Energia', 'icon' => 'heroicon-o-bolt', 'color_hex' => '#eab308'],
        ['name' => 'Água', 'icon' => 'phosphor-drop', 'color_hex' => '#3b82f6'],
        ['name' => 'Internet', 'icon' => 'phosphor-wifi-high', 'color_hex' => '#8b5cf6'],
        ['name' => 'Assinaturas', 'icon' => 'heroicon-o-arrow-path', 'color_hex' => '#a855f7'],

        // === ALIMENTAÇÃO, MERCADO & CASA ===
        // O generalzão: Para quando não quer desmembrar o cupom fiscal.
        ['name' => 'Mercado', 'icon' => 'heroicon-o-shopping-cart', 'color_hex' => '#84cc16'],

        // Os secos: Arroz, feijão, óleo, sal, pimenta, macarrão, enlatados.
        ['name' => 'Mercearia', 'icon' => 'heroicon-o-shopping-bag', 'color_hex' => '#65a30d'],

        // O café da manhã: Pão, queijo, presunto, requeijão, leite, manteiga.
        ['name' => 'Padaria & Frios', 'icon' => 'phosphor-bread', 'color_hex' => '#78716c'],

        // As carnes: Boi, frango, porco, peixe.
        ['name' => 'Açougue', 'icon' => 'phosphor-bone', 'color_hex' => '#ef4444'],

        // Os frescos: Frutas, verduras, legumes, ovos.
        ['name' => 'Hortifruti', 'icon' => 'phosphor-carrot', 'color_hex' => '#f97316'],

        // Os líquidos: Refrigerante, suco, cerveja, vinho.
        ['name' => 'Bebidas', 'icon' => 'phosphor-martini', 'color_hex' => '#b91c1c'],

        // Os produtos da casa: Sabão em pó, detergente, desinfetante, amaciante.
        ['name' => 'Limpeza', 'icon' => 'phosphor-sparkle', 'color_hex' => '#0ea5e9'],

        // Os cuidados pessoais: Sabonete, shampoo, pasta de dente, papel higiênico.
        ['name' => 'Higiene', 'icon' => 'phosphor-shower', 'color_hex' => '#a855f7'],

        // Consumo pronto fora do mercado:
        ['name' => 'Restaurantes', 'icon' => 'phosphor-fork-knife', 'color_hex' => '#f59e0b'],
        ['name' => 'Delivery', 'icon' => 'phosphor-hamburger', 'color_hex' => '#fbbf24'],

        // === TRANSPORTE ===
        ['name' => 'Transporte', 'icon' => 'phosphor-car', 'color_hex' => '#64748b'],
        ['name' => 'Combustível', 'icon' => 'phosphor-gas-pump', 'color_hex' => '#f97316'],

        // === SAÚDE & BEM-ESTAR ===
        ['name' => 'Saúde', 'icon' => 'heroicon-o-heart', 'color_hex' => '#ef4444'],
        ['name' => 'Farmácia', 'icon' => 'phosphor-first-aid', 'color_hex' => '#f43f5e'],
        ['name' => 'Vestuário', 'icon' => 'phosphor-t-shirt', 'color_hex' => '#ec4899'],
        ['name' => 'Academia', 'icon' => 'phosphor-barbell', 'color_hex' => '#10b981'],

        // === LAZER, EXTRAS E SERVIÇOS ===
        ['name' => 'Entretenimento', 'icon' => 'heroicon-o-film', 'color_hex' => '#8b5cf6'],
        ['name' => 'Jogos', 'icon' => 'phosphor-game-controller', 'color_hex' => '#14b8a6'],
        ['name' => 'Serviços', 'icon' => 'heroicon-o-wrench-screwdriver', 'color_hex' => '#78716c'],
        ['name' => 'Presentes', 'icon' => 'heroicon-o-gift', 'color_hex' => '#f43f5e'],
        ['name' => 'Impostos', 'icon' => 'phosphor-receipt', 'color_hex' => '#94a3b8'],
        ['name' => 'Diversos', 'icon' => 'heroicon-o-squares-2x2', 'color_hex' => '#9ca3af'],
    ],
];
