<?php
/**
 * Http throttling class intended to mitigate brute force attacks on th API. 
 * Especially for endpoints that call the auto-mailer, eg. forgot-password.
 *
 * Instantiating the class immediately causes the throttling to take effect.
 * Exits with code 429 if the client fails based on IP address.
 *
 * @param  string  $real_ip
 */
class Throttle {
	/** 
	 * Known endpoints array. Referenced by PHPUnit
	 */
	public const endpoints = array(
		'/user/confirm-registration' => 10,
		'/user/forgot-password' => 5,
		'/user/login' => 5,
		'/user/logout' => 100,
		'/user/me' => 150,
		'/user/register' => 4,
		'/user/resend-code' => 3,
		'/user/reset-password' => 3,
		'/user/update-password' => 5,
		'/user/login-mfa' => 8,
		'/user/send-mfa' => 3,
		'/user/confirm-mfa' => 3,
		'/user/update-mfa' => 3,
		'/user/update-totp' => 3,
		'/user/confirm-totp' => 3,
		'/user/update-email' => 3,
		'/user/confirm-update-email' => 3,
		'/user/update-avatar' => 2,
		'/user/get-iplog' => 10,

		'/admin/login' => 5,
		'/admin/login-mfa' => 8,
		'/admin/forgot-password' => 5,
		'/admin/reset-password' => 3,
		'/admin/me' => 150,
		'/admin/logout' => 100,
		'/admin/new-totp' => 3,
		'/admin/update-totp' => 3,
		'/admin/confirm-totp' => 3,
		'/admin/send-mfa' => 3,
		'/admin/confirm-mfa' => 3,
		'/admin/update-mfa' => 3,
		'/admin/update-email' => 3,
		'/admin/confirm-update-email' => 3,
		'/admin/update-password' => 5,
		'/admin/get-iplog' => 100,
		'/admin/update-setting' => 100,
		'/admin/update-avatar' => 4
	);

	function __construct(string $real_ip = '127.0.0.1') {
		// forget throttling during dev
		if(
			$real_ip == '127.0.0.1' ||
			$real_ip == 'localhost' ||
			$real_ip == '::1' ||
			$real_ip == '0:0:0:0:0:0:0:1' ||
			DEV_MODE
		) {
			return true;
		}

		global $db;

		$this->now = (int)time();
		$this->ip = $real_ip;
		$this->uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

		// no need to go any further for unit tests
		if($real_ip == 'unittest') {
			return true;
		}

		// check hit, log hit
		$query = "
			SELECT hit, last_request
			FROM throttle
			WHERE ip  = '$this->ip'
			AND   uri = '$this->uri'
		";

		$selection = $db->do_select($query);

		if(!$selection) {
			$query = "
				INSERT INTO throttle (
					ip,
					uri
				) VALUES (
					'$this->ip',
					'$this->uri'
				)
			";
			$db->do_query($query);
		}

		$minute = 60;
		$minute_limit = self::endpoints[$this->uri] ?? 30;
		$last_api_request = (int)($selection[0]['last_request'] ?? 0);
		$last_api_diff = $this->now - $last_api_request;
		$minute_throttle = (float)($selection[0]['hit'] ?? 0);
		$new_minute_throttle = $minute_throttle - $last_api_diff;
		$new_minute_throttle = $new_minute_throttle < 0 ? 0 : $new_minute_throttle;
		$new_minute_throttle += $minute / $minute_limit;
		$minute_hits_remaining = floor(($minute - $new_minute_throttle) * $minute_limit / $minute);
		$minute_hits_remaining = $minute_hits_remaining >= 0 ? $minute_hits_remaining : 0;

		if($new_minute_throttle > $minute) {
			_exit(
				"error",
				"Too many requests to this resource",
				429,
				"Too many requests to this resource"
			);
		}

		$query = "
			UPDATE throttle
			SET 
			hit          = $new_minute_throttle, 
			last_request = $this->now
			WHERE ip     = '$this->ip'
			AND   uri    = '$this->uri'
		";
		$db->do_query($query);
	}

	function __destruct() {
		// empty for now
	}

	/**
	 * Used by PHPUnit to verify known endpoints are accounted for in the router.
	 *
	 * @return array<string, int> endpoints
	 */
	public static function get_endpoints() {
		return self::endpoints;
	}
}
