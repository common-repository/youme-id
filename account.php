<?php
$this_user = wp_get_current_user();
$ymid_uid = get_user_meta($this_user->ID, "ymid_uid", true);
$ymid_access_token = get_user_meta($this_user->ID, "ymid_access_token", true);
$ymid_avatar = get_user_meta($this_user->ID, "ymid_avatar", true);
$ymid_email = get_user_meta($this_user->ID, "ymid_email", true);
require_once plugin_dir_path( __FILE__ ) . 'functions.php';

echo '<h2>' . __('Account linking to YouMe ID', 'youme_id') . '</h2>';

if (isset($_POST['uname']) && isset($_POST['pwd'])) {// Handling bind requests
    $exist_user = wp_authenticate($_POST['uname'], $_POST['pwd']);

    // Verify that the target account has been bound by YMID
    switch (check_user($exist_user)){
        case 1:
            // If you verify by account password, merge account
            update_user_meta($exist_user->ID, "ymid_uid", $ymid_uid);
            update_user_meta($exist_user->ID, "ymid_access_token", $ymid_access_token);
            update_user_meta($exist_user->ID, "ymid_avatar", $ymid_avatar);
            update_user_meta($exist_user->ID, "ymid_email", $ymid_email);
            update_user_meta($exist_user->ID, "ymid_did_band", 1);
            wp_delete_user($this_user->ID, $exist_user->ID);// Delete current account and transfer posts and links
            ymid_band_success_form();
            break;
        case 2:
            // Continue verification without verification
            echo '<p>';
            _e('Invalid username or password!', 'youme_id');
            echo '</p>';
            ymid_bind_form($ymid_email);
            break;
        case 3:
            // The existing account is already bound to the ym account -> refuse to bind again
            echo '<p>';
            _e('Account already been bound to another YouMe ID!', 'youme_id');
            echo '</p>';
            ymid_bind_form($ymid_email);
    }

} else { //Provide binding page
    ymid_bind_form($ymid_email);
}

function check_user($exist_user) {
    // Todo Add a request to ym to restrict illegal behavior (high frequency, etc.) or release the tying of multiple identities with the account

    // Verify account password
    if (is_wp_error($exist_user)) {
        return 2;
    }
    if (get_user_meta($exist_user->ID, "ymid_uid", true)) {
        return 3;
    }
    return 1;
}

function ymid_bind_form($ymid_email) {
    ?>
    <p><?php _e('Provide credential of existing account:', 'youme_id'); ?></p>
    <form name="loginform" id="loginform" action="<?php echo home_url() . '/wp-admin/admin.php?page=youme-id/account.php'; ?>"
          method="post">
        <div class="username">
            <label for="user_login"><?php _e('Username'); ?>: </label>
            <input type="text" name="uname" value="" size="20" id="user_login" tabindex="11"/>
        </div>
        <div class="password">
            <label for="user_pass"><?php _e('Password'); ?>: </label>
            <input type="password" name="pwd" value="" size="20" id="user_pass" tabindex="12"/>
        </div>
        <div class="login_fields">

            <input type="submit" name="user-submit" value="<?php _e('Link', 'youme_id'); ?>" tabindex="14" class="user-submit"/>
        </div>
    </form>
    <?php
}


function ymid_band_success_form() {
    ?>
    <p>
        <?php _e('Accounts linked successfully, please login again!', 'youme_id'); ?>
    </p>
    <script>
        setTimeout('window.location.href="<?php echo wp_login_url()?>"', 3000);
    </script>
    <?php
}

?>
