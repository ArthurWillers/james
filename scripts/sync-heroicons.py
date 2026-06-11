#!/usr/bin/env python3
"""
Sincroniza ícones do repositório oficial Heroicons (GitHub) e gera
componentes Blade individuais em resources/views/components/icons/.

Uso:
    python3 scripts/sync-heroicons.py
    python3 scripts/sync-heroicons.py --dry-run --verbose
    python3 scripts/sync-heroicons.py --variants outline solid
"""

import argparse
import os
import re
import shutil
import sys
import urllib.request
import zipfile

# ---------------------------------------------------------------------------
# Configuração
# ---------------------------------------------------------------------------

REPO_URL = "https://github.com/tailwindlabs/heroicons/archive/refs/heads/master.zip"
ZIP_FILENAME = ".heroicons_temp.zip"
EXTRACT_DIR = ".heroicons_temp_dir"
OUTPUT_DIR = os.path.join("resources", "views", "components", "icons")

# Caminhos relativos dentro do repositório extraído (sem a pasta raiz, que é dinâmica).
# Formato: variante → (caminho relativo ao root do repo, viewBox, classe padrão, atributos SVG)
VARIANTS = {
    "outline": {
        "relative_path": os.path.join("optimized", "24", "outline"),
        "viewbox": "0 0 24 24",
        "default_class": "size-6 shrink-0",
        "svg_attrs": 'fill="none" stroke="currentColor" stroke-width="1.5"',
    },
    "solid": {
        "relative_path": os.path.join("optimized", "24", "solid"),
        "viewbox": "0 0 24 24",
        "default_class": "size-6 shrink-0",
        "svg_attrs": 'fill="currentColor"',
    },
    "mini": {
        "relative_path": os.path.join("optimized", "20", "solid"),
        "viewbox": "0 0 20 20",
        "default_class": "size-5 shrink-0",
        "svg_attrs": 'fill="currentColor"',
    },
    "micro": {
        "relative_path": os.path.join("optimized", "16", "solid"),
        "viewbox": "0 0 16 16",
        "default_class": "size-4 shrink-0",
        "svg_attrs": 'fill="currentColor"',
    },
}

# Regex que remove atributos fixos do <svg> original para evitar duplicação
# com os que injetamos no Blade. Captura: xmlns, width, height, viewBox,
# class, fill, stroke, stroke-width e aria-hidden.
STRIP_ATTRS = re.compile(
    r'\s*(?:xmlns|width|height|viewBox|class|fill|stroke|stroke-width|aria-hidden)="[^"]*"',
)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Baixa Heroicons do GitHub e gera componentes Blade.",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Mostra o que seria feito sem modificar nada.",
    )
    parser.add_argument(
        "--verbose", "-v",
        action="store_true",
        help="Exibe cada ícone gerado.",
    )
    parser.add_argument(
        "--variants",
        nargs="+",
        choices=list(VARIANTS.keys()),
        default=list(VARIANTS.keys()),
        help="Variantes a gerar (padrão: todas).",
    )
    return parser.parse_args()


def download_and_extract(*, dry_run: bool = False) -> None:
    if dry_run:
        print("🔍 [dry-run] Baixaria o ZIP do Heroicons")
        return

    print("🚀 Baixando a versão mais recente do Heroicons...")
    urllib.request.urlretrieve(REPO_URL, ZIP_FILENAME)

    print("📦 Extraindo arquivos...")
    with zipfile.ZipFile(ZIP_FILENAME, "r") as zf:
        zf.extractall(EXTRACT_DIR)


def discover_root_dir() -> str:
    """Descobre dinamicamente o nome da pasta raiz dentro do ZIP extraído.

    O GitHub nomeia essa pasta como ``<repo>-<branch>`` (ex: ``heroicons-master``),
    mas isso muda quando se baixa uma tag (``heroicons-v2.2.0``). Em vez de
    hardcodar, inspecionamos o conteúdo de EXTRACT_DIR e pegamos a primeira
    (e geralmente única) subpasta.
    """
    entries = [
        entry
        for entry in os.listdir(EXTRACT_DIR)
        if os.path.isdir(os.path.join(EXTRACT_DIR, entry))
    ]

    if len(entries) == 0:
        raise RuntimeError(
            f"Nenhuma pasta encontrada em '{EXTRACT_DIR}' após extração do ZIP."
        )

    if len(entries) > 1:
        print(f"⚠️  Múltiplas pastas em '{EXTRACT_DIR}': {entries}. Usando '{entries[0]}'.")

    root = entries[0]
    print(f"📂 Pasta raiz detectada: {root}")
    return os.path.join(EXTRACT_DIR, root)


def strip_svg_attrs(svg_tag: str) -> str:
    """Remove atributos controlados pelo Blade da tag <svg> original.

    Isso evita conflitos visuais — por exemplo, um ``fill="none"`` do SVG
    original competindo com o ``fill="currentColor"`` que injetamos.
    Também remove ``width``/``height`` para que o Tailwind controle via
    classes utilitárias (``size-6``, ``size-5``, etc.).
    """
    return STRIP_ATTRS.sub("", svg_tag)


