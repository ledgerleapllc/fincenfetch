<?php
include_once('../../core.php');
/**
 *
 * POST /user/create-report
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $company_guid
 * @param string $company_name
 * @param string $company_email
 * @param string $company_phone
 *
 */
class UserCreateReport extends Endpoints {
	function __construct(
		$company_guid   = '',
		$company_name   = '',
		$company_email  = '',
		$company_phone  = '',
		$report_updated = 0
	) {
		global $db, $helper;

		require_method('POST');

		$auth       = authenticate_session();
		$role_check = authenticate_role($auth, 'firm');
		$firm_guid  = $auth['guid'] ?? '';
		$companies  = $helper->get_firm_companies($firm_guid);

		$company_guid   = parent::$params['company_guid'] ?? '';
		$company_name   = parent::$params['company_name'] ?? '';
		$company_email  = parent::$params['company_email'] ?? '';
		$company_phone  = parent::$params['company_phone'] ?? '';
		$report_updated = (int)(parent::$params['report_updated'] ?? 0);

		$email_sent     = false;
		$filing_year    = $helper->get_filing_year();
		$report_guid    = $helper->generate_guid('report');
		$now            = $helper->get_datetime();
		$firm           = $helper->get_user($firm_guid);
		$firm_name      = $firm['pii_data']['name'] ?? '';

		dlog('companies');
		dlog($companies);

		if ($company_guid) {
			$selected_company = false;

			// verify custody of company by guid
			foreach ($companies as $company) {
				$read_guid = $company['guid'] ?? '';

				if ($read_guid == $company_guid) {
					$selected_company = $company;
				}
			}

			if (!$selected_company) {
				_exit(
					'error',
					'Invalid company specified',
					400,
					'Invalid company specified'
				);
			}

			// check for email/phone update
			$selected_email = $selected_company['email'] ?? '';
			$selected_phone = $selected_company['pii_data']['phone'] ?? '';

			$company = $db->do_select("
				SELECT *
				FROM  users
				WHERE guid = '$company_guid'
			")[0] ?? array();

			if (
				$company_email != $selected_email &&
				$company_email != ''
			) {
				if (
					!filter_var(
						$company_email, 
						FILTER_VALIDATE_EMAIL
					)
				) {
					_exit(
						'error',
						'Invalid email address',
						400,
						'Invalid email address'
					);
				}

				//// check if able to change email, verified and a report filed

				// check if email exists
				$check = $db->do_select("
					SELECT email
					FROM  users 
					WHERE email = '$company_email'
				");

				if ($check) {
					_exit(
						'error',
						'Email address already exists in the system',
						400,
						'Email address already exists in the system'
					);
				}

				// update
				$db->do_query("
					UPDATE users
					SET   email = '$company_email'
					WHERE guid  = '$company_guid'
				");
			}

			if (
				$company_phone != $selected_phone &&
				$company_phone != ''
			) {
				$helper->sanitize_input(
					$company_phone,
					true,
					10,
					Regex::$phone['char_limit'],
					Regex::$phone['pattern'],
					'Company Phone'
				);

				$company_phone = str_replace('-', '', $company_phone);
				$company_phone = str_replace('(', '', $company_phone);
				$company_phone = str_replace(')', '', $company_phone);
				$company_phone = str_replace('+', '', $company_phone);
				$company_phone = str_replace(' ', '', $company_phone);

				$pii = $helper->decrypt_pii($company['pii_data'] ?? '');
				$pii = $pii ?? Structs::company_info;

				$pii['phone'] = $company_phone;
				$pii_enc      = $helper->encryt_pii($pii);

				// update
				$db->do_query("
					UPDATE users
					SET   pii_data = '$pii_enc'
					WHERE guid     = '$company_guid'
				");
			}
		}

		// new company
		else {
			// check name
			$helper->sanitize_input(
				$company_name,
				true,
				2,
				Regex::$company_name['char_limit'],
				Regex::$company_name['pattern'],
				'Company Name'
			);

			// check email
			if (
				!filter_var(
					$company_email, 
					FILTER_VALIDATE_EMAIL
				)
			) {
				_exit(
					'error',
					'Invalid email address',
					400,
					'Invalid email address'
				);
			}

			$email_check = $db->do_select("
				SELECT email
				FROM  users 
				WHERE email = '$company_email'
			");

			if ($email_check) {
				_exit(
					'error',
					'Email address already exists in the system',
					400,
					'Email address already exists in the system'
				);
			}

			// check phone
			$helper->sanitize_input(
				$company_phone,
				true,
				10,
				Regex::$phone['char_limit'],
				Regex::$phone['pattern'],
				'Company Phone'
			);

			$company_phone = str_replace('-', '', $company_phone);
			$company_phone = str_replace('(', '', $company_phone);
			$company_phone = str_replace(')', '', $company_phone);
			$company_phone = str_replace('+', '', $company_phone);
			$company_phone = str_replace(' ', '', $company_phone);

			// handle pii
			$pii_data          = Structs::company_info;
			$pii_data['name']  = $company_name;
			$pii_data['phone'] = $company_phone;
			$pii_enc           = $helper->encrypt_pii($pii_data);
			$confirmation_code = $helper->generate_hash();
			$company_guid      = $helper->generate_guid('company');

			// create company user
			$db->do_query("
				INSERT INTO users (
					guid,
					role,
					email,
					pii_data,
					created_at,
					confirmation_code
				) VALUES (
					'$company_guid',
					'company',
					'$company_email',
					'$pii_enc',
					'$now',
					'$confirmation_code'
				)
			");

			// create firm->company relationship
			$db->do_query("
				INSERT INTO firm_company_relations (
					firm_guid,
					company_guid,
					associated_at
				) VALUES (
					'$firm_guid',
					'$company_guid',
					'$now'
				)
			");

			$hash = $helper->aes_encrypt(
				$company_guid.'::'.
				$confirmation_code.'::'.
				(string)time()
			);

			$link = PROTOCOL.'://'.FRONTEND_URL.'/c/accept-invitation/'.$hash;
			dlog('link');
			dlog($link);

			// send invitation email
			$helper->schedule_email(
				'user-alert',
				$company_email,
				'Welcome to FincenFetch!',
				'Your account is now live with FincenFetch. Please use the following link to log in and set your password.',
				$link
			);

			$email_sent = true;
		}

		// check report exists
		$report = $db->do_select("
			SELECT *
			FROM  reports
			WHERE company_guid = '$company_guid'
			AND   filing_year  = '$filing_year'
		")[0] ?? null;

		// and a check for pre-existing reports for this company
		$initial_report = $db->do_select("
			SELECT report_guid
			FROM   reports
			WHERE  company_guid = '$company_guid'
		");

		if ($report) {
			//// add record of link sent by firm

			$helper->schedule_email(
				'user-alert',
				$company_email,
				'File your Report with FincenFetch!',
				'You have received a Beneficial Owners Report link from your firm. Follow the link to your dashboard to get started.',
				FRONTEND_URL.'/login?start='.$report_guid
			);
		}

		else {
			// define report object
			$pii_data = Structs::report_info;

			// add report_year
			$pii_data['report_year'] = $filing_year;

			// finish encoding initial data, and encrypt.
			$pii_enc  = $helper->encrypt_pii($pii_data);

			// find report_type
			$report_type = 'initial';

			if ($report_updated) {
				$report_type = 'updated';
			}

			if ($initial_report) {
				$report_type = 'updated';
			}

			$db->do_query("
				INSERT INTO reports (
					report_guid,
					company_guid,
					firm_guid,
					report_type,
					pii_data,
					created_at,
					updated_at,
					filing_year
				) VALUES (
					'$report_guid',
					'$company_guid',
					'$firm_guid',
					'$report_type',
					'$pii_enc',
					'$now',
					'$now',
					'$filing_year'
				)
			");
		}

		if (!$email_sent) {
			//// add record of link sent by firm

			$helper->schedule_email(
				'user-alert',
				$company_email,
				'File your Report with FincenFetch!',
				'You have received a Beneficial Owners Report link from your firm. Follow the link to your dashboard to get started.',
				FRONTEND_URL.'/login?start='.$report_guid
			);
		}

		_exit(
			'success',
			'BOI link sent'
		);
	}
}
new UserCreateReport();
