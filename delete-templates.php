<?php

/**
 * Plugin Name:       Theme remover
 * Plugin URI:        https://github.com/daniellucia/delete-templates
 * Description:       Easily delete unused themes
 * Version:           1.0.0
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

define('DELETE_THEMES_VERSION', '0.0.1');
define('DELETE_THEMES_PARAM', 'delete-item');
define('DELETE_THEMES_PARAM_RESPONSE', 'delete-item-response');
define('DELETE_THEMES_URL', 'themes.php?page=delete-themes');

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

require_once(plugin_dir_path(__FILE__) . 'includes/themes-list.php');
require_once(plugin_dir_path(__FILE__) . 'includes/messages.php');

if (is_admin()) {

    function delete_themes_options_page()
    {
        add_submenu_page(
            'themes.php',
            __('Theme remover', 'delete-templates'),
            __('Theme remover', 'delete-templates'),
            'manage_options',
            'delete-themes',
            'delete_themes_options_page_html',
        );
    }

    add_action('admin_menu', 'delete_themes_options_page');


    function delete_themes_register_param()
    {
        global $wp;
        $wp->add_query_var(DELETE_THEMES_PARAM);
    }

    add_action('init', 'delete_themes_register_param');
}

if (!function_exists('delete_themes_load_textdomain')) {
    function delete_themes_load_textdomain()
    {
        load_plugin_textdomain('delete-templates', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    add_action('init', 'delete_themes_load_textdomain');
}

if (!function_exists('delete_themes_check_execute')) {
    function delete_themes_check_execute()
    {
        $themes = delete_themes_get_list();

        if (isset($_REQUEST[DELETE_THEMES_PARAM])) {

            $url = DELETE_THEMES_URL . '&' . DELETE_THEMES_PARAM_RESPONSE . '=0';

            if (!wp_verify_nonce($_REQUEST['nonce'], $_REQUEST[DELETE_THEMES_PARAM])) {
                wp_redirect($url);
            }

            if (delete_themes_execute($_REQUEST[DELETE_THEMES_PARAM], $themes)) {
                $url = DELETE_THEMES_URL . '&' . DELETE_THEMES_PARAM_RESPONSE . '=1';
            }

            wp_redirect($url);
        }

        if (isset($_REQUEST[DELETE_THEMES_PARAM_RESPONSE])) {
            if ((int)$_REQUEST[DELETE_THEMES_PARAM_RESPONSE] == 1) {
                add_action('admin_notices', 'delete_themes_notice__success');
            } else {
                add_action('admin_notices', 'delete_themes_notice__error');
            }
        }
    }

    add_action('admin_init', 'delete_themes_check_execute');
}

function delete_themes_options_page_html()
{

    $themes = delete_themes_get_list();
    $themes_list = new Themes_List($themes);
?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p style="margin: 0;"><?php echo __('You must bear in mind that deleting a theme cannot be recovered. We recommend making a backup whenever possible.', 'delete-templates'); ?></p>
        <?php
        $themes_list->prepare_items();
        $themes_list->display();
        ?>
    </div>
<?php
}

if (!function_exists('delete_themes_get_list')) {
    function delete_themes_get_list(): array
    {

        $themes = wp_get_themes();
        $theme_active = wp_get_theme()->get('Name');

        $response = [];

        foreach ($themes as $slug => $theme) {

            $response[$slug] = [
                'name' => $theme->get('Name'),
                'author' => $theme->get('Author'),
                'version' => $theme->get('Version'),
                'slug' => $slug,
                'status' => $theme->get('Name') != $theme_active,
            ];
        }

        return  $response;
    }
}

if (!function_exists('delete_themes_execute')) {
    function delete_themes_execute(string $theme, array $themes)
    {

        if (!array_key_exists($theme, $themes)) {
            return false;
        }

        $theme_uri = get_theme_root() . '/' . $theme;

        if (is_dir($theme_uri)) {
            delete_themes_remove_recursive($theme_uri);

            return true;
        }

        return false;
    }
}


if (!function_exists('delete_themes_remove_recursive')) {
    function delete_themes_remove_recursive($directory)
    {
        $iterator = new RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
            } else {
                unlink($filename);
            }
        }

        rmdir($directory);
    }
}
