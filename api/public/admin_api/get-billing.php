<?php
/**
 *
 * GET /admin/get-billing
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $firm_guid Guid of firm 'F-...'
 *
 */
class AdminGetBilling extends Endpoints {
	function __construct(
		$firm_guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$firm_guid  = parent::$params['firm_guid'] ?? '';
		$billing    = $db->do_select("
			SELECT *
			FROM  subscriptions
		") ?? array();

		dlog('billing');
		dlog($billing);

		_exit(
			'success',
			$billing
		);
	}
}
new AdminGetBilling();
