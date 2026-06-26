INSERT INTO producto_lotes (empresa_id, producto_id, lote, cantidad, referencia, creado_en)
SELECT p.empresa_id, p.id, 'SYNC-STOCK', p.stock_disponible, 'fix-lotes', NOW()
FROM productos p
WHERE p.control_lote = 1
  AND NOT EXISTS (
    SELECT 1 FROM producto_lotes pl WHERE pl.producto_id = p.id AND pl.cantidad > 0
  );

UPDATE productos
SET unidad_medida = 'KG'
WHERE LOWER(nombre) LIKE '%kg%'
   OR LOWER(nombre) LIKE '% arroz %';

SELECT COUNT(*) AS lotes_sync FROM producto_lotes WHERE referencia = 'fix-lotes';
