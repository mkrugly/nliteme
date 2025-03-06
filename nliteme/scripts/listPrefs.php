<?php
/*
 * Script to fetch and print out the table columns preferences for a given database table
 */
	require '../libs/include.php';
	$name = '';
	if($argc > 1)
	{
		$name = $argv[1];
	}
	if(empty($name))
	{
		$preamble = 'Fetching preferences';
		$pref_cfg = Config::getInstance()->getPreferences();
	}
	else
	{
		$preamble = 'Fetching column config for '.$name;
		$pref_cfg = Config::getInstance()->getColumnConfig($name);
		if($pref_cfg === null)
		{
			$preamble = 'Fetching preference for '.$name;
			$pref_cfg = Config::getInstance()->getPreference($name);
		}
		if($pref_cfg === null)
		{
			$preamble = 'Fetching general settings';
			$pref_cfg = Config::getInstance()->getSettings();
		}		
	}
	
	print($preamble."\n");
	print_r($pref_cfg);
?>
