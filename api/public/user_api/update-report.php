<?php
/**
 *
 * PUT /user/update-report
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $report_guid
 * @param string $data_point
 *
 */
class UserUpdateReport extends Endpoints {
	function __construct(
		$report_guid = '',
		$data_point  = ''
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
		$data_point  = parent::$params['data_point'] ?? '';
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

			// define empty return
			$result = array(
				"success" => false,
				"message" => "Report not saved yet"
			);

			// intro
			if ($data_point == 'intro') {
				$result = Report::updateIntro(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// company name / report type
			if ($data_point == 'company_name') {
				$result = Report::updateCompanyName(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// dbas
			if ($data_point == 'dbas') {
				$result = Report::updateDbas(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// tax_number
			if ($data_point == 'tax_number') {
				$result = Report::updateTaxNumber(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// formation_location
			if ($data_point == 'formation_location') {
				$result = Report::updateFormationLocation(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// office address
			if ($data_point == 'address') {
				$result = Report::updateAddress(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// applicants
			if ($data_point == 'applicants') {
				$result = Report::updateApplicants(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// beneficial_owners
			if ($data_point == 'beneficial_owners') {
				$result = Report::updateBeneficialOwners(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// identification files
			if ($data_point == 'identification_files') {
				$result = Report::updateIdentificationFiles(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// identification docs
			if ($data_point == 'identification_docs') {
				$result = Report::updateIdentificationDocs(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			// review
			if ($data_point == 'review') {
				$result = Report::updateReview(
					$report_guid,
					$guid,
					parent::$params
				);
			}

			$success = (bool)($result['success'] ?? false);
			$message = (string)($result['message'] ?? '');

			if (!$success) {
				_exit(
					'error',
					$message,
					400,
					$message
				);
			}

			_exit(
				'success',
				'Report updated'
			);
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
