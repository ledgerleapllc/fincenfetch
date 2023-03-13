<?php
/**
 * Helper class contains vital methods for the functionality of the portal.
 * Made to be static for PHPUnit tests.
 *
 * @static cipher            Crytographic algorithm choosen.
 * @static company_bytes     Ledgerleap company bytes "LL"
 * @static $countries        Valid countries and country codes
 *
 * @method array   self_curl()
 * @method string  kebab_case()
 * @method string  string_from_regex()
 * @method array   get_contact_recipients()
 * @method string  format_hash()
 * @method string  fetch_setting()
 * @method bool    apply_setting()
 * @method array   get_user()
 * @method array   get_firm_companies()
 * @method string  check_authorized_devices()
 * @method null    add_authorized_device()
 * @method null    log_action()
 * @method null    sanitize_input()
 * @method string  generate_guid()
 * @method bool    verify_guid()
 * @method bool    guid_available()
 * @method string  generate_session_token()
 * @method string  generate_hash()
 * @method string  derive_hashed_id()
 * @method string  get_timedelta()
 * @method string  get_datetime()
 * @method string  get_filing_year()
 * @method bool    schedule_email()
 * @method bool    send_mfa()
 * @method string  verify_mfa()   String returned is a success/error reason message.
 * @method bool    create_mfa_allowance()
 * @method bool    consume_mfa_allowance()
 * @method string  b_encode()
 * @method string  b_decode()
 * @method string  aes_encrypt()
 * @method string  aes_decrypt()
 * @method string  encrypt_pii()
 * @method array   decrypt_pii()
 * @method array   get_dir_contents()
 * @method string  get_real_ip()
 * @method bool    in_CIDR_range()
 * @method bool    ISO3166_country()
 *
 */

class Helper {
	private const cipher       = "AES-256-CBC";
	public const company_bytes = "4c4c"; // LL (LedgerLeap)

	function __construct() {
		// do nothing yet
	}

	function __destruct() {
		// do nothing yet
	}

	/**
	 *
	 * Curl to api macro for integration testing
	 *
	 * @param  string $method
	 * @param  string $endpoint
	 * @param  array  $fields
	 * @param  array  $headers
	 * @return array  $response
	 *
	 */
	public static function self_curl(
		string $method,
		string $endpoint,
		array  $fields  = array(),
		array  $headers = array()
	) {
		$method = strtolower($method);
		$ch     = curl_init();

		curl_setopt(
			$ch, 
			CURLOPT_RETURNTRANSFER, 
			1
		);

		if ($method == 'get') {
			$arg_string = '';

			if (!empty($fields)) {
				$arg_string .= '?';
			}

			foreach ($fields as $key => $val) {
				$arg_string .= $key.'='.$val.'&';
			}

			$arg_string = rtrim($arg_string, '&');

			curl_setopt(
				$ch, 
				CURLOPT_URL, 
				PROTOCOL.'://'.CORS_SITE.$endpoint.$arg_string
			);
		} else

		if ($method == 'post') {
			curl_setopt(
				$ch, 
				CURLOPT_URL, 
				PROTOCOL.'://'.CORS_SITE.$endpoint
			);

			curl_setopt(
				$ch, 
				CURLOPT_POST, 
				1
			);

			curl_setopt(
				$ch, 
				CURLOPT_POSTFIELDS, 
				json_encode($fields)
			);
		} else

		if ($method == 'put') {
			curl_setopt(
				$ch, 
				CURLOPT_URL, 
				PROTOCOL.'://'.CORS_SITE.$endpoint
			);

			curl_setopt(
				$ch, 
				CURLOPT_CUSTOMREQUEST, 
				"PUT"
			);

			curl_setopt(
				$ch, 
				CURLOPT_POSTFIELDS, 
				json_encode($fields)
			);
		}

		else {
			return array();
		}

		curl_setopt(
			$ch, 
			CURLOPT_HTTPHEADER, 
			$headers
		);

		$response = curl_exec($ch);
		$response = json_decode($response, true);

		curl_close($ch);

		return $response;
	}

	/**
	 *
	 * Takes a camel case string and converts it to kebab string
	 *
	 * @param  string $string
	 * @return string $string
	 *
	 */
	public static function kebab_case(string $string) {
		$string = lcfirst($string);
		$string = strtolower(
			preg_replace(
				'/(?<!^)[A-Z]/', 
				'-$0', 
				$string
			)
		);

		return $string;
	}

	/**
	 *
	 * Generate a fake data string that matches provided regex
	 *
	 * @param  string $pattern
	 * @param  int    $length
	 * @return string $string
	 *
	 */
	public static function string_from_regex(
		string $pattern = '',
		int    $length
	) {
		$pattern_hex = unpack('H*', $pattern);
		$pattern_hex = $pattern_hex[1] ?? '';
		$string      = trim(shell_exec("python3 ".BASE_DIR."/classes/string_from_regex.py $pattern_hex"));

		if (
			ctype_xdigit($string) &&
			strlen($string) % 2 != 0
		) {
			$string = $string.'0';
		}

		return $string;
	}

