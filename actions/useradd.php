<?php
/**
 * Elgg add action
 *
 * @package Elgg
 * @subpackage Core
 */

elgg_make_sticky_form('useradd');

// Get variables
$username = get_input('username');
$password = get_input('password', null, false);
$password2 = get_input('password2', null, false);
$email = get_input('email');
$name = get_input('name');

// This param is not included in the useradd form by default,
// but it allows sites to easily add the feature if necessary.
$language = get_input('language', elgg_get_config('language'));

$admin = get_input('admin');
if (is_array($admin)) {
	$admin = $admin[0];
}

$autogen_password = get_input('autogen_password');
if ($autogen_password) {
	$password = generate_random_cleartext_password();
	$password2 = $password;
}

// no blank fields
if ($username == '' || $password == '' || $password2 == '' || $email == '' || $name == '') {
	register_error(elgg_echo('register:fields'));
	forward(REFERER);
}

if (strcmp($password, $password2) != 0) {
	register_error(elgg_echo('RegistrationException:PasswordMismatch'));
	forward(REFERER);
}

// For now, just try and register the user
try {
	$guid = register_user($username, $password, $name, $email, true);

	if ($guid) {
		$new_user = get_entity($guid);
		if ($new_user && $admin && elgg_is_admin_logged_in()) {
			$new_user->makeAdmin();
		}

		elgg_clear_sticky_form('useradd');

		$new_user->admin_created = true;
		// @todo ugh, saving a guid as metadata!
		$new_user->created_by_guid = elgg_get_logged_in_user_guid();

		// The user language is set also by register_user(), but it defaults to
		// language of the current user (admin), so we need to fix it here.
		$new_user->language = $language;

		$subject = elgg_echo('useradd:subject', [], $new_user->language);
		$body = elgg_echo('useradd:body', [
			$name,
			elgg_get_site_entity()->name,
			elgg_get_site_entity()->url,
			$username,
			$password,
		], $new_user->language);

		notify_user($new_user->guid, elgg_get_site_entity()->guid, $subject, $body, [
			'action' => 'useradd',
			'object' => $new_user,
			'password' => $password,
		]);

		system_message(elgg_echo("adduser:ok", [elgg_get_site_entity()->name]));
	} else {
		register_error(elgg_echo("adduser:bad"));
	}
} catch (RegistrationException $r) {
	register_error($r->getMessage());
}

forward(REFERER);
