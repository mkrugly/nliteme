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

class DashboardTabModel extends Model
{
	private $tabIndex = null; // tab index
	private $dashboardCfgId = null; // identifies dashbaord configuration to retrieve - for future use
	/*
	 * constructor
	 */ 
	public function __construct($tabIndex=0, $dashboardCfgId = 0)
	{
		$this->tabIndex = $tabIndex;
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
	 * function returns tab index
	 */	
	public function getTabIndex()
	{
		return $this->tabIndex;
	}
	/*
	 * function returns dashboard configuration in form of widget actions for particular dashboard columns
	 * - currently fixed one configuration
	 * TODO. when user's handling is available the dashboard widgets shall be specific for each user in users table
	 */ 
	protected function getConfiguration()
	{
		// TBD. this will be read from DB where user specific dashboard is defined
		return TempDashboardConfig::getInstance()->getDashboardConfig()->getDashboardTabList()->getItem($this->tabIndex);
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
?>
