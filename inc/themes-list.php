<?php


class Themes_List  extends WP_List_Table
{

    private $themes;

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
            'name' => __('Name', 'delete_themes'),
            'author'    => __('Author', 'delete_themes'),
            'version'      => __('Version', 'delete_themes'),
            'status'      => __('Delete', 'delete_themes'),
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
            case 'status':
                $url = $this->get_url_delete($item['slug']);
                return $item[$column_name] == true ? "<a href='$url' onclick='return confirm(\"¿Estás seguro? Esta acción no se puede deshacer\")'>Borrar</a>" : "Este theme está activo y no se puede borrar.";
        }
    }

    private function get_url_delete(string $slug): string
    {
        $url = DELETE_THEMES_URL;
        $param = DELETE_THEMES_PARAM;
        $path = "$url&$param=$slug";
        return admin_url($path);
    }
}
