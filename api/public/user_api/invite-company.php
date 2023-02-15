<?php
include_once('../../core.php');
/**
 *
 * POST /user/invite-company
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $company_name
 * @param string $company_email
 *
 */
class UserInviteCompany extends Endpoints {
	function __construct(
		$company_name  = '',
		$company_email = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth       = authenticate_session();
		$role_check = authenticate_role($auth, 'firm');

		$firm_guid     = $auth['guid'] ?? '';
		$company_name  = parent::$params['company_name'] ?? '';
		$company_email = parent::$params['company_email'] ?? 0;

		if (!filter_var($company_email, FILTER_VALIDATE_EMAIL)) {
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
			WHERE email = '$company_email'
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
			$company_name,
			true,
			2,
			Regex::$company_name['char_limit'],
			Regex::$company_name['pattern'],
			'Company name'
		);

		$company_guid  = $helper->generate_guid('company');
		$created_at    = $helper->get_datetime();
		$code          = $helper->generate_hash();

		$hash = $helper->aes_encrypt(
			$company_guid.'::'.
			$code.'::'.
			(string)time()
		);

		$link = PROTOCOL.'://'.FRONTEND_URL.'/c/accept-invitation/'.$hash;
		dlog($link);

		$company_pii          = Structs::company_info;
		$company_pii['name']  = $company_name;
		$enc_pii              = $helper->encrypt_pii($company_pii);
		dlog($company_pii);

		// create company user record
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
				'$company_guid',
				'company',
				'$company_email',
				'$enc_pii',
				'0',
				'',
				'$created_at',
				'$code'
			)
		");

		// create firm->company relationship record
		$db->do_query("
			INSERT INTO firm_company_relations (
				firm_guid,
				company_guid,
				associated_at
			) VALUES (
				'$firm_guid',
				'$company_guid',
				'$created_at'
			)
		");

		//// also create report record, and company->report relationship record

		$helper->schedule_email(
			'user-alert',
			$company_email,
			'Welcome to FincenFetch!',
			'Your account is now live with FincenFetch. Please use the following link to log in and set your password.',
			$link
		);

		_exit(
			'success',
			'Company invite sent'
		);
	}
}
new UserInviteCompany();
