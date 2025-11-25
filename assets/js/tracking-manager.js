/**
 * Tracking Manager - Gestión centralizada de parámetros de tracking
 * Captura y almacena parámetros UTM, click IDs y otros parámetros de tracking
 */

(function() {
    'use strict';

    // Configuración
    const COOKIE_EXPIRY_DAYS = 30;
    const COOKIE_PREFIX = 'uniacc_tracking_';

    /**
     * Obtiene un parámetro de la URL
     */
    function getUrlParameter(name) {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const value = urlParams.get(name);
            return value || null;
        } catch (e) {
            console.error('Error al obtener parámetro de URL:', name, e);
            return null;
        }
    }

    /**
     * Obtiene un valor de cookie
     */
    function getCookie(name) {
        const nameEQ = COOKIE_PREFIX + name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    }

    /**
     * Establece una cookie
     */
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        
        // Construir la cadena de cookie
        let cookieString = COOKIE_PREFIX + name + "=" + encodeURIComponent(value) + ";expires=" + expires.toUTCString() + ";path=/;SameSite=Lax";
        
        // Solo agregar Secure si estamos en HTTPS
        if (window.location.protocol === 'https:') {
            cookieString += ";Secure";
        }
        
        document.cookie = cookieString;
        
        // Verificar que la cookie se guardó correctamente (debugging mejorado)
        const savedValue = getCookie(name);
        if (savedValue !== value) {
            console.warn('⚠️ Cookie no se guardó correctamente:', name, 'Esperado:', value, 'Obtenido:', savedValue);
        } else if (value) {
            // Log cuando se guarda correctamente (solo si hay parámetros en URL)
            const hasUrlParams = window.location.search.includes('utm_') || window.location.search.includes('gclid') || window.location.search.includes('fbclid');
            if (hasUrlParams) {
                console.log('✅ Cookie guardada:', name, '=', value.substring(0, 50));
            }
        }
    }

    /**
     * Obtiene el referrer
     */
    function getReferrer() {
        return document.referrer || null;
    }

    /**
     * Detecta si el tráfico es orgánico
     */
    function detectOrganicSource() {
        const referrer = getReferrer();
        if (!referrer) return null;

        try {
            const referrerUrl = new URL(referrer);
            const hostname = referrerUrl.hostname.toLowerCase();

            // Motores de búsqueda conocidos
            const searchEngines = [
                'google.com', 'google.cl', 'google.co',
                'bing.com', 'yahoo.com', 'duckduckgo.com',
                'yandex.com', 'baidu.com'
            ];

            for (const engine of searchEngines) {
                if (hostname.includes(engine)) {
                    return engine.split('.')[0]; // Retorna 'google', 'bing', etc.
                }
            }

            return null;
        } catch (e) {
            return null;
        }
    }

    /**
     * Detecta el medio orgánico
     */
    function detectOrganicMedium() {
        const referrer = getReferrer();
        if (!referrer) return null;

        try {
            const referrerUrl = new URL(referrer);
            const hostname = referrerUrl.hostname.toLowerCase();

            if (hostname.includes('google') || hostname.includes('bing') || hostname.includes('yahoo')) {
                return 'organic';
            }

            return 'referral';
        } catch (e) {
            return null;
        }
    }

    /**
     * Captura y almacena todos los parámetros de tracking
     */
    function captureTrackingParams() {
        const params = {};

        // Parámetros UTM estándar
        const utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        utmParams.forEach(param => {
            const value = getUrlParameter(param);
            if (value) {
                params[param] = value;
                setCookie(param, value, COOKIE_EXPIRY_DAYS);
            } else {
                // Si no está en URL, intenta obtener de cookie
                const cookieValue = getCookie(param);
                if (cookieValue) {
                    params[param] = cookieValue;
                }
            }
        });

        // Click IDs de diferentes plataformas
        const clickIds = {
            'gclid': 'gclid',
            'gad_source': 'gad_source',
            'gbraid': 'gbraid',
            'wbraid': 'wbraid',
            'fbclid': 'fbclid',
            'msclkid': 'msclkid',
            'ttclid': 'ttclid',
            'twclid': 'twclid'
        };

        Object.keys(clickIds).forEach(key => {
            const value = getUrlParameter(key);
            if (value) {
                params[key] = value;
                setCookie(key, value, COOKIE_EXPIRY_DAYS);
            } else {
                const cookieValue = getCookie(key);
                if (cookieValue) {
                    params[key] = cookieValue;
                }
            }
        });

        // Landing page (primera página visitada)
        if (!getCookie('landing_page')) {
            const landingPage = window.location.href;
            setCookie('landing_page', landingPage, COOKIE_EXPIRY_DAYS);
            params['landing_page'] = landingPage;
        } else {
            params['landing_page'] = getCookie('landing_page');
        }

        // Referrer
        const referrer = getReferrer();
        if (referrer) {
            params['referrer'] = referrer;
            if (!getCookie('referrer')) {
                setCookie('referrer', referrer, COOKIE_EXPIRY_DAYS);
            }
        } else {
            const cookieReferrer = getCookie('referrer');
            if (cookieReferrer) {
                params['referrer'] = cookieReferrer;
            }
        }

        // Detección de fuente orgánica
        const organicSource = detectOrganicSource();
        if (organicSource) {
            params['organic_source'] = organicSource;
        } else {
            const cookieOrganicSource = getCookie('organic_source');
            if (cookieOrganicSource) {
                params['organic_source'] = cookieOrganicSource;
            }
        }

        // Detección de medio orgánico
        const organicMedium = detectOrganicMedium();
        if (organicMedium) {
            params['organic_medium'] = organicMedium;
        } else {
            const cookieOrganicMedium = getCookie('organic_medium');
            if (cookieOrganicMedium) {
                params['organic_medium'] = cookieOrganicMedium;
            }
        }

        // URL actual
        params['current_url'] = window.location.href;

        return params;
    }

    /**
     * Obtiene todos los parámetros de tracking
     * @returns {Object} Objeto con todos los parámetros de tracking
     */
    function getParams() {
        return captureTrackingParams();
    }

    /**
     * Obtiene un parámetro específico
     * @param {string} paramName - Nombre del parámetro
     * @returns {string|null} Valor del parámetro o null
     */
    function getParam(paramName) {
        const params = getParams();
        return params[paramName] || null;
    }

    /**
     * Limpia todos los parámetros de tracking (cookies)
     */
    function clearTracking() {
        const allParams = [
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
            'gclid', 'gad_source', 'gbraid', 'wbraid', 'fbclid', 'msclkid', 'ttclid', 'twclid',
            'landing_page', 'referrer', 'organic_source', 'organic_medium'
        ];

        allParams.forEach(param => {
            document.cookie = COOKIE_PREFIX + param + "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
        });
    }

    // Inicialización automática al cargar
    const trackingParams = captureTrackingParams();

    // API pública
    window.uniaccTrackingManager = {
        getParams: getParams,
        getParam: getParam,
        clearTracking: clearTracking
    };

    // Log para debugging - siempre mostrar si hay parámetros de tracking
    const hasTrackingParams = Object.keys(trackingParams).length > 2; // Más que solo landing_page y current_url
    const hasUrlParams = window.location.search.includes('utm_') || window.location.search.includes('gclid') || window.location.search.includes('fbclid');
    
    // Mostrar log si hay parámetros en URL o si hay parámetros capturados
    if (hasUrlParams || hasTrackingParams) {
        console.log('✅ Tracking Manager inicializado:', trackingParams);
        console.log('🍪 Cookies guardadas:', document.cookie.split(';').filter(c => c.includes('uniacc_tracking_')).length);
    }

})();