	/**
	 *
	 * Fetch all contact_recipients 
	 *
	 * @return array $recipients
	 *
	 */
	public static function get_contact_recipients() {
		global $db;

		$selection = $db->do_select("
			SELECT email
			FROM contact_recipients
		");

		$selection  = $selection ?? array();
		$recipients = array();

		foreach ($selection as $e) {
			$email = $e['email'] ?? '';

			if ($email) {
				$recipients[] = $email;
			}
		}

		return $recipients;
	}

	/**
	 *
	 * Format a long hash by places "..." between start and end chars
	 *
	 * @param  string $hash
	 * @param  int    $length
	 * @return string $formatted_hash
	 *
	 */
	public static function format_hash(
		string $hash,
		int    $length = 10
	) {
		if (strlen($hash) <= $length) {
			return $hash;
		}

		$b    = 1;
		$dots = '...';

		if ($length % 2 == 0) {
			$b    = 0;
			$dots = '..';
		}

		$split = (($length - $b) / 2) - 1;
		$first = substr($hash, 0, $split);
		$last  = substr($hash, strlen($hash) - $split);

		$formatted_hash = $first.$dots.$last;

		return $formatted_hash;
	}

	/**
	 *
	 * Fetches and decrypts settings by name
	 *
	 * @param  string       $name
	 * @return string|array $value
	 *
	 */
	public static function fetch_setting(
		string $name = '',
		bool   $json = false
	) {
		global $db;

		if (
			!$name ||
			!preg_match(Regex::$db_setting['pattern'], $name)
		) {
			return '';
		}

		$query = "
			SELECT value
			FROM settings
			WHERE name = '$name'
		";
		$value = $db->do_select($query);

		if ($value) {
			$value = $value[0]['value'] ?? '';
		} else {
			// create 'name' if not exist
			$query = "
				INSERT INTO settings (
					name,
					value
				) VALUES (
					'$name',
					''
				)
			";
			$db->do_query($query);
			$value = '';
		}

		$value = self::aes_decrypt($value);

		if ($json) {
			try {
				$value = json_decode($value);
			} catch (Exception $e) {}
		}

		return $value;
	}

	/**
	 *
	 * Encrypts and applies a settings by name/value
	 *
	 * @param  string     $name
	 * @param  string|int $value
	 * @return bool
	 *
	 */
	public static function apply_setting(
		string     $name = '',
		string|int $value
	) {
		global $db;

		if (
			!$name ||
			!preg_match(Regex::$db_setting['pattern'], $name)
		) {
			return false;
		}

		if (
			gettype($value) == 'object' ||
			gettype($value) == 'array'
		) {
			$value = json_encode($value);
		} else {
			$value = (string)$value;
		}

		// check if exists already
		$query = "
			SELECT value
			FROM settings
			WHERE name = '$name'
		";
		$check = $db->do_select($query);

		if (!$check) {
			$query = "
				INSERT INTO settings (
					name,
					value
				) VALUES (
					'$name',
					''
				)
			";
			$db->do_query($query);
		}

		// update
		$value = self::aes_encrypt($value);

		$query = "
			UPDATE settings
			SET value = '$value'
			WHERE name = '$name'
		";
		$result = $db->do_query($query);

		return $result;
	}

	/**
	 *
	 * Fetches and decrypts user array and PII and returns simple object for front end
	 *
	 * @param  string  $guid
	 * @return array   $user_array
	 *
	 */
	public static function get_user(string $guid) {
		global $db;

		if (!$guid) {
			return array();
		}

		$query = "
			SELECT
			guid,
			role, 
			email, 
			password,
			pii_data, 
			verified,
			verified_at,
			created_at, 
			twofa, 
			totp,
			avatar_url
			FROM users
			WHERE guid = '$guid'
		";

		$user_array = $db->do_select($query);
		$user_array = $user_array[0] ?? null;

		if (!$user_array) {
			return array();
		}

		if (isset($user_array['password'])) {
			if ($user_array['password']) {
				$user_array['password'] = true;
			} else {
				$user_array['password'] = false;
			}
		}

		$pii_data_enc = $user_array['pii_data'] ?? '';
		$pii_data = self::decrypt_pii($pii_data_enc);

		if (isset($user_array['pii_data'])) {
			unset($user_array['pii_data']);
		}

		if (!$pii_data) {
			$pii_data = Structs::user_info;
		}

		$user_array['pii_data'] = $pii_data;

		// attach notifications
		$now = self::get_datetime();
		$notifications = $db->do_select("
			SELECT
			b.id AS notification_id,
			b.title,
			b.message,
			b.type,
			b.dismissable,
			b.priority,
			b.cta
			FROM user_notifications AS a
			JOIN notifications AS b
			ON a.notification_id = b.id
			WHERE a.guid = '$guid'
			AND a.dismissed_at IS NULL
			AND b.visible = 1
			AND
			(
				(
					b.activate_at   < '$now' AND
					b.deactivate_at > '$now'
				) OR (
					b.activate_at IS NULL
				) OR (
					b.activate_at   < '$now' AND
					b.deactivate_at IS NULL
				)
			)
		");
		$notifications = $notifications ?? array();
		$user_array['notifications'] = $notifications;

		return $user_array;
	}

	/**
	 *
	 * Fetches and decrypts client facing user array and returns simple object for front end. PII safe
	 *
	 * @param  string  $guid
	 * @return array   $user_array
	 *
	 */
	public function get_user_safe(string $guid) {
		global $db;

		if (!$guid) {
			return array();
		}

		$query = "
			SELECT
			guid,
			role, 
			email, 
			pii_data,
			verified
			FROM users
			WHERE guid = '$guid'
		";

		$user_array = $db->do_select($query);
		$user_array = $user_array[0] ?? null;

		if (!$user_array) {
			return array();
		}

		$pii_data_enc = $user_array['pii_data'] ?? '';
		$pii_data = self::decrypt_pii($pii_data_enc);

		if (isset($user_array['pii_data'])) {
			unset($user_array['pii_data']);
		}

		if (!$pii_data) {
			$pii_data = Structs::user_info;
		}

		$user_array['pii_data'] = $pii_data;

		// user status
		if (!$user_array['verified']) {
			$user_array['status'] = 'Invited';
		} else {
			//// get real status from subscriptions table
			$user_array['status'] = 'Trial';
		}

		return $user_array;
	}

	/**
	 *
	 * Decrypts and returns firm PII array by firm guid
	 *
	 * @param  string  $guid
	 * @return array   $firm
	 *
	 */
	public static function get_firm(string $guid) {
		global $db;

		if (!$guid) {
			return array();
		}

		$firm = $db->do_select("
			SELECT *
			FROM firms
			WHERE firm_guid = '$guid'
		")[0] ?? array();

		$pii_data = $firm['pii_data'] ?? '';
		$pii_data = self::decrypt_pii($pii_data);

		if (!$pii_data) {
			$pii_data = Structs::firm_info;
		}

		$firm['pii_data'] = $pii_data;

		return $firm;
	}

	/**
	 *
	 * Decrypts and returns company PII array by firm guid
	 *
	 * @param  string  $guid
	 * @return array   $company_array
	 *
	 */
	public static function get_firm_companies(string $guid) {
		global $db;

		if (!$guid) {
			return array();
		}

		$company_array = array();

		$companies = $db->do_select("
			SELECT company_guid
			FROM  firm_company_relations
			WHERE firm_guid = '$guid'
		");

		$companies = $companies ?? array();

		foreach ($companies as &$company) {
			$company_guid = $company['company_guid'] ?? '';

			$c = $db->do_select("
				SELECT 
				guid,
				email,
				created_at,
				verified_at,
				clicked_invite_at,
				pii_data
				FROM  users
				WHERE guid = '$company_guid'
				ORDER BY created_at DESC
			");

			$c = $c[0] ?? array();

			$company_pii_enc = $c['pii_data'] ?? '';
			$company_pii     = self::decrypt_pii($company_pii_enc);

			if (!$company_pii) {
				$company_pii = Structs::company_info;
			}

			$c['pii_data']   = $company_pii;
			$company_array[] = $c;
		}

		return $company_array;
	}

	/**
	 *
	 * Decrypts and returns company PII array by company guid
	 *
	 * @param  string  $guid
	 * @return array   $company
	 *
	 */
	public static function get_company(string $guid) {
		global $db;

		if (!$guid) {
			return array();
		}

		$company = $db->do_select("
			SELECT 
			guid,
			email,
			pii_data,
			created_at
			FROM  users
			WHERE role = 'company'
			AND   guid = '$guid'
		")[0] ?? array();

		$pii_data = $company['pii_data'] ?? '';
		$pii_data = self::decrypt_pii($pii_data);

		if (!$pii_data) {
			$pii_data = Structs::user_info;
		}

		$company['pii_data'] = $pii_data;

		return $company;
	}

	/**
	 *
	 * Check user security parameters for red flags upon login
	 *
	 * Returns true|false if user needs to pass a 2FA check before authenticating.
	 *
	 * @param  string          $guid
	 * @param  string          $ip
	 * @param  string          $user_agent
	 * @param  string|int|null $cookie
	 * @return bool            Reason the login attempt is being flagged
	 *
	 */
	public static function check_authorized_devices(
		string          $guid,
		string          $ip         = '',
		string          $user_agent = '',
		string|int|null $cookie     = ''
	) {
		global $db;

		if (!$cookie) {
			$cookie = 'nill';
		}

		// check agent/IP/cookie
		$expired_time = self::get_datetime(-2629800); // 1 month ago avg
		$query = "
			SELECT *
			FROM authorized_devices
			WHERE guid = '$guid'
			AND created_at > '$expired_time'
			AND (
				(
					cookie     = '$cookie' AND
					user_agent = '$user_agent'
				) OR (
					ip = '$ip'
				)
			)
		";
		$result = $db->do_select($query);

		if(!$result) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * Add user authorized device to avoid 2FA
	 *
	 * @param  string  $guid
	 * @param  string  $ip
	 * @param  string  $user_agent
	 * @param  string  $cookie
	 * @return null
	 *
	 */
	public static function add_authorized_device(
		string          $guid,
		string          $ip         = '',
		string          $user_agent = '',
		string|int|null $cookie     = ''
	) {
		global $db;

		if (!$cookie) {
			$cookie = '';
		}

		// fetch similar first
		$query = "
			SELECT guid
			FROM authorized_devices
			WHERE guid       = '$guid'
			AND   ip         = '$ip'
			AND   user_agent = '$user_agent'
			AND   cookie     = '$cookie'
		";

		$similar = $db->do_select($query);
		$now     = self::get_datetime();

		if ($similar) {
			// update instead of insert
			$query = "
				UPDATE authorized_devices
				SET   created_at = '$now'
				WHERE guid       = '$guid'
				AND   ip         = '$ip'
				AND   user_agent = '$user_agent'
				AND   cookie     = '$cookie'
			";
		} else {
			// generate new cookie
			$cookie = self::generate_hash(32);

			// insert new record
			$query = "
				INSERT INTO authorized_devices (
					guid,
					ip,
					user_agent,
					cookie,
					created_at
				) VALUES (
					'$guid',
					'$ip',
					'$user_agent',
					'$cookie',
					'$now'
				)
			";
		}

		$db->do_query($query);

		return $cookie;
	}

	/**
	 *
	 * Logs login attempts and other actions for security auditing
	 *
	 * @param  string   $guid
	 * @param  string   $email
	 * @param  int|bool $successful
	 * @param  string   $detail
	 * @param  string   $ip
	 * @param  string   $user_agent
	 * @return null
	 *
	 */
	public static function log_action(
		string   $guid,
		string   $email,
		int|bool $successful,
		string   $detail     = '',
		string   $ip         = '',
		string   $user_agent = ''
	) {
		global $db;

		$event_at   = self::get_datetime();
		$successful = (bool)$successful;
		$successful = (int)$successful;
		$source     = strtolower($_SERVER['HTTP_ORIGIN'] ?? '');

		$query = "
			INSERT INTO action_log (
				guid,
				email,
				event_at,
				successful,
				detail,
				ip,
				user_agent,
				source
			) VALUES (
				'$guid',
				'$email',
				'$event_at',
				$successful,
				'$detail',
				'$ip',
				'$user_agent',
				'$source'
			)
		";
		$db->do_query($query);
	}

	/**
	 *
	 * Sanitize required GET/POST/PUT parameter inputs
	 *
	 * Handles string length, regex, format check of all parameter arguments.
	 * _exits's with proper error handling if a problem is encountered.
	 *
	 * @param  string  $parameter      Parameter to be sanitized/checked.
	 * @param  bool    $required       Check if parameter is required.
	 * @param  int     $min_length     Minimum length of the parameter.
	 * @param  int     $max_length     Maximum length of the parameter.
	 * @param  string  $regex_pattern  Regex pattern to be used.
	 * @param  string  $name           Name of parameter to used in error returns and logging.
	 * @return null
	 *
	 */
	public static function sanitize_input(
		$parameter      = null,
		bool $required  = true,
		int $min_length = 0,
		int $max_length = 256,
		$regex_pattern  = null,
		string $name    = ''
	) {
		$extra_parameter = 'parameter ';

		if (!$name) {
			$name = 'Parameter';
			$extra_parameter = '';
		}

		if ($required) {
			if (!$parameter) {
				_exit(
					'error', 
					'Please provide required '.$extra_parameter.$name, 
					400, 
					'Failed to provide required '.$extra_parameter.$name
				);
			}
		}

		if (gettype($parameter) == 'string') {
			if ($min_length == $max_length) {
				if (strlen($parameter) != $max_length) {
					if($max_length == 1) {
						$chars = 'character';
					} else {
						$chars = 'characters';
					}

					$error_msg = $name.' must be exactly '.$max_length.' '.$chars.' in length.';

					_exit(
						'error',
						$error_msg,
						400,
						$error_msg
					);
				}
			}

			if (
				strlen($parameter) > $max_length ||
				strlen($parameter) < $min_length
			) {
				if($min_length == 1) {
					$chars_min = 'character';
				} else {
					$chars_min = 'characters';
				}

				if($max_length == 1) {
					$chars_max = 'character';
				} else {
					$chars_max = 'characters';
				}

				$error_msg = $name.' must be ';

				if ($min_length > 0) {
					$error_msg .= 'at least '.$min_length.' '.$chars_min.' and ';
				}

				$error_msg .= 'at most '.$max_length.' '.$chars_max.' in length.';

				_exit(
					'error',
					$error_msg,
					400,
					$error_msg
				);
			}

			if(
				gettype($regex_pattern) == 'string' &&
				strlen($parameter) > 0
			) {
				if (!preg_match($regex_pattern, $parameter)) {
					_exit(
						'error',
						'Invalid '.$name.'. Contains forbidden special characters or is not in the correct format.',
						400,
						'Invalid '.$name.'. Contains forbidden special characters or is not in the correct format.'
					);
				}
			}
		}

		if (
			gettype($parameter) == 'integer' ||
			gettype($parameter) == 'double' ||
			gettype($parameter) == 'float'
		) {
			if (
				$parameter > $max_length ||
				$parameter < $min_length
			) {
				_exit(
					'error',
					$name.' must be less than '.$max_length.', and greater than '.$min_length.'.',
					400,
					$name.' must be less than '.$max_length.', and greater than '.$min_length.'.'
				);
			}
		}
	}

	/**
	 *
	 * Generate augmented GUID string
	 *
	 * Crypto safe.
	 * 5th set is always self::company_bytes
	 * Always begins with one of:
	 * - A: Admin guid
	 * - F: Firm guid
	 * - C: Company guid
	 * - R: Report guid
	 *
	 * Example: F-eaa4a536-3ab3-35aa-4c4c-c4fe30ab200c
	 *
	 * @param  string $role
	 * @return string
	 *
	 */
	public static function generate_guid(
		string $role = 'user'
	) {
		$prepend = 'U-';

		if ($role == 'admin') {
			$prepend = 'A-';
		}

		if ($role == 'company') {
			$prepend = 'C-';
		}

		if ($role == 'report') {
			$prepend = 'R-';
		}

		if ($role == 'user') {
			$prepend = 'U-';
		}

		if ($role == 'firm') {
			$prepend = 'F-';
		}

		return (
			$prepend.
			bin2hex(openssl_random_pseudo_bytes(4)).'-'.
			chunk_split(bin2hex(openssl_random_pseudo_bytes(4)), 4, '-').
			self::company_bytes.'-'.
			bin2hex(openssl_random_pseudo_bytes(6))
		);
	}

	/**
	 *
	 * Verify a GUID against our backend standard
	 *
	 * 5th set should always be self::company_bytes
	 * First set should always be one of:
	 * - A: Admin guid
	 * - F: Firm guid
	 * - C: Company guid
	 *
	 * Example: F-eaa4a536-3ab3-35aa-4c4c-c4fe30ab200c
	 *
	 * @return bool
	 *
	 */
	public static function verify_guid(string $guid) {
		$split = explode('-', $guid);
		$set0  = $split[0];
		$set1  = $split[1] ?? '';
		$set2  = $split[2] ?? '';
		$set3  = $split[3] ?? '';
		$set4  = $split[4] ?? '';
		$set5  = $split[5] ?? '';

		if (
			$set0 != 'A' &&
			$set0 != 'F' &&
			$set0 != 'C'
		) {
			return false;
		}

		if (
			strlen($set1) != 8 ||
			strlen($set2) != 4 ||
			strlen($set3) != 4 ||
			strlen($set4) != 4 ||
			strlen($set5) != 12 ||
			!ctype_xdigit($set1) ||
			!ctype_xdigit($set2) ||
			!ctype_xdigit($set3) ||
			!ctype_xdigit($set4) ||
			!ctype_xdigit($set5)
		) {
			return false;
		}

		if ($set4 != self::company_bytes) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * Verify a GUID against our backend standard
	 *
	 * 5th set should always be self::company_bytes
	 *
	 * Example: F-eaa4a536-3ab3-35aa-4c4c-c4fe30ab200c
	 *
	 * @return bool
	 *
	 */
	public static function guid_available(string $guid) {
		global $db;

		$query = "
			SELECT guid
			FROM users
			WHERE guid = '$guid'
		";
		$check = $db->do_select($query);

		if ($check) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * Generate Session token
	 *
	 * 128 bytes, crypto safe
	 *
	 * @return string
	 *
	 */
	public static function generate_session_token() {
		return bin2hex(openssl_random_pseudo_bytes(128));
	}

	/**
	 *
	 * Generate User friedly hash. For MFA, confirmation codes, etc
	 *
	 * Default 10 char length
	 *
	 * @param  int    $length
	 * @return string
	 *
	 */
	public static function generate_hash(
		int $length = 10
	) {
		$seed = str_split(
			'ABCDEFGHJKLMNPQRSTUVWXYZ'.
			'2345678923456789'
		);
		// dont use 0, 1, o, O, l, I
		shuffle($seed);
		$hash = '';

		foreach(array_rand($seed, $length) as $k) {
			$hash .= $seed[$k];
		}

		return $hash;
	}

	/**
	 *
	 * Get date/time delta from seconds
	 *
	 * Outputs format days:hours:minutes:seconds, accurate up to 365 days.
	 *
	 * @param  int $seconds Seconds converted to modulated time delta
	 * @return string
	 *
	 */
	public static function get_timedelta(int $seconds) {
		date_default_timezone_set('UTC');
		return(gmdate('z:H:i:s', $seconds));
	}

	/**
	 *
	 * Get standard format date/time
	 *
	 * Behaves similar to epoch timestamp when compared with <=> operators
	 *
	 * @param  int $future  Can be positive for future timestamp, negative for past timestamp
	 * @return string
	 *
	 */
	public static function get_datetime(
		int $future = 0
	) {
		$time = time();
		date_default_timezone_set('UTC');
		return(date('Y-m-d H:i:s', $time + $future));
	}

	/**
	 *
	 * Get current filing year
	 *
	 * Collects only 'Y' from date().
	 *
	 * @return string $filing_year
	 *
	 */
	public static function get_filing_year() {
		date_default_timezone_set('UTC');
		return date('Y', time());
	}

	/**
	 *
	 * Schedule an email
	 *
	 * Instead of sending emails immediately, this feeds a cron job that scheduled sends mail every 60 seconds
	 *
	 * @param  string  $template_id
	 * @param  string  $recipient
	 * @param  string  $subject
	 * @param  string  $body
	 * @param  string  $link
	 * @return bool    $return   Indicating if queue was successful
	 *
	 */
	public static function schedule_email(
		string $template_id,
		string $recipient,
		string $subject,
		string $body,
		string $link = ''
	) {
		global $db;

		/*
		Template IDs

		 - welcome
		 - approved
		 - denied
		 - twofa
		 - register
		 - register-admin
		 - forgot-password

		*/

		/* Check exploitable endpoints for similar mail requests */
		$partial_email  = explode('+', $recipient);
		$partial_email1 = $partial_email[0];
		$partial_email2 = $partial_email[1] ?? '';
		$partial_email2 = explode('@', $partial_email2);
		$partial_email2 = $partial_email2[1] ?? '';
		$similarity_check = $db->do_select("
			SELECT *
			FROM  schedule
			WHERE template_id = '$template_id'
			AND   complete    = 0
			AND (
				email = '$recipient' OR (
					email LIKE '%$partial_email1%' AND
					email LIKE '%$partial_email2%'
				)
			)
			AND subject = '$subject'
			ORDER BY id DESC
		");
		$similarity_check = $similarity_check ?? array();

		foreach ($similarity_check as $item) {
			$sid = $item['id'] ?? 0;
			$db->do_query("
				UPDATE schedule
				SET   complete = 1
				WHERE id       = $sid
			");
		}

		/* Create schedule item */
		$created_at = self::get_datetime();

		$query = "
			INSERT INTO schedule (
				template_id,
				subject,
				body,
				link,
				email,
				created_at
			) VALUES (
				'$template_id',
				'$subject',
				'$body',
				'$link',
				'$recipient',
				'$created_at'
			)
		";
		return $db->do_query($query);
	}

	/**
	 *
	 * Send MFA code
	 *
	 * For MFA authenticated functions
	 *
	 * @param  string  $guid
	 * @return bool
	 *
	 */
	public static function send_mfa(string $guid) {
		global $db;

		$query = "
			SELECT email
			FROM   users
			WHERE  guid = '$guid'
		";

		$selection  = $db->do_select($query);
		$email      = $selection[0]['email'] ?? '';
		$code       = self::generate_hash(6);
		$created_at = self::get_datetime();

		if($selection) {
			$query = "
				DELETE FROM twofa
				WHERE guid = '$guid'
			";
			$db->do_query($query);

			$query = "
				INSERT INTO twofa (
					guid,
					created_at,
					code
				) VALUES (
					'$guid',
					'$created_at',
					'$code'
				)
			";
			$db->do_query($query);

			self::schedule_email(
				'twofa',
				$email,
				APP_NAME.' - Multi Factor Authentication',
				'Hello, please find your MFA code for '.APP_NAME.'. This code expires in 5 minutes.',
				$code
			);

			return true;
		}
		return false;
	}

	/**
	 *
	 * Verfiy MFA code
	 *
	 * Once successfully verified, MFA allowance lasts 5 minutes
	 *
	 * @param  string  $guid
	 * @param  string  $mfa_code
	 * @return string
	 *
	 */
	public static function verify_mfa(
		string $guid,
		string $mfa_code
	) {
		global $db;

		if(strlen($mfa_code) > 8) {
			return 'incorrect';
		}

		// check mfa type first
		$query = "
			SELECT totp
			FROM users
			WHERE guid = '$guid'
		";
		$mfa_type = $db->do_select($query);
		$mfa_type = (int)($mfa_type[0]['totp'] ?? 0);

		// totp type mfa
		if($mfa_type == 1) {
			$verified = Totp::check_code($guid, $mfa_code);

			if($verified) {
				self::create_mfa_allowance($guid);
				return 'success';
			}

			return 'incorrect';
		}

		// email type mfa
		$query = "
			SELECT code, created_at
			FROM  twofa
			WHERE guid = '$guid'
			AND   code = '$mfa_code'
		";

		$selection    = $db->do_select($query);
		$fetched_code = $selection[0]['code'] ?? '';
		$created_at   = $selection[0]['created_at'] ?? 0;
		$expire_time  = self::get_datetime(-300); // 5 minutes ago

		if($selection) {
			if($mfa_code == $fetched_code) {
				$query = "
					DELETE FROM twofa
					WHERE guid = '$guid'
				";
				$db->do_query($query);

				if($expire_time < $created_at) {
					self::create_mfa_allowance($guid);
					return 'success';
				} else {
					return 'expired';
				}
			}
		}
		return 'incorrect';
	}

	/**
	 *
	 * Create an MFA Allowance
	 *
	 * Happens when MFA is successfully verified.
	 * Lasts 5 minutes.
	 * Purposed for user ability to submit MFA and then submit authenticated request sequentially. 
	 *
	 * @param  string  $guid
	 * @return bool
	 *
	 */
	public static function create_mfa_allowance(string $guid) {
		global $db;

		$expires_at = self::get_datetime(300); // 5 minutes from now

		$query = "
			DELETE FROM mfa_allowance
			WHERE guid = '$guid'
		";
		$db->do_query($query);

		$query = "
			INSERT INTO mfa_allowance (
				guid,
				expires_at
			) VALUES (
				'$guid',
				'$expires_at'
			)
		";
		$return = $db->do_query($query);

		return $return;
	}

	/**
	 *
	 * Consume MFA Allowance
	 *
	 * Once successfully consumed, MFA allowance is purged.
	 * If allowance is attempted to be consumed and found to be expired, it purges record and returns false.
	 *
	 * @param  string  $guid
	 * @return bool
	 *
	 */
	public static function consume_mfa_allowance(string $guid) {
		global $db;

		$query = "
			SELECT expires_at
			FROM mfa_allowance
			WHERE guid = '$guid'
		";
		$selection = $db->do_select($query);

		if(!$selection) {
			return false;
		}

		$expires_at = $selection[0]['expires_at'] ?? '';
		$now_time   = self::get_datetime();

		if($now_time > $expires_at) {
			$return = false;
		} else {
			$return = true;
		}

		$query = "
			DELETE FROM mfa_allowance
			WHERE guid = '$guid'
		";
		$db->do_query($query);

		return $return;
	}

	/**
	 *
	 * Base64 encode data quickly
	 *
	 * @param  string  $data
	 * @return string
	 *
	 */
	public static function b_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 *
	 * Base64 decode data quickly
	 *
	 * @param  string  $data
	 * @return string
	 *
	 */
	public static function b_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

	/**
	 *
	 * Encrypt data quickly. Crypto safe
	 *
	 * @param  string  $data
	 * @return string  $ciphertext
	 *
	 */
	public static function aes_encrypt($data) {
		$iv = openssl_random_pseudo_bytes(16);

		$ciphertext = openssl_encrypt(
			$data,
			self::cipher,
			hex2bin(MASTER_KEY),
			0,
			$iv
		);

		$ciphertext = self::b_encode(self::b_encode($ciphertext).'::'.bin2hex($iv));

		return $ciphertext;
	}

	/**
	 *
	 * Decrypt data quickly. Crypto safe
	 *
	 * @param  string  $data
	 * @return string
	 *
	 */
	public static function aes_decrypt($data) {
		$decoded = self::b_decode($data);
		$split   = explode('::', $decoded);
		$iv      = $split[1] ?? '';

		if(strlen($iv) % 2 == 0 && ctype_xdigit($iv)) {
			$iv = hex2bin($iv);
		} else {
			return self::b_decode($data);
		}

		$data = self::b_decode($split[0]);

		$decrypted = openssl_decrypt(
			$data,
			self::cipher,
			hex2bin(MASTER_KEY),
			OPENSSL_ZERO_PADDING,
			$iv
		);

		return rtrim($decrypted, "\0..\32");
	}

	/**
	 *
	 * Encrypt a PII array
	 *
	 * @param  array  $data
	 * @return string $data_json_enc  Encrypted json string ciphertext
	 *
	 */
	public static function encrypt_pii(
		array $data = array()
	) {
		$data_json     = json_encode($data);
		$data_json_enc = self::aes_encrypt($data_json);

		return $data_json_enc;
	}

	/**
	 *
	 * Decrypt a PII ciphertext string
	 *
	 * @param  string  $enc_json
	 * @return array   $data     Array object matching a Structs class const
	 *
	 */
	public static function decrypt_pii(
		string $ciphertext = ''
	) {
		$data_json = self::aes_decrypt($ciphertext);

		try {
			$data = json_decode($data_json, true);
		} catch (\Exception $e) {
			$data = array();
		}

		return $data;
	}

	/**
	 *
	 * Get Dir Contents
	 *
	 * Recursively get all files/folders in the specied directory $dir.
	 * Returns list of items relative to base $__dir supplied to method, meant as __DIR__.
	 *
	 * @param  string  $__dir
	 * @param  string  $dir
	 * @return array   $result
	 *
	 */
	public static function get_dir_contents(
		$__dir, 
		$dir, 
		&$result = array()
	) {
		$files = scandir($dir);

		foreach ($files as $key => $val) {
			$path = realpath($dir.DIRECTORY_SEPARATOR.$val);
			$path = str_replace($__dir.'/' , '', $path);

			if (!is_dir($path)) {
				$result[] = $path;
			} elseif (
				$val != '.' &&
				$val != '..'
			) {
				self::get_dir_contents($__dir, $path, $result);
				$result[] = $path;
			}
		}

		return $result;
	}

	/**
	 *
	 * Get real IP address
	 *
	 * @return string
	 *
	 */
	public static function get_real_ip() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		}

		if ($ip == '::1')
			return '127.0.0.1';

		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
			return '127.0.0.1';

		return $ip;
	}

	/**
	 *
	 * Verify provided IP is in a provided CIDR range
	 *
	 * @param  string  $ip
	 * @param  string  $iprange
	 * @return bool
	 *
	 */
	public static function in_CIDR_range(
		string $ip, 
		string $iprange
	) {
		if (!$iprange || $iprange == '') {
			return true;
		}

		if (strpos($iprange, '/') === false) {
			if (inet_pton($ip) == inet_pton($iprange)) {
				return true;
			}
		} else {
			list($subnet, $bits) = explode('/', $iprange);
			// Convert subnet to binary string of $bits length
			// Subnet in Hex
			$subnet = unpack('H*', inet_pton($subnet));

			foreach($subnet as $i => $h) {
				// Array of Binary
				$subnet[$i] = base_convert($h, 16, 2); 
			}

			$subnet = substr(implode('', $subnet), 0, $bits);
			// Subnet in Binary, only network bits
			// Convert remote IP to binary string of $bits length
			$ip = unpack('H*', inet_pton($ip));
			// IP in Hex

			foreach($ip as $i => $h) {
				// Array of Binary
				$ip[$i] = base_convert($h, 16, 2);
			}

			// IP in Binary, only network bits
			$ip = substr(implode('', $ip), 0, $bits);

			// Check network bits match
			if ($subnet == $ip) {
				return true;
			}
		}
		return false;
	}

	public static $states = array(
		'AK' => "Alaska",
		'AZ' => "Arizona",
		'AR' => "Arkansas",
		'CA' => "California",
		'CO' => "Colorado",
		'CT' => "Connecticut",
		'DE' => "Delaware",
		'DC' => "District Of Columbia",
		'FL' => "Florida",
		'GA' => "Georgia",
		'HI' => "Hawaii",
		'ID' => "Idaho",
		'IL' => "Illinois",
		'IN' => "Indiana",
		'IA' => "Iowa",
		'KS' => "Kansas",
		'KY' => "Kentucky",
		'LA' => "Louisiana",
		'ME' => "Maine",
		'MD' => "Maryland",
		'MA' => "Massachusetts",
		'MI' => "Michigan",
		'MN' => "Minnesota",
		'MS' => "Mississippi",
		'MO' => "Missouri",
		'MT' => "Montana",
		'NE' => "Nebraska",
		'NV' => "Nevada",
		'NH' => "New Hampshire",
		'NJ' => "New Jersey",
		'NM' => "New Mexico",
		'NY' => "New York",
		'NC' => "North Carolina",
		'ND' => "North Dakota",
		'OH' => "Ohio",
		'OK' => "Oklahoma",
		'OR' => "Oregon",
		'PA' => "Pennsylvania",
		'RI' => "Rhode Island",
		'SC' => "South Carolina",
		'SD' => "South Dakota",
		'TN' => "Tennessee",
		'TX' => "Texas",
		'UT' => "Utah",
		'VT' => "Vermont",
		'VA' => "Virginia",
		'WA' => "Washington",
		'WV' => "West Virginia",
		'WI' => "Wisconsin",
		'WY' => "Wyoming",
		// canada
		'AB' => 'Alberta',
		'BC' => 'British Columbia',
		'MB' => 'Manitoba',
		'NB' => 'New Brunswick',
		'NL' => 'Newfoundland and Labrador',
		'NS' => 'Nova Scotia',
		'NT' => 'Northwest Territories',
		'NU' => 'Nunavut',
		'ON' => 'Ontario',
		'PE' => 'Prince Edward Island',
		'QC' => 'Quebec',
		'SK' => 'Saskatchewan',
		'YT' => 'Yukon'
	);

	/**
	 *
	 * Verify US States
	 *
	 * @param  string  $state
	 * @return bool
	 *
	 */
	public static function is_state(string $state) {
		if (in_array($state, self::$states)) {
			return true;
		}

		if (array_key_exists($state, self::$states)) {
			return true;
		}

		return false;
	}

	public static $countries = array(
		'AF' => 'Afghanistan',
		'AX' => 'Aland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua And Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia And Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CD' => 'Congo, Democratic Republic',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Cote D\'Ivoire',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island & Mcdonald Islands',
		'VA' => 'Holy See (Vatican City State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran, Islamic Republic Of',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle Of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KR' => 'Korea',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia, Federated States Of',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory, Occupied',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barthelemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts And Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin',
		'PM' => 'Saint Pierre And Miquelon',
		'VC' => 'Saint Vincent And Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome And Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia And Sandwich Isl.',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard And Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad And Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks And Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Viet Nam',
		'VG' => 'Virgin Islands, British',
		'VI' => 'Virgin Islands, U.S.',
		'WF' => 'Wallis And Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);

	/**
	 *
	 * Verify ISO 3166 Country Codes
	 *
	 * @param  string  $country
	 * @return bool
	 *
	 */
	public static function ISO3166_country(string $country) {
		if (in_array($country, self::$countries)) {
			return true;
		}

		if (array_key_exists($country, self::$countries)) {
			return true;
		}

		return false;
	}
}