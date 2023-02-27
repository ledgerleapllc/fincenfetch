<?php
/**
 *
 * PUT /user/update-report
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $report_guid
 *
 */
class UserUpdateReport extends Endpoints {
	function __construct(
		$report_guid = ''
	) {
		global $db, $helper;

		require_method('PUT');

		$auth       = authenticate_session();
		$role_check = authenticate_role(
			$auth, 
			array('company', 'firm')
		);

		$guid        = $auth['guid'] ?? '';
		$role        = $auth['role'] ?? '';
		$report_guid = parent::$params['report_guid'] ?? '';
		$now         = $helper->get_datetime();

		$helper->sanitize_input(
			$report_guid,
			true,
			Regex::$guid['char_limit'],
			Regex::$guid['char_limit'],
			Regex::$guid['pattern'],
			'Report GUID'
		);

		// company
		if ($role == 'company') {
			//// check if can modify report at this time

			// intro
			$intro        = (bool)(parent::$params['intro'] ?? '');

			// company name / report type
			$company_name = parent::$params['company_name'] ?? '';
			$report_type  = parent::$params['report_type'] ?? '';

			// dbas
			$dbas         = parent::$params['dbas'] ?? null;

			// office address
			$office_address1 = parent::$params['office_address1'] ?? '';
			$office_address2 = parent::$params['office_address2'] ?? null;
			$office_city     = parent::$params['office_city'] ?? '';
			$office_state    = parent::$params['office_state'] ?? '';
			$office_zip      = parent::$params['office_zip'] ?? '';

			// formation location
			$formation_state = parent::$params['formation_state'] ?? '';
			$formation_date  = parent::$params['formation_date'] ?? '';

			// tax_number
			$tax_number      = parent::$params['tax_number'] ?? '';

			// fetch/decrypt report pii for new data
			$report_pii = $db->do_select("
				SELECT pii_data
				FROM  reports
				WHERE report_guid  = '$report_guid'
				AND   company_guid = '$guid'
			")[0]['pii_data'] ?? '';
			$report_pii = $helper->decrypt_pii($report_pii);

			// check invalid report guid
			if (
				!$report_pii ||
				empty($report_pii)
			) {
				_exit(
					'error',
					'Invalid report specified',
					400,
					'Invalid report specified'
				);
			}

			// intro
			if ($intro) {
				$db->do_query("
					UPDATE reports
					SET 
					      status       = 'resume',
					      updated_at   = '$now'
					WHERE company_guid = '$guid'
					AND   report_guid  = '$report_guid'
				");
			}

			// company name / report type
			if ($company_name) {
				$helper->sanitize_input(
					$company_name,
					true,
					2,
					Regex::$company_name['char_limit'],
					Regex::$company_name['pattern'],
					'Company Name'
				);

				// encode new data to report pii
				$report_pii['company_name'] = $company_name;
			}

			if (
				$report_type == 'initial' ||
				$report_type == 'updated'
			) {
				$db->do_query("
					UPDATE reports
					SET 
					      report_type  = '$report_type',
					      updated_at   = '$now'
					WHERE company_guid = '$guid'
					AND   report_guid  = '$report_guid'
				");
			}

			// dbas
			if ($dbas !== null) {
				$dbas = (array)$dbas;
				$report_pii['dbas'] = array();

				foreach ($dbas as $dba) {
					$helper->sanitize_input(
						$dba,
						false,
						2,
						Regex::$company_name['char_limit'],
						Regex::$company_name['pattern'],
						'DBA'
					);
				}

				// encode new data to report pii
				foreach ($dbas as $index => $dba) {
					$report_pii['dbas'][
						hash(
							'sha256', 
							'dba'.$guid.$report_guid.$index
						)
					] = (string)$dba;
				}
			}

			// office address
			if ($office_address1) {
				$helper->sanitize_input(
					$office_address1,
					true,
					2,
					Regex::$address['char_limit'],
					Regex::$address['pattern'],
					'Address line 1'
				);

				if (!$office_address2) {
					$office_address2 = null;
				}

				$helper->sanitize_input(
					$office_address2,
					false,
					2,
					Regex::$address['char_limit'],
					Regex::$address['pattern'],
					'Address line 2'
				);

				$helper->sanitize_input(
					$office_city,
					true,
					2,
					Regex::$city['char_limit'],
					Regex::$city['pattern'],
					'City'
				);

				$helper->sanitize_input(
					$office_state,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'State'
				);

				$helper->sanitize_input(
					$office_zip,
					true,
					2,
					Regex::$postal_code['char_limit'],
					Regex::$postal_code['pattern'],
					'Zip code'
				);

				// encode new data to report pii
				$report_pii['office']
				['created_at']        = $now;
				$report_pii['office']
				['updated_at']        = $now;
				$report_pii['office']
				['country']           = 'United States';
				$report_pii['office']
				['address1']          = $office_address1;
				$report_pii['office']
				['address2']          = $office_address2;
				$report_pii['office']
				['city']              = $office_city;
				$report_pii['office']
				['state_or_province'] = $office_state;
				$report_pii['office']
				['postal_code']       = $office_zip;
			}

			// formation location
			if ($formation_state) {
				$helper->sanitize_input(
					$formation_state,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Formation state'
				);

				$helper->sanitize_input(
					$formation_date,
					true,
					Regex::$date['char_limit'],
					Regex::$date['char_limit'],
					Regex::$date['pattern'],
					'Formation date'
				);

				// encode new data to report pii
				$report_pii['formation_location']
				['created_at']        = $now;
				$report_pii['formation_location']
				['updated_at']        = $now;
				$report_pii['formation_location']
				['formed_at']         = $formation_date;
				$report_pii['formation_location']
				['state_or_province'] = $formation_state;
			}

			// tax number
			if ($tax_number) {
				$helper->sanitize_input(
					$tax_number,
					true,
					5,
					Regex::$registration_number['char_limit'],
					Regex::$registration_number['pattern'],
					'Tax number (EIN)'
				);

				// encode new data to report pii
				$report_pii['tax_number'] = $tax_number;
			}

			//// beneficial_owners
			// if ($beneficial_owners) {
			// }

			// finish update report pii
			$pii_data = $helper->encrypt_pii($report_pii);
			$db->do_query("
				UPDATE reports
				SET 
				      pii_data     = '$pii_data',
				      updated_at   = '$now'
				WHERE company_guid = '$guid'
				AND   report_guid  = '$report_guid'
			");
		}

		// firm
		else {
			$db->do_query("
				//
			");
		}

		_exit(
			'success',
			'Report updated'
		);
	}
}
new UserUpdateReport();
