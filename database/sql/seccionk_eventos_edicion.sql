-- Permiso temporal para que el admin vuelva a editar un evento cuya fecha ya pasó.
-- No altera seccionk_eventos, seccionk_participaciones ni la data ya cargada.

CREATE TABLE IF NOT EXISTS `seccionk_eventos_edicion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evento_id` bigint unsigned NOT NULL,
  `abierto` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seccionk_eventos_edicion_evento_id_unique` (`evento_id`),
  CONSTRAINT `seccionk_eventos_edicion_evento_id_foreign`
    FOREIGN KEY (`evento_id`) REFERENCES `seccionk_eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_04_000001_seccionk_eventos_edicion', IFNULL((SELECT MAX(batch) FROM (SELECT batch FROM migrations) t), 0) + 1
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'migrations')
  AND NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_04_000001_seccionk_eventos_edicion');
