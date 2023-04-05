<?php

/*
 * Plugin Name:       Eliminador de plantillas
 * Plugin URI:        https://github.com/daniellucia/delete-templates
 * Description:       Elimina de manera sencilla las themes no usados
 * Version:           0.0.1
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
define('DELETE_THEMES_URL', 'themes.php?page=delete-themes');

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

require_once(plugin_dir_path(__FILE__) . 'includes/themes-list.php');

if (is_admin()) {
    add_action('admin_menu', 'delete_themes_options_page');
    function delete_themes_options_page()
    {
        add_submenu_page(
            'themes.php',
            'Eliminador de themes',
            'Eliminador de themes',
            'manage_options',
            'delete-themes',
            'delete_themes_options_page_html',
        );
    }

    add_action('init', 'delete_themes_register_param');
    function delete_themes_register_param()
    {
        global $wp;
        $wp->add_query_var(DELETE_THEMES_PARAM);
    }
}

if (!function_exists('delete_themes_check_execute')) {
    function delete_themes_check_execute()
    {
        $themes = delete_themes_get_list();

        if (isset($_GET[DELETE_THEMES_PARAM])) {

            if (!wp_verify_nonce($_REQUEST['nonce'], $_GET[DELETE_THEMES_PARAM])) {
                wp_die("Operación no permitida");
            }

            if (delete_themes_execute($_GET[DELETE_THEMES_PARAM], $themes)) {
                wp_redirect(DELETE_THEMES_URL);
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


if (!function_Exists('delete_themes_remove_recursive')) {
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
