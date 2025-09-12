<?php

namespace DL\DeleteTemplates;

defined('ABSPATH') || exit;

class Plugin
{
    private $version = '';

    public function __construct()
    {
        $this->version = DELETE_THEMES_VERSION;
    }

    /**
     * Añadimos assets
     * @param mixed $hook
     * @return void
     * @author Daniel Lucia
     */
    public function enqueueScripts($hook)
    {
        if ($hook !== 'themes.php') {
            return;
        }

        wp_enqueue_script(
            'delete-themes',
            plugin_dir_url(DELETE_THEMES_FILE) . 'assets/js/delete-themes.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_style(
            'delete-themes',
            plugin_dir_url(DELETE_THEMES_FILE) . 'assets/css/delete-themes.css',
            [],
            $this->version
        );

        // Obtenemos todos los temas instalados y pasamos enlaces a la vista
        $themes = wp_get_themes();
        $delete_links = [];

        foreach ($themes as $slug => $theme) {
            $delete_links[$slug] = $this->getUrlDelete($slug);
        }

        wp_localize_script('delete-themes', 'RW_DELETE_THEMES', [
            'links' => $delete_links,
            'confirmation_text' => __('Are you sure you want to delete this template? This action cannot be undone.', 'delete-templates'),
            'alert_text' => __('You can\'t delete the default theme.', 'delete-templates'),
            'button_text' => __('Delete', 'delete-templates')
        ]);
    }

    /**
     * Obtenemos url actual
     * @return string
     * @author Daniel Lucia
     */
    private function current_url()
    {
        return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Generamos la url de eliminación del theme
     * @param string $slug
     * @return string
     * @author Daniel Lucia
     */
    public function getUrlDelete(string $slug): string
    {
        $actual = $this->current_url();

        $url = add_query_arg(
            array(
                'delete-item' => $slug,
                'referer' => $actual
            ),
            $actual
        );

        return wp_nonce_url($url, $slug, 'nonce');
    }

    /**
     * Comprobamos si debemos ejecutar la eliminación
     * @return void
     * @author Daniel Lucia
     */
    public function checkExecute()
    {
        $themes = $this->getList();

        if (isset($_REQUEST['delete-item'])) {

            $referer = isset($_REQUEST['referer']) ? esc_url_raw($_REQUEST['referer']) : admin_url('themes.php');

            if (!is_admin()) {
                wp_redirect($referer);
                exit;
            }

            if (empty($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], $_REQUEST['delete-item'])) {
                wp_die(__('Security error! Invalid or missing nonce.', 'delete-templates'));
                exit;
            }

            if ($this->execute($_REQUEST['delete-item'], $themes)) {

                $url = add_query_arg(
                    array(
                        'delete-item-response' => 1,
                    ),
                    $referer
                );
            }

            //redireccionamos
            wp_redirect($url ?: $referer);

            exit;
        }

        if (isset($_REQUEST['delete-item-response'])) {
            if ((int)$_REQUEST['delete-item-response'] == 1) {
                add_action('admin_notices', [$this, 'delete_themes_notice__success']);
            } else {
                add_action('admin_notices', [$this, 'delete_themes_notice__error']);
            }
        }
    }

    /**
     * Generamos la lista de themes
     * @return array{author: array|bool|string, name: array|bool|string, screenshot: string, slug: mixed, status: bool, version: array|bool|string[]}
     * @author Daniel Lucia
     */
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

    /**
     * Ejecutamos la eliminación del theme
     * @param string $theme
     * @param array $themes
     * @return bool
     * @author Daniel Lucia
     */
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

    /**
     * Ejecutamos la eliminación de un directorio de forma recursiva
     * @param string $directory
     * @return void
     * @author Daniel Lucia
     */
    public function removeRecursive(string $directory)
    {
        $iterator = new \RecursiveIteratorIterator(
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

    /**
     * Generamos la notificación de éxito
     * @return void
     * @author Daniel Lucia
     */
    public function delete_themes_notice__success()
    {
?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Theme deleted successfully', 'delete-templates'); ?></p>
        </div>
    <?php
    }

    /**
     * Generamos la notificación de error
     * @return void
     * @author Daniel Lucia
     */
    public function delete_themes_notice__error()
    {
    ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('An error occurred while deleting the theme', 'delete-templates'); ?></p>
        </div>
<?php
    }
}
