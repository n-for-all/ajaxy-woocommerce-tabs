<?php

defined( 'ABSPATH' ) || exit;

class AWT_Plugin_Tabs
{
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action( 'save_post',  array($this, 'save_product'), 10, 3 );
    }
    public function add_meta_boxes(){
        add_meta_box( 'awt_tabs', 'Custom Tabs', 'AWT_Plugin_Tab::output', 'product', 'normal', 'default' );

    }
    public function save_product( $post_id, $post, $update ) {

        /*
         * In production code, $slug should be set only once in the plugin,
         * preferably as a class property, rather than in each function that needs it.
         */
        $post_type = get_post_type($post_id);

        // If this isn't a 'book' post, don't update it.
        if ( "product" != $post_type ) return;

        // - Update the post's metadata.
        update_post_meta( $post_id, 'awt_tabs', $_POST['awt_tabs'] );

    }
}

class AWT_Plugin_Tab {
	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function output( $post, $tab ) {
        $tabs = get_option( 'awt_tabs' );
        ?>
        <h2 class="nav-tab-wrapper">
        <?php
        $i = 0;

        $data = get_post_meta( $post->ID, 'awt_tabs', true );
        foreach($tabs as $awt_tab){
        ?>
            <a href="#!tab/<?php echo $awt_tab['name']; ?>" class="nav-tab <?php echo $i == 0 ? 'nav-tab-active' : ''; ?>"><?php echo $awt_tab['title']; ?></a>
        <?php
            ++$i;
        }
        ?></h2>
        <?php
        $i = 0;
        foreach($tabs as $awt_tab){
            ?><div class="tab-content <?php echo $i == 0 ? 'tab-content-active' : ''; ?>" id="tab-<?php echo $awt_tab['name']; ?>"><?php
    		$settings = array(
    			'textarea_name' => 'awt_tabs['.$awt_tab['name'].'][content]',
    			'quicktags'     => array( 'buttons' => 'em,strong,link' ),
    			'tinymce'       => array(
    				'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
    				'theme_advanced_buttons2' => '',
    			),
    			'editor_css'    => '<style>#wp-'.$awt_tab['name'].'-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
    		);
    		wp_editor( htmlspecialchars_decode( isset($data[$awt_tab['name']]['content']) ? $data[$awt_tab['name']]['content']: '', ENT_QUOTES ), $awt_tab['name'], $settings );?>
            <p><input value="yes" type="checkbox" name="awt_tabs[<?php echo  $awt_tab['name'] ?>][hide]" <?php echo isset($data[$awt_tab['name']]['hide']) && $data[$awt_tab['name']]['hide'] == 'yes'? 'checked': '' ?>/> <?php echo __('Hide this tab', AWT_TEXT_DOMAIN); ?></p>
            <p><input value="yes" type="checkbox" name="awt_tabs[<?php echo  $awt_tab['name'] ?>][show_if_empty]" <?php echo isset($data[$awt_tab['name']]['show_if_empty']) && $data[$awt_tab['name']]['show_if_empty'] == 'yes'? 'checked': '' ?>/> <?php echo __('Show tab even if content is empty', AWT_TEXT_DOMAIN); ?></p>
            <p><input value="yes" type="checkbox" name="awt_tabs[<?php echo  $awt_tab['name'] ?>][ignore_default]" <?php echo isset($data[$awt_tab['name']]['ignore_default']) && $data[$awt_tab['name']]['ignore_default'] == 'yes'? 'checked': '' ?>/> <?php echo __('Override default content', AWT_TEXT_DOMAIN); ?></p>
        </div>
            <?php
            ++$i;
        }?>
        <script type="text/javascript">
            function toggleTab(content, nav){
                jQuery('#awt_tabs .nav-tab-wrapper > a').removeClass('nav-tab-active');
                nav.addClass('nav-tab-active');
                jQuery('#awt_tabs .tab-content').removeClass('tab-content-active');
                content.addClass('tab-content-active');
            }
            jQuery(window).on('hashchange', function(e) {
                var hash = window.location.hash.replace(/^#!/, '');
                if (hash) {
                    var path = hash.split('/');
                    if (path[0] == 'tab') {
                        if (path[1] && path[1].trim() != '') {
                            toggleTab(jQuery('#tab-'+ path[1].trim()), jQuery('#awt_tabs .nav-tab-wrapper a[href="' + window.location.hash + '"]'));
                        }
                    }
                }
            });
        </script>
        <?php
	}
}
