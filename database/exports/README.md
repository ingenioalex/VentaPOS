# Exportaciones demo VentaPOS

Generadas con:

```bash
php artisan ventapos:export-demo both --force
```

| Archivo | Contenido |
|---------|-----------|
| `ventapos-minimal.sql.gz` | Super admin + 1 empresa básica vacía (5 productos) |
| `ventapos-showcase-pro.sql.gz` | Super admin + Distribuidora San Martín (plan Pro, ~3600 ventas Ene-Jun 2026) |

## Credenciales

**Super admin:** `super` / `superadmin123`

**Empresa showcase:** código `san-martin` · `admin` / `admin123`

## Restaurar en VPS

```bash
gunzip -c ventapos-showcase-pro.sql.gz | mysql -u ventapos -p ventapos
```
