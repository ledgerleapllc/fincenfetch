<?php
include_once('../../core.php');
/**
 *
 * POST /admin/invite-firm
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $firm_name
 * @param string $firm_phone
 * @param string $firm_email
 *
 */
class AdminInviteFirm extends Endpoints {
	function __construct(
		$firm_name  = '',
		$firm_phone = '',
		$firm_email = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);

		$admin_guid = $auth['guid'] ?? '';
		$firm_name  = parent::$params['firm_name'] ?? '';
		$firm_phone = parent::$params['firm_phone'] ?? 0;
		$firm_email = parent::$params['firm_email'] ?? 0;

		if (!filter_var($firm_email, FILTER_VALIDATE_EMAIL)) {
			_exit(
				'error',
				'Invalid email address',
				400,
				'Invalid email address'
			);
		}

		// email check
		$check = $db->do_select("
			SELECT email
			FROM users
			WHERE email = '$firm_email'
		");

		if ($check) {
			_exit(
				'error',
				'Email address already in use',
				400,
				'Email address already in use'
			);
		}

		$helper->sanitize_input(
			$firm_name,
			true,
			2,
			Regex::$human_name['char_limit'],
			Regex::$human_name['pattern'],
			'Firm name'
		);

		$helper->sanitize_input(
			$firm_phone,
			true,
			Regex::$phone['char_limit'] - 8,
			Regex::$phone['char_limit'],
			Regex::$phone['pattern'],
			'Phone number'
		);

		$firm_phone = str_replace('-', '', $firm_phone);
		$firm_phone = str_replace('(', '', $firm_phone);
		$firm_phone = str_replace(')', '', $firm_phone);
		$firm_phone = str_replace('+', '', $firm_phone);
		$firm_phone = str_replace(' ', '', $firm_phone);
		$firm_guid  = $helper->generate_guid('firm');
		$created_at = $helper->get_datetime();
		$code       = $helper->generate_hash();

		$hash       = $helper->aes_encrypt(
			$firm_guid.'::'.
			$code.'::'.
			(string)time()
		);

		$link = PROTOCOL.'://'.FRONTEND_URL.'/f/accept-invitation/'.$hash;

		$firm_pii          = Structs::firm_info;
		$firm_pii['name']  = $firm_name;
		$firm_pii['phone'] = $firm_phone;
		$enc_pii           = $helper->encrypt_pii($firm_pii);

		$db->do_query("
			INSERT INTO users (
				guid,
				role,
				email,
				pii_data,
				verified,
				password,
				created_at,
				confirmation_code
			) VALUES (
				'$firm_guid',
				'firm',
				'$firm_email',
				'$enc_pii',
				'0',
				'',
				'$created_at',
				'$code'
			)
		");

		$helper->schedule_email(
			'user-alert',
			$firm_email,
			'Welcome to FincenFetch!',
			'Your account is now live with FincenFetch. Please use the following link to log in and set your password.',
			$link
		);

		_exit(
			'success',
			'Firm invite sent'
		);
	}
}
new AdminInviteFirm();
