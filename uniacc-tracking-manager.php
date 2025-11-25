<?php
/**
 * Plugin Name: UNIACC Tracking Manager
 * Plugin URI: https://www.uniacc.cl
 * Description: Gestión centralizada de parámetros de tracking (UTM, click IDs) para formularios. Completamente independiente del tema.
 * Version: 1.0.0
 * Author: UNIACC
 * Author URI: https://www.uniacc.cl
 * License: GPL v2 or later
 * Text Domain: uniacc-tracking-manager
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('UNIACC_TM_VERSION', '1.0.1');
define('UNIACC_TM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UNIACC_TM_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Cargar el tracking manager en todas las páginas
 * Completamente independiente del tema
 */
class UNIACC_Tracking_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Cargar tracking-manager.js de forma temprana en múltiples hooks para máxima compatibilidad
        add_action('wp_head', array($this, 'load_tracking_manager'), 1);
        add_action('wp_footer', array($this, 'load_tracking_manager'), 1);
        add_action('wp_enqueue_scripts', array($this, 'load_tracking_manager'), 1);
        // Hook adicional para páginas que no usan wp_head/wp_footer
        add_action('wp_print_scripts', array($this, 'load_tracking_manager'), 1);
        
        // Fallback TEMPRANO: Ejecutar también en wp_head con prioridad alta para detectar problemas
        add_action('wp_head', array($this, 'load_tracking_manager_fallback'), 999);
        
        // Cargar funciones de formulario de forma temprana
        add_action('wp_footer', array($this, 'load_form_functions'), 999);
        add_action('wp_print_footer_scripts', array($this, 'load_form_functions'), 999);
        
        // Fallback: Cargar dinámicamente si no se cargó en los hooks anteriores
        // Ejecutar en múltiples hooks para máxima compatibilidad
        add_action('wp_footer', array($this, 'load_tracking_manager_fallback'), 9999);
        add_action('wp_print_footer_scripts', array($this, 'load_tracking_manager_fallback'), 9999);
        // También ejecutar en wp_head como último recurso
        add_action('wp_head', array($this, 'load_tracking_manager_fallback'), 99999);
        
        // Hook adicional para páginas de Elementor Canvas que pueden no ejecutar wp_head/wp_footer
        add_action('wp_print_scripts', array($this, 'load_tracking_manager_fallback'), 999);
    }
    
    /**
     * Cargar tracking-manager.js
     * SOLO desde el plugin - completamente independiente del tema
     */
    public function load_tracking_manager() {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        
        // Solo cargar en frontend (no en admin)
        if (is_admin()) {
            return;
        }
        
        // SOLO usar el archivo del plugin
        $plugin_path = UNIACC_TM_PLUGIN_DIR . 'assets/js/tracking-manager.js';
        
        if (file_exists($plugin_path)) {
            $script_url = UNIACC_TM_PLUGIN_URL . 'assets/js/tracking-manager.js';
            // Usar echo directo para máxima compatibilidad con todas las páginas
            echo '<script src="' . esc_url($script_url) . '?ver=' . UNIACC_TM_VERSION . '"></script>' . "\n";
            $loaded = true;
        } else {
            // Si el archivo no existe, mostrar error en consola
            echo '<script>console.error("UNIACC Tracking Manager: Archivo tracking-manager.js no encontrado en el plugin");</script>' . "\n";
        }
    }
    
    /**
     * Fallback: Cargar tracking manager dinámicamente si no se cargó antes
     * SOLO desde el plugin
     * Este método se ejecuta en múltiples hooks para máxima compatibilidad
     */
    public function load_tracking_manager_fallback() {
        static $executed = false;
        
        // Solo ejecutar si el tracking manager no está disponible
        $plugin_path = UNIACC_TM_PLUGIN_DIR . 'assets/js/tracking-manager.js';
        
        if (!file_exists($plugin_path)) {
            return;
        }
        
        $script_url = UNIACC_TM_PLUGIN_URL . 'assets/js/tracking-manager.js';
        
        // Permitir ejecutar múltiples veces pero solo una vez por hook
        $hook_name = current_filter();
        static $executed_hooks = array();
        if (isset($executed_hooks[$hook_name])) {
            return;
        }
        $executed_hooks[$hook_name] = true;
        
        // Script inline que verifica y carga dinámicamente si es necesario
        // Este script se ejecuta de forma inmediata y agresiva
        echo '<script id="uniacc-tracking-manager-fallback-' . esc_attr($hook_name) . '">
        (function() {
            // Verificar si el tracking manager ya está cargado
            if (typeof window.uniaccTrackingManager !== "undefined") {
                console.log("✅ Tracking Manager ya está cargado (fallback desde ' . esc_js($hook_name) . ')");
                return; // Ya está cargado, no hacer nada
            }
            
            console.log("🔍 Fallback ejecutándose desde hook: ' . esc_js($hook_name) . '");
            
            // Verificar si ya existe un script con el mismo ID para evitar duplicados
            var existingScript = document.getElementById("uniacc-tracking-manager-script");
            if (existingScript) {
                console.log("⚠️ Script tracking-manager.js ya existe en el DOM, pero Tracking Manager no está disponible");
                console.log("   Esto puede indicar un error en el script o que no se ejecutó correctamente");
                // Intentar eliminar y recargar
                existingScript.remove();
            }
            
            // Función para cargar el script de forma síncrona
            function loadTrackingManager() {
                // Verificar nuevamente antes de cargar
                if (typeof window.uniaccTrackingManager !== "undefined") {
                    console.log("✅ Tracking Manager ya disponible, no es necesario cargar");
                    return;
                }
                
                // Verificar si ya hay un script cargándose
                var existingScript = document.getElementById("uniacc-tracking-manager-script");
                if (existingScript && existingScript.getAttribute("data-loading") === "true") {
                    return; // Ya se está cargando
                }
                
                var script = document.createElement("script");
                script.src = "' . esc_js($script_url) . '?ver=' . UNIACC_TM_VERSION . '&t=" + Date.now();
                script.async = false;
                script.defer = false;
                script.id = "uniacc-tracking-manager-script";
                script.setAttribute("data-loading", "true");
                
                // Esperar a que el script se cargue
                script.onload = function() {
                    console.log("📥 Script tracking-manager.js cargado, verificando disponibilidad...");
                    script.removeAttribute("data-loading");
                    setTimeout(function() {
                        if (typeof window.uniaccTrackingManager !== "undefined") {
                            console.log("✅ Tracking Manager cargado dinámicamente desde fallback");
                        } else {
                            console.error("❌ Tracking Manager no se cargó correctamente después de onload");
                            console.error("   El script se cargó pero window.uniaccTrackingManager no está disponible");
                            console.error("   Posibles causas: error de sintaxis en tracking-manager.js o conflicto con otro script");
                            // Intentar recargar una vez más después de un delay
                            setTimeout(function() {
                                if (typeof window.uniaccTrackingManager === "undefined") {
                                    console.log("🔄 Reintentando carga del Tracking Manager...");
                                    loadTrackingManager();
                                }
                            }, 1000);
                        }
                    }, 200);
                };
                
                script.onerror = function() {
                    console.error("❌ Error al cargar Tracking Manager desde:", script.src);
                    console.error("   Verificar que el archivo existe en el servidor");
                    script.removeAttribute("data-loading");
                };
                
                console.log("🔄 Intentando cargar Tracking Manager desde:", script.src);
                
                // Insertar en el head o body de forma inmediata
                var target = document.head || document.getElementsByTagName("head")[0] || document.body || document.documentElement;
                if (target) {
                    target.appendChild(script);
                } else {
                    // Si no hay target, esperar un momento y reintentar
                    setTimeout(function() {
                        var target = document.head || document.body || document.documentElement;
                        if (target) {
                            target.appendChild(script);
                        }
                    }, 50);
                }
            }
            
            // Intentar cargar inmediatamente sin esperar nada
            loadTrackingManager();
            
            // También intentar después de pequeños delays para asegurar carga
            setTimeout(loadTrackingManager, 50);
            setTimeout(loadTrackingManager, 200);
            setTimeout(loadTrackingManager, 500);
            setTimeout(loadTrackingManager, 1000);
            setTimeout(loadTrackingManager, 2000);
            
            // Si el DOM aún no está listo, esperar a que lo esté
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(loadTrackingManager, 100);
                });
            } else if (document.readyState === "interactive") {
                setTimeout(loadTrackingManager, 100);
            }
            
            // También intentar cuando la página esté completamente cargada
            if (document.readyState !== "complete") {
                window.addEventListener("load", function() {
                    setTimeout(function() {
                        if (typeof window.uniaccTrackingManager === "undefined") {
                            loadTrackingManager();
                        }
                    }, 100);
                });
            } else {
                // Si la página ya está cargada, intentar de inmediato
                setTimeout(loadTrackingManager, 100);
            }
        })();
        </script>' . "\n";
    }
    
    /**
     * Cargar funciones de formulario inline para máxima compatibilidad
     */
    public function load_form_functions() {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        
        // Cargar funciones directamente inline, asegurando que jQuery esté disponible
        echo '<script>' . $this->get_form_functions_js() . '</script>' . "\n";
        $loaded = true;
    }
    
    /**
     * Obtener el código JavaScript de las funciones de formulario
     */
    private function get_form_functions_js() {
        return '
        // UNIACC Tracking Manager - Funciones de formulario
        // Definir funciones directamente sin esperar a jQuery
        // NO usar IIFE para que las funciones estén disponibles globalmente
        
        // Función GetValueBT - se define inmediatamente
        // Incluye lógica de persistencia de parámetros de tracking
        if (typeof window.GetValueBT === "undefined") {
            window.GetValueBT = function(){
                    // FORZAR CAPTURA DE PARÁMETROS JUSTO ANTES DE RETORNAR
                    // Asegurar que el tracking manager capture los parámetros actuales
                    let trackingParams = {};
                    if (window.uniaccTrackingManager && typeof window.uniaccTrackingManager.getParams === "function") {
                        // Forzar captura de parámetros actuales (por si hay cambios en la URL)
                        trackingParams = window.uniaccTrackingManager.getParams();
                        
                        // Guardar en sessionStorage como respaldo (persiste durante la sesión)
                        try {
                            sessionStorage.setItem("uniacc_tracking_params", JSON.stringify(trackingParams));
                        } catch(e) {
                            // sessionStorage no disponible, continuar sin guardar
                        }
                    }
                    
                    var $ = window.jQuery || window.$;
                    if (!$) {
                        console.warn("UNIACC Tracking: jQuery no está disponible aún, retornando solo parámetros de tracking");
                        return {
                            utm_source: trackingParams.utm_source || "",
                            utm_medium: trackingParams.utm_medium || "",
                            utm_term: trackingParams.utm_term || "",
                            utm_content: trackingParams.utm_content || "",
                            utm_campaign: trackingParams.utm_campaign || "",
                            gclid: trackingParams.gclid || "",
                            gad_source: trackingParams.gad_source || "",
                            gbraid: trackingParams.gbraid || "",
                            wbraid: trackingParams.wbraid || "",
                            fbclid: trackingParams.fbclid || "",
                            msclkid: trackingParams.msclkid || "",
                            ttclid: trackingParams.ttclid || "",
                            twclid: trackingParams.twclid || "",
                            organic_source: trackingParams.organic_source || "",
                            organic_medium: trackingParams.organic_medium || "",
                            landing_page: trackingParams.landing_page || "",
                            referrer: trackingParams.referrer || "",
                            current_url: trackingParams.current_url || window.location.href
                        };
                    }
                    
                    try {
                        // Obtener datos del formulario
                        var formData = {
                            Nombre: $("#nombre").val() || "",
                            PrimerApellido: $("#primerApellido").val() || "",
                            Rut: $("#inputRut").val() || "",
                            Pasaporte: $("#inputPasaporte").val() || "",
                            telefono: $("#telefono").length > 0 && typeof $.fn.intlTelInput !== "undefined" 
                                ? $("#telefono").intlTelInput("getNumber") 
                                : $("#telefono").val() || "",
                            email: $("#email").val() || "",
                            codigo_region: $("#regionBT").val() || "",
                            programa: $("#programaBT").val() || "",
                            modalidadHorario: $("#modalidadHorarioBT").val() || "",
                            FormatoPrueba: $("#formatoBT").val() || "",
                            // Parámetros de tracking: primero del formulario, luego del tracking manager
                            utm_source: $("#utm_source").val() || trackingParams.utm_source || "",
                            utm_medium: $("#utm_medium").val() || trackingParams.utm_medium || "",
                            utm_term: $("#utm_term").val() || trackingParams.utm_term || "",
                            utm_content: $("#utm_content").val() || trackingParams.utm_content || "",
                            utm_campaign: $("#utm_campaign").val() || trackingParams.utm_campaign || "",
                            gclid: $("#gclid").val() || trackingParams.gclid || "",
                            gad_source: $("#gad_source").val() || trackingParams.gad_source || "",
                            gbraid: $("#gbraid").val() || trackingParams.gbraid || "",
                            wbraid: $("#wbraid").val() || trackingParams.wbraid || "",
                            fbclid: $("#fbclid").val() || trackingParams.fbclid || "",
                            msclkid: $("#msclkid").val() || trackingParams.msclkid || "",
                            ttclid: $("#ttclid").val() || trackingParams.ttclid || "",
                            twclid: $("#twclid").val() || trackingParams.twclid || "",
                            organic_source: $("#organic_source").val() || trackingParams.organic_source || "",
                            organic_medium: $("#organic_medium").val() || trackingParams.organic_medium || "",
                            landing_page: $("#landing_page").val() || trackingParams.landing_page || "",
                            referrer: $("#referrer").val() || trackingParams.referrer || "",
                            current_url: $("#current_url").val() || trackingParams.current_url || window.location.href
                        };
                        
                        return formData;
                    } catch (e) {
                        console.error("UNIACC Tracking: Error en GetValueBT:", e);
                        // Retornar al menos los parámetros de tracking si hay error
                        return {
                            utm_source: trackingParams.utm_source || "",
                            utm_medium: trackingParams.utm_medium || "",
                            utm_campaign: trackingParams.utm_campaign || "",
                            landing_page: trackingParams.landing_page || "",
                            current_url: trackingParams.current_url || window.location.href
                        };
                    }
                };
            }
            
            // Función GetValue - se define inmediatamente
            // Incluye lógica de persistencia de parámetros de tracking
            if (typeof window.GetValue === "undefined") {
                window.GetValue = function(){
                    // FORZAR CAPTURA DE PARÁMETROS JUSTO ANTES DE RETORNAR
                    // Asegurar que el tracking manager capture los parámetros actuales
                    let trackingParams = {};
                    if (window.uniaccTrackingManager && typeof window.uniaccTrackingManager.getParams === "function") {
                        // Forzar captura de parámetros actuales (por si hay cambios en la URL)
                        trackingParams = window.uniaccTrackingManager.getParams();
                        
                        // Guardar en sessionStorage como respaldo (persiste durante la sesión)
                        try {
                            sessionStorage.setItem("uniacc_tracking_params", JSON.stringify(trackingParams));
                        } catch(e) {
                            // sessionStorage no disponible, continuar sin guardar
                        }
                    }
                    
                    var $ = window.jQuery || window.$;
                    if (!$) {
                        console.warn("UNIACC Tracking: jQuery no está disponible aún, retornando solo parámetros de tracking");
                        return {
                            utm_source: trackingParams.utm_source || "",
                            utm_medium: trackingParams.utm_medium || "",
                            utm_term: trackingParams.utm_term || "",
                            utm_content: trackingParams.utm_content || "",
                            utm_campaign: trackingParams.utm_campaign || "",
                            gclid: trackingParams.gclid || "",
                            gad_source: trackingParams.gad_source || "",
                            gbraid: trackingParams.gbraid || "",
                            wbraid: trackingParams.wbraid || "",
                            fbclid: trackingParams.fbclid || "",
                            msclkid: trackingParams.msclkid || "",
                            ttclid: trackingParams.ttclid || "",
                            twclid: trackingParams.twclid || "",
                            organic_source: trackingParams.organic_source || "",
                            organic_medium: trackingParams.organic_medium || "",
                            landing_page: trackingParams.landing_page || "",
                            referrer: trackingParams.referrer || "",
                            current_url: trackingParams.current_url || window.location.href
                        };
                    }
                    
                    try {
                        // Obtener datos del formulario
                        var formData = {
                            Nombre: $("#nombre").val() || "",
                            PrimerApellido: $("#primerApellido").val() || "",
                            SegundoApellido: $("#segundoApellido").val() || "",
                            Rut: $("#inputRut").val() || "",
                            Pasaporte: $("#inputPasaporte").val() || "",
                            telefono: $("#telefono").length > 0 && typeof $.fn.intlTelInput !== "undefined" 
                                ? $("#telefono").intlTelInput("getNumber") 
                                : $("#telefono").val() || "",
                            email: $("#email").val() || "",
                            programa: $("#programa").val() || "",
                            modalidadHorario: $("#modalidadHorario").val() || "",
                            // Parámetros de tracking: primero del formulario, luego del tracking manager
                            utm_source: $("#utm_source").val() || trackingParams.utm_source || "",
                            utm_medium: $("#utm_medium").val() || trackingParams.utm_medium || "",
                            utm_term: $("#utm_term").val() || trackingParams.utm_term || "",
                            utm_content: $("#utm_content").val() || trackingParams.utm_content || "",
                            utm_campaign: $("#utm_campaign").val() || trackingParams.utm_campaign || "",
                            gclid: $("#gclid").val() || trackingParams.gclid || "",
                            gad_source: $("#gad_source").val() || trackingParams.gad_source || "",
                            gbraid: $("#gbraid").val() || trackingParams.gbraid || "",
                            wbraid: $("#wbraid").val() || trackingParams.wbraid || "",
                            fbclid: $("#fbclid").val() || trackingParams.fbclid || "",
                            msclkid: $("#msclkid").val() || trackingParams.msclkid || "",
                            ttclid: $("#ttclid").val() || trackingParams.ttclid || "",
                            twclid: $("#twclid").val() || trackingParams.twclid || "",
                            organic_source: $("#organic_source").val() || trackingParams.organic_source || "",
                            organic_medium: $("#organic_medium").val() || trackingParams.organic_medium || "",
                            landing_page: $("#landing_page").val() || trackingParams.landing_page || "",
                            referrer: $("#referrer").val() || trackingParams.referrer || "",
                            current_url: $("#current_url").val() || trackingParams.current_url || window.location.href
                        };
                        
                        return formData;
                    } catch (e) {
                        console.error("UNIACC Tracking: Error en GetValue:", e);
                        // Retornar al menos los parámetros de tracking si hay error
                        return {
                            utm_source: trackingParams.utm_source || "",
                            utm_medium: trackingParams.utm_medium || "",
                            utm_campaign: trackingParams.utm_campaign || "",
                            landing_page: trackingParams.landing_page || "",
                            current_url: trackingParams.current_url || window.location.href
                        };
                    }
                };
            }
            
        // Exponer funciones globalmente (sin window. para acceso directo)
        // Las funciones ya están en window.GetValue y window.GetValueBT
        // Ahora las exponemos también sin window. para compatibilidad
        if (typeof window.GetValue !== "undefined") {
            GetValue = window.GetValue;
        }
        if (typeof window.GetValueBT !== "undefined") {
            GetValueBT = window.GetValueBT;
        }
        
        // Log de confirmación
        if (typeof window.uniaccTrackingManager !== "undefined") {
            console.log("✅ UNIACC Tracking Manager: Funciones GetValue y GetValueBT cargadas correctamente");
        }
        ';
    }
}

// Inicializar el plugin
UNIACC_Tracking_Manager::get_instance();
