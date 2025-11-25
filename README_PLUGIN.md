# UNIACC Tracking Manager

**Versión**: 1.0.1  
**Autor**: UNIACC  
**Fecha de creación**: 25 de Noviembre, 2025  
**Última actualización**: 25 de Noviembre, 2025

---

## 📋 Descripción

Plugin de WordPress para la gestión centralizada de parámetros de tracking (UTM, click IDs) para formularios. Completamente independiente del tema, lo que garantiza que las actualizaciones del tema o de WordPress no afecten el tracking.

### Solución implementada

Un plugin independiente que:
- Se carga en todas las páginas mediante múltiples hooks de WordPress
- Tiene fallbacks agresivos para garantizar la carga
- Almacena parámetros en cookies para persistencia
- Expone funciones globales para los formularios

---

## ✨ Características

- ✅ **Captura automática** de parámetros UTM y click IDs
- ✅ **Persistencia en cookies** (30 días de duración)
- ✅ **Compatible con Elementor** (Canvas, Full Width, etc.)
- ✅ **Funciones globales** `GetValue()` y `GetValueBT()` para formularios
- ✅ **Fallbacks múltiples** para garantizar carga en todas las páginas
- ✅ **Independiente del tema** - no se afecta por actualizaciones
- ✅ **Sin dependencias externas**

---

## 📁 Estructura del Plugin

```
wp-content/plugins/uniacc-tracking-manager/
├── uniacc-tracking-manager.php    # Archivo principal del plugin
├── assets/
│   └── js/
│       └── tracking-manager.js    # Script de captura de parámetros
└── README_PLUGIN.md               # Esta documentación
```

---

## 🔧 Instalación

El plugin ya está instalado y activo. Para verificar:

1. Ve a **WordPress Admin > Plugins**
2. Busca **"UNIACC Tracking Manager"**
3. Debe estar **Activo**

---

## 📊 Parámetros Capturados

### Parámetros UTM
| Parámetro | Descripción |
|-----------|-------------|
| `utm_source` | Fuente de tráfico (google, facebook, etc.) |
| `utm_medium` | Medio de tráfico (cpc, organic, email, etc.) |
| `utm_campaign` | Nombre de la campaña |
| `utm_term` | Término de búsqueda (para ads) |
| `utm_content` | Contenido del anuncio |

### Click IDs (Plataformas de Ads)
| Parámetro | Plataforma |
|-----------|------------|
| `gclid` | Google Ads |
| `gad_source` | Google Ads |
| `gbraid` | Google Ads (iOS) |
| `wbraid` | Google Ads (web-to-app) |
| `fbclid` | Facebook/Meta Ads |
| `msclkid` | Microsoft Ads (Bing) |
| `ttclid` | TikTok Ads |
| `twclid` | Twitter/X Ads |

### Parámetros Adicionales
| Parámetro | Descripción |
|-----------|-------------|
| `landing_page` | URL de la primera página visitada |
| `current_url` | URL actual |
| `referrer` | Página de referencia |
| `organic_source` | Fuente orgánica detectada |
| `organic_medium` | Medio orgánico (siempre "organic") |

---

## 🍪 Cookies Utilizadas

Todas las cookies tienen el prefijo `uniacc_tracking_` y duran **30 días**.

| Cookie | Contenido |
|--------|-----------|
| `uniacc_tracking_utm_source` | Valor de utm_source |
| `uniacc_tracking_utm_medium` | Valor de utm_medium |
| `uniacc_tracking_utm_campaign` | Valor de utm_campaign |
| `uniacc_tracking_gclid` | Valor de gclid |
| `uniacc_tracking_fbclid` | Valor de fbclid |
| `uniacc_tracking_landing_page` | URL de landing page |
| ... | (y más según los parámetros capturados) |

---

## 💻 Uso en Formularios

### Funciones Disponibles

#### `GetValueBT()`
Obtiene los datos del formulario de Beca Talento junto con parámetros de tracking.

```javascript
const formData = GetValueBT();
console.log(formData);
// {
//   Nombre: "Juan",
//   PrimerApellido: "Pérez",
//   email: "juan@email.com",
//   utm_source: "google",
//   utm_medium: "cpc",
//   gclid: "abc123",
//   landing_page: "https://www.uniacc.cl/?utm_source=google",
//   ...
// }
```

#### `GetValue()`
Obtiene los datos del formulario general junto con parámetros de tracking.

```javascript
const formData = GetValue();
console.log(formData);
// Similar a GetValueBT() pero para el formulario general
```

#### `window.uniaccTrackingManager.getParams()`
Obtiene solo los parámetros de tracking (sin datos del formulario).

