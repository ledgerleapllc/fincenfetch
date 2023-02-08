<?php
/**
 * Mysql database class. Includes entire schemas that build/re-build tables if integrity check fails.
 *
 * @author @blockchainthomas
 *
 * @method array|null  do_select()
 * @method bool        do_query()
 * @method null        check_integrity()
 *
 */
class DB {
	public $connect = null;

	function __construct() {
		$this->connect = new mysqli(
			DB_HOST,
			DB_USER,
			DB_PASS,
			DB_NAME,
			DB_PORT
		);

		if ($this->connect->connect_error) {
			$this->connect = null;
		}
	}

	function __destruct() {
		if ($this->connect) {
			$this->connect->close();
		}
	}

	/**
	 * Do DB selection
	 *
	 * @param string $query
	 * @return array $return
	 *
	 */
	public function do_select($query) {
		$return = null;

		if ($this->connect) {
			$result = $this->connect->query($query);

			if ($result != null && $result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$return[] = $row;
				}
			}
		}

		return $return;
	}

	/**
	 * Do DB query
	 *
	 * @param string $query
	 * @return bool
	 *
	 */
	public function do_query($query) {
		$flag = false;

		if ($this->connect) {
			$flag = $this->connect->query($query);
		}

		return $flag;
	}

	/**
	 * Check DB integrity
	 */
	public function check_integrity() {	
		$query      = "SHOW TABLES";
		$db_tables2 = $this->do_select($query);
		$db_tables  = array();

		// default admin
		$admin_email         = getenv('ADMIN_EMAIL');
		$admin_password      = getenv('ADMIN_PASSWORD');
		$admin_password_hash = password_hash(
			$admin_password, 
			PASSWORD_DEFAULT
		);

		if ($db_tables2) {
			foreach ($db_tables2 as $table) {
				$db_tables[] = $table['Tables_in_'.DB_NAME];
			}
		}

		$my_tables = array(
			"reports"             => array(
				"fields"          => array(
					"report_guid" => array(
						"type"    => "varchar(38)",
						"default" => "DEFAULT NULL"
					),
					"company_guid"=> array(
						"type"    => "varchar(38)",
						"default" => "DEFAULT NULL"
					),
					"firm_guid"   => array(
						"type"    => "varchar(38)",
						"default" => "DEFAULT NULL"
					),
					"report_type" => array(
						"type"    => "enum('initial', 'updated')",
						"default" => "DEFAULT 'initial'"
					),
					"status"      => array(
						"type"    => "enum('start', 'resume', 'view')",
						"default" => "DEFAULT 'start'"
					),
					"pii_data"    => array(
						"type"    => "MEDIUMTEXT",
						"default" => "NOT NULL"
					),
					"filing_year" => array(
						"type"    => "varchar(6)",
						"default" => ""
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"updated_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
				),
				"insert_records"  => array()
			),
			"contact_recipients"  => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "DEFAULT NULL"
					),
					"email"       => array(
						"type"    => "varchar(255)",
						"default" => "NOT NULL"
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
				),
				"primary"         => "email",
				"insert_records"  => array()
			),
			"schedule"            => array(
				"fields"          => array(
					"id"          => array(
						"type"    => "int",
						"default" => "NOT NULL AUTO_INCREMENT"
					),
					"template_id" => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					),
					"subject"     => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT ''"
					),
					"body"        => array(
						"type"    => "text",
						"default" => ""
					),
					"link"        => array(
						"type"    => "text",
						"default" => ""
					),
					"email"       => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"sent_at"     => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"complete"    => array(
						"type"    => "int",
						"default" => "DEFAULT '0'"
					)
				),
				"primary"         => "id",
				"insert_records"  => array()
			),
			"sessions"            => array(
				"fields"          => array(
					"id"          => array(
						"type"    => "int",
						"default" => "NOT NULL AUTO_INCREMENT"
					),
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"bearer"      => array(
						"type"    => "text",
						"default" => ""
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"expires_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"limit_at"    => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"primary"         => "id",
				"insert_records"  => array()
			),
			"settings"            => array(
				"fields"          => array(
					"name"        => array(
						"type"    => "varchar(64)",
						"default" => "NOT NULL"
					),
					"value"       => array(
						"type"    => "text",
						"default" => ""
					)
				),
				"primary"         => "name",
				"insert_records"  => array()
			),
			"subscriptions"       => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"email"       => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					),
					"source"      => array(
						"type"    => "varchar(32)",
						"default" => "DEFAULT NULL"
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"primary"         => "guid",
				"insert_records"  => array()
			),
			"authorized_devices"  => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"ip"          => array(
						"type"    => "varchar(256)",
						"default" => "DEFAULT NULL"
					),
					"user_agent"  => array(
						"type"    => "text",
						"default" => ""
					),
					"cookie"      => array(
						"type"    => "text",
						"default" => ""
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"insert_records"  => array()
			),
			"users"               => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"role"        => array(
						"type"    => "enum('admin','firm','company')",
						"default" => "DEFAULT 'firm'"
					),
					"email"       => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					),
					"pii_data"    => array(
						"type"    => "MEDIUMTEXT",
						"default" => ""
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"verified"    => array(
						"type"    => "int",
						"default" => "DEFAULT '0'"
					),
					"verified_at" => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"clicked_invite_at" => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"password"    => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					),
					"confirmation_code" => array(
						"type"    => "varchar(64)",
						"default" => "DEFAULT NULL"
					),
					"twofa"       => array(
						"type"    => "int",
						"default" => "DEFAULT '0'"
					),
					"totp"        => array(
						"type"    => "int",
						"default" => "DEFAULT '0'"
					),
					"avatar_url"  => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					),
					"stripe_customer_id" => array(
						"type"    => "varchar(64)",
						"default" => "DEFAULT NULL"
					)
				),
				"primary"         => "guid",
				"insert_records"  => array(
					array(
						'A-5a199618-682d-2006-4c4c-c0cde9e672d5',
						'admin',
						"$admin_email",
						'',
						1,
						"$admin_password_hash",
						'2022-01-01 14:30:00',
						'ABC123',
						0,
						0,
						'',
						''
					)
				)
			),
			"firm_company_relations" => array(
				"fields"          => array(
					"firm_guid"   => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"company_guid"=> array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"associated_at" => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"insert_records"  => array()
			),
			"twofa"               => array(
				"fields"          => array(
					"id"          => array(
						"type"    => "int",
						"default" => "NOT NULL AUTO_INCREMENT"
					),
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"code"        => array(
						"type"    => "varchar(12)",
						"default" => "NOT NULL"
					)
				),
				"primary"         => "id",
				"insert_records"  => array()
			),
			"mfa_allowance"       => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"expires_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"insert_records"  => array()
			),
			"throttle"            => array(
				"fields"          => array(
					"ip"          => array(
						"type"    => "varchar(64)",
						"default" => "DEFAULT NULL"
					),
					"uri"         => array(
						"type"    => "text",
						"default" => ""
					),
					"hit"         => array(
						"type"    => "float",
						"default" => "DEFAULT NULL"
					),
					"last_request" => array(
						"type"    => "int",
						"default" => "DEFAULT '0'"
					)
				),
				"insert_records"  => array()
			),
			"password_resets"     => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"code"        => array(
						"type"    => "varchar(12)",
						"default" => "NOT NULL"
					)
				),
				"insert_records"  => array()
			),
			"email_changes"       => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"new_email"   => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					),
					"code"        => array(
						"type"    => "varchar(12)",
						"default" => "NOT NULL"
					),
					"success"     => array(
						"type"    => "int",
						"default" => "DEFAULT '0'"
					),
					"dead"        => array(
						"type"    => "int",
						"default" => "DEFAULT '0'"
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"insert_records"  => array()
			),
			"totp"                => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"secret"      => array(
						"type"    => "text",
						"default" => ""
					),
					"hash"        => array(
						"type"    => "varchar(64)",
						"default" => "DEFAULT NULL"
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"active"      => array(
						"type"    => "int",
						"default" => "DEFAULT '1'"
					)
				),
				"insert_records"  => array()
			),
			"totp_logins"         => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"expires_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"insert_records"  => array()
			),
			"login_attempts"      => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"email"       => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					),
					"logged_in_at" => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"successful"  => array(
						"type"    => "int(1)",
						"default" => "DEFAULT '0'"
					),
					"detail"      => array(
						"type"    => "varchar(64)",
						"default" => "DEFAULT NULL"
					),
					"ip"          => array(
						"type"    => "varchar(64)",
						"default" => "DEFAULT NULL"
					),
					"user_agent"  => array(
						"type"    => "text",
						"default" => ""
					),
					"source"      => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT NULL"
					)
				),
				"insert_records"  => array()
			),
			"avatar_changes"      => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"updated_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"primary"         => "guid",
				"insert_records"  => array()
			),
			"notifications"       => array(
				"fields"          => array(
					"id"          => array(
						"type"    => "int",
						"default" => "NOT NULL AUTO_INCREMENT"
					),
					"title"       => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT ''"
					),
					"message"     => array(
						"type"    => "text",
						"default" => ""
					),
					"type"        => array(
						"type"    => "enum('warning', 'error', 'question', 'info')",
						"default" => "DEFAULT 'info'"
					),
					"dismissable" => array(
						"type"    => "int(1)",
						"default" => "DEFAULT 1"
					),
					"priority"    => array(
						"type"    => "int",
						"default" => "DEFAULT 1"
					),
					"visible"     => array(
						"type"    => "int(1)",
						"default" => "DEFAULT 1"
					),
					"cta"         => array(
						"type"    => "varchar(255)",
						"default" => "DEFAULT ''"
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"activate_at" => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"deactivate_at" => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"primary"         => "id",
				"insert_records"  => array()
			),
			"user_notifications"  => array(
				"fields"          => array(
					"notification_id" => array(
						"type"    => "int",
						"default" => "NOT NULL"
					),
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"created_at"  => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					),
					"dismissed_at" => array(
						"type"    => "timestamp",
						"default" => "NULL DEFAULT NULL"
					)
				),
				"insert_records"  => array()
			),
			"permissions"         => array(
				"fields"          => array(
					"guid"        => array(
						"type"    => "varchar(38)",
						"default" => "NOT NULL"
					),
					"dashboard"   => array(
						"type"    => "int(1)",
						"default" => "DEFAULT 1"
					)
				),
				"primary"         => "guid",
				"insert_records"  => array()
			),
		);

		foreach ($my_tables as $table_name => $table) {
			if (!in_array($table_name, $db_tables)) {
				$fields  = $table['fields'] ?? array();
				$primary = $table['primary'] ?? '';
				$query   = "CREATE TABLE `$table_name` (\n";

				foreach ($fields as $field_name => $field) {
					$type    = $field['type'] ?? 'varchar(255)';
					$default = $field['default'] ?? '';
					$query  .= "`$field_name` $type".($default ? ' '.$default : '');

					if ($field_name != array_key_last($fields)) {
						$query .= ",\n";
					}
				}

				if ($primary) {
					$query .= ",\nPRIMARY KEY (`$primary`)";
				}

				$query .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

				$this->do_query($query);
				elog("DB: Created $table_name table");

				$insert_records = $table['insert_records'] ?? array();

				foreach ($insert_records as $record) {
					$query = "INSERT INTO $table_name (\n";

					foreach ($fields as $field_name => $field_value) {
						$query .= $field_name;

						if ($field_name != array_key_last($fields)) {
							$query .= ",\n";
						}
					}

					$query .= "\n) VALUES (\n";

					foreach ($record as $field_index => $field_value) {
						if (gettype($field_value) === 'string') {
							$query .= "'$field_value'";
						} elseif (strtoupper(gettype($field_value)) === 'NULL') {
							$query .= 'NULL';
						} else {
							$query .= $field_value;
						}

						if ($field_index != array_key_last($record)) {
							$query .= ",\n";
						}
					}

					$query .= "\n)";

					$this->do_query($query);
					elog("DB: Inserted default record into $table_name table");
				}
			} else {
				$query      = "DESCRIBE $table_name";
				$desc       = $this->do_select($query);
				$desc       = $desc ?? array();
				$array_keys = array_keys($my_tables[$table_name]['fields']);
				$db_fields  = array();

				foreach ($desc as $column) {
					$field = $column['Field'] ?? '';

					if ($field) {
						$db_fields[] = $field;
					}
				}

				foreach ($array_keys as $array_key) {
					if (!in_array($array_key, $db_fields)) {
						elog("Detected change in $table_name table - Adding field '$array_key'");
						$type    = $my_tables[$table_name]['fields'][$array_key]['type']    ?? '';
						$default = $my_tables[$table_name]['fields'][$array_key]['default'] ?? '';
						$query   = "
							ALTER TABLE $table_name 
							ADD COLUMN $array_key
							$type $default
						";
						$this->do_query($query);
					}
				}
			}
		}
	}
}
?>