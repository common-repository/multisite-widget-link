<?php
/*
  Plugin Name: MultiSite Widget Link
  Description: Easily add widgets to link another blog in a multisite instance
  Version: 1.0.2
  Author: bastho
  Author URI:  https://apps.avecnous.eu/?mtm_campaign=wp-plugin&mtm_kwd=multisite-widget-link&mtm_medium=dashboard&mtm_source=auhthor
  License: GPLv2
  Text Domain: multisite-widget-link
  Domain Path: /languages/
  Tags: widget,banner,multisite,network
  Network: 1
 */


load_plugin_textdomain('multisite-widget-link', false, 'multisite-widget-link/languages');

class MultiSiteWidgetLink extends WP_Widget {

    function __construct() {
        // Instantiate the parent object
        parent::__construct(false, __('Link to neighbor site', 'multisite-widget-link'));
    }

    function MultiSiteWidgetLink() {
        $this->__construct();
    }

    function widget($args, $instance) {
        global $wpdb;
        $blog_id = isset($instance['blog_id']) ? $instance['blog_id'] : 1;
        $show_banner = isset($instance['show_banner']) ? $instance['show_banner'] : 1;
        $title = isset($instance['title']) ? $instance['title'] : '';

        $blog = get_blog_details($blog_id);
        $img = get_blog_option($blog_id, 'header_img');
        if ($show_banner == 0 || empty($img)) {
            $img = $blog->blogname;
        } else {
            $img = '<img src="' . $img . '" alt="' . str_replace('"', '', $blog->blogname) . '"/>';
        }
        echo $args['before_widget'];
        if ($title != '') {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo $args['before_content'];
        ?>
        <a href="<?php echo $blog->siteurl; ?>"><?php echo $img ?></a>
        <?php
        echo $args['after_content'] . $args['after_widget'];
    }

    function update($new_instance, $old_instance) {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'save-widget') {
            $instance = array();
            $instance['title'] = strip_tags($new_instance['title']);
            $instance['blog_id'] = strip_tags($new_instance['blog_id']);
            $instance['show_banner'] = abs($new_instance['show_banner']);
            return $instance;
        }
    }

    function form($instance) {
        global $wpdb;
        $blog_id = isset($instance['blog_id']) ? $instance['blog_id'] : 1;
        $show_banner = isset($instance['show_banner']) ? $instance['show_banner'] : 1;
        $title = isset($instance['title']) ? $instance['title'] : '';
        //$blogs_list = wp_get_sites(array('limit' => 0, 'deleted' => false, 'archived' => false, 'spam' => false));
        //wp_get_sites does not provide sort option
        $sql = 'SELECT `blog_id`,`domain` FROM `'.$wpdb->blogs.'` WHERE `public`=1 AND `archived`=\'0\' AND `mature`=0 AND `spam`=0 AND  `deleted`=0 ORDER BY `domain`';
        $blogs_list = $wpdb->get_results($sql, ARRAY_A);
        ?>
        <input type="hidden" id="<?php echo $this->get_field_id('title'); ?>-title" value="<?php echo $title; ?>">
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'multisite-widget-link'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('blog_id'); ?>">
                <?php _e('Site :', 'multisite-widget-link') ?>
            </label>
            <select name="<?php echo $this->get_field_name('blog_id'); ?>" id="<?php echo $this->get_field_id('blog_id'); ?>">
                <?php foreach ($blogs_list as $blog):  ?>
                <option value="<?php echo $blog['blog_id']; ?>" <?php selected($blog['blog_id'], $blog_id, true); ?>>
                    <?php echo $blog['domain']; ?>
                </option>
                <?php endforeach; ?>
            </select>

        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_banner'); ?>">
                <?php _e('Banner :', 'multisite-widget-link') ?>
            </label>
            <select name="<?php echo $this->get_field_name('show_banner'); ?>" id="<?php echo $this->get_field_id('show_banner'); ?>">
                <option value='1' <?php selected($show_banner, 1, true); ?>><?php _e('Show', 'multisite-widget-link') ?></option>
                <option value='0' <?php selected($show_banner, 0, true); ?>><?php _e('Hide', 'multisite-widget-link') ?></option>
            </select>
        </p>
        <?php
    }

}

function MultiSiteWidgetLink_register_widgets() {
    register_widget('MultiSiteWidgetLink');
}

add_action('widgets_init', 'MultiSiteWidgetLink_register_widgets');
