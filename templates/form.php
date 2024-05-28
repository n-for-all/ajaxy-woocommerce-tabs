<?php
$active_tab = 'list';
if (isset($_GET['tab']) && trim($_GET['tab']) != '') {
    $active_tab = $_GET['tab'];
}
$data = array();
global $AWT_Plugin;
?>
<div class="wrap">
    <div>
        <h1 class="wp-heading-inline">Tabs <a href="edit.php?post_type=product&page=awt-setting-admin&tab=new" class="page-title-action">New Tab</a></h1>
    </div>
    <hr />
    <h2 class="nav-tab-wrapper">
        <a href="edit.php?post_type=product&page=awt-setting-admin" class="nav-tab <?php echo 'list' == $active_tab ? 'nav-tab-active' : ''; ?>">Tabs</a>
        <?php if ('new' == $active_tab) : ?><a href="edit.php?post_type=product&page=awt-setting-admin&tab=new" class="nav-tab <?php echo 'new' == $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo __('New Tab', AWT_TEXT_DOMAIN); ?></a><?php endif; ?>
        <?php if ('edit' == $active_tab) : ?><a href="edit.php?post_type=product&page=awt-setting-admin&tab=edit" class="nav-tab <?php echo 'edit' == $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo __('Edit Tab', AWT_TEXT_DOMAIN); ?></a><?php endif; ?>
        <a href="edit.php?post_type=product&page=awt-setting-admin&tab=settings" class="nav-tab <?php echo 'settings' == $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo __('Settings', AWT_TEXT_DOMAIN); ?></a>
    </h2>

    <form method="post" action="" enctype="multipart/form-data">
        <?php if ('list' == $active_tab) {
            settings_errors();
        ?>
            <?php
            $data = get_option('awt_tabs');
            unset($data['_settings']);
            $columns = array(
                "title" => "Title",
                "content" => "Default Content Preview",
                "actions" => "Actions",
            );
            $table = new \Ajaxy\WP\List_Table(new \Ajaxy\WP\List_Table_Columns($columns));
            $table->set_items($data);
            $table->on_column(function ($column_name, $item) {
                switch ($column_name) {
                    case 'title':
                        echo $item[$column_name];
                        break;
                    case 'content':
                        echo $item[$column_name];
                        break;
                    case 'actions':
                        echo sprintf('<a class="button button-secondary" onclick="return confirm(\'Are you sure you want to delete this tab?\');" href="%s">%s</a>', add_query_arg(array(
                            'post_type' => 'product',
                            'page' => 'awt-setting-admin',
                            'delete' => isset($item['name']) ? $item['name'] : '',
                        ), 'edit.php'), __('Delete', AWT_TEXT_DOMAIN));
                        echo " ";
                        echo sprintf('<a class="button button-primary" href="%s"><span style="font-weight:bold; ">%s</span></a>', add_query_arg(array(
                            'post_type' => 'product',
                            'page' => 'awt-setting-admin',
                            'tab' => 'edit',
                            'edit' => isset($item['name']) ? $item['name'] : '',
                        ), 'edit.php'), __('Edit Tab', AWT_TEXT_DOMAIN));
                        break;
                    default:
                        echo $item[$column_name];
                }
            });
            $table->display();
        } else if ('new' == $active_tab || 'edit' == $active_tab) {
            settings_errors();
            ?>
            <?php settings_fields('awt_tabs_group'); ?>
            <?php do_settings_sections('awt-tabs-setting-admin'); ?>
            <hr />
            <?php submit_button('Save tab'); ?>
        <?php
        } else if ('settings' == $active_tab) {
            settings_errors();
        ?>
            <?php settings_fields('awt_tabs_group_settings'); ?>
            <?php do_settings_sections('awt-tabs-setting-admin-settings'); ?>
            <hr />
            <?php submit_button('Save Settings', 'primary', 'awt_tabs_settings[submit]'); ?>
        <?php
        } ?>
    </form>
</div>