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
/*
 * Abstract class that encapsulates the behaviour specific to widget controler
 * e.g. retriving the WidgetConfiguration from DB etc.
 * 
 * All Widget controllers shall derived from that class
 */ 

abstract class WidgetController extends Controller
{
	private $widgetConfig = null;
	/*
	 * constructor
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->setWidgetConfig();
	}

	/*
	 * function define a default action
	 * has to be added to each controller
	 */ 
	public function defaultAction()
	{
	}
	
	/*
	 * function retrives correspoding WidgetConfig object 
	 * or null is does not exists
	 */ 
	protected function getWidgetConfig()
	{
		return $this->widgetConfig;
	}
	
	/*
	 * function returns an WidgetConfig from preferences in DB
	 * TBD. currently uses TempConfig, but shall be changed to Config as soon as
	 * the preferences table contains widget config
	 */ 
	private function getWidgetConfigByName($name)
	{
		return TempConfig::getInstance()->getWidgetConfigList()->getItemByName($name);
	}

	/*
	 * function sets $this->widgetConfig to sutiable WidgetConfig object from WidgetConfigList stored in preferences in DB
	 * it first tries to get the config using $_GET['widget'] argument as WidgetConfig name
	 * if not found it tries to get the config using parsed controller class name
	 */ 	
	private function setWidgetConfig()
	{
		// get WidgetConfig based on $_GET['widget'] argument
		if(isset($_GET['widget']))
		{
			$this->widgetConfig = $this->getWidgetConfigByName($_GET['widget']);
		}
		// if it did not work so try to use class name
		if(empty($this->widgetConfig))
		{
			$this->widgetConfig = $this->getWidgetConfigByName(preg_replace('/Controller/', '', get_class($this)));
		}	
	}
}


/*
 * TBD. Temporary used, before widget config is places in the DB
 */
 class TempConfig
{
	private static $instance;
	private static $widgets = null;
	private function __construct() {}
	private function __clone() {}

    /*
     * function returns a reference to Config singleton instance 
     */ 
	public static function getInstance()
	{
        if (! isset(self::$instance) ) {
            self::$instance = new TempConfig();
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
		if(empty(self::$widgets))
		{
			$widgetParams = new WidgetParams(null);
			$widgetParams->setParam('numOfDays', '5');
			$widgetParams->setParam('numOfBuilds', '10');
			$widgetParams->setParam('groupingColumns', array('increment','build','tsname','tlname','extracolumn_0'));
			$widgetConf = new WidgetConfig(null);
			$widgetConf->setName('TestActivities');
			$widgetConf->setUrl('action=com.nliteme.TestActivities');
			$widgetConf->setUseIframe('no');
			$widgetConf->setWidgetParams($widgetParams);
		
			$widgetParams2 = new WidgetParams(null);
			$widgetParams2->setParam('numOfDays', '3');
			$widgetParams2->setParam('numOfBuilds', '5');
			$widgetParams2->setParam('groupingColumns', array('increment','build','tsname','tlname','extracolumn_0'));
			$widgetParams2->setParam('staticConditions', array('tsid'=>array(1,6)));
			$widgetConf2 = new WidgetConfig(null);
			$widgetConf2->setName('TestActivities_TS1');
			$widgetConf2->setTitle('TS1 Latest Activities');
			$widgetConf2->setUrl('action=com.nliteme.TestActivities&widget=TestActivities_TS1');
			$widgetConf2->setUseIframe('no');
			$widgetConf2->setWidgetParams($widgetParams2);
			
			$widgetParams3 = new WidgetParams(null);
			$widgetParams3->setParam('numOfDays', '3');
			$widgetParams3->setParam('numOfBuilds', '5');
			$widgetParams3->setParam('groupingColumns', array('increment','build','tsname','tlname','extracolumn_0'));
			$widgetParams3->setParam('staticConditions',array('tsid'=>array(2,7)));
			$widgetConf3 = new WidgetConfig(null);
			$widgetConf3->setName('TestActivities_TS2');
			$widgetConf3->setTitle('TS2 Latest Activities');
			$widgetConf3->setUrl('action=com.nliteme.TestActivities&widget=TestActivities_TS2');
			$widgetConf3->setUseIframe('no');
			$widgetConf3->setWidgetParams($widgetParams3);

			$widgetParams4 = new WidgetParams(null);
			$widgetParams4->setParam('numOfDays', '3');
			$widgetParams4->setParam('numOfBuilds', '5');
			$widgetParams4->setParam('groupingColumns', array('increment','build','tsname','tlname','extracolumn_0'));
			$widgetParams4->setParam('staticConditions',array('tsid'=>array(3,8)));
			$widgetConf4 = new WidgetConfig(null);
			$widgetConf4->setName('TestActivities_TS3');
			$widgetConf4->setTitle('TS3 Latest Activities');
			$widgetConf4->setUrl('action=com.nliteme.TestActivities&widget=TestActivities_TS3');
			$widgetConf4->setUseIframe('no');
			$widgetConf4->setWidgetParams($widgetParams4);
			
			$widgetParams5 = new WidgetParams(null);
			$widgetParams5->setParam('numOfDays', '3');
			$widgetParams5->setParam('numOfBuilds', '5');
			$widgetParams5->setParam('groupingColumns', array('increment','build','tsname','tlname','extracolumn_0'));
			$widgetParams5->setParam('staticConditions',array('tsid'=>array(4,9)));
			$widgetConf5 = new WidgetConfig(null);
			$widgetConf5->setName('TestActivities_TS4');
			$widgetConf5->setTitle('TS4 Latest Activities');
			$widgetConf5->setUrl('action=com.nliteme.TestActivities&widget=TestActivities_TS4');
			$widgetConf5->setUseIframe('no');
			$widgetConf5->setWidgetParams($widgetParams5);

			$widgetParams6 = new WidgetParams(null);
			$widgetParams6->setParam('numOfDays', '3');
			$widgetParams6->setParam('numOfBuilds', '5');
			$widgetParams6->setParam('groupingColumns', array('increment','build','tsname','tlname','extracolumn_0'));
			$widgetParams6->setParam('staticConditions',array('tsid'=>array(5,10)));
			$widgetConf6 = new WidgetConfig(null);
			$widgetConf6->setName('TestActivities_TS5');
			$widgetConf6->setTitle('TS5 Latest Activities');
			$widgetConf6->setUrl('action=com.nliteme.TestActivities&widget=TestActivities_TS5');
			$widgetConf6->setUseIframe('no');
			$widgetConf6->setWidgetParams($widgetParams6);
				
			self::$widgets = new WidgetConfigList(array( $widgetConf, $widgetConf2, $widgetConf3, $widgetConf4, $widgetConf5, $widgetConf6 ));

			// custom part
			
			$widgetParams = new WidgetParams(null);
			$widgetParams->setParam('height', 800);
			$widgetConf = new WidgetConfig(null);
			$widgetConf->setName('StatusPage');
			$widgetConf->setTitle('Status');
			$widgetConf->setUrl('http://status_page_address');
			$widgetConf->setUseIframe('yes');
			$widgetConf->setWidgetParams($widgetParams);
			self::$widgets->addItem($widgetConf);



		}
	}
	
	/*
	 * function returns an WidgetConfigList object containing WidgetConfig
	 */ 
	public function getWidgetConfigList()
	{
		return self::$widgets;
	}
}
?>
