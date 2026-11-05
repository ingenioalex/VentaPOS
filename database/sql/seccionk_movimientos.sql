-- Ingresos, egresos, préstamos y devoluciones del fondo de la sección.
-- No altera seccionk_eventos, seccionk_participaciones ni la data ya cargada.

CREATE TABLE IF NOT EXISTS `seccionk_movimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(20) NOT NULL,
  `persona` varchar(150) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `comentario` text DEFAULT NULL,
  `prestamo_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seccionk_movimientos_tipo_index` (`tipo`),
  KEY `seccionk_movimientos_prestamo_id_index` (`prestamo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_04_000002_seccionk_movimientos', IFNULL((SELECT MAX(batch) FROM (SELECT batch FROM migrations) t), 0) + 1
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'migrations')
  AND NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_04_000002_seccionk_movimientos');
