<?php
/**
 *
 * GET /admin/get-firm
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $firm_guid Guid of firm 'F-...'
 *
 */
class AdminGetFirm extends Endpoints {
	function __construct(
		$firm_guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$firm_guid  = parent::$params['firm_guid'] ?? '';
		$firm       = $helper->get_firm($firm_guid);
		$primary_guid = $firm['primary_user'] ?? '';

		if (!$firm || empty($firm)) {
			_exit(
				'error',
				'Firm does not exist',
				400,
				'Firm does not exist'
			);
		}

		$primary_user      = $helper->get_user($primary_guid);
		$firm['email']     = $primary_user['email'] ?? '';
		$firm['companies'] = $helper->get_firm_companies($firm_guid);

		dlog($firm);

		_exit(
			'success',
			$firm
		);
	}
}
new AdminGetFirm();