```javascript
const params = window.uniaccTrackingManager.getParams();
console.log(params);
// {
//   utm_source: "google",
//   utm_medium: "cpc",
//   utm_campaign: "test",
//   gclid: "abc123",
//   landing_page: "https://www.uniacc.cl/",
//   ...
// }
```

#### `window.uniaccTrackingManager.getParam(name)`
Obtiene un parámetro específico.

```javascript
const source = window.uniaccTrackingManager.getParam('utm_source');
console.log(source); // "google"
```

#### `window.uniaccTrackingManager.clearTracking()`
Limpia todas las cookies de tracking.

```javascript
window.uniaccTrackingManager.clearTracking();
// Todas las cookies uniacc_tracking_* son eliminadas
```

---

## 🧪 Script de Diagnóstico

Ejecuta este código en la consola del navegador para verificar que todo funciona:

```javascript
// DIAGNÓSTICO COMPLETO - UNIACC TRACKING MANAGER
console.log('%c=== DIAGNÓSTICO UNIACC TRACKING ===', 'color: #4CAF50; font-size: 16px; font-weight: bold;');

// 1. Verificar Tracking Manager
const tmLoaded = typeof window.uniaccTrackingManager !== 'undefined';
console.log('1. Tracking Manager:', tmLoaded ? '✅ SÍ' : '❌ NO');

// 2. Verificar script en DOM
const scriptTag = document.querySelector('script[src*="tracking-manager.js"]');
console.log('2. Script en DOM:', scriptTag ? '✅ SÍ' : '❌ NO');

// 3. Verificar funciones
console.log('3. GetValue:', typeof GetValue === 'function' ? '✅ SÍ' : '❌ NO');
console.log('4. GetValueBT:', typeof GetValueBT === 'function' ? '✅ SÍ' : '❌ NO');

// 4. Parámetros
if (tmLoaded) {
    const params = window.uniaccTrackingManager.getParams();
    console.log('5. Parámetros:', params);
}

// 5. Cookies
const cookies = document.cookie.split(';').filter(c => c.trim().startsWith('uniacc_tracking_'));
console.log('6. Cookies de tracking:', cookies.length);

// 6. Resumen
const allOk = tmLoaded && typeof GetValue === 'function' && typeof GetValueBT === 'function';
console.log(allOk ? '%c✅ TODO FUNCIONA CORRECTAMENTE' : '%c⚠️ HAY PROBLEMAS', 
    allOk ? 'color: #4CAF50; font-weight: bold;' : 'color: #F44336; font-weight: bold;');
```

---

## 🔄 Flujo de Funcionamiento

```
1. Usuario llega con parámetros UTM
   https://www.uniacc.cl/?utm_source=google&utm_medium=cpc&gclid=abc123
   
2. tracking-manager.js captura los parámetros
   └── Guarda en cookies (30 días)
   └── Guarda landing_page
   
3. Usuario navega a otra página (ej: /beca-talento-2025/)
   └── tracking-manager.js lee las cookies
   └── Parámetros disponibles en window.uniaccTrackingManager
   
4. Usuario llena el formulario y envía
   └── GetValueBT() combina datos del formulario + tracking
   └── Se envía todo al CRM vía AJAX
   
5. Usuario es redirigido a página de gracias
   └── Los parámetros siguen disponibles (cookies persisten)
```

---

## ⚠️ Troubleshooting

### El Tracking Manager no se carga en algunas páginas

**Causa probable**: Caché de Cloudflare

**Solución**:
1. Ir a WordPress Admin > Configuración > Cloudflare
2. Click en "Purge Cache" > "Purge Everything"
3. O desde el panel de Cloudflare: Caching > Configuration > Purge Everything

### Error "Unexpected token 'catch'" en scripts.js

**Causa probable**: Caché del navegador

**Solución**:
1. Forzar recarga: Ctrl+F5 (Windows) o Cmd+Shift+R (Mac)
2. O purgar caché de Cloudflare

### GetValue/GetValueBT no están disponibles

**Causa probable**: El script aún no terminó de cargar

**Solución**:
```javascript
// Esperar a que cargue
setTimeout(function() {
    if (typeof GetValueBT === 'function') {
        const data = GetValueBT();
        console.log(data);
    }
}, 1000);
```

### Los parámetros no persisten entre páginas

**Verificar**:
1. Que las cookies estén habilitadas en el navegador
2. Que no haya bloqueadores de cookies
3. Ejecutar diagnóstico para ver si hay cookies guardadas

---

## 📝 Historial de Cambios

