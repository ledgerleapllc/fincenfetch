<?php
include_once('../../core.php');
/**
 *
 * GET /user/get-report
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $report_guid
 *
 */
class UserGetReport extends Endpoints {
	function __construct(
		$report_guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session();
		$role_check = authenticate_role(
			$auth, 
			array('company', 'firm')
		);

		$guid        = $auth['guid'] ?? '';
		$role        = $auth['role'] ?? '';
		$report_guid = parent::$params['report_guid'] ?? '';

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
			$report = $db->do_select("
				SELECT *
				FROM  reports 
				WHERE company_guid = '$guid'
				AND   report_guid  = '$report_guid'
			")[0] ?? array();
		}

		// firm
		else {
			$report = $db->do_select("
				SELECT *
				FROM  reports 
				WHERE firm_guid   = '$guid'
				AND   report_guid = '$report_guid'
			")[0] ?? array();
		}

		if (!$report || empty($report)) {
			_exit(
				'error',
				'Report does not exist',
				400,
				'Report does not exist'
			);
		}

		$pii_data = $helper->decrypt_pii($report['pii_data'] ?? '');
		$report['pii_data'] = $pii_data ?? array();
		$report['company']  = $helper->get_user_safe($report['company_guid'] ?? '');

		dlog('report');
		dlog($report);

		_exit(
			'success',
			$report
		);
	}
}
new UserGetReport();
