<?php

class ym_login
{
    public static function init_ymid_login() {
        if (get_option('ymid_captcha_key') && get_option('ymid_captcha_secret') && in_array("ym_login", get_option('ymid_ym_login', array()))) {
            add_action('login_enqueue_scripts', 'enqueue_ymid_scripts_css');
            add_action('login_form', array('ym_login', 'ymid_login_form'), 20);
            add_action('authenticate', array('ym_login', 'ymid_login'), 20);
        }

        add_action('show_user_profile', array('ym_login','ymid_band_acount_form')); // editing your own profile

        if (isset($_GET['is_first_login'])) {
            add_action('all_admin_notices', array('ym_login','ymid_create_account_success_notice'));
        }
        if($_GET['unbind']==="true"){
            add_action('all_admin_notices', array('ym_login','ymid_unbind_account_success_notice'));
        }
    }

    // perform login after authorizing to obtain token
    public static function ymid_login($user) {
        if (isset($_POST['ym-login-response']) && strlen(filter_string($_POST['ym-login-response'])) > 0) {
            $code = filter_string($_POST['ym-login-response']);
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
                    'code_type' => 'quicklogin',
                    'grant_type' => 'authorization_code'
                )
            );

            $result = wp_remote_post($token_url, $url_options);
            if (is_wp_error($result)) {
                return new WP_Error('network_error', __('<strong>Network Error</strong> : Request timeout, please try again.', 'youme_id'));
            }
            $response = json_decode($result['body']);
            $ymid_access_token = $response->access_token;

            $user_url = get_ymid_url("get_user");
            $url_option = array('headers' => array('Content-Type' => 'application/json', 'authorization' => 'Bearer ' . $ymid_access_token));
            $userymid = wp_remote_get($user_url, $url_option);
            if (is_wp_error($userymid)) {
                return new WP_Error('network_error', __('<strong>Network Error</strong> : Request timeout, please try again.', 'youme_id'));
            }
            $userinfo = json_decode($userymid['body'], true);

            // Extract information from the return
            $ymid_uid = filter_string($userinfo['sub']);
            $username = filter_string($userinfo['given_name']);
            $avatar = filter_string($userinfo['avatar']);
            $email = filter_string($userinfo['email']);
            $local_email = (is_email($email) && !get_user_by('email', $email)) ? $email : "";

            if (!is_string($ymid_uid) || strlen($ymid_uid) === 0) {
                return new WP_Error('invalid_account', __('<strong>ERROR</strong>&nbsp;: Incorrect YouMeID, please try again.', 'youme_id'));
            }

            //Logged in - bind identity
            // if (is_user_logged_in()) {
            //     $this_user = wp_get_current_user();
            //     update_user_meta($this_user->ID, "ymid_uid", $ymid_uid);
            //     update_user_meta($this_user->ID, "ymid_access_token", $ymid_access_token);
            //     update_user_meta($this_user->ID, "ymid_avatar", $avatar);
            //     update_user_meta($this_user->ID, "ymid_email", $email);
            // } else {
            $user_ymid = get_users(array("meta_key" => "ymid_uid", "meta_value" => $ymid_uid));

            // Not logged in - no account - create user
            if (is_wp_error($user_ymid) || !count($user_ymid)) {
                $login_name = wp_create_nonce($ymid_uid);
                if (get_users(array("login" => $login_name))) {
                    $login_name = substr('YM_' . $ymid_uid, 0, 60);
                }
                $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
                $userdata = array(
                    'user_login' => $login_name,
                    'display_name' => $username,
                    'user_pass' => $random_password,
                    'nickname' => $username,
                    'user_email' => $local_email,
                    'role' => get_option('default_role')
                );
                $user_id = wp_insert_user($userdata);
                update_user_meta($user_id, "ymid_uid", $ymid_uid);
                update_user_meta($user_id, "ymid_access_token", $ymid_access_token);
                update_user_meta($user_id, "ymid_avatar", $avatar);
                update_user_meta($user_id, "ymid_email", $email);
                update_user_meta($user_id, "ymid_did_band", 0);
                wp_set_auth_cookie($user_id);

                // if in an iframe,do not redirect
                if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],"interim-login"))
                    return get_user_by( 'id', $user_id);
                ymid_ouath_redirect(home_url() . '/wp-admin/profile.php?is_first_login=true' . (isset($_POST['redirect_to']) ? '&redirect_to=' . $_POST['redirect_to'] : ''));

                // Not logged in - have account - update user information
            } else {
                update_user_meta($user_ymid[0]->ID, "ymid_access_token", $ymid_access_token);
                update_user_meta($user_ymid[0]->ID, "ymid_avatar", $avatar);
                wp_update_user(array('ID' => $user_ymid[0]->ID, 'display_name' => $username, 'nickname' => $username));
                wp_set_auth_cookie($user_ymid[0]->ID);

                // if in an iframe,do not redirect
                if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],"interim-login"))
                    return get_user_by( 'id', $user_ymid[0]->ID);
                ymid_ouath_redirect();
            }
            // }
        } else {
            return $user;
        }

    }

    // Display the QuickLogin function in the login form.
    public static function ymid_login_form() {
        ?>
        <script>
            var ymid = document.getElementsByClassName("ym-id")[0];
            if(ymid){
               ymid.innerHTML +='<div class="ym-login" data-callback="submit_form" data-scope="email nickname avatar"></div>';
            }
            function submit_form(data) {
                document.getElementById('loginform').submit();
            }
        </script>
        <?php
    }

    // On the user's personal information page, the account binding information is displayed.
    public static function ymid_band_acount_form() {
        $this_user = wp_get_current_user();
        $ymid_did_band = get_user_meta($this_user->ID, "ymid_did_band", true);

        if ($ymid_did_band === "0") {// Is ym account and no binding -> provide binding
            echo '<h1>' . __("YouMe ID Account Linkage", 'youme_id') . '</h1>';
            echo sprintf('<p>' . __('Link to existing account %shere%s.', 'youme_id') . '</p>', '<a href="users.php?page=youme-id/account.php">', '</a>');
        }elseif($ymid_did_band === "1"){// Already bound -> unbind or display relation
            if($_GET['unbind']==="true"){
                delete_user_meta($this_user->ID,'ymid_uid');
                delete_user_meta($this_user->ID,'ymid_access_token');
                delete_user_meta($this_user->ID,'ymid_avatar');
                delete_user_meta($this_user->ID,'ymid_email');
                delete_user_meta($this_user->ID,'ymid_did_band');
            }else{
                echo '<h1>' . __("Account Linkage", 'youme_id') . '</h1>';
                ?>
                <table>
                    <tbody>
                    <tr>
                        <th><?php _e('YouMe ID: ', 'youme_id'); ?></th>
                        <td>
                            <?php echo get_user_meta($this_user->ID, "ymid_uid", true); ?>
                            <a href="profile.php?unbind=true"><?php _e('Unlink', 'youme_id'); ?></a>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <?php
            }
        }
    }

    // A prompt indicating that the account was successfully created is displayed.
    public static function ymid_create_account_success_notice() {
        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __('New account created！Link to existing account %shere%s.', 'youme_id'), '<a href="users.php?page=youme-id/account.php">', '</a>') . '</p></div>';
    }

    // A prompt indicating that the account is successfully disassociated is displayed.
    public static function ymid_unbind_account_success_notice() {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Unlinked successfully!', 'youme_id') . '</p></div>';
    }
}