### v1.0.1 (25 Nov 2025)
- Mejorado sistema de fallback con múltiples intentos de carga
- Agregado soporte para páginas Elementor Canvas
- Mejorado diagnóstico con mensajes de consola más claros
- Agregados más hooks de WordPress para garantizar carga
- Agregado manejo de errores mejorado

### v1.0.0 (25 Nov 2025)
- Versión inicial del plugin
- Captura de parámetros UTM y click IDs
- Persistencia en cookies
- Funciones GetValue() y GetValueBT()
- Sistema de fallback básico

---

## 🔗 Integración con Backend (CRM.php)

El archivo `functions/CRM.php` del tema procesa los datos enviados por los formularios. **Este archivo permanece en el tema** porque:
- Procesa datos completos del formulario (no solo tracking)
- Tiene dependencias de ACF (`get_field`) para códigos de programa
- Ya está correctamente configurado

### Ubicación
```
wp-content/themes/AstraChildTheme/functions/CRM.php
```

### Funciones que reciben tracking

#### `envDatosCRM()` - Formulario General
- **Endpoint CRM**: `https://crmadmision.uniacc.cl/webservice/formulario_web.php`
- **Tabla backup**: `wp_backup_form_general`
- **Parámetros de tracking recibidos**:
  ```php
  $utm_source, $utm_medium, $utm_term, $utm_content, $utm_campaign,
  $gclid, $gad_source, $gbraid, $wbraid, $fbclid, $msclkid, $ttclid, $twclid,
  $organic_source, $organic_medium, $landing_page, $referrer, $current_url
  ```

#### `SendBTCRM()` - Formulario Beca Talento
- **Endpoint CRM**: `https://crmdifusion.uniacc.cl/webservice/ws_recibeBT.php`
- **Tabla backup**: `wp_backup_form_bt`
- **Parámetros de tracking recibidos**: (mismos que envDatosCRM)

### Flujo de datos completo

```
┌─────────────────────────────────────────────────────────────────┐
│  FRONTEND (Plugin)                                              │
├─────────────────────────────────────────────────────────────────┤
│  1. tracking-manager.js captura UTM/gclid de URL                │
│  2. Guarda en cookies (30 días)                                 │
│  3. GetValueBT() combina formulario + tracking                  │
│  4. Envía vía AJAX a admin-ajax.php                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  BACKEND (Tema - CRM.php)                                       │
├─────────────────────────────────────────────────────────────────┤
│  5. SendBTCRM() recibe $_POST['form']                           │
│  6. Extrae parámetros de tracking                               │
│  7. Obtiene código de programa (ACF)                            │
│  8. Envía JSON al CRM externo (cURL)                            │
│  9. Guarda backup en wp_backup_form_bt                          │
│  10. Retorna respuesta al frontend                              │
└─────────────────────────────────────────────────────────────────┘
```

### Endpoints AJAX

| Action | Función | Uso |
|--------|---------|-----|
| `envDatosCRM` | `envDatosCRM()` | Formulario general |
| `SendBTCRM` | `SendBTCRM()` | Formulario Beca Talento |
| `modalidadesProgramas` | `modalidadesProgramas()` | Obtener modalidades |

---

## 📂 Archivos del Tema Modificados

Los siguientes archivos del tema fueron modificados para remover el código de tracking duplicado:

### `functions/Core.php`
- Líneas 44-58: Comentado `enqueue_tracking_manager_global`
- Línea 68: Actualizado versión de scripts.js a 1.8.8

### `assets/js/scripts.js`
- Líneas 1-90: Comentadas funciones GetValue y GetValueBT originales
- Línea 589: Comentario indicando que las funciones vienen del plugin

### `header.php`
- Removido script directo de tracking-manager.js

### `functions/CRM.php`
- **Sin cambios** - Ya estaba preparado para recibir todos los parámetros de tracking
- Recibe y procesa: UTM, click IDs, landing_page, referrer, current_url

**Nota**: El código de tracking está comentado (no eliminado) por si necesitas revertir.

---

## 🔒 Seguridad

- El plugin solo se carga en el frontend (no en admin)
- Los valores de cookies son sanitizados con `encodeURIComponent`
- No se ejecutan scripts de terceros
- Compatible con políticas de cookies (SameSite=Lax)

---

## 📞 Soporte

Para problemas o preguntas sobre este plugin, contactar al equipo de desarrollo.

---

## 📋 Checklist de Verificación Post-Despliegue

- [ ] Plugin activo en WordPress
- [ ] Caché de Cloudflare purgada
- [ ] Diagnóstico ejecutado en página principal
- [ ] Diagnóstico ejecutado en /beca-talento-2025/
- [ ] Parámetros persisten entre páginas
- [ ] Formulario envía parámetros de tracking al CRM
- [ ] Sin errores en consola JavaScript
