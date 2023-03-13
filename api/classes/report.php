<?php
/**
 *
 * Handles requests to create/modify all parts of a report
 *
 */
class Report {
	function __construct() {}
	function __destruct() {}

	private static function done(
		bool   $success = true,
		string $message = 'Report saved'
	) {
		return array(
			"success" => $success,
			"message" => $message
		);
	}

	private static function fetchReport(
		$report_guid,
		$company_guid
	) {
		global $db, $helper;

		$report_pii = $db->do_select("
			SELECT pii_data
			FROM  reports
			WHERE report_guid  = '$report_guid'
			AND   company_guid = '$company_guid'
		")[0]['pii_data'] ?? '';

		return $helper->decrypt_pii($report_pii);
	}

	public static function createReport() {
		//
	}

	public static function updateIntro(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// parse params
		$intro = (bool)($params['intro'] ?? '');

		if (!$intro) {
			return self::done(
				false,
				'Report not yet saved'
			);
		}

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// do update
		$db->do_query("
			UPDATE reports
			SET 
			    status         = 'resume',
			    report_started = '$now'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateCompanyName(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// parse/sanitize params
		$company_name       = $params['company_name'] ?? '';
		$report_type        = $params['report_type'] ?? '';
		$foreign_investment = (int)($params['foreign_investment'] ?? 0);
		$exempt             = (int)($params['exempt'] ?? 0);

		$helper->sanitize_input(
			$company_name,
			true,
			2,
			Regex::$company_name['char_limit'],
			Regex::$company_name['pattern'],
			'Company Name'
		);

		if (
			$report_type != 'initial' &&
			$report_type != 'updated' &&
			$report_type != 'correction'
		) {
			$report_type = 'initial';
		}

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// do update
		$report_pii['company_name']       = $company_name;
		$report_pii['foreign_investment'] = (bool)$foreign_investment;
		$report_pii['exempt']             = (bool)$exempt;

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      report_type  = '$report_type',
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateDbas(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// parse/sanitize params
		$dbas = (array)($params['dbas'] ?? array());

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

		if (empty($dbas)) {
			$has_dbas = false;
		} else {
			$has_dbas = true;
		}

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// do update
		$report_pii['dbas']     = array();
		$report_pii['has_dbas'] = $has_dbas;

		foreach ($dbas as $index => $dba) {
			$report_pii['dbas'][
				hash(
					'sha256', 
					'dba'.$company_guid.$report_guid.$index
				)
			] = (string)$dba;
		}

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateTaxNumber(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// parse/sanitize params
		$tax_number      = $params['tax_number'] ?? '';
		$tax_number_type = $params['tax_number_type'] ?? '';
		$foreign_tax_number_country = $params
		['foreign_tax_number_country'] ?? '';

		$helper->sanitize_input(
			$tax_number,
			true,
			5,
			Regex::$registration_number['char_limit'],
			Regex::$registration_number['pattern'],
			'Tax number (EIN)'
		);

		if (
			$tax_number_type != 'ein' &&
			$tax_number_type != 'ssn' &&
			$tax_number_type != 'foreign'
		) {
			$tax_number_type = 'ein';
		}

		if (
			$tax_number_type == 'foreign' &&
			!$helper->ISO3166_country($foreign_tax_number_country)
		) {
			return self::done(
				false,
				'Invalid tax number country specified'
			);
		}

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// do update
		$report_pii['tax_number']      = $tax_number;
		$report_pii['tax_number_type'] = $tax_number_type;

		if ($tax_number_type == 'foreign') {
			$report_pii['foreign_tax_number_country'] = $foreign_tax_number_country;
		}

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateFormationLocation(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// parse/sanitize params
		$state_or_tribe           = $params['state_or_tribe'] ?? 'state';
		$state_of_formation       = $params['state_of_formation'] ?? '';
		$tribe_of_formation       = $params['tribe_of_formation'] ?? '';
		$state_of_registration    = $params['state_of_registration'] ?? '';
		$tribe_of_registration    = $params['tribe_of_registration'] ?? '';
		$formation_date           = $params['formation_date'] ?? '';
		$company_origination_type = $params['company_origination_type'] ?? '';

		if (
			$state_or_tribe != 'state' &&
			$state_or_tribe != 'tribe'
		) {
			return self::done(
				false,
				'Must specify company origination state or tribe'
			);
		}

		if (
			$company_origination_type != 'domestic' &&
			$company_origination_type != 'foreign'
		) {
			return self::done(
				false,
				'Invalid company origination type. Must one of domestic/foreign'
			);
		}

		if ($company_origination_type == 'domestic') {
			if ($state_or_tribe == 'state') {
				$helper->sanitize_input(
					$state_of_formation,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Formation state'
				);
			}

			else {
				$helper->sanitize_input(
					$tribe_of_formation,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Formation tribe'
				);
			}
		}

		else {
			if ($state_or_tribe == 'state') {
				$helper->sanitize_input(
					$state_of_registration,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Registration state'
				);
			}

			else {
				$helper->sanitize_input(
					$tribe_of_registration,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Registration tribe'
				);
			}
		}

		$helper->sanitize_input(
			$formation_date,
			true,
			Regex::$date['char_limit'],
			Regex::$date['char_limit'],
			Regex::$date['pattern'],
			'Formation date'
		);

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// do update
		$report_pii['formation_date']           = $formation_date;
		$report_pii['state_of_formation']       = $state_of_formation;
		$report_pii['tribe_of_formation']       = $tribe_of_formation;
		$report_pii['state_of_registration']    = $state_of_registration;
		$report_pii['tribe_of_registration']    = $tribe_of_registration;
		$report_pii['company_origination_type'] = $company_origination_type;

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateAddress(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// parse/sanitize params
		$address1 = $params['office_address1'] ?? '';
		$address2 = $params['office_address2'] ?? null;
		$city     = $params['office_city'] ?? '';
		$state    = $params['office_state'] ?? '';
		$zip      = $params['office_zip'] ?? '';

		$helper->sanitize_input(
			$address1,
			true,
			2,
			Regex::$address['char_limit'],
			Regex::$address['pattern'],
			'Address line 1'
		);

		if (!$address2) {
			$address2 = null;
		}

		$helper->sanitize_input(
			$address2,
			false,
			2,
			Regex::$address['char_limit'],
			Regex::$address['pattern'],
			'Address line 2'
		);

		$helper->sanitize_input(
			$city,
			true,
			2,
			Regex::$city['char_limit'],
			Regex::$city['pattern'],
			'City'
		);

		$helper->sanitize_input(
			$state,
			true,
			2,
			Regex::$state_or_province['char_limit'],
			Regex::$state_or_province['pattern'],
			'State'
		);

		$helper->sanitize_input(
			$zip,
			true,
			2,
			Regex::$postal_code['char_limit'],
			Regex::$postal_code['pattern'],
			'Zip code'
		);

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// do update
		$report_pii['us_office_address_1'] = $address1;
		$report_pii['us_office_address_2'] = $address2;
		$report_pii['us_office_city']      = $city;
		$report_pii['us_office_state']     = $state;
		$report_pii['us_office_zip']       = $zip;

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateApplicants(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// parse/sanitize params
		$applicants = (array)($params['applicants'] ?? array());
		$company_before_2024 = (bool)($params['company_before_2024'] ?? false);
		$applicant_needed    = (bool)($params['applicant_needed'] ?? false);
		$report_pii['applicants'] = array();

		if (!$company_before_2024) {
			foreach ($applicants as $index => $applicant) {
				$suffix       = $applicant['suffix'] ?? '';
				$first_name   = $applicant['first_name'] ?? '';
				$middle_name  = $applicant['middle_name'] ?? null;
				$last_name    = $applicant['last_name'] ?? '';
				$address_type = $applicant['address_type'] ?? '';
				$address1     = $applicant['us_address_1'] ?? '';
				$address2     = $applicant['us_address_2'] ?? null;
				$city         = $applicant['us_city'] ?? '';
				$state        = $applicant['us_state'] ?? '';
				$zip          = $applicant['us_zip'] ?? '';
				$country      = $applicant['country'] ?? '';
				$dob          = $applicant['date_of_birth'] ?? '';

				$has_fincen_id = (bool)($applicant['has_fincen_id'] ?? false);
				$fincen_id     = $applicant['fincen_id'] ?? '';

				// address_type check
				if (
					$address_type != 'personal' &&
					$address_type != 'business'
				) {
					$address_type = 'personal';
				}

				$hash = hash(
					'sha256', 
					'applicant'.$company_guid.$report_guid.$index
				);

				$report_pii['applicants'][$hash] = Structs::applicant;
				$report_pii['applicants'][$hash]['created_at']    = $now;
				$report_pii['applicants'][$hash]['updated_at']    = $now;
				$report_pii['applicants'][$hash]["has_fincen_id"] = $has_fincen_id;

				if ($has_fincen_id) {
					$helper->sanitize_input(
						$fincen_id,
						true,
						2,
						Regex::$registration_number['char_limit'],
						Regex::$registration_number['pattern'],
						'FinCEN ID'
					);

					$report_pii['applicants'][$hash]["fincen_id"] = $fincen_id;
				}

				else {
					$helper->sanitize_input(
						$first_name,
						true,
						2,
						Regex::$human_name['char_limit'],
						Regex::$human_name['pattern'],
						'Applicant first name'
					);

					if (!$middle_name) {
						$middle_name = null;
					}

					$helper->sanitize_input(
						$middle_name,
						false,
						1,
						Regex::$human_name['char_limit'],
						Regex::$human_name['pattern'],
						'Applicant middle name (initial)'
					);

					$helper->sanitize_input(
						$last_name,
						true,
						2,
						Regex::$human_name['char_limit'],
						Regex::$human_name['pattern'],
						'Applicant last name'
					);

					$helper->sanitize_input(
						$address1,
						true,
						2,
						Regex::$address['char_limit'],
						Regex::$address['pattern'],
						'Applicant address line 1'
					);

					if (!$address2) {
						$address2 = null;
					}

					$helper->sanitize_input(
						$address2,
						false,
						2,
						Regex::$address['char_limit'],
						Regex::$address['pattern'],
						'Applicant address line 2'
					);

					$helper->sanitize_input(
						$city,
						true,
						2,
						Regex::$city['char_limit'],
						Regex::$city['pattern'],
						'Applicant city'
					);

					$helper->sanitize_input(
						$state,
						true,
						2,
						Regex::$state_or_province['char_limit'],
						Regex::$state_or_province['pattern'],
						'Applicant state'
					);

					$helper->sanitize_input(
						$zip,
						true,
						2,
						Regex::$postal_code['char_limit'],
						Regex::$postal_code['pattern'],
						'Applicant zip code'
					);

					$helper->sanitize_input(
						$dob,
						true,
						Regex::$date['char_limit'],
						Regex::$date['char_limit'],
						Regex::$date['pattern'],
						'Applicant date of birth'
					);

					if (!$helper->ISO3166_country($country)) {
						return self::done(
							false,
							'Invalid applicant country specified'
						);
					}

					$report_pii['applicants'][$hash]["address_type"]  = $address_type;
					$report_pii['applicants'][$hash]["us_address_1"]  = $address1;
					$report_pii['applicants'][$hash]["us_address_2"]  = $address2;
					$report_pii['applicants'][$hash]["us_city"]       = $city;
					$report_pii['applicants'][$hash]["us_state"]      = $state;
					$report_pii['applicants'][$hash]["us_zip"]        = $zip;
					$report_pii['applicants'][$hash]["country"]       = $country;
					$report_pii['applicants'][$hash]["first_name"]    = $first_name;
					$report_pii['applicants'][$hash]["middle_name"]   = $middle_name;
					$report_pii['applicants'][$hash]["last_name"]     = $last_name;
					$report_pii['applicants'][$hash]["date_of_birth"] = $dob;
				}
			}
		}

		// do update
		$report_pii['company_before_2024'] = $company_before_2024;
		$report_pii['applicant_needed']    = $applicant_needed;

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateBeneficialOwners(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// parse/sanitize params
		$beneficial_owners = (array)($params['beneficial_owners'] ?? array());

		if (empty($beneficial_owners)) {
			_exit(
				'error',
				'Beneficial owner cannot be blank',
				400,
				'Beneficial owner cannot be blank'
			);
		}

		foreach ($beneficial_owners as $index => $owner) {
			$suffix      = $owner['suffix'] ?? '';
			$first_name  = $owner['first_name'] ?? '';
			$middle_name = $owner['middle_name'] ?? null;
			$last_name   = $owner['last_name'] ?? '';
			$address1    = $owner['us_address_1'] ?? '';
			$address2    = $owner['us_address_2'] ?? null;
			$city        = $owner['us_city'] ?? '';
			$state       = $owner['us_state'] ?? '';
			$zip         = $owner['us_zip'] ?? '';
			$country     = $owner['country'] ?? '';
			$dob         = $owner['date_of_birth'] ?? '';

			$has_fincen_id      = (bool)($owner['has_fincen_id'] ?? false);
			$fincen_id          = $owner['fincen_id'] ?? '';
			$is_exempt_entity   = (bool)($owner['is_exempt_entity'] ?? false);
			$exempt_entity_name = $owner['exempt_entity_name'] ?? '';

			$hash = hash(
				'sha256', 
				'beneficial_owner'.$company_guid.$report_guid.$index
			);

			$report_pii['beneficial_owners'][$hash] = Structs::beneficial_owner;
			$report_pii['beneficial_owners'][$hash]['created_at']       = $now;
			$report_pii['beneficial_owners'][$hash]['updated_at']       = $now;
			$report_pii['beneficial_owners'][$hash]["has_fincen_id"]    = $has_fincen_id;
			$report_pii['beneficial_owners'][$hash]["is_exempt_entity"] = $is_exempt_entity;

			if ($has_fincen_id) {
				$helper->sanitize_input(
					$fincen_id,
					true,
					2,
					Regex::$registration_number['char_limit'],
					Regex::$registration_number['pattern'],
					'FinCEN ID'
				);

				$report_pii['beneficial_owners'][$hash]["fincen_id"] = $fincen_id;
			}

			else {
				if ($is_exempt_entity) {
					$helper->sanitize_input(
						$exempt_entity_name,
						true,
						2,
						Regex::$company_name['char_limit'],
						Regex::$company_name['pattern'],
						'Exempt Company name'
					);

					$report_pii['beneficial_owners'][$hash]["exempt_entity_name"] = $exempt_entity_name;
				}

				else {
					$helper->sanitize_input(
						$first_name,
						true,
						2,
						Regex::$human_name['char_limit'],
						Regex::$human_name['pattern'],
						'First name'
					);

					if (!$middle_name) {
						$middle_name = null;
					}

					$helper->sanitize_input(
						$middle_name,
						false,
						1,
						Regex::$human_name['char_limit'],
						Regex::$human_name['pattern'],
						'Middle name (initial)'
					);

					$helper->sanitize_input(
						$last_name,
						true,
						2,
						Regex::$human_name['char_limit'],
						Regex::$human_name['pattern'],
						'Last name'
					);

					$helper->sanitize_input(
						$dob,
						true,
						Regex::$date['char_limit'],
						Regex::$date['char_limit'],
						Regex::$date['pattern'],
						'Date of birth'
					);

					$report_pii['beneficial_owners'][$hash]["first_name"]    = $first_name;
					$report_pii['beneficial_owners'][$hash]["middle_name"]   = $middle_name;
					$report_pii['beneficial_owners'][$hash]["last_name"]     = $last_name;
					$report_pii['beneficial_owners'][$hash]["date_of_birth"] = $dob;
				}

				$helper->sanitize_input(
					$address1,
					true,
					2,
					Regex::$address['char_limit'],
					Regex::$address['pattern'],
					'Address line 1'
				);

				if (!$address2) {
					$address2 = null;
				}

				$helper->sanitize_input(
					$address2,
					false,
					2,
					Regex::$address['char_limit'],
					Regex::$address['pattern'],
					'Address line 2'
				);

				$helper->sanitize_input(
					$city,
					true,
					2,
					Regex::$city['char_limit'],
					Regex::$city['pattern'],
					'City'
				);

				$helper->sanitize_input(
					$state,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'State or Province'
				);

				$helper->sanitize_input(
					$zip,
					true,
					2,
					Regex::$postal_code['char_limit'],
					Regex::$postal_code['pattern'],
					'Zip code'
				);

				if (!$helper->ISO3166_country($country)) {
					return self::done(
						false,
						'Invalid beneficial owner country specified'
					);
				}

				$report_pii['beneficial_owners'][$hash]["us_address_1"] = $address1;
				$report_pii['beneficial_owners'][$hash]["us_address_2"] = $address2;
				$report_pii['beneficial_owners'][$hash]["us_city"]      = $city;
				$report_pii['beneficial_owners'][$hash]["us_state"]     = $state;
				$report_pii['beneficial_owners'][$hash]["us_zip"]       = $zip;
				$report_pii['beneficial_owners'][$hash]["country"]      = $country;
			}
		}

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateIdentificationFiles(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// parse/sanitize params
		$file_url  = $params['file_url'] ?? '';
		$file_name = $params['file_name'] ?? '';
		$owner     = (int)($params['owner'] ?? 0);
		$applicant = (int)($params['applicant'] ?? 0);
		$kind      = $params['kind'] ?? '';

		if (
			$kind != 'owner' &&
			$kind != 'applicant'
		) {
			$kind = 'owner';
		}

		if (!$file_url) {
			return self::done(
				false,
				'Invalid document file specified'
			);
		}

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// do update
		$i = 0;

		if ($kind == 'owner') {
			foreach ($report_pii['beneficial_owners'] as &$item) {
				if ($i == $owner) {
					$item['identifying_document']
					['file_url'] = $file_url;
					$item['identifying_document']
					['file_name'] = $file_name;
					break;
				}
				$i++;
			}
		}

		else {
			foreach ($report_pii['applicants'] as &$item) {
				if ($i == $applicant) {
					$item['identifying_document']
					['file_url'] = $file_url;
					$item['identifying_document']
					['file_name'] = $file_name;
					break;
				}
				$i++;
			}
		}

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateIdentificationDocs(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		$owners     = (array)($params['owners'] ?? array());
		$applicants = (array)($params['applicants'] ?? array());

		foreach ($owners as $index => $owner) {
			$id = (array)($owner['identifying_document'] ?? array());
			$id_type = $id['type'] ?? '';

			// sanitize
			$document_number         = $id['document_number'] ?? '';
			$drivers_license_state   = $id['drivers_license_state'] ?? '';
			$id_card_state           = $id['id_card_state'] ?? '';
			$id_card_tribe           = $id['id_card_tribe'] ?? '';
			$id_card_state_or_tribe  = $id['id_card_state_or_tribe'] ?? '';
			$foreign_passport_issuer = $id['foreign_passport_issuer'] ?? '';

			// check if exempt
			$is_exempt_entity        = (bool)($owner['is_exempt_entity'] ?? false);
			$has_fincen_id           = (bool)($owner['has_fincen_id'] ?? false);

			if (
				$is_exempt_entity ||
				$has_fincen_id
			) {
				continue;
			}

			// define blank identifying document
			$hash = hash(
				'sha256', 
				'beneficial_owner'.$company_guid.$report_guid.$index
			);

			$helper->sanitize_input(
				$document_number,
				true,
				2,
				Regex::$registration_number['char_limit'],
				Regex::$registration_number['pattern'],
				'Beneficial owner document number'
			);

			// $report_pii['beneficial_owners'][$hash]
			// ['identifying_document'] = Structs::identifying_document;

			$report_pii['beneficial_owners'][$hash]
			['identifying_document']
			['document_number'] = $document_number;

			if ($id_type == 'drivers_license') {
				$helper->sanitize_input(
					$drivers_license_state,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Beneficial owner document issue state'
				);

				$report_pii['beneficial_owners'][$hash]
				['identifying_document']['type'] = $id_type;

				$report_pii['beneficial_owners'][$hash]
				['identifying_document']
				['drivers_license_state'] = $drivers_license_state;
			}

			elseif ($id_type == 'state_or_tribe_id') {
				if (!$id_card_state) {
					$id_card_state = null;
				}

				if (!$id_card_tribe) {
					$id_card_tribe = null;
				}

				$helper->sanitize_input(
					$id_card_state,
					false,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Beneficial owner document issue state'
				);

				$helper->sanitize_input(
					$id_card_tribe,
					false,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Beneficial owner document issue tribe'
				);

				if (
					!$id_card_state &&
					!$id_card_tribe
				) {
					return self::done(
						false,
						'Must provide one of either: Issue State, Issue Tribe'
					);
				}

				$report_pii['beneficial_owners'][$hash]
				['identifying_document']['type'] = $id_type;

				$report_pii['beneficial_owners'][$hash]
				['identifying_document']
				['id_card_state'] = $id_card_state;

				$report_pii['beneficial_owners'][$hash]
				['identifying_document']
				['id_card_tribe'] = $id_card_tribe;
			}

			elseif ($id_type == 'us_passport') {
				$report_pii['beneficial_owners'][$hash]
				['identifying_document']['type'] = $id_type;

				// nothing else needed
			}

			elseif ($id_type == 'foreign_passport') {
				if (!$helper->ISO3166_country($foreign_passport_issuer)) {
					return self::done(
						false,
						'Invalid beneficial owner foreign passport country specified'
					);
				}

				$report_pii['beneficial_owners'][$hash]
				['identifying_document']['type'] = $id_type;

				$report_pii['beneficial_owners'][$hash]
				['identifying_document']
				['foreign_passport_issuer'] = $foreign_passport_issuer;
			}

			else {
				return self::done(
					false,
					'Invalid document type'
				);
			}
		}

		foreach ($applicants as $applicant) {
			$id = (array)($applicant['identifying_document'] ?? array());
			$id_type = $id['type'] ?? '';

			// sanitize
			$document_number         = $id['document_number'] ?? '';
			$drivers_license_state   = $id['drivers_license_state'] ?? '';
			$id_card_state           = $id['id_card_state'] ?? '';
			$id_card_tribe           = $id['id_card_tribe'] ?? '';
			$id_card_state_or_tribe  = $id['id_card_state_or_tribe'] ?? '';
			$foreign_passport_issuer = $id['foreign_passport_issuer'] ?? '';

			// check if exempt
			$has_fincen_id           = (bool)($owner['has_fincen_id'] ?? false);

			if ($has_fincen_id) {
				continue;
			}

			// define blank identifying document
			$hash = hash(
				'sha256', 
				'applicant'.$company_guid.$report_guid.$index
			);

			$helper->sanitize_input(
				$document_number,
				true,
				2,
				Regex::$registration_number['char_limit'],
				Regex::$registration_number['pattern'],
				'Beneficial applicant document number'
			);

			// $report_pii['applicants'][$hash]
			// ['identifying_document'] = Structs::identifying_document;

			$report_pii['applicants'][$hash]
			['identifying_document']
			['document_number'] = $document_number;

			if ($id_type == 'drivers_license') {
				$helper->sanitize_input(
					$drivers_license_state,
					true,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Applicant document issue state'
				);

				$report_pii['applicants'][$hash]
				['identifying_document']['type'] = $id_type;

				$report_pii['applicants'][$hash]
				['identifying_document']
				['drivers_license_state'] = $drivers_license_state;
			}

			elseif ($id_type == 'state_or_tribe_id') {
				if (!$id_card_state) {
					$id_card_state = null;
				}

				if (!$id_card_tribe) {
					$id_card_tribe = null;
				}

				$helper->sanitize_input(
					$id_card_state,
					false,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Applicant document issue state'
				);

				$helper->sanitize_input(
					$id_card_tribe,
					false,
					2,
					Regex::$state_or_province['char_limit'],
					Regex::$state_or_province['pattern'],
					'Applicant document issue tribe'
				);

				if (
					!$id_card_state &&
					!$id_card_tribe
				) {
					return self::done(
						false,
						'Must provide one of either: Issue State, Issue Tribe'
					);
				}

				$report_pii['applicants'][$hash]
				['identifying_document']['type'] = $id_type;

				$report_pii['applicants'][$hash]
				['identifying_document']
				['id_card_state'] = $id_card_state;

				$report_pii['applicants'][$hash]
				['identifying_document']
				['id_card_tribe'] = $id_card_tribe;
			}

			elseif ($id_type == 'us_passport') {
				$report_pii['applicants'][$hash]
				['identifying_document']['type'] = $id_type;

				// nothing else needed
			}

			elseif ($id_type == 'foreign_passport') {
				if (!$helper->ISO3166_country($foreign_passport_issuer)) {
					return self::done(
						false,
						'Invalid applicant foreign passport country specified'
					);
				}

				$report_pii['applicants'][$hash]
				['identifying_document']['type'] = $id_type;

				$report_pii['applicants'][$hash]
				['identifying_document']
				['foreign_passport_issuer'] = $foreign_passport_issuer;
			}

			else {
				return self::done(
					false,
					'Invalid document type'
				);
			}
		}

		$pii_data = $helper->encrypt_pii($report_pii);

		$db->do_query("
			UPDATE reports
			SET 
			      pii_data     = '$pii_data'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}

	public static function updateReview(
		$report_guid,
		$company_guid,
		$params
	) {
		global $db, $helper;

		$now = $helper->get_datetime();

		// fetch report
		$report_pii = self::fetchReport(
			$report_guid,
			$company_guid
		);

		// check invalid report guid
		if (
			!$report_pii ||
			empty($report_pii)
		) {
			return self::done(
				false,
				'Invalid report specified'
			);
		}

		// check prerequisites
		if (
			!$report_pii['company_name'] ||
			$report_pii['has_dbas'] === null ||
			!$report_pii['tax_number_type'] ||
			!$report_pii['tax_number'] ||
			!$report_pii['company_origination_type'] ||
			!$report_pii['formation_date'] ||
			!$report_pii['us_office_address_1'] ||
			!$report_pii['us_office_city'] ||
			!$report_pii['us_office_state'] ||
			!$report_pii['us_office_zip'] ||
			$report_pii['company_before_2024'] === null ||
			$report_pii['applicant_needed'] === null ||
			count($report_pii['beneficial_owners'] ?? array()) == 0
		) {
			return self::done(
				false,
				'Incomplete report cannot be marked as complete'
			);
		}

		if (
			!$report_pii['state_of_formation'] &&
			!$report_pii['tribe_of_formation']
		) {
			return self::done(
				false,
				'Incomplete report cannot be marked as complete'
			);
		}

		foreach ($report_pii['beneficial_owners'] as $bo) {
			$doc    = $bo['identifying_document']['file_url'] ?? '';
			$exempt = (bool)($bo['is_exempt_entity'] ?? false);
			$hf_id  = (bool)($bo['has_fincen_id'] ?? false);
			$f_id   = $bo['fincen_id'] ?? '';

			if (
				!$doc &&
				!$exempt
			) {
				if (
					!$hf_id ||
					!$f_id
				) {
					return self::done(
						false,
						'Incomplete report cannot be marked as complete'
					);
				}
			}
		}

		// do update
		$db->do_query("
			UPDATE reports
			SET 
			   report_returned = '$now'
			WHERE company_guid = '$company_guid'
			AND   report_guid  = '$report_guid'
		");

		return self::done();
	}
}
