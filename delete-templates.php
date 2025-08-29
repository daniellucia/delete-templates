<?php

/**
 * Plugin Name:       Theme remover
 * Plugin URI:        https://github.com/daniellucia/delete-templates
 * Description:       Easily delete unused themes
 * Version:           2.0.0
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

define('DELETE_THEMES_VERSION', '2.0.0');
define('DELETE_THEMES_PARAM', 'delete-item');
define('DELETE_THEMES_PARAM_RESPONSE', 'delete-item-response');
define('DELETE_THEMES_URL', 'themes.php?page=delete-themes');

//if (!class_exists('WP_List_Table')) {
//    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
//}

//require_once(plugin_dir_path(__FILE__) . 'includes/themes-list.php');
//require_once(plugin_dir_path(__FILE__) . 'includes/messages.php');

class DeleteThemesPlugin
{

    private $version = '2.0.0';

    public function __construct()
    {

        if (is_admin()) {
            add_action('admin_menu', [$this, 'addMenu']);
            add_action('admin_init', [$this, 'checkExecute']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        }

        add_action('init', [$this, 'loadTextdomain']);
    }

    public function enqueueScripts($hook)
    {

        if ($hook !== 'themes.php') {
            return;
        }

        wp_enqueue_script(
            'rw-delete-themes',
            plugin_dir_url(__FILE__) . 'assets/js/rw-delete-themes.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_style(
            'rw-delete-themes',
            plugin_dir_url(__FILE__) . 'assets/css/rw-delete-themes.css',
            [],
            $this->version
        );

        // Obtenemos todos los temas instalados y pasamos enlaces a la vista
        $themes = wp_get_themes();
        $delete_links = [];

        foreach ($themes as $slug => $theme) {
            $delete_links[$slug] = $this->getUrlDelete($slug);
        }

        wp_localize_script('rw-delete-themes', 'RW_DELETE_THEMES', [
            'links' => $delete_links,
            'confirmation_text' => __('Are you sure you want to delete this template? This action cannot be undone.', 'delete-templates'),
            'alert_text' => __('You can\'t delete the default theme.', 'delete-templates'),
            'button_text' => __('Delete', 'delete-templates')
        ]);
    }

    public function getUrlDelete(string $slug): string
    {
        $url = DELETE_THEMES_URL;
        $param = DELETE_THEMES_PARAM;
        $path = "$url&$param=$slug";
        return wp_nonce_url(admin_url($path), $slug, 'nonce');
    }

    public function addMenu()
    {
        add_submenu_page(
            'themes.php',
            __('Theme remover', 'delete-templates'),
            __('Theme remover', 'delete-templates'),
            'manage_options',
            'delete-themes',
            [$this, 'optionsPageHtml']
        );
    }

    public function loadTextdomain()
    {
        load_plugin_textdomain('delete-templates', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function checkExecute()
    {
        $themes = $this->getList();

        if (isset($_REQUEST[DELETE_THEMES_PARAM])) {
            $url = DELETE_THEMES_URL . '&' . DELETE_THEMES_PARAM_RESPONSE . '=0';

            if (!is_admin()) {
                wp_redirect($url);
                exit;
            }

            if (!wp_verify_nonce($_REQUEST['nonce'], $_REQUEST[DELETE_THEMES_PARAM])) {
                wp_redirect($url);
                exit;
            }

            if ($this->execute($_REQUEST[DELETE_THEMES_PARAM], $themes)) {
                $url = DELETE_THEMES_URL . '&' . DELETE_THEMES_PARAM_RESPONSE . '=1';
            }

            //redireccionamos a referer
            wp_redirect(wp_get_referer() ?: $url);
            exit;
        }

        if (isset($_REQUEST[DELETE_THEMES_PARAM_RESPONSE])) {
            if ((int)$_REQUEST[DELETE_THEMES_PARAM_RESPONSE] == 1) {
                add_action('admin_notices', 'delete_themes_notice__success');
            } else {
                add_action('admin_notices', 'delete_themes_notice__error');
            }
        }
    }

    public function optionsPageHtml()
    {
        $themes = $this->getList();
        $themes_list = new Themes_List($themes);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p style="margin: 0;"><?php echo __('You must bear in mind that deleting a theme cannot be recovered. We recommend making a backup whenever possible.', 'delete-templates'); ?></p>
            <?php
            $themes_list->prepare_items();
            $themes_list->display();
            ?>
            <style type="text/css">
                .wp-list-table.themes .column-screenshot {
                    width: 120px !important;
                    overflow: hidden;
                    text-align: center;
                }

                .wp-list-table.themes .column-status {
                    width: 120px !important;
                    overflow: hidden;
                    text-align: center;
                }

                .wp-list-table.themes .column-version {
                    width: 120px !important;
                }

                .wp-list-table.themes .column-status a {
                    color: #a94040;
                    text-decoration: underline;
                }
            </style>
        </div>
<?php
    }

    public function getList(): array
    {
        $themes = wp_get_themes();
        $theme_active = wp_get_theme()->get('Name');
        $response = [];

        foreach ($themes as $slug => $theme) {
            $screenshot = '';
            if (file_exists(get_theme_root() . '/' . $slug . '/screenshot.jpg')) {
                $screenshot = esc_url(get_theme_root_uri() . '/' . $slug . '/screenshot.jpg?ver=' . $theme->get('Version'));
            }
            if (file_exists(get_theme_root() . '/' . $slug . '/screenshot.png')) {
                $screenshot = esc_url(get_theme_root_uri() . '/' . $slug . '/screenshot.png?ver=' . $theme->get('Version'));
            }
            $response[$slug] = [
                'name' => $theme->get('Name'),
                'screenshot' => $screenshot,
                'author' => $theme->get('Author'),
                'version' => $theme->get('Version'),
                'slug' => $slug,
                'status' => $theme->get('Name') != $theme_active,
            ];
        }
        return $response;
    }

    public function execute(string $theme, array $themes)
    {
        if (!array_key_exists($theme, $themes)) {
            return false;
        }
        $theme_uri = get_theme_root() . '/' . $theme;
        if (is_dir($theme_uri)) {
            $this->removeRecursive($theme_uri);
            return true;
        }
        return false;
    }

    public function removeRecursive(string $directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
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

// Inicializa el plugin
new DeleteThemesPlugin();
