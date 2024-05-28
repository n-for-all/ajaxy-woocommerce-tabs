<?php

class AWT_Plugin_Settings
{
    private $options = array();
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function add_plugin_page()
    {
        add_submenu_page(
            'edit.php?post_type=product',
            'Tabs',
            'Tabs',
            'manage_options',
            'awt-setting-admin',
            array($this, 'create_admin_page')
        );
    }

    public function save_tab($tabs, $page)
    {
        if ($page == 'settings') {
            if (isset($_POST['awt_tabs_settings'])) {
                $tabs['_settings'] = $_POST['awt_tabs_settings'];
                update_option('awt_tabs', $tabs);
                return true;
            }
        } else {
            if (isset($_GET['edit']) && !isset($tabs[$_GET['edit']])) {
                return new WP_Error('awt-tabs', 'Tab doesn\'t exist');
            }
            if (isset($_GET['delete'])) {
                $id = $_GET['delete'];
                if (!isset($tabs[$id])) {
                    return new WP_Error('awt-tabs', 'Tab doesn\'t exist');
                };
                unset($tabs[$id]);
                update_option('awt_tabs', $tabs);
                wp_redirect(esc_url_raw(add_query_arg(array(
                    'post_type' => 'product',
                    'page' => 'awt-setting-admin',
                ), 'edit.php')));
                die();
            }
            if (isset($_POST['awt_tabs'])) {
                $tab = $_POST['awt_tabs'];
                if (!isset($tab['title']) || trim($tab['title']) == '') {
                    return new WP_Error('awt-tabs', 'Tab title cannot be empty');
                }
                $id = null;
                if (isset($_GET['edit'])) {
                    $id = $_GET['edit'];
                    $tabs[$id] = $tab;
                    $tabs[$id]['name'] = $id;
                } else {
                    $id = sanitize_title($tab['title']);
                    $tabs[$id] = $tab;
                    $tabs[$id]['name'] = $id;
                }
                update_option('awt_tabs', $tabs);
                return $id;
            }
        }

        return null;
    }

    public function create_admin_page()
    {
        $page = isset($_GET['tab']) ? $_GET['tab'] : 'list';
        $tabs = (array)get_option('awt_tabs');
        $save = $this->save_tab($tabs, $page);
        if (isset($_GET['error'])) {
            add_settings_error(
                '',
                esc_attr('settings_updated'),
                $_GET['error'],
                'inline error'
            );
        }
        if (is_wp_error($save)) {
            add_settings_error(
                '',
                esc_attr('settings_updated'),
                $save->get_error_message(),
                'inline error'
            );
        } elseif ($save) {
            wp_redirect(esc_url_raw(add_query_arg(array(
                'post_type' => 'product',
                'page' => 'awt-setting-admin',
                'edit' => $save,
                'tab' => 'edit'
            ), 'edit.php'))); 
            die();
        }

        $this->options = $page == 'settings' ? $tabs['_settings'] ?? [] : (isset($_GET['edit']) && isset($tabs[$_GET['edit']]) ? $tabs[$_GET['edit']] : []);
        $this->options = array_replace($this->options, $_POST['awt_tabs'] ?? $_POST['awt_tabs_settings'] ?? []);
        include(AWT_PLUGIN_PATH . '/templates/form.php');
    }


    /**
     * Register and add settings.
     */
    public function page_init()
    {
        register_setting(
            'awt_tabs_group', // Option group
            'awt_tabs', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'awt-tabs-setting-section',
            '',
            array($this, 'print_section_info'),
            'awt-tabs-setting-admin'
        );

        add_settings_field(
            'title',
            'Title',
            array($this, 'title_callback'),
            'awt-tabs-setting-admin',
            'awt-tabs-setting-section' // Section
        );
        add_settings_field(
            'content',
            'Default Content',
            array($this, 'content_callback'),
            'awt-tabs-setting-admin',
            'awt-tabs-setting-section' // Section
        );
        add_settings_field(
            'order',
            'Tab Order',
            array($this, 'order_callback'),
            'awt-tabs-setting-admin',
            'awt-tabs-setting-section' // Section
        );
        add_settings_field(
            'ifempty',
            'Display tab even if content is empty?',
            array($this, 'ifempty_callback'),
            'awt-tabs-setting-admin',
            'awt-tabs-setting-section' // Section
        );
        add_settings_field(
            'exclude_categories',
            'Exclude Categories',
            array($this, 'exclude_categories_callback'),
            'awt-tabs-setting-admin',
            'awt-tabs-setting-section' // Section
        );
        add_settings_field(
            'exclude_products',
            'Exclude Products',
            array($this, 'exclude_products_callback'),
            'awt-tabs-setting-admin',
            'awt-tabs-setting-section' // Section
        );

        register_setting(
            'awt_tabs_group_settings', // Option group
            'awt_tabs', // Option name
            array($this, 'sanitize')
        );

        add_settings_section(
            'awt-tabs-setting-section-settings',
            '',
            array($this, 'print_section_info'),
            'awt-tabs-setting-admin-settings'
        );

        add_settings_field(
            'hide_tabs',
            'Hide Tabs',
            array($this, 'hide_callback'),
            'awt-tabs-setting-admin-settings',
            'awt-tabs-setting-section-settings'
        );
    }

