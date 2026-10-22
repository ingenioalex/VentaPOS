#!/bin/bash
# Redirige al instalador de la raiz del repo. Funciona tambien con:
#   curl -fsSL https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/actualizar-seccionk-vps.sh | bash

set -euo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(dirname "$HERE")"

if [ -f "$ROOT/actualizar-seccionk-vps.sh" ] && [ "$ROOT/actualizar-seccionk-vps.sh" != "$0" ]; then
    exec bash "$ROOT/actualizar-seccionk-vps.sh"
fi

BRANCH="${SECCIONK_BRANCH:-cursor/seccionk-module-0c7c}"
TMP="$(mktemp)"
curl -fsSL "https://raw.githubusercontent.com/ingenioalex/VentaPOS/${BRANCH}/actualizar-seccionk-vps.sh" -o "$TMP"
bash "$TMP"
rm -f "$TMP"
