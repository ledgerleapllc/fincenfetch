
<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-firms
 *
 * HEADER Authorization: Bearer
 *
 * @api
 *
 */
class AdminGetFirms extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';

		$firms = $db->do_select("
			SELECT 
			firm_guid,
			primary_user,
			status,
			pii_data,
			associated_at
			FROM firms
		") ?? array();

		foreach ($firms as &$firm) {
			$firm_guid    = $firm['firm_guid'] ?? '';
			$enc_pii      = $firm['pii_data'] ?? '';
			$pii          = $helper->decrypt_pii($enc_pii);
			$firm['name'] = $pii['name'] ?? '';

			$total_reports = $db->do_select("
				SELECT count(report_guid) AS rCount
				FROM  reports
				WHERE firm_guid = '$firm_guid'
			");

			$firm['total_reports'] = (int)($total_reports[0]['rCount'] ?? 0);

			$total_companies = $db->do_select("
				SELECT count(company_guid) AS cCount
				FROM  firm_company_relations
				WHERE firm_guid = '$firm_guid'
			");

			$firm['total_companies'] = (int)($total_companies[0]['cCount'] ?? 0);

			$firm['total_paid'] = 0;
		}

		_exit(
			'success',
			$firms
		);
	}
}
new AdminGetFirms();
