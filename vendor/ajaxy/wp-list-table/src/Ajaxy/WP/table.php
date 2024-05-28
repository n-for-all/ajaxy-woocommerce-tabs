<?php

namespace Ajaxy\WP;

if (!defined('ABSPATH')) {
    die('Ajaxy/WP/List_table package is intended to be used with wordpress');
}

if (!class_exists('\WP_List_Table')) {
    require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

class List_Table extends \WP_List_Table
{
    public $items = array();
    private $columns = null;
    private $on_column = null;
    private $classes = array( 'widefat', 'striped' );
    private $nav = array( 'top', 'bottom' );
    private $headers = array( 'top', 'bottom' );

    private $total = 0;
    private $per_page = 10;

    public function __construct(List_Table_Columns $columns = null, $args = array())
    {
        parent::__construct($args);
        if ($columns) {
            $this->columns = $columns;
            $this->_column_headers = $this->columns->get_all_columns();
        }
    }

    public function set_columns(List_Table_Columns $columns)
    {
        $this->columns = $columns;
        $this->_column_headers = $this->columns ? $this->columns->get_all_columns() : array();
    }

    public function on_column($callable)
    {
        $this->on_column = $callable;
    }

    public function set_items($items)
    {
        $this->items = $items;
        $this->total = is_array($items) ? count($items) : 0;

        $this->set_pagination_args(array(
            'total_items' => $this->total,
            'per_page' => $this->per_page,
        ));
    }

    public function set_total_items($count)
    {
        $this->total = intval($count);
    }

    public function set_per_page($per_page)
    {
        $this->per_page = intval($per_page);
    }


    /**
     * Override the parent columns method. Defines the columns to use in your listing table.
     *
     * @return array
     */
    public function get_columns()
    {
        if ($this->columns) {
            return $this->columns->get_columns();
        }

        return array();
    }

    /**
     * Define which columns are hidden.
     *
     * @return array
     */
    public function get_hidden_columns()
    {
        if ($this->columns) {
            return $this->columns->get_hidden_columns();
        }

        return array();
    }

    /**
     * Define the sortable columns.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        if ($this->columns) {
            return $this->columns->get_sortable_columns();
        }

        return array();
    }

    /**
     * Define what data to show on each column of the table.
     *
     * @param array  $item        Data
     * @param string $column_name - Current column name
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        if($this->on_column){
            call_user_func($this->on_column, $column_name, $item);
        }
    }

    public function get_table_classes() {
        return $this->classes;
    }

    public function set_table_classes($classes) {
        $this->classes = array_unique(array_merge($this->classes, array( 'widefat', 'striped', $this->_args['plural'] )));
    }
    /**
	 * Print column headers, accounting for hidden and sortable columns.
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
        if(in_array('bottom', $this->headers) && !$with_id){
            parent::print_column_headers($with_id);
        }elseif(in_array('top', $this->headers) && $with_id){
            parent::print_column_headers($with_id);
        }
    }
    public function set_column_headers($headers) {
        $this->headers = (array)$headers;
    }
    public function set_table_navs($nav) {
        $this->nav = (array)$nav;
    }

    /**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @param string $which
	 */
	public function display_tablenav( $which ) {
        if(in_array($which, $this->nav)){
            parent::display_tablenav($which);
        }
	}
    /**
     * Allows you to sort the data by the variables set in the $_GET.
     *
     * @return mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        $result = strcmp($a[$orderby], $b[$orderby]);
        if ('asc' === $order) {
            return $result;
        }

        return -$result;
    }
}
