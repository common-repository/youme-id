<?php
/*
Plugin Name: YouMe ID
Plugin URI: https://wordpress.org/plugins/youme-id/
Description: Provides CAPTCHA service, thwarting automated hacking attempts
Author: YouMe Identity
Version: 0.8.0
Author URI: https://www.youmeid.com/products
Text Domain: youme_id
Domain Path: /languages/
*/

if (!function_exists('add_action')) {
    die();
}
if (!function_exists('is_user_logged_in')) require(ABSPATH . WPINC . '/pluggable.php');

class YMID
{
    private static $instance;

    private function __construct() {
        $this->includes();
        $this->actions();
        ym_captcha::init_ymid_captcha();
        ym_login::init_ymid_login();
    }

    public static function init() {
        if ( ! self::$instance instanceof self ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function includes() {
        require_once plugin_dir_path( __FILE__ ) . 'functions.php';
        require_once plugin_dir_path( __FILE__ ) . 'ym_captcha.php';
        require_once plugin_dir_path( __FILE__ ) . 'ym_login.php';
    }

    private function actions() {
        add_action('plugins_loaded', array('YMID', 'load_textdomain'));
        add_action('admin_menu', array('YMID', 'register_menu_page'));
        add_action('admin_init', array('YMID', 'register_settings'));
        add_action('admin_notices', array('YMID', 'admin_notices'));
        add_filter('shake_error_codes', array('YMID', 'add_shake_error_codes'));

        add_action('login_form', array('YMID', 'ymid_div'), 10);
        add_action('comment_form_after_fields', array('YMID', 'ymid_div'), 10);
        add_action('woocommerce_register_form', array('YMID', 'ymid_div'), 10);
        add_action('register_form', array('YMID', 'ymid_div'), 10);
        add_action('signup_extra_fields', array('YMID', 'ymid_div'), 10);
        add_action('signup_blogform', array('YMID', 'ymid_div'), 10);
        add_action('lostpassword_form', array('YMID', 'ymid_div'), 10);
        add_action('woocommerce_lostpassword_form', array('YMID', 'ymid_div'), 10);
        add_action('resetpass_form', array('YMID', 'ymid_div'), 10);
        add_action('woocommerce_resetpassword_form', array('YMID', 'ymid_div'), 10);
    }

    public static function load_textdomain() {
        load_plugin_textdomain('youme_id', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public static function register_menu_page() {
        add_options_page(__('Login LastCatpcha Options', 'youme_id'), __('YouMe ID', 'youme_id'), 'manage_options', plugin_dir_path(__FILE__) . 'admin.php');
        if (in_array("ym_login", get_option('ymid_ym_login', array())) && get_user_meta(wp_get_current_user()->ID, "ymid_uid", true)&& get_user_meta(wp_get_current_user()->ID, "ymid_did_band", true) === "0") {
            add_users_page(__('YouMe ID Account Linkage', 'youme_id'), __('YouMe ID Account Linkage', 'youme_id'),'read',plugin_dir_path(__FILE__) . 'account.php');
        }
    }

    public static function register_settings() {

        /* user-configurable values */
        add_option('ymid_captcha_key', '');
        add_option('ymid_captcha_secret', '');
        add_option('ymid_forms', array(''));
        add_option('ymid_failed_login_allow', '0');
        add_option('ymid_ym_login', array(''));
        add_option('ymid_redirect_option', 'back');
        add_option('ymid_redirect_page', '');

        /* user-configurable value checking public static functions */
        register_setting('youme_id', 'ymid_captcha_key', 'filter_string');
        register_setting('youme_id', 'ymid_captcha_secret', 'filter_string');
        register_setting('youme_id', 'ymid_forms');
        register_setting('youme_id', 'ymid_failed_login_allow', 'filter_int');
        register_setting('youme_id', 'ymid_ym_login');
        register_setting('youme_id', 'ymid_redirect_option');
        register_setting('youme_id', 'ymid_redirect_page');

        /* system values to determine if captcha is working and display useful error messages */
        add_option('ymid_working', true);
    }

    // Prompt when plugin is not working.
    public static function admin_notices() {
        if (false == get_option('ymid_working')) {
            echo '<div class="update-nag">' . "\n";
            echo '    <p>' . "\n";
            echo get_option('ymid_error');
            echo '    </p>' . "\n";
            echo '</div>' . "\n";
        }
    }

    public static function add_shake_error_codes($shake_error_codes) {
        $shake_error_codes[] = 'no_captcha';
        $shake_error_codes[] = 'invalid_captcha';
        return $shake_error_codes;
    }

    // Generate the most basic DOM in the form.
    public static function ymid_div() {
        ?>
        <div class="ym-id" data-sitekey="<?php echo get_option('ymid_captcha_key'); ?>"></div>
        <?php
    }
}

YMID::init();
