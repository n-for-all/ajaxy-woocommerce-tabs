<?php

namespace Ajaxy\WP;

class List_Table_Columns
{
    private $columns = array();
    private $hidden = array();
    private $sortable = array(); 

    public function __construct($columns, $sortable = array(), $hidden = array())
    {
        $this->add_columns($columns, $sortable);
        $this->add_hidden_columns($hidden);
    }

    /**
     * get the columns.  
     *
     * @since  1.0.0
     * @date   2019-05-19
     *
     * @return {Array} $columns
     */
    public function get_columns()
    {
        return $this->columns;
    }

    /**
     * get all the columns.
     *
     * @since  1.0.0
     * @date   2019-05-19
     *
     * @return {Array} $columns
     */
    public function get_all_columns()
    {
        return array($this->columns, $this->get_hidden_columns(), $this->get_sortable_columns());
    }

    /**
     * add a new column.
     *
     * @since 1.0.0
     * @date  2019-05-19
     *
     * @param {string} $column_name
     * @param {string} $label
     * @param {Boolean}  [$sortable=false]
     * @param {String}   [$sortable_order='asc']
     */
    public function add_column($column_name, $label, $sortable = false, $sortable_order = 'asc')
    {
        $this->columns[$column_name] = $label;
        if ($sortable) {
            $this->add_sortable_column($column_name, $sortable_order);
        }
    }

    /**
     * Add new columns.
     *
     * @since 1.0.0
     * @date  2019-05-19
     *
     * @param {array} $columns
     * @param {array} $sortable
     */
    public function add_columns($columns, $sortable = array())
    {
        foreach ((array) $columns as $key => $order) {
            $this->add_column($key, $order, isset($sortable[$key]) ? true : false, isset($sortable[$key]) ? $sortable[$key] : 'asc');
        }

        return $this;
    }

    /**
     * Delete a column.
     *
     * @since  1.0.0
     * @date   2019-05-19
     *
     * @param {string} $column_name
     */
    public function delete_column($column_name)
    {
        if (isset($this->columns[$column_name])) {
            unset($this->columns[$column_name]);
        }
        if (isset($this->sortable[$column_name])) {
            unset($this->sortable[$column_name]);
        }
        if (isset($this->hidden[$column_name])) {
            unset($this->hidden[$column_name]);
        }

        return $this;
    }

    /**
     * Define the sortable columns.
     *
     * @since  1.0.0
     * @date   2019-05-19
     *
     * @return {Array} [$sortable]
     */
    public function get_sortable_columns()
    {
        return $this->sortable;
    }

    /**
     * add a sortable column.
     *
     * @since 1.0.0
     * @date  2019-05-19
     *
     * @param {string} $column_name
     * @param {String}   [$order='ASC']
     *
     * @return AWT_List_Table_Columns $this
     */
    public function add_sortable_column($column_name, $order = 'ASC')
    {
        if (!in_array($column_name, array_keys($this->columns))) {
            throw new \Exception('Column doesn\'t exist');
        }
        $this->sortable[$column_name] = array($column_name, 'desc' == strtolower($order) ? true : false);

        return $this;
    }

    /**
     * Delete a sortable column.
     *
     * @since 1.0.0
     * @date  2019-05-19
     *
     *  @param {string}   $column_name
     *
     * @return AWT_List_Table_Columns $this
     */
    public function delete_sortable_column($column_name)
    {
        if (isset($this->sortable[$column_name])) {
            unset($this->sortable[$column_name]);
        }

        return $this;
    }

    /**
     * add the sortable columns.
     *
     * @since 1.0.0
     * @date  2019-05-19
     *
     *  @param {Array}   $columns
     *
     * @return AWT_List_Table_Columns $this
     */
    public function add_sortable_columns($columns)
    {
        foreach ((array) $columns as $key => $order) {
            $this->add_sortable_column($key, $order);
        }

        return $this;
    }

    /**
     * Define the hidden columns.
     *
     * @since  1.0.0
     * @date   2019-05-19
     *
     * @return array
     */
    public function get_hidden_columns()
    {
        return $this->hidden;
    }

    /**
     * add a hidden column.
     *
     * @since 1.0.0
     * @date  2019-05-19
     *
     *  @param {[type]}   $column_name
     * @param {String}   [$order='ASC']
     *
     * @return AWT_List_Table_Columns $this
     */
    public function add_hidden_column($column_name, $order = 'ASC')
    {
        if (!in_array($column_name, array_keys($this->columns))) {
            throw new \Exception('Column doesn\'t exist');
        }
        $this->hidden[$column_name] = $column_name;

        return $this;
    }

    /**
     * Delete hidden column.
     *
     * @since 1.0.0
     * @date  2019-05-19
     *
     * @return AWT_List_Table_Columns $this
     */
    public function delete_hidden_column($column_name)
    {
        if (isset($this->hidden[$column_name])) {
            unset($this->hidden[$column_name]);
        }

        return $this;
    }

    /**
     * add hidden columns.
     *
     * @since 1.0.0
     * @date  2019-05-19
     *
     * @return AWT_List_Table_Columns $this
     */
    public function add_hidden_columns($columns)
    {
        foreach ((array) $columns as $value) {
            $this->add_hidden_column($value);
        }

        return $this;
    }
}
