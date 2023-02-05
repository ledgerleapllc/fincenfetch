<?php
include_once('../../core.php');
/**
 *
 * POST /user/set-password
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $password
 *
 */
class UserSetPassword extends Endpoints {
	function __construct(
		$password = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth     = authenticate_session();
		$guid     = $auth['guid'] ?? '';
		$password = parent::$params['password'] ?? '';

		if(
			!$password ||
			strlen($password) < 8 ||
			!preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $password) ||
			!preg_match('/[0-9]/', $password)
		) {
			_exit(
				'error',
				'Invalid password. Must be at least 8 characters long, contain at least one (1) special character, and one (1) number',
				400,
				'Invalid password. Failed complexity requirements'
			);
		}

		$already_set = $db->do_select("
			SELECT password
			FROM users
			WHERE guid = '$guid'
		");

		$already_set = $already_set[0]['password'] ?? '';

		if ($already_set) {
			_exit(
				'error',
				'Password already set',
				400,
				'Password already set'
			);
		}

		$password_hash = password_hash($password, PASSWORD_DEFAULT);

		$db->do_query("
			UPDATE users
			SET   password = '$password_hash'
			WHERE guid     = '$guid'
		");

		_exit(
			'success',
			'Password set successfully'
		);
	}
}
new UserSetPassword();