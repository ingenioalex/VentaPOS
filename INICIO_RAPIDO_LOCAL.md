# ⚡ INICIO RÁPIDO EN LOCAL

## 🎯 3 Pasos para Probar SeccionK en Local

### 1️⃣ Requisitos

**Windows (Opción Fácil):**
- Descarga e instala **Laragon Full**: https://laragon.org/download/
- ¡Ya tienes todo! (PHP, MySQL, Node.js)

**O instala manualmente:**
- PHP 8.2+: https://www.php.net/downloads
- Composer: https://getcomposer.org/

### 2️⃣ Clonar Repositorio

```bash
git clone https://github.com/ingenioalex/VentaPOS.git
cd VentaPOS
git checkout cursor/seccionk-module-0c7c
```

### 3️⃣ Ejecutar

**Windows:**
```cmd
INICIAR-SERVIDOR.bat
```

**Linux/Mac:**
```bash
chmod +x iniciar-servidor.sh
./iniciar-servidor.sh
```

Se abrirá automáticamente en tu navegador: **http://localhost:8000/seccionk**

---

## 🌐 URLs

| Sistema | URL |
|---------|-----|
| SeccionK Público | http://localhost:8000/seccionk |
| SeccionK Admin | http://localhost:8000/seccionk/admin/login |
| VentaPOS | http://localhost:8000 |

---

## 🔐 Credenciales

**Admin:**
- Usuario: `admin`
- Contraseña: `seccionk2026`

**Búsqueda pública:**
- DNI: `12345678`
- Apellido: `García`

---

## 📚 Más Información

- **Guía completa:** `SETUP_LOCAL.md`
- **Setup manual:** `setup-local.bat` o `setup-local.sh`
- **Troubleshooting:** `SETUP_LOCAL.md` (sección "Solución de Problemas")

---

## ✨ Qué Probar

1. **Búsqueda pública** en `/seccionk`
   - Busca por DNI + apellido
   - Ve el perfil con timeline de 15 cuotas

2. **Panel admin** en `/seccionk/admin/login`
   - Ve estadísticas en dashboard
   - Gestiona miembros
   - Crea eventos
   - Asigna participantes

3. **Registro** en `/seccionk/registro`
   - Registra un nuevo miembro
   - Búscalo después

---

## 🚀 Después de Probar

Si todo funciona bien en local:

**Despliega a tu VPS:**
1. Lee: `DESPLEGAR_AQUI.md`
2. Ejecuta: `deploy-vps-ruta.sh`
3. ¡Listo en producción!

---

**Estado:** ✅ Listo para probar  
**Branch:** cursor/seccionk-module-0c7c
