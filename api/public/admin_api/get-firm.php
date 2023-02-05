<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-firm
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $guid Guid of firm 'F-...'
 *
 */
class AdminGetFirm extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$firm_guid  = parent::$params['firm_guid'] ?? '';

		$firm              = $helper->get_user($firm_guid);
		$firm['companies'] = $helper->get_user_entities($firm_guid);

		dlog($firm);
		_exit(
			'success',
			$firm
		);
	}
}
new AdminGetFirm();
