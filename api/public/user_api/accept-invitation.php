<?php
include_once('../../core.php');
/**
 *
 * POST /user/accept-invitation
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $password
 *
 */
class UserAcceptInvitation extends Endpoints {
	function __construct(
		$hash     = '',
		$password = ''
	) {
		global $helper, $db, $authentication;

		require_method('POST');

		$password = parent::$params['password'] ?? '';
		$hash     = parent::$params['hash'] ?? '';

		$uri  = $helper->aes_decrypt($hash);
		$uri  = explode('::', $uri);
		$guid = $uri[0];
		$code = $uri[1] ?? '';
		$time = (int)($uri[2] ?? 0);

		// check
		$check = $db->do_select("
			SELECT 
			guid, 
			confirmation_code, 
			email
			FROM  users
			WHERE confirmation_code = '$code'
			AND   guid              = '$guid'
		");

		if (!$check) {
			_exit(
				'error',
				'Invitation code is expired. Please try registering again',
				403,
				'Invitation code is expired'
			);
		}

		$email = $check[0]['email'] ?? '';

		// 1 month for accepting invitation
		$expire_time = 2592000;

		if ($time < (int)time() - $expire_time) {
			_exit(
				'error',
				'Invitation code is expired. Please try registering again',
				403,
				'Invitation code is expired'
			);
		}

		if (
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
		$now           = $helper->get_datetime();

		$db->do_query("
			UPDATE users
			SET
			password    = '$password_hash',
			verified    = 1,
			verified_at = '$now'
			WHERE guid  = '$guid'
		");

		/* create session */
		$bearer     = $authentication->issue_session($guid);
		$user_agent = filter($_SERVER['HTTP_USER_AGENT'] ?? '');
		$ip         = $helper->get_real_ip();

		/* register new authorized device */
		$cookie = $helper->add_authorized_device(
			$guid,
			$ip,
			$user_agent
		);

		/* log login */
		$helper->log_login(
			$guid,
			$email,
			1,
			'First login',
			$ip,
			$user_agent
		);

		/* get new user */
		$me = $helper->get_user($guid);

		if ($me) {
			$subject = 'Hello and welcome to FincenFetch';
			$body    = 'Hello and welcome to FincenFetch';

			$helper->schedule_email(
				'user-alert',
				$email,
				$subject,
				$body,
				''
			);

			_exit(
				'success',
				array(
					'bearer' => $bearer,
					'cookie' => $cookie
				)
			);
		}

		_exit(
			'error', 
			'Failed to register user', 
			500, 
			'Failed to register user'
		);

		_exit(
			'success',
			'Password set successfully'
		);
	}
}
new UserAcceptInvitation();