<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Copias de seguridad de base de datos
  |--------------------------------------------------------------------------
  |
  | La hora y retención pueden sobreescribirse desde Admin → Backups
  | (storage/app/backup-settings.json).
  |
  */

  'directorio' => 'backups',

  'retencion_dias' => 30,

  'hora_automatica' => '03:00',

  'automatico_activo' => true,

  /*
  | Ruta al binario mysqldump. En Laragon (Windows) suele ser:
  | C:\laragon\bin\mysql\mysql-8.x.x-winx64\bin\mysqldump.exe
  */
  'mysqldump_bin' => env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'),

];
