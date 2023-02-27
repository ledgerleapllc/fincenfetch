<?php
/**
 *
 * POST /user/register
 *
 * @api
 * @param string $email
 *
 */
class UserRegister extends Endpoints {
	function __construct(
		$email = ''
	) {
		global $db, $helper, $authentication;

		require_method('POST');

		$email = parent::$params['email'] ?? null;

		/* For live tests */
		$phpunittesttoken = parent::$params['phpunittesttoken'] ?? '';

		/* Pre-check string formats and lengths */
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			_exit(
				'error', 
				'Invalid email address', 
				400, 
				'Invalid email address'
			);
		}

		/* check pre-existing email */
		$check = $db->do_select("
			SELECT guid
			FROM  users
			WHERE email    = '$email'
			AND   verified = 1
		");

		if ($check) {
			_exit(
				'error',
				'An account with this email address already exists',
				400,
				'An account with this email address already exists'
			);
		}

		$guid              = $helper->generate_guid();
		$created_at        = $helper->get_datetime();
		$confirmation_code = $helper->generate_hash(6);
		$registration_ip   = $helper->get_real_ip();

		// clear incomplete previous record
		$db->do_query("
			DELETE FROM users
			WHERE email    = '$email'
			AND   verified = 0
		");

		$db->do_query("
			INSERT INTO users (
				guid,
				role,
				email,
				created_at,
				confirmation_code
			) VALUES (
				'$guid',
				'firm',
				'$email',
				'$created_at',
				'$confirmation_code'
			)
		");

		/* create session */
		$bearer     = $authentication->issue_session($guid);
		$user_agent = filter($_SERVER['HTTP_USER_AGENT'] ?? '');

		/* register new authorized device */
		$cookie = $helper->add_authorized_device(
			$guid,
			$registration_ip,
			$user_agent
		);

		/* log login */
		$helper->log_action(
			$guid,
			$email,
			1,
			'First login',
			$registration_ip,
			$user_agent
		);

		/* get new user */
		$me = $helper->get_user($guid);

		if ($me) {
			$recipient = $email;
			$subject   = 'Verify Your '.APP_NAME.' Account';
			$body      = 'Hello and welcome to the '.APP_NAME.' Portal. Your registration code is below:<br><br>';
			$link      = $confirmation_code;

			$helper->schedule_email(
				'verify-registration',
				$recipient,
				$subject,
				$body,
				$link
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
	}
}
new UserRegister();