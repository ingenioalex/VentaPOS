# ¿Dónde trabajar? Kali Linux vs Windows 10

## Respuesta corta

| Tarea | Mejor entorno |
|-------|---------------|
| **VentaPOS Laravel (nuevo producto)** | **Windows 10** + Laragon |
| **Probar RMS desktop original** | Kali o Windows con Python+PostgreSQL |
| **Desarrollo diario con Cursor** | **Windows 10** (donde ya estás) |
| **Servidor producción** | Linux VPS (Ubuntu, no Kali) |

**Recomendación:** Desarrolla VentaPOS en **Windows 10**. Usa Kali solo como referencia del desktop viejo si lo necesitas.

---

## Por qué NO usar Kali como entorno principal

Kali está pensado para **pentesting**, no para desarrollo de productos:

- Distro rolling (cambios frecuentes, menos estable)
- No es estándar en hosting producción
- PHP/Laravel no es el foco de Kali
- Cursor/IDE en Windows ya te funciona
- Despliegue real será Ubuntu/Debian en VPS, no Kali

---

## Por qué SÍ Windows 10 para VentaPOS

| Ventaja | Detalle |
|---------|---------|
| Ya tienes el proyecto aquí | `D:\Proyectos\VentaPOS\`, `rms_desktop\` |
| Cursor + Laragon | PHP 8.2, MySQL, Composer en 30 min |
| Clientes en Chiclayo | Soporte presencial desde tu PC |
| Mismo stack que hosting | PHP/MySQL en cualquier VPS Perú |
| rms_desktop de referencia | Puedes leer código sin ejecutarlo |

---

## Cuándo usar Kali (opcional)

- Ejecutar el **desktop PySide6** original para comparar pantallas
- Tener **PostgreSQL con datos viejos** si no migraste el dump
- Probar que `main.py` sigue funcionando

No necesitas Kali para construir VentaPOS web.

---

## Setup recomendado (Windows 10)

```
D:\Proyectos\
├── VentaPOS\          ← TRABAJO PRINCIPAL (Laravel)
├── rms_desktop\       ← REFERENCIA (Python desktop)
│   └── RMS_bd\Basedatos\rms_db.sql  ← Lógica BD original
└── PreInventario\     ← Otro proyecto (no mezclar)
```

### Instalar en Windows

1. **Laragon Full** → PHP 8.2 + MySQL 8 + Composer
2. **Git** (ya lo tienes)
3. **Cursor** (ya lo tienes)
4. Opcional: **Docker Desktop** → PostgreSQL solo para consultar legacy

### Comandos diarios (VentaPOS)

```bash
cd D:\Proyectos\VentaPOS
php artisan serve
# http://localhost:8000
```

---

## Producción (futuro)

| Componente | Servidor |
|------------|----------|
| VentaPOS web | VPS Ubuntu 22.04 + Nginx + PHP 8.2 + MySQL |
| Dominio | ventapos.pe |
| Backups | Snapshots VPS |

Kali **nunca** va a producción.

---

## Flujo de trabajo híbrido (si insistes en Kali)

```
Kali (VM o dual boot)
  └── Solo: correr rms_desktop + PostgreSQL legacy

Windows 10 (principal)
  └── VentaPOS Laravel + Cursor + ventas Chiclayo
```

Sincroniza código con Git o carpeta compartida `D:\Proyectos\`.

---

## Decisión final

> **Trabaja VentaPOS en Windows 10 con Laragon.**  
> Usa `rms_db.sql` como documentación de la lógica de negocio, no como BD final.  
> La BD final es MySQL multi-tenant en `database/schema_rms_completo.sql`.
