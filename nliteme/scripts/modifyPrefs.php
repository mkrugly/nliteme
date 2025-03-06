<?php
/*
 * Example script to customize table columns preferences 
 * e.g. enable/disable columns, make them showable (i.e. visible on the web page), make them searchable (i.e. visible and selectable in the search forms).
 * NOTE. Modifications done here without understanding may cause a wrong behavior of the application
 *
 */
	require '../libs/include.php';
	
	$columnConfig = Config::getInstance()->getColumnConfig('testresults');
	
	print_r($columnConfig);
	$columnConfig->getDbColumnByRealName('extracolumn_2')->setParam('enabled','yes');
	$columnConfig->getDbColumnByRealName('extracolumn_2')->setParam('searchable','yes');
    $columnConfig->getDbColumnByRealName('extracolumn_2')->setParam('showable','yes');
	$columnConfig->getDbColumnByRealName('extracolumn_2')->setParam('iteratable','yes');

	Config::getInstance()->updateColumnConfig($columnConfig, 'testresults');
	print_r(Config::getInstance()->getColumnConfig('testresults'));

?>
