<?php
/**
 *
 * Data structure templates for Casper Association Portal encrypted PII objects
 *
 * @static firm_info      Struct for user pii.
 * @static company_info   Struct for entity pii.
 *
 */
class Structs {
	static $max_size = 64;

	public const firm_info = array(
		"name"  => "",
		"ip"    => "",
		"phone" => ""
	);

	public const company_info = array(
		"entity_name"          => "",
		"entity_type"          => "",
		"registration_number"  => "",
		"registration_country" => "",
		"tax_id"               => "",
		"document_url"         => "",
		"document_page"        => ""
	);
}

?>