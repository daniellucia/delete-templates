<?php

/**
 * Clase para mostrar el listado de themes
 */
class Themes_List  extends WP_List_Table
{

    private $themes;
    public $items;
    public $_column_headers;

    public function __construct(array $themes)
    {

        parent::__construct([
            'singular' => 'Theme',
            'plural' => 'Themes',
            'ajax' => false
        ]);

        $this->themes = $themes;
    }

    function get_columns()
    {
        $columns = [
            'screenshot' => __('Screenshot', 'delete-templates'),
            'name' => __('Name', 'delete-templates'),
            'author'    => __('Author', 'delete-templates'),
            'version'      => __('Version', 'delete-templates'),
            'status'      => __('Delete', 'delete-templates'),
        ];

        return $columns;
    }

    function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->themes;
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
            case 'author':
            case 'version':
                return $item[$column_name];

            case 'screenshot':
                if ($item[$column_name] == '') {
                    return '';
                }
                return "<img src='{$item[$column_name]}' alt='{$item['name']}' style='height: auto;width: 70px;'/>";

            case 'status':
                $url = $this->get_url_delete($item['slug']);
                return $item[$column_name] == true ? "<a href='$url' onclick='return confirm(\"" . __('You\'re sure? This action can not be undone', 'delete-templates') . "\")'>" . __('Delete', 'delete-templates') . "</a>" : __('This theme is active and cannot be deleted.', 'delete-templates');
        }
    }

    public function get_url_delete(string $slug): string
    {
        $url = DELETE_THEMES_URL;
        $param = DELETE_THEMES_PARAM;
        $path = "$url&$param=$slug";
        return wp_nonce_url(admin_url($path), $slug, 'nonce');
    }
}
