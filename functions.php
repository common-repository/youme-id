<?php

/**
 * Get YMID-post's post id in the database
 * @return int post id
 */
function post_id() {
    global $wpdb;
    static $post_id;

    if (!absint(get_option('ymid_failed_login_allow'))) {
        return 0;
    }
    if (is_numeric($post_id)) {
        return $post_id;
    }
    $post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'YMID-post' LIMIT 1");

    if (!$post_id) {
        $wpdb->insert($wpdb->posts, array('post_type' => 'YMID-post'));
        $post_id = $wpdb->insert_id;
    }
    $post_id = absint($post_id);

    return $post_id;
}

/**
 * Remove characters that are potentially harmful to the application.
 * @param $string The string to filter.
 * @return string Filtered string.
 */
function filter_string($string) {
    return trim(filter_var($string, FILTER_SANITIZE_STRING)); //must consist of valid string characters
}

// Filter target to get a integer.
function filter_int($num) {
    return absint($num);
}

function check_captcha_work_status() {
    $token_url = get_ymid_url('get_token');
    $key = get_option('ymid_captcha_key');
    $secret = get_option('ymid_captcha_secret');
    if (strlen($key) == 0 || strlen($secret) == 0) {
        return false;
    }
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
            'code' => '',
            'code_type' => 'lastcaptcha',
            'grant_type' => 'authorization_code'
        )
    );
    $result = wp_remote_post($token_url, $url_options);

    if(strpos($result['body'],'invalid_grant') !== false){
        return true;
    }else{
        return false;
    }
}

// register js and css
function enqueue_ymid_scripts_css() {
    if (!wp_script_is('ymid_api_js', 'registered')) {
        wp_register_script('ymid_api_js', get_ymid_url("apijs"));
        wp_register_style('ymid_api_css', get_ymid_url("apicss"));
        wp_enqueue_script('ymid_api_js');
        wp_enqueue_style('ymid_api_css');
    }
    if(!wp_script_is('ymid_css', 'registered')) {
        wp_register_style('ymid_css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        wp_enqueue_style('ymid_css');
    }
}

// Redirect to the specified page.
function ymid_ouath_redirect($url = '') {
    if ($url == '') {
        if (get_option('ymid_redirect_option') === 'back' && isset($_POST['redirect_to'])) {
            $url = $_POST['redirect_to'];
        } elseif (get_option('ymid_redirect_option') === 'custom' && strlen(get_option('ymid_redirect_page')) > 0) {
            $url = get_option('ymid_redirect_page');
        } else {
            $url = home_url();
        }
    }
    wp_redirect($url);
    exit;
}

// Url management container.
function get_ymid_url($url_name) {
    switch ($url_name) {
        case "apijs":
            return "https://www.youmeid.com/youmeid/v1/api.js?onload=go&hl=" . get_locale();
        case "apicss":
            return "https://www.youmeid.com/youmeid/v1/api.css";
        case "get_token":
            return "https://oidc.youmeid.com/v1/token";
        case "get_user":
            return "https://oidc.youmeid.com/v1/me";
    }
}