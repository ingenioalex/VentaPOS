-- SeccionK: crea tablas y datos de prueba en la misma base ventapos
-- No toca las tablas de VentaPOS. Se puede ejecutar desde phpMyAdmin o MySQL.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `seccionk_promociones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dni` varchar(8) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `celular` varchar(15) DEFAULT NULL,
  `participa_cuotas` tinyint(1) NOT NULL DEFAULT 0,
  `referencias` text DEFAULT NULL COMMENT 'Referencias de otras promociones',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seccionk_promociones_dni_unique` (`dni`),
  KEY `seccionk_promociones_dni_index` (`dni`),
  KEY `seccionk_promociones_apellido_paterno_index` (`apellido_paterno`),
  KEY `seccionk_promociones_activo_index` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seccionk_eventos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tipo` enum('evento','cuota_mensual') NOT NULL DEFAULT 'evento',
  `mes_cuota` varchar(20) DEFAULT NULL COMMENT 'Formato: Octubre 2026',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seccionk_eventos_fecha_index` (`fecha`),
  KEY `seccionk_eventos_tipo_index` (`tipo`),
  KEY `seccionk_eventos_activo_index` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seccionk_participaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `promocion_id` bigint unsigned NOT NULL,
  `evento_id` bigint unsigned NOT NULL,
  `participo` tinyint(1) NOT NULL DEFAULT 0,
  `pago` tinyint(1) NOT NULL DEFAULT 0,
  `monto_aportado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `numero_transaccion` varchar(100) DEFAULT NULL,
  `imagen_sustento` varchar(255) DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seccionk_participaciones_promocion_evento_unique` (`promocion_id`,`evento_id`),
  KEY `seccionk_participaciones_evento_id_foreign` (`evento_id`),
  KEY `seccionk_participaciones_participo_index` (`participo`),
  KEY `seccionk_participaciones_pago_index` (`pago`),
  KEY `seccionk_participaciones_fecha_pago_index` (`fecha_pago`),
  CONSTRAINT `seccionk_participaciones_promocion_id_foreign` FOREIGN KEY (`promocion_id`) REFERENCES `seccionk_promociones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seccionk_participaciones_evento_id_foreign` FOREIGN KEY (`evento_id`) REFERENCES `seccionk_eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seccionk_usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seccionk_usuarios_usuario_unique` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

INSERT IGNORE INTO `seccionk_usuarios` (`usuario`, `password`, `nombre`, `activo`, `created_at`, `updated_at`)
VALUES (
  'admin',
  '$2y$12$NQueLsrfaFcNTLf36TjYGehgXdLIgP0H5J0ESSuhWcPoL45sZ59cS',
  'Administrador SecciónK',
  1,
  NOW(),
  NOW()
);

INSERT IGNORE INTO `seccionk_promociones`
  (`dni`, `nombre`, `apellido_paterno`, `apellido_materno`, `direccion`, `celular`, `participa_cuotas`, `referencias`, `activo`, `created_at`, `updated_at`)
VALUES
  ('12345678', 'Juan Carlos', 'García', 'López', 'Av. Los Álamos 123, San Isidro', '987654321', 1, 'Sección A 2000, Sección B 2002', 1, NOW(), NOW()),
  ('23456789', 'María Elena', 'Rodríguez', 'Sánchez', 'Jr. Las Flores 456, Miraflores', '987654322', 1, NULL, 1, NOW(), NOW()),
  ('34567890', 'Pedro Miguel', 'Fernández', 'Torres', 'Calle Los Pinos 789, Surco', '987654323', 0, 'Sección K 2000', 1, NOW(), NOW());

INSERT INTO `seccionk_eventos`
  (`nombre`, `descripcion`, `fecha`, `monto`, `tipo`, `mes_cuota`, `activo`, `created_at`, `updated_at`)
SELECT * FROM (
  SELECT 'Cuota Mensual Octubre 2026' AS nombre, 'Aporte mensual correspondiente a Octubre 2026' AS descripcion, '2026-10-01' AS fecha, 50.00 AS monto, 'cuota_mensual' AS tipo, 'Octubre 2026' AS mes_cuota, 1 AS activo, NOW() AS created_at, NOW() AS updated_at
  UNION ALL SELECT 'Cuota Mensual Noviembre 2026', 'Aporte mensual correspondiente a Noviembre 2026', '2026-11-01', 50.00, 'cuota_mensual', 'Noviembre 2026', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Diciembre 2026', 'Aporte mensual correspondiente a Diciembre 2026', '2026-12-01', 50.00, 'cuota_mensual', 'Diciembre 2026', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Enero 2027', 'Aporte mensual correspondiente a Enero 2027', '2027-01-01', 50.00, 'cuota_mensual', 'Enero 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Febrero 2027', 'Aporte mensual correspondiente a Febrero 2027', '2027-02-01', 50.00, 'cuota_mensual', 'Febrero 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Marzo 2027', 'Aporte mensual correspondiente a Marzo 2027', '2027-03-01', 50.00, 'cuota_mensual', 'Marzo 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Abril 2027', 'Aporte mensual correspondiente a Abril 2027', '2027-04-01', 50.00, 'cuota_mensual', 'Abril 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Mayo 2027', 'Aporte mensual correspondiente a Mayo 2027', '2027-05-01', 50.00, 'cuota_mensual', 'Mayo 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Junio 2027', 'Aporte mensual correspondiente a Junio 2027', '2027-06-01', 50.00, 'cuota_mensual', 'Junio 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Julio 2027', 'Aporte mensual correspondiente a Julio 2027', '2027-07-01', 50.00, 'cuota_mensual', 'Julio 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Agosto 2027', 'Aporte mensual correspondiente a Agosto 2027', '2027-08-01', 50.00, 'cuota_mensual', 'Agosto 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Septiembre 2027', 'Aporte mensual correspondiente a Septiembre 2027', '2027-09-01', 50.00, 'cuota_mensual', 'Septiembre 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Octubre 2027', 'Aporte mensual correspondiente a Octubre 2027', '2027-10-01', 50.00, 'cuota_mensual', 'Octubre 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Noviembre 2027', 'Aporte mensual correspondiente a Noviembre 2027', '2027-11-01', 50.00, 'cuota_mensual', 'Noviembre 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Cuota Mensual Diciembre 2027', 'Aporte mensual correspondiente a Diciembre 2027', '2027-12-01', 50.00, 'cuota_mensual', 'Diciembre 2027', 1, NOW(), NOW()
  UNION ALL SELECT 'Partido de Fútbol', 'Encuentro deportivo de confraternidad', '2026-09-21', 5.00, 'evento', NULL, 1, NOW(), NOW()
) AS nuevos
WHERE NOT EXISTS (
  SELECT 1 FROM `seccionk_eventos` e
  WHERE (e.nombre = nuevos.nombre AND e.fecha = nuevos.fecha)
     OR (nuevos.tipo = 'cuota_mensual' AND e.tipo = 'cuota_mensual' AND e.mes_cuota = nuevos.mes_cuota)
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_11_000001_crear_tablas_seccionk', IFNULL((SELECT MAX(batch) FROM (SELECT batch FROM migrations) t), 0) + 1
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'migrations')
  AND NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_11_000001_crear_tablas_seccionk');
