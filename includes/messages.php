<?php

if (!function_exists('delete_themes_notice__success')) {
    /**
     * Función para mostrar el mensaje de
     * que se ha borrado el theme correctamente
     *
     * @author Daniel Lucia <daniellucia84@gmail.com>
     */
    function delete_themes_notice__success()
    {
    ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Theme deleted successfully', 'delete-templates'); ?></p>
        </div>
    <?php
    }
}
if (!function_exists('delete_themes_notice__error')) {
    /**
     * Función para mostrar el mensaje de que
     * no se ha podido borrar el theme
     *
     * @author Daniel Lucia <daniellucia84@gmail.com>
     */
    function delete_themes_notice__error()
    {
    ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('An error occurred while deleting the theme', 'delete-templates'); ?></p>
        </div>
    <?php
    }
}