    /**
     * Sanitize each setting field as needed.
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {

        if (isset($input['title'])) {
            $input['title'] = sanitize_text_field($input['title']);
        }

        return $input;
    }

    /**
     * Print the Section text.
     */
    public function print_section_info()
    {
        echo '';
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="awt_tabs[title]" value="%s" /><p class="description">%s</p>',
            isset($this->options['title']) ? esc_attr($this->options['title']) : '',
            __('Add a title for your tab', AWT_TEXT_DOMAIN)
        );
    }
    /**
     * Get the settings option array and print one of its values.
     */
    public function exclude_categories_callback()
    {
        printf(
            '<input type="text" id="exclude_categories" name="awt_tabs[exclude_categories]" value="%s" /><p class="description">%s</p>',
            isset($this->options['exclude_categories']) ? esc_attr($this->options['exclude_categories']) : '',
            __('Comma separated list of category ids to exclude', AWT_TEXT_DOMAIN)
        );
    }
    /**
     * Get the settings option array and print one of its values.
     */
    public function exclude_products_callback()
    {
        printf(
            '<input type="text" id="exclude_products" name="awt_tabs[exclude_products]" value="%s" /><p class="description">%s</p>',
            isset($this->options['exclude_products']) ? esc_attr($this->options['exclude_products']) : '',
            __('Comma separated list of product ids to exclude', AWT_TEXT_DOMAIN)
        );
    }
    /**
     * Get the settings option array and print one of its values.
     */
    public function ifempty_callback()
    {
        printf(
            '<input type="checkbox" id="ifempty" name="awt_tabs[ifempty]" value="%s" /><p class="description">%s</p>',
            isset($this->options['ifempty']) ? esc_attr($this->options['ifempty']) : '',
            __('Show the tab in the frontend if the tab have no content', AWT_TEXT_DOMAIN)
        );
    }
    /**
     * Get the settings option array and print one of its values.
     */
    public function order_callback()
    {
        printf(
            '<input type="number" id="order" name="awt_tabs[order]" value="%s" /><p class="description">%s</p>',
            isset($this->options['order']) ? esc_attr($this->options['order']) : '',
            __('The order of the tab in the frontend', AWT_TEXT_DOMAIN)
        );
    }
    /**
     * Get the settings option array and print one of its values.
     */
    public function content_callback()
    {
        $settings = array(
            'textarea_name' => 'awt_tabs[content]',
            'quicktags'     => array('buttons' => 'em,strong,link'),
            'tinymce'       => array(
                'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                'theme_advanced_buttons2' => '',
            ),
            'editor_css'    => '<style>#wp-tabs-excerpt-editor-container .wp-editor-area{max-height:375px; width:100%;}</style>',
        );
        wp_editor(htmlspecialchars_decode(isset($this->options['content']) ? esc_attr($this->options['content']) : '', ENT_QUOTES), 'tabs-excerpt', $settings);
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function hide_callback()
    {
        $tabs = [
            'description' => __('Description', AWT_TEXT_DOMAIN),
            'additional_information' => __('Additional Information', AWT_TEXT_DOMAIN),
            'reviews' => __('Reviews', AWT_TEXT_DOMAIN)
        ];

        foreach ($tabs as $key => $tab) {
            printf(
                '<label><input type="checkbox" name="awt_tabs_settings[hide_tabs][]" value="%s" %s/>%s</label><br/>',
                $key,
                checked(isset($this->options['hide_tabs']) && in_array($key, $this->options['hide_tabs']), true, false),
                $tab
            );
        }
    }
}
