<?php
include_once('../../core.php');
/**
 *
 * POST /user/set-name
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $name
 * @param string $phone
 *
 */
class UserSetName extends Endpoints {
	function __construct(
		$name  = '',
		$phone = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth  = authenticate_session();
		$guid  = $auth['guid'] ?? '';
		$name  = parent::$params['name'] ?? '';
		$phone = parent::$params['phone'] ?? '';

		$already_set = $helper->get_firm($guid);
		$already_set = $already_set['pii_data']['name'] ?? false;

		if ($already_set) {
			_exit(
				'error',
				'Name/phone already set',
				400,
				'Name/phone already set'
			);
		}

		// sanitize
		$helper->sanitize_input(
			$name,
			true,
			2,
			Regex::$human_name['char_limit'],
			Regex::$human_name['pattern'],
			'Name'
		);

		$helper->sanitize_input(
			$phone,
			true,
			7,
			Regex::$phone['char_limit'],
			Regex::$phone['pattern'],
			'Phone number'
		);

		$pii_data = Structs::firm_info;
		$pii_data['name']  = $name;
		$pii_data['phone'] = $phone;
		$enc_pii = $helper->encrypt_pii($pii_data);

		$db->do_query("
			UPDATE firms
			SET   pii_data     = '$enc_pii'
			WHERE primary_user = '$guid'
		");

		_exit(
			'success',
			'Name/phone set successfully'
		);
	}
}
new UserSetName();