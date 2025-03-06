<?php
/*
 * Example script to generate the dashboard layout and fill it with the preconfigured widgets
 * Note. The referenced widgets have to exist in the WidgetConfig (see documentation for details)
 * In this exa
 */
   require '../libs/include.php';
	
	$tabArr = array();
	
	// TAB1
	$wCL_0 = new WidgetConfigList(array( getWidgetConfigStripped(TempConfig::getInstance()->getWidgetConfigList()->getItemByName('TestActivities'))
										,getWidgetConfigStripped(TempConfig::getInstance()->getWidgetConfigList()->getItemByName('TestActivities_TS3'))));
	$wCL_1 = new WidgetConfigList(array( getWidgetConfigStripped(TempConfig::getInstance()->getWidgetConfigList()->getItemByName('TestActivities_TS1'))
										,getWidgetConfigStripped(TempConfig::getInstance()->getWidgetConfigList()->getItemByName('TestActivities_TS4'))));	
	$wCL_2 = new WidgetConfigList(array( getWidgetConfigStripped(TempConfig::getInstance()->getWidgetConfigList()->getItemByName('TestActivities_TS2'))
										,getWidgetConfigStripped(TempConfig::getInstance()->getWidgetConfigList()->getItemByName('TestActivities_TS5'))));
	$dColumn_1 = new DashboardColumn(array($wCL_0));
	$dColumn_2 = new DashboardColumn(array($wCL_1));
	$dColumn_3 = new DashboardColumn(array($wCL_2));
	$dColumnList = new DashboardColumnList(array($dColumn_1));
	$dColumnList->addItem($dColumn_2);
	$dColumnList->addItem($dColumn_3);  
	$dTab_1 = new DashboardTab(array($dColumnList)); 
	$dTab_1->setName('General');
	
	array_push($tabArr,$dTab_1);

	// TAB2 
	$wCL_0 = new WidgetConfigList(array( getWidgetConfigStripped(TempConfig::getInstance()->getWidgetConfigList()->getItemByName('StatusPage'))));
	$dColumn_1 = new DashboardColumn(array($wCL_0));
	$dColumnList = new DashboardColumn(array($dColumn_1));   
	$dTab_1 = new DashboardTab(array($dColumnList)); 
	$dTab_1->setName('StatusPage');
	
	array_push($tabArr,$dTab_1);
	
	$dTabList = new DashboardTabList($tabArr); 
	$dashboard = new DashboardConfig(array($dTabList));
	$dashboard->setName("Regression Tests");
	print_r($dashboard->encode());
print("\n");


function getWidgetConfigStripped(WidgetConfig& $widgetConfig)
{
	$newWdConfig = new WidgetConfig(null);
	$newWdConfig->setName($widgetConfig->getName());
	$newWdConfig->setUrl($widgetConfig->getUrl());
	$newWdConfig->setUseIframe($widgetConfig->getUseIframe());
	$newWdConfig->setTitle($widgetConfig->getTitle());
	return $newWdConfig;
}

?>
