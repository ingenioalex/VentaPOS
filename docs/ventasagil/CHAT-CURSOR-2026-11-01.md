# Chat Cursor — continuación VentasÁgil (1 nov 2026)

Hilo abierto en Cursor sobre `E:\Proyectos\VentaPOS_build` para retomar el proyecto **ventasagil.com**.

- Mensajes de este chat (usuario y asistente).
- Sin secretos.
- Fecha: 2026-11-01.

El historial largo (247 mensajes, hasta el cambio de PC) sigue en [CHAT-CURSOR-2026.md](CHAT-CURSOR-2026.md).

---

## Usuario (1)

traslada aqui la conversacion del poyecto ventasagil ventapos

### Asistente

En esta PC no estaba el texto de ese hilo. El índice local solo tenía un chat corto de hoy. En la nube del repo aparecían agentes sueltos (Uso local pos, interfaces en español, Kali, perfil), sin el mensaje a mensaje.

## Usuario (2)

buscalo en github.com/ingenioalex/VentaPOS/tree/main/docs/ventasagil

### Asistente

Ahí está el archivo. Se copió a este proyecto:

- `docs/ventasagil/CHAT-CURSOR-2026.md` — 247 mensajes, exportado el 1 nov 2026, hilo `89fc26d9-47ea-4261-97b6-4812db4435f2`.
- `docs/ventasagil/README.md`

Empieza con el error de comillas en `iniciar.ps1` y recorre POS, usuarios, impresora, marca, DNS/SSL y arranque local.

Donde se cortó:

1. Let's Encrypt ya se renueva solo. Para `*.ventasagil.com` falta un token DNS nuevo de Hostinger y `deploy/ventasagil-ssl-wildcard.sh`. No restablecer los registros DNS.
2. El local se apaga si se cierra la ventana de `iniciar.ps1`. Recuperación: `recuperar-local.bat` o `.\iniciar.ps1`. URL: http://127.0.0.1:8000/login
3. En la PC nueva: abrir la carpeta `VentaPOS_build` ya copiada. No clonar encima. El `.env` y MySQL no viajan solos con Git.
4. El último pedido de ese hilo fue exportar el chat a `docs/ventasagil` y subirlo. Eso ya está en `main`.

## Usuario (3)

sube este chat aqui para seguir con el proyecto de ventasagil.com y ver mejoras de look y procesos en el camino trabajar con git hub y con el local del proyecto en disco E:\Proyectos\VentaPOS_build y con el VPS contratado pero en el VPS no debemos modificar la base de datos a menos que se indique

### Asistente

Se deja este hilo y el acuerdo de trabajo en `docs/ventasagil/` y se sube a `main`.

- Local: `E:\Proyectos\VentaPOS_build`
- GitHub: https://github.com/ingenioalex/VentaPOS
- VPS: ventasagil.com, sin cambios de base de datos salvo indicación explícita
- En el camino: mejoras de look y de procesos, documentadas en `docs/`

Detalle operativo: [CONTEXTO-TRABAJO.md](CONTEXTO-TRABAJO.md).
