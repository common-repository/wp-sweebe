<?php
/*
  Plugin Name: sweebe.com - The Official WP Plugin
  Plugin URI: http://www.sweebe.com/
  Description: Displays next 10 upcoming events in Dublin
  Version: 0.1.8
  Author: Andreas Glaser
  Author URI: http://www.blog.sweebe.com/wp-plugin
  License: GPLv2 or later
 */

define('WP_SWEEBE_VERSION', '0.1.8');
define('WP_SWEEBE_PLUGIN_URL', plugin_dir_url(__FILE__));

// include files
require_once dirname(__FILE__) . '/inputfields.php';
require_once dirname(__FILE__) . '/view.php';

class WP_Sweebe {
    // constants
    const REGEX_UPCOMING_EVENTS = '~{sweebe_upcoming_events}~';
    const OPTION_GROUP = 'wp_sweebe_options';
    const API_BASE = 'http://www.sweebe.com/api/';

    public static function install() {

        // set default options
        $array = array(
            'api_cache' => 60,
        );

        // add default options
        add_option(WP_Sweebe::OPTION_GROUP, $array);
        add_option('wp_sweebe_cache', array());
        add_option('wp_sweebe_cache_refreshed', 0);
    }

    public static function uninstall() {

        // delete options
        delete_option(WP_Sweebe::OPTION_GROUP);
        delete_option('wp_sweebe_cache');
        delete_option('wp_sweebe_cache_refreshed');
    }

    public static function settings() {

        // load options
        $options = get_option(WP_Sweebe::OPTION_GROUP);

        register_setting(WP_Sweebe::OPTION_GROUP, WP_Sweebe::OPTION_GROUP, array('WP_Sweebe', 'options_validate'));

        // api cache
        add_settings_section('main_section', 'API Settings', array('WP_Sweebe', 'settings_top'), __FILE__);
        add_settings_field('wp_sweebe_cache', 'Cache Lifetime (Mins)', array('InputFields', 'text'), __FILE__, 'main_section', array('id' => 'wp_sweebe_cache', 'value' => $options['api_cache'], 'option' => 'api_cache'));
    }

    public static function settings_top() {
        // Between Heading and fields
    }

    public static function menu_admin() {
        add_options_page('sweebe.com - Options', 'sweebe.com', 'manage_options', 'wp-sweebe-options', array('WP_Sweebe', 'page_admin_options'));
    }

    public static function page_admin_options() {

        // make sure user has right to see current page
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>

        <div class="wrap">
            <div class="icon32" id="icon-options-general"><br></div>
            <h2>sweebe.com - Options</h2>
            <form action="options.php" method="post">
                <?php settings_fields(WP_Sweebe::OPTION_GROUP); ?>
                <?php do_settings_sections(__FILE__); ?>
                <p class="submit">
                    <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
                </p>
            </form>
        </div>

        <?php
    }

    public static function options_validate($input) {
        return $input;
    }

    public static function print_styles() {
        /**
         * Register the style handle
         */
        wp_register_style('wp-sweebe-css-all', plugins_url('css/sweebe.css', __FILE__), array(), WP_SWEEBE_VERSION, 'all');

        /**
         * Now enqueue it
         */
        wp_enqueue_style('wp-sweebe-css-all');
    }

    public static function content_filter($content) {


        // only proceed if tag has been found in content
        if (preg_match(WP_Sweebe::REGEX_UPCOMING_EVENTS, $content)) {

            static $wp_sweebe_events_instance = 0;

            // get options
            $options = get_option(WP_Sweebe::OPTION_GROUP);

            // see if update is necessary
            if ((get_option('wp_sweebe_cache_refreshed', 0) + ($options['api_cache'] * 60)) < time()) {
                WP_Sweebe::api_get_events();
            }

            $wp_sweebe_events = get_option('wp_sweebe_cache');
            $wp_sweebe_events = $wp_sweebe_events['events'];

            // load view
            $view = View::factory(dirname(__FILE__) . '/views/events_upcoming.php');
            $view->set('events', $wp_sweebe_events);
            $view->set('instance', $wp_sweebe_events_instance);

            $content = preg_replace(WP_Sweebe::REGEX_UPCOMING_EVENTS, $view, $content);



            $wp_sweebe_events_instance++;
        }

        return $content;
    }

    protected static function api_get_events() {

        // get options
        $options = get_option(WP_Sweebe::OPTION_GROUP);
        $error = FALSE;

        try {
            $result = json_decode(file_get_contents('http://www.sweebe.com/api/event/search?city_id=2&type=upcoming&limit=10'), true);
        } catch (Exception $e) {
            $error = TRUE;
        }

        // make sure there are no errors
        if ($result['has_errors']) {
            $error = TRUE;
        }

        // update cache if no error occured
        if ($error === FALSE) {
            update_option('wp_sweebe_cache', $result['responses']);
            update_option('wp_sweebe_cache_refreshed', time());
        }
    }

}

// instalation hook
register_activation_hook(__FILE__, array('WP_Sweebe', 'install'));
register_deactivation_hook(__FILE__, array('WP_Sweebe', 'uninstall'));

add_action('admin_menu', array('WP_Sweebe', 'menu_admin'));
add_action('admin_init', array('WP_Sweebe', 'settings'));


// add filters
add_filter('the_content', array('WP_Sweebe', 'content_filter'));
add_action('wp_print_styles', array('WP_Sweebe', 'print_styles'));