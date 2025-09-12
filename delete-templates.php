<?php

/**
 * Plugin Name:       Theme remover
 * Plugin URI:        https://github.com/daniellucia/delete-templates
 * Description:       Easily delete unused themes
 * Version:           2.5.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Daniel Lucia
 * Author URI:        http://www.daniellucia.es/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        http://www.daniellucia.es/
 * Text Domain:       delete-templates
 * Domain Path:       /languages
 */

/*
Copyright (C) 2025  Daniel Lucia (https://daniellucia.es)

Este programa es software libre: puedes redistribuirlo y/o modificarlo
bajo los términos de la Licencia Pública General GNU publicada por
la Free Software Foundation, ya sea la versión 2 de la Licencia,
o (a tu elección) cualquier versión posterior.

Este programa se distribuye con la esperanza de que sea útil,
pero SIN NINGUNA GARANTÍA; ni siquiera la garantía implícita de
COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO PARTICULAR.
Consulta la Licencia Pública General GNU para más detalles.

Deberías haber recibido una copia de la Licencia Pública General GNU
junto con este programa. En caso contrario, consulta <https://www.gnu.org/licenses/gpl-2.0.html>.
*/

use DL\DeleteTemplates\Plugin;

defined('ABSPATH') || exit;

$autoload_file = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload_file)) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo __('Theme Remover: Composer autoload file not found. Please run "composer install".', 'delete-templates');
        echo '</p></div>';
    });
    return;
}

require_once $autoload_file;

define('DELETE_THEMES_VERSION', '2.5.0');
define('DELETE_THEMES_FILE', __FILE__);

add_action('plugins_loaded', function () {

    load_plugin_textdomain('delete-templates', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $plugin = new Plugin();

    if (is_admin()) {
        add_action('admin_init', [$plugin, 'checkExecute']);
        add_action('admin_enqueue_scripts', [$plugin, 'enqueueScripts']);
    }
});


/**
 * Limpiamos caché al activar o desactivar el plugin
 */
register_activation_hook(__FILE__, function() {
    wp_cache_flush();
});

register_deactivation_hook(__FILE__, function() {
    wp_cache_flush();
});