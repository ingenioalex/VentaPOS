# Cómo seguimos — VentasÁgil

Acuerdo de trabajo a partir del 1 de noviembre de 2026. El hilo anterior está en [CHAT-CURSOR-2026.md](CHAT-CURSOR-2026.md). Este hilo está en [CHAT-CURSOR-2026-11-01.md](CHAT-CURSOR-2026-11-01.md).

## Dónde se trabaja

| Lugar | Uso |
|-------|-----|
| Disco local `E:\Proyectos\VentaPOS_build` | Código del día a día. Arranque con `.\iniciar.ps1` o `recuperar-local.bat`. |
| GitHub [ingenioalex/VentaPOS](https://github.com/ingenioalex/VentaPOS) | Historial y copia del proyecto. Rama de trabajo publicada: `main`. |
| VPS de producción (`ventasagil.com`, app en `/var/www/ventapos`) | Sitio en línea. Cambios de código solo cuando se pida desplegar. |

## Base de datos del VPS

No modificar la base de datos del VPS (ni `migrate`, ni SQL, ni scripts que escriban o borren datos) **salvo que se indique de forma explícita**.

En local sí se puede migrar y probar. Un cambio de esquema se prepara en el repo y se aplica en el servidor solo con esa indicación.

## Mejoras

En el camino se pueden mejorar el aspecto (look) y los procesos del POS, la landing y el panel, siempre dejando el cambio en el repo y documentado en `docs/`.
