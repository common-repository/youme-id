=== YouMe ID ===
Contributors: youmeid
Tags: captcha,lastcaptcha,recaptcha,security,bots,login,registration,comments,password
Requires at least: 4.4
Tested up to: 5.2.2
Stable tag: 0.8.0
Requires PHP: 5.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The YouMe ID plugin provides two security features, i.e. an alternative CAPTCHA service and a quick login using smart phones.

a CAPTCHA mechanism to distinguish human beings from bots to thwart automated attacks on web forms.

== Installation ==

1. Install as normal for WordPress plugins；

2. Obtain a pair of site key and secret key from [YouMe ID service](https://developer.youmeid.com/register.html);

3. Fill the key pair in the setting for the plugin.

== Description ==

First of all, this plugin provides an ultimate solution for CAPTCHA. It tries to replace the traditional approach of solving a garbled image or puzzle with an identity established on a user's smart phone. It prmotes the use of this new way of doing CAPTCHA that is more convenient and secure, while accommodating legacy users.

For those site owners who wish to provide quick login to those users with smart phones, this plug-in provides a password-free login mechanism that eliminates the need for users to go through a registration process. 


= Features =

The CAPTCHA and quick login features can be activated indivdually.

You can select which of the following forms should be protected by the CAPTCHA mechanism:

* Login
* Register
* Multisite User Signup
* Comment
* Lost Password
* Reset Password

You can also specify how many failed login attempts before login CAPTCHA will show.

= Cloud-based service =

Our CAPTCHA + Quick login service relies on a cloud-based backend, accessed through a RESTful API provisioned at youmeid.com. For details, please visit [our web page](https://www.youmeid.com/products).

= Privacy notice =

* This plugin sends IP address to our service or Google's service for verification. For Google's service, please read [its privacy policy](https://policies.google.com/).

* Recurring users with identities serviced by us will have their presence cached in their browser's cookie to streamline verification.

* [Our privacy policy](https://www.youmeid.com/products/privacy)

= Support =

For questions or feedbacks, please visit [our support](https://www.youmeid.com/support).

== Frequently Asked Questions ==

= There are many plugins for CAPTCHA, why should I use <em>this</em> one? =

This plugin solves the problem in a unique way, i.e. by servicing identities for online users, rather than by taxing a human on either brain power or eyesight. This is the ultimate solution called for in the AI age. Our solution incorporates the best practices in CAPTCHA service by providing traditional solutions as an option, making it potentially the last CAPTCHA you ever need to install.

= Online FAQ

[Online FAQ](http://www.youmeid.com/products/faq/)

== Screenshots ==

1. Widget start page.

2. The new lastCAPTCHA option.

3. Traditional CAPTCHA alternative.

4. Ideal use case for recurring users.

5. Selection between QuickLogin and traditional login.

6. The new QuickLogin option.

== Changelog ==

= 0.5.0 =

* Initial beta release

= 0.6.0 =

* Remove related configuration settings when uninstalling the plugin.

* Added the function of quick login through YouMe ID.

* Added account binding function to bind YouMe ID account with existing account.

= 0.7.0 =

* Optimized interactive interface and text.

* Updated URL of YouMe ID service.

* Fixed some bugs.

= 0.8.0 =

* Update the url pointing to YouMe ID.

* Fixed wrong relative paths for stylesheet.

== Upgrade Notice ==

None yet.

