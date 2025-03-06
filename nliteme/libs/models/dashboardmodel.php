<?php

/**
***************************************************************************************************
 * @Author		Michal Krugly
 * 
 * Copyright (c) 2013 by Michal Krugly (mailto: mickrugly[at]gmail.com)
 * 
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 *   - Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 *   - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 *   - Neither the name of the Michal Krugly nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

 * DISCLAIMER:
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. 
 
**************************************************************************************************
**/

class DashboardModel extends Model
{
	private $defaultTab = null; // index of the tab to activate
	private $dashboardCfgId = null; // identifies dashbaord configuration to retrieve - for future use
	/*
	 * constructor
	 */ 
	public function __construct($defaultTab=0, $dashboardCfgId = 0)
	{
		$this->defaultTab = $defaultTab;
		$this->dashboardCfgId = $dashboardCfgId;
	}
	
	/*
	 * function to perform show details action
	 */ 
	public function show()
	{
		return $this->getConfiguration();
	}
	
	/*
	 * function returns active tab index
	 */	
	public function getDefaultTab()
	{
		return $this->defaultTab;
	}
	
	/*
	 * function returns dashboard configuration in form of widget actions for particular dashboard columns
	 * - currently fixed one configuration
	 * TODO. when user's handling is available the dashboard widgets shall be specific for each user in users table
	 */ 
	protected function getConfiguration()
	{
		// TBD. this will be read from DB where user specific dashboard is defined
		return TempDashboardConfig::getInstance()->getDashboardConfig();
	}
	
	/*
	 * function returns dashboard configuration idenifier
	 * FFU. can be used e.g. to identify the configuration in the DB
	 */ 
	protected function getCfgId()
	{
		// TBD. this will be read from DB where user specific dashboard is defined
		return $this->dashboardCfgId;
	}
}

/*
 * TBD. Temporary used, before dashboard config is placed in the DB
 */
 class TempDashboardConfig
{
	private static $instance;
	private static $dashboardConfig = null;
	private function __construct() {}
	private function __clone() {}

    /*
     * function returns a reference to Config singleton instance 
     */ 
	public static function getInstance()
	{
        if (! isset(self::$instance) ) {
            self::$instance = new TempDashboardConfig();
            self::reinitialize();
        }
        return self::$instance;
    }
    
    /*
     * function to re/initialize the singleton with settings read from TABLE_PREFIX."preferences"
     */ 
    public static function reinitialize()
    {
		// read preferences table to the temp array
		if(empty(self::$dashboardConfig))
		{
			$dashboardConfigString = '{"dashboardtablist":{"0":{"dashboardcolumnlist":{"0":{"widgetconfiglist":{"0":{"name":"TestActivities","url":"action=com.nliteme.TestActivities","useiframe":"no","title":null},"1":{"name":"TestActivities_TS3","url":"action=com.nliteme.TestActivities&widget=TestActivities_TS3","useiframe":"no","title":"TS3 Latest Activities"}}},"1":{"widgetconfiglist":{"0":{"name":"TestActivities_TS1","url":"action=com.nliteme.TestActivities&widget=TestActivities_TS1","useiframe":"no","title":"TS1 Latest Activities"},"1":{"name":"TestActivities_TS4","url":"action=com.nliteme.TestActivities&widget=TestActivities_TS4","useiframe":"no","title":"TS4 Latest Activities"}}},"2":{"widgetconfiglist":{"0":{"name":"TestActivities_TS2","url":"action=com.nliteme.TestActivities&widget=TestActivities_TS2","useiframe":"no","title":"TS2 Latest Activities"},"1":{"name":"TestActivities_TS5","url":"action=com.nliteme.TestActivities&widget=TestActivities_TS5","useiframe":"no","title":"TS5 Latest Activities"}}}},"name":"General"},"1":{"0":{"0":{"widgetconfiglist":{"0":{"name":"StatusPage","url":"http:\/\/status_page_address","useiframe":"yes","title":"Status"}}}},"name":"StatusPage"}},"name":"Regression Tests"}';
			self::$dashboardConfig = new DashboardConfig($dashboardConfigString);
		}
	}
	
	/*
	 * function returns an WidgetConfigList object containing WidgetConfig
	 */ 
	public function getDashboardConfig()
	{
		return self::$dashboardConfig;
	}
}

?>