def extract_inner_svg(content: str) -> str:
    """Extrai apenas o conteúdo interno do <svg>, removendo a tag wrapper."""
    match = re.search(r"<svg[^>]*>(.*)</svg>", content, re.DOTALL)
    return match.group(1).strip() if match else content


def clean_svg_content(content: str) -> str:
    """Pipeline completo de limpeza do SVG original.

    1. Encontra a tag ``<svg ...>`` de abertura.
    2. Remove atributos que serão controlados pelo Blade (via STRIP_ATTRS).
    3. Extrai apenas o conteúdo interno (paths, circles, etc.).
    """
    # Passo 1: limpar atributos da tag <svg> para garantir que não sobrem
    # resquícios de width, height, fill, stroke, etc.
    cleaned = re.sub(
        r"<svg([^>]*)>",
        lambda m: "<svg" + strip_svg_attrs(m.group(1)) + ">",
        content,
        count=1,
    )

    # Passo 2: extrair apenas o conteúdo interno
    return extract_inner_svg(cleaned)


def build_blade_svg(inner: str, config: dict[str, str]) -> str:
    """Monta o SVG Blade completo com $attributes->merge."""
    default_class = config["default_class"]
    attrs_merge = "{{ $attributes->merge(['class' => '" + default_class + "']) }}"

    return (
        f'<svg xmlns="http://www.w3.org/2000/svg" '
        f'viewBox="{config["viewbox"]}" '
        f'{config["svg_attrs"]} '
        f'{attrs_merge}>'
        f"\n{inner}\n"
        f"</svg>\n"
    )


def resolve_source_dir(root_dir: str, config: dict[str, str]) -> str:
    """Monta o caminho absoluto da variante usando a raiz descoberta dinamicamente."""
    return os.path.join(root_dir, config["relative_path"])


def process_variant(
    variant: str,
    config: dict[str, str],
    root_dir: str,
    *,
    dry_run: bool = False,
    verbose: bool = False,
) -> int:
    """Processa uma variante de ícones. Retorna a quantidade gerada."""
    source_dir = resolve_source_dir(root_dir, config)

    if not os.path.isdir(source_dir):
        print(f"⚠️  Diretório não encontrado para '{variant}': {source_dir}")
        return 0

    output_dir = os.path.join(OUTPUT_DIR, variant)

    if not dry_run:
        os.makedirs(output_dir, exist_ok=True)

    count = 0
    for filename in sorted(os.listdir(source_dir)):
        if not filename.endswith(".svg"):
            continue

        filepath = os.path.join(source_dir, filename)
        with open(filepath, "r", encoding="utf-8") as f:
            content = f.read()

        inner = clean_svg_content(content)
        blade_content = build_blade_svg(inner, config)

        blade_name = filename.replace(".svg", ".blade.php")
        blade_path = os.path.join(output_dir, blade_name)

        if verbose:
            print(f"  → {blade_path}")

        if not dry_run:
            with open(blade_path, "w", encoding="utf-8") as f:
                f.write(blade_content)

        count += 1

    return count


def clean_output_dir(*, dry_run: bool = False) -> None:
    """Remove ícones antigos antes de regenerar."""
    if os.path.isdir(OUTPUT_DIR):
        if dry_run:
            print(f"🔍 [dry-run] Removeria {OUTPUT_DIR}/")
            return
        shutil.rmtree(OUTPUT_DIR)
        print(f"🧹 Diretório antigo removido: {OUTPUT_DIR}/")


def cleanup_temp() -> None:
    """Remove arquivos temporários de download."""
    if os.path.exists(ZIP_FILENAME):
        os.remove(ZIP_FILENAME)
    if os.path.isdir(EXTRACT_DIR):
        shutil.rmtree(EXTRACT_DIR)


def main() -> None:
    args = parse_args()

    # Garante que estamos na raiz do projeto
    if not os.path.isfile("artisan"):
        print("❌ Execute este script a partir da raiz do projeto Laravel.")
        sys.exit(1)

    try:
        download_and_extract(dry_run=args.dry_run)

        # Descoberta dinâmica da pasta raiz (em vez de hardcodar "heroicons-master")
        if args.dry_run:
            if os.path.isdir(EXTRACT_DIR):
                root_dir = discover_root_dir()
            else:
                print("🔍 [dry-run] Pasta raiz seria descoberta após download")
                root_dir = ""
        else:
            root_dir = discover_root_dir()

        clean_output_dir(dry_run=args.dry_run)

        total = 0
        for variant in args.variants:
            config = VARIANTS[variant]
            count = process_variant(
                variant,
                config,
                root_dir,
                dry_run=args.dry_run,
                verbose=args.verbose,
            )
            status = "[dry-run] " if args.dry_run else ""
            print(f"✅ {status}{count} ícones '{variant}' (classe padrão: {config['default_class']})")
            total += count

        print(f"\n🎉 Total: {total} ícones {'seriam gerados' if args.dry_run else 'gerados'}!")

    except Exception as e:
        print(f"❌ Erro: {e}")
        sys.exit(1)

    finally:
        if not args.dry_run:
            cleanup_temp()
            print("🧹 Arquivos temporários removidos.")


if __name__ == "__main__":
    main()
