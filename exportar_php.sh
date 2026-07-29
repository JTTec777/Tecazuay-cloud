#!/bin/bash
# Exportar todos los archivos PHP de TEC AZUAY
FECHA=$(date +%Y%m%d_%H%M%S)
SALIDA="/root/tecazuay_php_$FECHA.tar.gz"

mkdir -p /root
find /var/www/html/tecazuay -name "*.php" -type f -print0 | tar -czf "$SALIDA" --null -T -

echo "✅ Exportado: $SALIDA"
echo "📦 Archivos:"
tar -tzf "$SALIDA"
