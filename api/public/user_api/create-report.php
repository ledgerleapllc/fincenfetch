<?php
include_once('../../core.php');
/**
 *
 * POST /user/create-report
 *
 * HEADER Authorization: Bearer
 *
 * @api
 *
 */
class UserCreateReport extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('POST');

		$auth       = authenticate_session();
		$role_check = authenticate_role($auth, 'firm');
		$firm_guid  = $auth['guid'] ?? '';
		$companies  = $helper->get_firm_companies($firm_guid);

		$company_guid  = parent::$params['company_guid'] ?? '';
		$company_name  = parent::$params['company_name'] ?? '';
		$company_email = parent::$params['company_email'] ?? '';
		$company_phone = parent::$params['company_phone'] ?? '';

		dlog($companies);

		if ($company_guid) {
			$valid_guid = false;

			foreach ($companies as $company) {
				$read_name = $company['pii_data']['name'] ?? '';

				if (
					$read_name == $company_name &&
					$read_name != ''
				) {
					$valid_guid = true;
				}
			}

			if (!$valid_guid) {
				_exit(
					'error',
					'Invalid company specified',
					400,
					'Invalid company specified'
				);
			}

			//// do company update
		}

		//// new company

		_exit(
			'success',
			$companies,
			400
		);
	}
}
new UserCreateReport();
