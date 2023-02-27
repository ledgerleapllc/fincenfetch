<?php
/**
 *
 * GET /admin/get-activity-log
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $firm_guid Guid of firm 'F-...'
 *
 */
class AdminGetActivityLog extends Endpoints {
	function __construct(
		$firm_guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$firm_guid  = parent::$params['firm_guid'] ?? '';
		$actions    = $db->do_select("
			SELECT *
			FROM  action_log
			WHERE guid = '$firm_guid'
		") ?? array();

		dlog('activity log');
		dlog($actions);

		_exit(
			'success',
			$actions
		);
	}
}
new AdminGetActivityLog();
