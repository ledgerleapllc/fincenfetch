<?php
/**
 *
 * GET /user/get-companies
 *
 * HEADER Authorization: Bearer
 *
 * @api
 *
 */
class UserGetCompanies extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session();
		$role_check = authenticate_role($auth, 'firm');
		$firm_guid  = $auth['guid'] ?? '';
		$companies  = $helper->get_firm_companies($firm_guid);

		foreach ($companies as &$company) {
			//// get real reports data
			// $company['latest_link_sent']   = '2022-12-13 00:00:00';
			// $company['latest_link_access'] = '2022-12-13 00:00:00';
			$company['total_reports'] = count($companies);
		}

		dlog('companies');
		dlog($companies);
		_exit(
			'success',
			$companies
		);
	}
}
new UserGetCompanies();
