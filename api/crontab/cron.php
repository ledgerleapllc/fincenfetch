<?php
/**
 *
 * One cron to rule them all - intended to run every minute.
 * Controls all crons with one command called from crontab.
 * Meant for only one special instance among many load balanced servers.
 *
 * Add this file in your crontab:
 *
 * * * * * * php /path/to/repo/crontab/cron.php
 *
 * To manually trigger a specific cronjob, specify the name on the cli:
 *
 * $ php crontab/cron.php garbage
 *
 * @var Cron $cron Master cron controller.
 *
 */
include_once(__DIR__.'/../core.php');
include_once(BASE_DIR.'/classes/cron.php');

$target_cron = '';

if (isset($argv)) {
	$target_cron = $argv[1] ?? '';
}

$definition = array(
	array(
		"name"     => "schedule",
		"interval" => 1
	),
	array(
		"name"     => "garbage",
		"interval" => 15
	)
);

$cron = new Cron($definition);
$cron->run_crons($target_cron);
