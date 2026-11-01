# Guía: subir CV a LinkedIn y usar el documento mejorado

## Archivos generados

| Archivo | Uso |
|---------|-----|
| `docs/personal/Curriculum_Yuri_Vargas_Lazo_MEJORADO.md` | Texto base del CV mejorado |
| `docs/personal/GUIA_LINKEDIN.md` | Esta guía |
| `public/cv/yuri-vargas-lazo-cv-2026.pdf` | CV en PDF (web y LinkedIn) |
| Word / PDF local | `d:\YURI ALEXANDER VARGAS LAZO_CV 2026_ok.pdf` |

**LinkedIn:** https://www.linkedin.com/in/yuri-alexander-vargas-l-1774142b9/  
**CV público en web:** https://ventasagil.com/cv/yuri-vargas-lazo-cv-2026.pdf

---

## 1. Pasar el CV a Word (5 minutos)

1. Abre `Curriculum_Yuri_Vargas_Lazo_MEJORADO.md` en Cursor o Bloc de notas.
2. Copia todo el contenido.
3. Pégalo en tu Word (`Curriculum_Yuri_Vargas_Lazo_FORMATO.docx`) reemplazando el texto antiguo.
4. Ajusta fuente (Calibri 11), títulos en negrita y márgenes.
5. **Quita datos duplicados** del Word viejo (había párrafos repetidos).
6. Guarda como PDF: **Archivo → Guardar como → PDF** (`CV_Yuri_Vargas_2026.pdf`).

---

## 2. Crear / actualizar perfil LinkedIn

### Titular (headline) — máx. 220 caracteres

```
Gerente Retail & Líder Comercial | 20+ años en Tottus y Falabella | Ingeniero de Sistemas | Fundador de VentasÁgil (POS para bodegas)
```

### Acerca de (About)

Copia la sección **Perfil profesional** del CV mejorado y añade al final:

```
Hoy lidero VentasÁgil (ventasagil.com), un POS en la nube pensado para bodegas y negocios locales en Chiclayo y el Perú, uniendo mi experiencia en piso de venta con desarrollo de software.
```

### Experiencia — cargar en este orden

1. **Fundador — VentasÁgil** (2024 – actualidad)
2. **Gerente de Tienda — Hiperbodega Precio Uno** (mar 2022 – abr 2025)
3. **Jefe Comercial Alimentación y Perecibles — Hiperbodega** (2020 – 2022)
4. **Jefe Comercial Non Food — Hiperbodega** (2017 – 2020)
5. **Jefe Control de Existencias — Tottus** (2015 – 2017)
6. (Opcional) cargos anteriores Tottus y sistemas

En cada puesto: 3–5 viñetas con **resultados**, no solo tareas.

### Educación

- Ingeniero de Sistemas — UNPRG (titulado 2018)

### Licencias y certificaciones (destacar)

- Liderazgo Organizacional — Overlap/Tottus (2021)
- Programa Líderes de Ecosistema — Falabella (2023)
- Control Merma Desconocida — Tottus (2016)
- Diplomado Oracle 10g — UNPRG

Puedes añadir más desde tu Excel (`report_437_...xlsx`): filtra por "Liderazgo", "Problem Solving", "Ética".

### Competencias (Skills) — añadir y pedir endorsements

Gerencia retail · Liderazgo de equipos · Ventas · Control de inventarios · Excel avanzado · Gestión comercial · Punto de venta · Laravel · MySQL · Atención al cliente · KPIs · Merma y shrinkage

---

## 3. Subir el CV en PDF a LinkedIn

Usa tu archivo: **`YURI ALEXANDER VARGAS LAZO_CV 2026_ok.pdf`** (o el mismo en la web).

1. Entra a [linkedin.com/in/yuri-alexander-vargas-l-1774142b9](https://www.linkedin.com/in/yuri-alexander-vargas-l-1774142b9/).
2. Clic en **Añadir sección del perfil** (o icono **+** en el perfil).
3. Elige **Destacado** → **Medios** → **Añadir**.
4. Sube `YURI ALEXANDER VARGAS LAZO_CV 2026_ok.pdf`.
5. **Título:** `CV Yuri Vargas Lazo — Gerencia Retail & VentasÁgil`.
6. **Descripción:** `Ingeniero de Sistemas | 20+ años retail Tottus/Falabella | Fundador VentasÁgil`.
7. Guardar.

Opcional: en **Información de contacto** verifica que aparezcan `ingeniovargas@gmail.com` y el enlace a ventasagil.com.

---

## 4. URL personalizada

**Editar perfil público** → URL personalizada:  
`linkedin.com/in/yuri-alexander-vargas-l-1774142b9` (ya activa).

Actualiza el enlace en la página de contacto de VentasÁgil cuando lo tengas.

---

## 5. Foto y banner

- **Foto:** fondo neutro, rostro visible, sin filtros fuertes.
- **Banner:** logo VentasÁgil + texto “POS para bodegas · Chiclayo · ventasagil.com” (Canva 1584×396 px).

---

## 6. PowerShell — subir código a GitHub (opcional)

```powershell
cd D:\Proyectos\VentaPOS_build
.\deploy\git-push-github.ps1 -Mensaje "Seccion fundador en contacto"
```

(Pide token de GitHub; usuario `ingenioalex`.)

---

*Generado junio 2026 — VentaPOS / VentasÁgil*
