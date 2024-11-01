<?php

class ym_captcha
{
    public static function init_ymid_captcha() {
        if(get_option('ymid_captcha_key')&&get_option('ymid_captcha_secret')){
            if (in_array("comment", get_option('ymid_forms', array())) && (!is_admin() || !current_user_can('moderate_comments'))) {
                if (!is_user_logged_in()) {
                    add_action('comment_form_after_fields', 'enqueue_ymid_scripts_css');
                    add_action('comment_form_after_fields', array('ym_captcha', 'ymid_captcha_form'));
                    add_filter('pre_comment_approved', array('ym_captcha', 'authenticate'), 30);
                }
            }

            if (in_array("login", get_option('ymid_forms', array()))) {
                if (ym_captcha::show_login_captcha()) {
                    add_action('login_enqueue_scripts', 'enqueue_ymid_scripts_css');
                    add_action('login_form', array('ym_captcha', 'ymid_captcha_form'));
                    add_action('authenticate', array('ym_captcha', 'authenticate'), 30);
                    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                        add_action('wp_head', 'enqueue_ymid_scripts_css');
                        add_action('woocommerce_login_form', array('ym_captcha', 'ymid_captcha_form'));
                    }
                }
                add_action('authenticate', array('ym_captcha', 'login_record'), 999, 3);
                add_action('wp_login', array('ym_captcha', 'clear_data'), 10, 2);
            }

            if (in_array("registration", get_option('ymid_forms', array()))) {
                add_action('register_form', 'enqueue_ymid_scripts_css');
                    add_action('register_form', array('ym_captcha', 'ymid_captcha_form'));
                add_action('registration_errors', array('ym_captcha', 'authenticate'), 30);
                add_action('woocommerce_register_form', 'enqueue_ymid_scripts_css');
                add_action('woocommerce_register_form', array('ym_captcha', 'ymid_captcha_form'));
                add_action('woocommerce_registration_errors', array('ym_captcha', 'authenticate'), 30);
            }

            if (in_array("ms_user_signup", get_option('ymid_forms', array())) && is_multisite()) {
                add_action('signup_extra_fields', 'enqueue_ymid_scripts_css');
                add_action('signup_extra_fields', array('ym_captcha', 'ymid_captcha_form'));
                add_action('wpmu_validate_user_signup', array('ym_captcha', 'authenticate'), 30);
                add_action('signup_blogform', 'enqueue_ymid_scripts_css');
                add_action('signup_blogform', array('ym_captcha', 'ymid_captcha_form'));
                add_action('wpmu_validate_blog_signup', array('ym_captcha', 'authenticate'), 30);
            }

            if (in_array("lost_password", get_option('ymid_forms', array()))) {
                add_action('lostpassword_form', 'enqueue_ymid_scripts_css');
                add_action('lostpassword_form', array('ym_captcha', 'ymid_captcha_form'));
                add_action('woocommerce_lostpassword_form', 'enqueue_ymid_scripts_css');
                add_action('woocommerce_lostpassword_form', array('ym_captcha', 'ymid_captcha_form'));
                add_action('lostpassword_post', array('ym_captcha', 'authenticate'), 30);
            }

            if (in_array("reset_password", get_option('ymid_forms', array()))) {
                add_action('resetpass_form', 'enqueue_ymid_scripts_css');
                add_action('resetpass_form', array('ym_captcha', 'ymid_captcha_form'));
                add_action('woocommerce_resetpassword_form', 'enqueue_ymid_scripts_css');
                add_action('woocommerce_resetpassword_form', array('ym_captcha', 'ymid_captcha_form'));
                add_action('validate_password_reset', array('ym_captcha', 'authenticate'), 30);
            }
        }
    }

    // Show captcha in the form
    public static function ymid_captcha_form() {
        ?>
        <script>
            var ymid = document.getElementsByClassName("ym-id")[0];
            if(ymid){
                ymid.innerHTML = '<div class="ym-captcha" data-callback="submitEnable" data-expired-callback="submitDisable" data-error-callback="submitEnable"></div>';
            }
            function submitEnable() {
                var button = document.getElementById('wp-submit');
                if (button === null) {
                    button = document.getElementById('submit');
                }
                if (button !== null) {
                    button.removeAttribute('disabled');
                }
            }

            function submitDisable() {
                var button = document.getElementById('wp-submit');
                <?php if (!is_admin()) {?>
                if (button === null) {
                    button = document.getElementById('submit');
                }
                <?php } ?>
                if (button !== null) {
                    button.setAttribute('disabled', 'disabled');
                }
            }

            function docready(fn) {/in/.test(document.readyState) ? setTimeout('docready(' + fn + ')', 9) : fn()}

            docready(function () {submitDisable();});

            var timer = setTimeout(function () {
                submitEnable();
            }, 4000);
            // go() => the callback function when api.js is load success.
            window.go = function () {
                clearTimeout(timer);
            };

        </script>
        <?php
    }

    // Check if the code in the form is correct, reject or release
    public static function authenticate($user) {
        if(is_wp_error($user))
            return $user;

        if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) !== 'wp-login.php' &&
            !isset($_POST['woocommerce-login-nonce'])) {
            return $user;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || isset($_POST['ym-login-response'])) {
            return $user;
        }

        if (isset($_POST['ym-captcha-response'])) {
            $code_type = 'lastcaptcha';
            $code = filter_string($_POST['ym-captcha-response']);
        } elseif (isset($_POST['g-recaptcha-response'])) {
            $code_type = 'recaptcha';
            $code = filter_string($_POST['g-recaptcha-response']);
        } else {
            update_option('ymid_working', true);
            if(check_captcha_work_status()){
                return new WP_Error('no_captcha', __('<strong>ERROR</strong>&nbsp;: Please check the YouMe ID box.', 'youme_id'));
            }else{
                return $user;
            }
        }

        $key = get_option('ymid_captcha_key');
        $secret = get_option('ymid_captcha_secret');
        $token_url = get_ymid_url("get_token");
        $url_options = array(
            'method' => 'POST',
            'timeout' => '5',
            'redirection' => '5',
            'blocking' => true,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($key . ":" . $secret)
            ),
            'body' => array(
                'code' => $code,
                'code_type' => $code_type,
                'grant_type' => 'authorization_code'
            )
        );

        $result = wp_remote_post($token_url, $url_options);
        if(is_wp_error($result)){
            error_log(json_encode($result));
            $result = wp_remote_post($token_url, $url_options);
            if(is_wp_error($result))
                return $user;
        }
        $httpcode = wp_remote_retrieve_response_code($result);
        $g_response = json_decode($result['body']);

        if (is_object($g_response)) {
            if ($httpcode == 200) {
                update_option('ymid_working', true);
                return $user; // success, let them in
            } else if ($httpcode == 500 || $httpcode == 502) {
                // allow login when token check service down
                return $user;
            } else if (isset($g_response->{'error'}) && ('invalid_client' == $g_response->{'error'})) {
                // allow login when key or secret is invalid
                update_option('ymid_working', false);
                update_option('ymid_google_error', 'error');
                update_option('ymid_error', sprintf(__('YouMe ID is not working. <a href="%s">Please check your settings</a>.', 'youme_id'), 'options-general.php?page=youme-id/admin.php') . ' ' . __('The response from YouMe Identity was not valid.', 'youme_id'));
                return $user;
            } else {
                update_option('ymid_working', true);
                if (is_wp_error($user)) {
                    $user->add('invalid_captcha', __('<strong>ERROR</strong>&nbsp;: Incorrect YouMeID, please try again.', 'youme_id'));
                    return $user;
                } else {
                    return new WP_Error('invalid_captcha', __('<strong>ERROR</strong>&nbsp;: Incorrect YouMeID, please try again.', 'youme_id'));
                }
            }
        } else {
            update_option('ymid_working', false);
            update_option('ymid_google_error', 'error');
            update_option('ymid_error', sprintf(__('YouMe ID is not working. <a href="%s">Please check your settings</a>.', 'youme_id'), 'options-general.php?page=youme-id/admin.php') . ' ' . __('The response from YouMe Identity was not valid.', 'youme_id'));
            return $user;
        }
    }

    // When trying to log in, record the IP.
    public static function login_record($user, $username = '') {
        global $wpdb;

        $show_captcha = ym_captcha::show_login_captcha();

        if ($username == '')
            return $user;

        if (!($user instanceof WP_User)) {
            if (!$show_captcha && ($post_id = post_id())) {
                $wpdb->insert($wpdb->postmeta, array('post_id' => $post_id, 'meta_key' => md5($_SERVER['REMOTE_ADDR']), 'meta_value' => $username), array('%d', '%s', '%s'));
            }
        }

        return $user;
    }

    // Clear IP records after successful login.
    public static function clear_data($user_login) {
        global $wpdb;

        if ($post_id = post_id()) {
            $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id = %d AND ( meta_key = %s OR meta_value = %s )", $post_id, md5($_SERVER['REMOTE_ADDR']), $user_login));
        }
    }

    // Prevent violent robot attacks based on IP records.
    private static function show_login_captcha() {
        global $wpdb;

        $show_captcha = true;
        $ip = $_SERVER['REMOTE_ADDR'];
        $count = absint(get_option('ymid_failed_login_allow'));
        $post_id = post_id();

        if ($count && $post_id && filter_var($ip, FILTER_VALIDATE_IP)) {
            $user_logins = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, md5($ip)));

            if (count($user_logins) < $count && count(array_unique($user_logins)) <= 1) {
                $show_captcha = false;
            }
        }

        return $show_captcha;
    }
}