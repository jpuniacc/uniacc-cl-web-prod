# UNIACC Tracking Manager

![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.0%2B-purple.svg)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)

Plugin de WordPress para la gestión centralizada de parámetros de tracking (UTM y Click IDs) en formularios web. Completamente independiente del tema de WordPress.

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Uso](#-uso)
- [Parámetros Soportados](#-parámetros-soportados)
- [API JavaScript](#-api-javascript)
- [Arquitectura](#-arquitectura)
- [Integración con CRM](#-integración-con-crm)
- [Troubleshooting](#-troubleshooting)
- [Documentación Técnica](#-documentación-técnica)

## ✨ Características

### Captura Automática de Parámetros
- **Parámetros UTM**: `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`
- **Click IDs de Plataformas**:
  - Google Ads: `gclid`, `gad_source`, `gbraid`, `wbraid`
  - Facebook/Meta: `fbclid`
  - Microsoft Ads: `msclkid`
  - TikTok: `ttclid`
  - Twitter/X: `twclid`

### Persistencia Inteligente
- ✅ Almacenamiento en cookies por 30 días
- ✅ Detección automática de tráfico orgánico
- ✅ Tracking de landing page
- ✅ Fallback automático a cookies cuando no hay parámetros en URL

### Compatibilidad y Confiabilidad
- ✅ Compatible con Elementor (Canvas, Full Width)
- ✅ Independiente del tema de WordPress
- ✅ Sistema de carga con múltiples fallbacks
- ✅ No requiere jQuery (opcional para formularios)
- ✅ Funciona con actualizaciones de WordPress y temas

## 🔧 Requisitos

- WordPress 5.0 o superior
- PHP 7.0 o superior
- Navegador moderno con soporte para:
  - URLSearchParams API
  - Cookies API
  - ES6+ JavaScript

## 📦 Instalación

### Opción 1: Instalación Manual

1. Descarga o clona este repositorio
2. Copia la carpeta completa a `/wp-content/plugins/`
3. Activa el plugin desde el panel de WordPress

```bash
cd wp-content/plugins/
git clone [URL-del-repositorio] uniacc-tracking-manager
```

### Opción 2: Upload via WordPress Admin

1. Comprime la carpeta del plugin en formato ZIP
2. Ve a **Plugins > Añadir nuevo > Subir plugin**
3. Selecciona el archivo ZIP y haz clic en **Instalar ahora**
4. Activa el plugin

## 🚀 Uso

### Captura Automática

El plugin captura automáticamente los parámetros cuando un usuario visita tu sitio con una URL como:

```
https://tudominio.cl/?utm_source=google&utm_medium=cpc&utm_campaign=verano2024&gclid=ABC123
```

### Integración en Formularios

#### Función GetValueBT() - Formulario Beca Talento

```javascript
// Captura datos del formulario + tracking
var datos = GetValueBT();

// Estructura del objeto retornado:
{
    nombre: "Juan",
    primerApellido: "Silva",
    rut: "12345678-9",
    email: "juan@example.com",
    telefono: "+56912345678",

    // Parámetros de tracking
    fuente: "google",
    medio: "cpc",
    campana: "verano2024",
    gclid: "ABC123",
    // ... más parámetros
}
```

#### Función GetValue() - Formulario General

```javascript
// Captura datos del formulario + tracking
var datos = GetValue();

// Envío por AJAX
jQuery.ajax({
    type: 'POST',
    url: '/wp-admin/admin-ajax.php',
    data: {
        action: 'envDatosCRM',
        datos: JSON.stringify(datos)
    },
    success: function(response) {
        console.log('Datos enviados correctamente');
    }
});
```

### API de Bajo Nivel

```javascript
// Obtener todos los parámetros de tracking
var params = window.uniaccTrackingManager.getParams();

// Obtener un parámetro específico
var source = window.uniaccTrackingManager.getParam('fuente');

// Limpiar todos los parámetros
window.uniaccTrackingManager.clearTracking();
```

## 📊 Parámetros Soportados

### Parámetros UTM

| Parámetro URL | Cookie | Descripción |
|--------------|--------|-------------|
| `utm_source` | `uniacc_tracking_fuente` | Fuente de tráfico (google, facebook, etc.) |
| `utm_medium` | `uniacc_tracking_medio` | Medio (cpc, email, social, etc.) |
| `utm_campaign` | `uniacc_tracking_campana` | Nombre de la campaña |
| `utm_term` | `uniacc_tracking_palabraclave` | Palabra clave de búsqueda |
| `utm_content` | `uniacc_tracking_contenido` | Variante del anuncio |

### Click IDs de Plataformas

| Parámetro URL | Cookie | Plataforma |
|--------------|--------|------------|
| `gclid` | `uniacc_tracking_gclid` | Google Ads |
| `gad_source` | `uniacc_tracking_gad_source` | Google Ads (nueva) |
| `gbraid` | `uniacc_tracking_gbraid` | Google Analytics 4 |
| `wbraid` | `uniacc_tracking_wbraid` | Google Analytics 4 |
| `fbclid` | `uniacc_tracking_fbclid` | Facebook/Meta |
| `msclkid` | `uniacc_tracking_msclkid` | Microsoft Ads |
| `ttclid` | `uniacc_tracking_ttclid` | TikTok |
| `twclid` | `uniacc_tracking_twclid` | Twitter/X |

### Parámetros Automáticos

| Cookie | Descripción |
|--------|-------------|
| `uniacc_tracking_landing_page` | Primera URL visitada |
| `uniacc_tracking_referencia` | Dominio de referencia (detectado automáticamente) |

## 🏗️ Arquitectura

### Estructura del Proyecto

```
uniacc-tracking-manager/
├── uniacc-tracking-manager.php     # Plugin principal (PHP)
├── assets/
│   └── js/
│       └── tracking-manager.js     # Lógica de captura (JavaScript)
├── README.md                        # Este archivo
└── README_PLUGIN.md                # Documentación técnica completa
```

### Flujo de Datos

```
Usuario llega con parámetros UTM
    ↓
tracking-manager.js captura desde URL
    ↓
Almacena en cookies (30 días)
    ↓
Usuario navega a página de formulario
    ↓
Cookies se cargan automáticamente
    ↓
GetValueBT() o GetValue() combina datos
    ↓
Envío AJAX a admin-ajax.php
    ↓
Backend CRM.php procesa
    ↓
Datos enviados a CRM externo
    ↓
Respaldo guardado en base de datos
```

### Sistema de Carga con Fallbacks

El plugin implementa una estrategia agresiva de carga para garantizar funcionamiento en cualquier configuración:

1. **Carga Principal**: Hooks de WordPress (wp_head, wp_enqueue_scripts, wp_footer)
2. **Fallbacks PHP**: Múltiples hooks con prioridades diferentes
3. **Fallbacks JavaScript**: Timeouts a 50ms, 200ms, 500ms, 1s, 2s
4. **Eventos DOM**: DOMContentLoaded y load event

## 🔗 Integración con CRM

### Backend Esperado

El plugin espera que existan las siguientes funciones en tu tema:

**Ubicación**: `wp-content/themes/AstraChildTheme/functions/CRM.php`

```php
// Para formularios generales
function envDatosCRM() {
    // Recibe datos via $_POST['datos']
    // Procesa y envía a CRM
}

// Para formulario Beca Talento
function SendBTCRM() {
    // Recibe datos via $_POST['datos']
    // Procesa y envía a CRM específico
}
```

### Endpoints CRM Externos

- **General**: `https://crmadmision.uniacc.cl/webservice/formulario_web.php`
- **Beca Talento**: `https://crmdifusion.uniacc.cl/webservice/ws_recibeBT.php`

### Tablas de Respaldo

- `wp_backup_form_general` - Formularios generales
- `wp_backup_form_bt` - Formularios Beca Talento

## 🐛 Troubleshooting

### Verificar que el Plugin Funciona

Abre la consola del navegador (F12) y ejecuta:

```javascript
// Ver todos los parámetros capturados
console.log(window.uniaccTrackingManager.getParams());

// Ver cookies
document.cookie.split(';').filter(c => c.includes('uniacc_tracking'));
```

### Problemas Comunes

#### El script no carga

1. Verifica que el plugin está activado
2. Limpia caché de WordPress/servidor
3. Revisa la consola del navegador por errores
4. Verifica que no estés en una página de admin

#### Los parámetros no se capturan

1. Verifica que la URL tenga parámetros válidos
2. Comprueba que las cookies estén habilitadas
3. Revisa que no haya bloqueadores de cookies activos
4. Limpia las cookies y prueba con una URL completa

#### GetValue() no está definido

- Asegúrate de que el script haya cargado completamente
- Verifica que no haya conflictos con otros plugins
- Revisa los fallbacks en la consola del navegador

### Debug Mode

Para ver logs detallados, abre la consola y busca mensajes que comiencen con:
- `[UNIACC Tracking Manager]`
- `[Tracking Manager]`

## 📚 Documentación Técnica

Para documentación técnica completa, consulta:

- **[README_PLUGIN.md](README_PLUGIN.md)** - Documentación técnica detallada en español
  - Especificaciones técnicas completas
  - Diagramas de flujo de datos
  - Guía de integración con backend
  - Checklist post-despliegue
  - Troubleshooting avanzado

## 🔒 Seguridad

- ✅ Prevención de acceso directo a archivos PHP
- ✅ Sanitización de inputs con funciones WordPress
- ✅ Cookies con atributos SameSite=Lax y Secure
- ✅ No ejecuta código en admin de WordPress
- ✅ Validación de parámetros antes de almacenar

## 📝 Changelog

### v1.0.1 (2025-11-25)
- Mejoras en el sistema de fallbacks
- Soporte para Elementor Canvas
- Diagnósticos mejorados en consola
- Más hooks de WordPress para mayor confiabilidad
- Mejor manejo de errores

### v1.0.0 (2025-11-25)
- Lanzamiento inicial
- Captura de UTM y Click IDs
- Persistencia en cookies
- Funciones GetValue() y GetValueBT()
- Sistema básico de fallbacks

## 👥 Soporte

Para reportar problemas o solicitar características:
- Abre un issue en este repositorio
- Contacta al equipo de desarrollo de UNIACC

## 📄 Licencia

Este es un proyecto propietario de UNIACC. Todos los derechos reservados.

---

**Desarrollado para Universidad UNIACC** | Última actualización: Noviembre 2025
