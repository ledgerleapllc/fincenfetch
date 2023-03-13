<?php
/**
 *
 * GET /user/get-company
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $company_guid
 *
 */
class UserGetCompany extends Endpoints {
	function __construct(
		$company_guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth         = authenticate_session();
		$role_check   = authenticate_role($auth, 'firm');
		$firm_guid    = $auth['guid'] ?? '';
		$company_guid = parent::$params['company_guid'] ?? '';
		$companies    = $helper->get_firm_companies($firm_guid);
		$company      = array();

		foreach ($companies as $c) {
			if ($c['guid'] == $company_guid) {
				$company = $c;
				break;
			}
		}

		dlog('company');
		dlog($company);
		_exit(
			'success',
			$company
		);
	}
}
new UserGetCompany();
