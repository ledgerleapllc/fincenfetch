<?php
/**
 *
 * GET /user/get-reports
 *
 * HEADER Authorization: Bearer
 *
 * @api
 *
 */
class UserGetReports extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session();
		$role_check = authenticate_role(
			$auth, 
			array('company', 'firm')
		);
		$guid = $auth['guid'] ?? '';
		$role = $auth['role'] ?? '';

		// company
		if ($role == 'company') {
			$reports = $db->do_select("
				SELECT 
				a.*,
				b.email       AS firm_email
				FROM  reports AS a
				JOIN  users   AS b
				ON    a.firm_guid  = b.guid
				WHERE company_guid = '$guid'
			") ?? array();
		}

		// firm
		else {
			$reports   = array();
			$companies = $helper->get_firm_companies($guid);

			foreach ($companies as $company) {
				$company_guid = $company['guid'] ?? '';

				$report = $db->do_select("
					SELECT 
					a.*, 
					b.email       AS firm_email
					FROM  reports AS a
					JOIN  users   AS b
					ON    a.firm_guid  = b.guid
					WHERE company_guid = '$company_guid'
				")[0] ?? null;

				if ($report) {
					$reports[] = $report;
				}
			}
		}

		foreach ($reports as &$report) {
			$pii_data = $helper->decrypt_pii($report['pii_data'] ?? '');
			$report['pii_data'] = $pii_data ?? array();
			$report['company']  = $helper->get_user_safe($report['company_guid'] ?? '');
		}

		dlog('reports');
		dlog($reports);

		_exit(
			'success',
			$reports
		);
	}
}
new UserGetReports();
