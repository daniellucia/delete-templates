<?php

/**
 * Plugin Name:       Theme remover
 * Plugin URI:        https://github.com/daniellucia/delete-templates
 * Description:       Easily delete unused themes
 * Version:           2.0.5
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Daniel Lucia
 * Author URI:        http://www.daniellucia.es/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        http://www.daniellucia.es/
 * Text Domain:       delete-templates
 * Domain Path:       /languages
 */

use DL\DeleteTemplates\Plugin;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

define('DELETE_THEMES_VERSION', '2.0.5');
define('DELETE_THEMES_FILE', __FILE__);

add_action('plugins_loaded', function () {

    load_plugin_textdomain('delete-templates', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $plugin = new Plugin();

    if (is_admin()) {
        add_action('admin_init', [$plugin, 'checkExecute']);
        add_action('admin_enqueue_scripts', [$plugin, 'enqueueScripts']);
    }
});
