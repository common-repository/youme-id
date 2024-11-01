<?php
if (!is_admin()) {
    die();
}
require_once plugin_dir_path( __FILE__ ) . 'functions.php';
?>
<div class="wrap">
    <h2><?php _e('YouMe ID Options', 'youme_id'); ?></h2>
    <?php if (get_locale() !== 'en_US'){ ?>
        <h3 style="color:red">WARNING: Sorry, We do not support the website language at present, You can deactivate the plugin.</h3>
    <?php }else{ ?>
        <form id="id_ymid_form" method="post" action="options.php">
        <?php echo settings_fields('youme_id'); ?>
        <p><?php echo sprintf(__('<a href="%s" target="_blank">Click here</a> to create keys for YouMe ID or <a href="%s" target="_blank">view keys</a> from YouMe ID service.', 'youme_id'), 'https://developer.youmeid.com/register.html', 'https://developer.youmeid.com/console/index.html#/apps'); ?></p>
        <table class="form-table">

            <tr valign="top">
                <th scope="row"><label for="id_ymid_captcha_key"><?php _e('Site Key', 'youme_id'); ?>
                        : </span>
                    </label></th>
                <td><input type="text" id="id_ymid_captcha_key" name="ymid_captcha_key"
                           value="<?php echo get_option('ymid_captcha_key'); ?>" size="40"/></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="id_ymid_captcha_secret"><?php _e('Secret Key', 'youme_id'); ?>
                        : </span>
                    </label></th>
                <td><input type="text" id="id_ymid_captcha_secret" name="ymid_captcha_secret"
                           value="<?php echo get_option('ymid_captcha_secret'); ?>" size="40"/></td>
            </tr>

            <tr>
                <th scope="row">Enabled Forms</th>
                <td>
                    <input type="hidden" name="ymid_forms[]" value="">
                    <label><input type="checkbox" id="ymid_forms_login" name="ymid_forms[]" value="login" <?php if(in_array("login", get_option('ymid_forms')))echo "checked='checked'" ?>> <?php _e('Login Form', 'youme_id'); ?></label><br>
                    <label><input type="checkbox" id="ymid_forms_registration" name="ymid_forms[]" value="registration" <?php if(in_array("registration", get_option('ymid_forms')))echo "checked='checked'" ?>> <?php _e('Registration Form', 'youme_id'); ?></label><br>
                    <label><input type="checkbox" id="ymid_forms_ms_user_signup" name="ymid_forms[]" value="ms_user_signup" <?php if(in_array("ms_user_signup", get_option('ymid_forms')))echo "checked='checked'" ?>> <?php _e('Multisite User Signup Form', 'youme_id'); ?></label><br>
                    <label><input type="checkbox" id="ymid_forms_lost_password" name="ymid_forms[]" value="lost_password" <?php if(in_array("lost_password", get_option('ymid_forms')))echo "checked='checked'" ?>> <?php _e('Lost Password Form', 'youme_id'); ?></label><br>
                    <label><input type="checkbox" id="ymid_forms_reset_password" name="ymid_forms[]" value="reset_password" <?php if(in_array("reset_password", get_option('ymid_forms')))echo "checked='checked'" ?>> <?php _e('Reset Password Form', 'youme_id'); ?></label><br>
                    <label><input type="checkbox" id="ymid_forms_comment" name="ymid_forms[]" value="comment"  <?php if(in_array("comment", get_option('ymid_forms')))echo "checked='checked'" ?>> <?php _e('Comment Form', 'youme_id'); ?></label><br>
                </td>
            </tr>
            <tr>
                <th scope="row">Failed login Captcha</th>
                <td>
                    <input type="number" id="ymid_failed_login_allow" name="ymid_failed_login_allow" value="<?php echo get_option('ymid_failed_login_allow') ?>" >
                    <p style="color: #777;"><?php _e('Show login Captcha after how many failed attempts? 0 = show always', 'youme_id'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">YouMe Account</th>
                <td>
                    <input type="hidden" name="ymid_ym_login[]" value="">
                    <div>
                        <input type="checkbox" name="ymid_ym_login[]" id="ymid_login_cb" onclick="display_login_option()" value="ym_login" <?php if(in_array("ym_login", get_option('ymid_ym_login')))echo "checked='checked'"  ?>> <?php _e('Sign in quickly with your YouMe account', 'youme_id'); ?>
                        <p style="color: #777;"><?php _e('Regular login method will still be retained. Set default roles for new users in general options.', 'youme_id'); ?></p>
                    </div>
                </td>
            </tr>


            <tr class="ymid_login_tr">
                <th scope="row"><?php _e('Redirect after login :', 'youme_id');?></th>
                <td>
                    <div>
                        <p>
                            <input type="radio" id="ymid_redirect_option_back" name="ymid_redirect_option" value="back" <?php if(get_option('ymid_redirect_option')==='back')echo "checked='checked'" ?>>
                            <label for="ymid_redirect_option_back"><?php _e('Redirect users back to the current page <b>(Default)</b>', 'youme_id');?></label>
                        </p>
                        <p>
                            <input type="radio" id="ymid_redirect_option_home" name="ymid_redirect_option" value="home" <?php if(get_option('ymid_redirect_option')==='home')echo "checked='checked'" ?> >
                            <label for="ymid_redirect_option_home"><?php _e('Redirect users to the homepage of my blog', 'youme_id');?></label>
                        </p>
                        <p>
                            <input type="radio" id="ymid_redirect_option_custom" name="ymid_redirect_option" value="custom" <?php if(get_option('ymid_redirect_option')==='custom')echo "checked='checked'" ?>>
                            <label for="ymid_redirect_option_custom"><?php _e('Redirect users to the following url:', 'youme_id');?></label>
                        <input type="text" name="ymid_redirect_page" value="<?php if(get_option('ymid_redirect_option')==='custom')echo get_option('ymid_redirect_page') ?>" size="40"/>
                        </p>
                    </div>

                </td>
            </tr>
        </table>
        <p>
            <div id="subbtn" class="button button-primary" onclick="subclick()">
                <?php _e('Save Changes', 'youme_id'); ?>
            </div>

            <div name="reset" id="reset" class="button" onclick="resetclick()">
                <?php _e('Delete Keys and Disable', 'youme_id'); ?>
            </div>
        </p>
    </form>
    <?php } ?>
    <h3><?php _e('Next Steps', 'youme_id'); ?></h3>
    <ol>
        <li><?php _e('If you see an error message above, check your keys before proceeding.', 'youme_id'); ?></li>
        <li><?php _e('If you see saved successfully above, proceed as follows:', 'youme_id'); ?></li>
        <ol>
            <li><?php _e('Open a completely different browser than this one', 'youme_id'); ?></li>
            <li><?php _e('If you are logged in on that new browser, log out', 'youme_id'); ?></li>
            <li><?php _e('Attempt to log in to your site admin from that new browser', 'youme_id'); ?></li>
        </ol>
        <li><?php _e('Do <em>not</em> close this window or log out from this browser until you are confident that YouMeID is working and you will be able to log in again. <br /><strong>You have been warned</strong>.', 'youme_id'); ?></li>
        <li><?php echo sprintf(__('If you have any problems logging in, click "%s" above and/or deactivate the plugin.', 'youme_id'), __('Delete Keys and Disable', 'youme_id')); ?></li>
    </ol>
</div>
<script>
    var subbtn = document.getElementById("subbtn");
    var outer = document.getElementsByClassName("wrap")[0];

    function subclick() {
        subbtn.disabled = true;
        checkCaptchaKeyAndSecret();
    }

    function resetclick() {
        document.getElementById("id_ymid_captcha_key").setAttribute('value', '');
        document.getElementById("id_ymid_captcha_secret").setAttribute('value', '');
        document.getElementById("id_ymid_form").submit();
    }
    function checkCaptchaKeyAndSecret() {
        let key = document.getElementById("id_ymid_captcha_key").value;
        let secret = document.getElementById("id_ymid_captcha_secret").value;
        if (!key && !secret) {
            document.getElementById("id_ymid_form").submit();
        }
        var base = new Base64();
        var result = base.encode(`${key}:${secret}`);
        var postData = "code=1&code_type=1&grant_type=authorization_code&client_type=captcha";

        if (window.XMLHttpRequest) {
            var xhr = new XMLHttpRequest();
        } else {
            var xhr = new ActiveXObject('Microsoft.XMLHTTP');
        }

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                subbtn.disabled = false;
                console.log("captcha:",xhr.responseText);
                if (xhr.responseText.indexOf('invalid_client')!==-1) {
                    addNotice("<?php _e('YouMe ID Site key or secret key error!', 'youme_id'); ?>");
                } else if(xhr.responseText.indexOf('invalid_grant')!==-1){
                    document.getElementById("id_ymid_form").submit();
                }else {
                    addNotice("<?php _e('Network error! Please try again.', 'youme_id'); ?>");
                }
            }
        }

        xhr.open("POST", "<?php echo get_ymid_url('get_token') ?>", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("Authorization", "Basic " + result);
        xhr.send(postData);
    }

    function addNotice(str) {
        let ym_notice = document.getElementsByClassName('ym-notice')[0];
        if(ym_notice !== undefined)
            ym_notice.parentNode.removeChild(ym_notice);

        outer.innerHTML = "<div class='notice notice-error is-dismissible ym-notice' >" +
            "<p><strong>" + str + "</strong></p>" +
            "<button type='button' class='notice-dismiss close-ym-notice' onclick='clearNotice(this)'><span class='screen-reader-text'>Dismiss this notice.</span></button>" +
            "</div>" + outer.innerHTML;
    }
    function clearNotice(t) {
        outer.removeChild(t.parentNode);
    }

    function Base64() {
        let _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        let _utf8_encode = function (string) {
            string = string.replace(/\\r\\n/g, "\\n");
            let utftext = "";
            for (let n = 0; n < string.length; n++) {
                let c = string.charCodeAt(n);
                if (c < 128) {
                    utftext += String.fromCharCode(c);
                } else if ((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                } else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }
            return utftext;
        }
        this.encode = function (input) {
            let output = "";
            let chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            let i = 0;
            input = _utf8_encode(input);
            while (i < input.length) {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);
                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;
                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }
                output = output +
                    _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
                    _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
            }
            return output;
        }
    }
</script>

<script>
    function display_login_option() {
        if(document.getElementById('ymid_login_cb').checked){
            for (let len = document.getElementsByClassName('ymid_login_tr').length; len > 0; len--) {
                document.getElementsByClassName('ymid_login_tr')[len-1].style.display = '';
            }
        }else {
            for (let len = document.getElementsByClassName('ymid_login_tr').length; len > 0; len--) {
                document.getElementsByClassName('ymid_login_tr')[len-1].style.display = 'none';
            }
        }
    }
    display_login_option();
</script>
<style>
    #subbtn + #reset {
        margin-left: 1em;
    }
</style>
