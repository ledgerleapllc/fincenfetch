<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-companies
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $firm_guid Guid of firm 'F-...'
 *
 */
class AdminGetCompanies extends Endpoints {
	function __construct(
		$firm_guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth      = authenticate_session(2);
		$firm_guid = parent::$params['firm_guid'] ?? '';
		$companies = $helper->get_firm_companies($firm_guid);

		dlog('companies');
		dlog($companies);

		_exit(
			'success',
			$companies
		);
	}
}
new AdminGetCompanies();
