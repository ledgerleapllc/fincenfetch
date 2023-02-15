<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-reports
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $firm_guid Guid of firm 'F-...'
 *
 */
class AdminGetReports extends Endpoints {
	function __construct(
		$firm_guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session(2);
		$firm_guid  = parent::$params['firm_guid'] ?? '';
		$reports    = $db->do_select("
			SELECT 
			report_guid,
			company_guid,
			firm_guid,
			report_type,
			status,
			filing_year,
			created_at,
			reviewed
			FROM reports
			WHERE firm_guid = '$firm_guid'
		") ?? array();

		foreach ($reports as &$report) {
			$company_guid = $report['company_guid'] ?? '';
			$report['company_name'] = $helper->get_company($company_guid)['pii_data']['name'] ?? '';
		}

		dlog('reports');
		dlog($reports);

		_exit(
			'success',
			$reports
		);
	}
}
new AdminGetReports();
