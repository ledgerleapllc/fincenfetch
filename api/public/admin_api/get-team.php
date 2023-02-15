<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-team
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $firm_guid Guid of firm 'F-...'
 *
 */
class AdminGetTeam extends Endpoints {
	function __construct(
		$firm_guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth      = authenticate_session(2);
		$firm_guid = parent::$params['firm_guid'] ?? '';
		$team      = $db->do_select("
			SELECT user_guid
			FROM  user_firm_relations
			WHERE firm_guid = '$firm_guid'
		");

		$primary_guid = $db->do_select("
			SELECT primary_user
			FROM  firms
			WHERE firm_guid = '$firm_guid'
		")[0]['primary_user'] ?? '';

		foreach ($team as &$member) {
			$member_guid = $member['user_guid'] ?? '';
			$member      = $helper->get_user($member_guid);

			if (
				$member_guid == $primary_guid &&
				$member_guid != ''
			) {
				$member['primary'] = true;
			} else {
				$member['primary'] = false;
			}
		}

		dlog('team');
		dlog($team);

		_exit(
			'success',
			$team
		);
	}
}
new AdminGetTeam();
