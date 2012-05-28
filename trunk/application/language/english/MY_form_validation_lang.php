<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MY_form_validation_lang
 * 
 * Where you can store language values for the
 * form
 *
 * @author Simon Emms <simon@simonemms.com>
 */

/* Form labels */
$lang['form_emailaddress'] = 'Email Address';
$lang['form_username'] = 'Username';
$lang['form_password'] = 'Password';
$lang['form_forgotten_password'] = 'Forgotten password';
$lang['form_save_login'] = 'Save login on this computer';

/* General stuff */
$lang['form_yes'] = 'Yes';
$lang['form_no'] = 'No';
$lang['form_day_select'] = '- Day -';
$lang['form_month_select'] = '- Month -';
$lang['form_year_select'] = '- Year -';
$lang['dropdown_select'] = '- Select -';
$lang['required_message'] = '%l denotes required field';
$lang['form_per_page'] = 'Results Per Page';
$lang['form_display_order'] = 'Display Order';

/* Errors */
$lang['form_error_title'] = 'Oops!';
$lang['required_dropdown'] = 'The %s field is required.';
$lang['form_invalid_date'] = 'The date used for %s is invalid';
$lang['form_before_min_date'] = 'The date for %s must be after %d';
$lang['form_after_max_date'] = 'The date for %s must be before %d';
$lang['alnum_space'] = "The %s field may only contain alpha-numeric characters and spaces.";
$lang['alpha_dash_space'] = "The %s field may only contain alpha-numeric characters, underscores, dashes and spaces.";
$lang['alpha_space'] = "The %s field may only contain alpha characters and spaces.";

/* Action menu buttons */
$lang['action_menu_submit'] = 'Submit';
$lang['action_menu_login'] = 'Login';
$lang['action_menu_reset'] = 'Reset';
$lang['action_menu_go'] = 'Go';

/* CAPTCHA language */
$lang['recaptcha_class_initialized'] = 'reCaptcha Library Initialized';
$lang['recaptcha_no_private_key'] = 'You did not supply an API key for Recaptcha';
$lang['recaptcha_no_remoteip'] = 'For security reasons, you must pass the remote ip to reCAPTCHA';
$lang['recaptcha_socket_fail'] = 'Could not open socket';
$lang['recaptcha_incorrect_response'] = 'Incorrect Security Image Response';
$lang['recaptcha_field_name'] = 'Security Image';
$lang['recaptcha_html_error'] = 'Error loading security image.  Please try again later';

?>