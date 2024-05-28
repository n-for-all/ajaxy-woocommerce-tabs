<?php
/*
Plugin Name: Ajaxy WooCommerce Tabs
Plugin URI: http://aaagency.ae
Description:
Version: 1.0.0
Author: Naji Amer - @n-for-all
Author URI: https://aaagency.ae
*/

define('AWT_TEXT_DOMAIN', 'awt');
define('AWT_PLUGIN_URL', plugins_url('', __FILE__));
define('AWT_PLUGIN_PATH', dirname(__FILE__));

require_once AWT_PLUGIN_PATH . '/vendor/autoload.php';

require_once 'settings.php';
require_once 'tabs.php';

class AWT_Plugin
{
    private $messages = [];
    private $errors = [];
    public function __construct()
    {
        new AWT_Plugin_Tabs();
        add_action('admin_notices', array(&$this, 'admin_notice'));
        add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts'));
        add_action('woocommerce_product_tabs', array(&$this, 'woocommerce_product_tabs'), 20);
    }
    public function admin_scripts()
    {
        wp_enqueue_style(AWT_TEXT_DOMAIN . "-style", AWT_PLUGIN_URL . '/css/styles.css');
    }
    public function woocommerce_product_tabs($tabs)
    {
        $awt_tabs = (array)get_option('awt_tabs');
        $settings  = $awt_tabs['_settings'] ?? [];
        unset($awt_tabs['_settings']);
        uasort($awt_tabs, function ($a, $b) {
            if ($a['order'] == $b['order']) {
                return 0;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
        });

        $hide_tabs = $settings['hide_tabs'] ?? [];
        foreach($tabs as $key => $tab){
            if(in_array($key, $hide_tabs)){
                unset($tabs[$key]);
            }
        }

        global $post;

        $post_categories = [];
        $categories = get_the_terms($post->ID, 'product_cat');
        if ($categories && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                $post_categories[] = $category->term_id;
            }
        }
        $data = get_post_meta($post->ID, 'awt_tabs', true);

        foreach ($awt_tabs as $key => $value) {
            $exclude_products = array_map('intval', explode(",", $value['exclude_products'] ?? ''));
            $exclude_categories = array_map('intval', explode(",", $value['exclude_categories'] ?? ''));
            if (!$value || in_array($post->ID, $exclude_products) || (count(array_intersect($exclude_categories, $post_categories)))) {
                continue;
            }
            $tab = $this->get_post_tab($key, $data, $post);
            $content = $value['content'] ?? '';

            if ($tab) {
                $show_if_empty = isset($tab['show_if_empty']) ? $tab['show_if_empty'] : false;
                $hide = isset($tab['hide']) ? $tab['hide'] : false;

                $ignore_default = isset($tab['ignore_default']) ? $tab['ignore_default'] : false;
                $content = $ignore_default ? '' : $value['content'];
                if ($tab['content'] != '') {
                    $content = $tab['content'];
                }
                if ($hide || (!$show_if_empty && $content == '')) {
                    continue;
                }
            }
            $ifempty = isset($value['ifempty']) ? $value['ifempty'] : false;
            if (($content == '' && $ifempty) || $content != '') {

                $tabs[$key] = array(
                    'title' => $value['title'],
                    'content' => $content,
                    'priority' => 10 * (intval($value['order'] ?? 1)) + 1,
                    'callback' => array(&$this, 'print_tab_content')
                );
            }
        }

        return $tabs;
    }

    public function get_post_tab($key, $data, $post)
    {
        foreach ((array)$data as $_key => $tab) {
            if ($key == $_key) {
                return $tab;
            }
        }
        return false;
    }
    public function update_post_tab($_key, $_tab, $_post = null, $fields = null)
    {
        global $post;
        if (!$_post) {
            $_post = $post;
        }
        if (!$_post) {
            return false;
        }
        $id = $_post->post_type == 'product' ? $_post->get_id() : $_post->ID;

        $awt_tabs = (array)get_option('awt_tabs');
        foreach ((array)$awt_tabs as $key => $tab) {
            if ($key == $_key) {
                $data = get_post_meta($id, 'awt_tabs', true);
                if (!isset($data[$_key])) {
                    $data[$_key] = array();
                }
                if (empty($fields)) {
                    // update all fields
                    $data[$_key] = $_tab;
                } else {
                    foreach ((array)$fields as $field) {
                        $data[$_key][$field] = isset($_tab[$field]) ? $_tab[$field] : '';
                    }
                }

                update_post_meta($id, 'awt_tabs', $data);
                return $data[$_key];
            }
        }
        return false;
    }
    public function print_tab_content($key, $tab)
    {
        echo do_shortcode($tab['content']);
    }
    public function admin_notice()
    {
        foreach ($this->messages as $message) {
            $class = 'notice notice-info';
            $message = $message;

            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        }
        foreach ($this->errors as $message) {
            $class = 'notice notice-error';
            $message = $message;

            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        }
    }
}

global $AWT_Plugin;
$AWT_Plugin = new AWT_Plugin();
if (is_admin()) {
    $settings = new AWT_Plugin_Settings();
    // $settings = new AWT_Plugin_Admin_Settings();
}
