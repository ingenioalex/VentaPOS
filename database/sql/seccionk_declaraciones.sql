-- Tabla nueva para cierre y declaración de fondos.
-- No altera seccionk_eventos, seccionk_participaciones ni la data ya cargada.

CREATE TABLE IF NOT EXISTS `seccionk_declaraciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evento_id` bigint unsigned NOT NULL,
  `cerrado` tinyint(1) NOT NULL DEFAULT 1,
  `monto_utilizado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_cierre` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seccionk_declaraciones_evento_id_unique` (`evento_id`),
  CONSTRAINT `seccionk_declaraciones_evento_id_foreign`
    FOREIGN KEY (`evento_id`) REFERENCES `seccionk_eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_22_000001_seccionk_cierre_eventos', IFNULL((SELECT MAX(batch) FROM (SELECT batch FROM migrations) t), 0) + 1
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'migrations')
  AND NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_22_000001_seccionk_cierre_eventos');
