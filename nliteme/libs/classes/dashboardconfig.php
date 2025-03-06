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
*
* DashboardConfig class defines widget parameters
* constructor argument can be an array or json string with relevant params
*/
class DashboardConfig extends JsonObject
{
	public function __construct($dashboard = null)
	{
		parent::__construct($dashboard);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{	
		foreach ($datastructure AS $key => $value) 
		{
            //print(memory_get_usage() . "\n");
			if( $value instanceof DashboardTabList ) {
				$this->setDashboardTabList( $value );
			} else if ( is_array($value) ) {
				$this->setDashboardTabList( new DashboardTabList($value) );
			} else {
				$this->setParam($key, $value);
			}
        }
	}
	
	/*
	 * function to add the an DashboardTabList object to the list
	 */  
	public function setDashboardTabList(DashboardTabList $list)
	{
		$this->setParam('dashboardtablist', $list);
	}
	
	/*
	 * function returns DashboardTabList object
	 */  
	public function getDashboardTabList()
	{
		return $this->getParam('dashboardtablist');
	}
	
	/*
	 * function returns tab name
	 */
	public function getName()
	{
		return $this->getParam('name');
	}
	
	/*
	 * function sets tab name
	 */
	public function setName($tabName)
	{
		$this->setParam('name', $tabName);
	}
}

/*
 * DashboardColumn class define the LookAndFeel and content of the column in dashboard
 */
class DashboardColumn extends JsonObject
{
	public function __construct($column = null)
	{
		parent::__construct($column);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{	
		foreach ($datastructure AS $key => $value) 
		{
            //print(memory_get_usage() . "\n");
			if( $value instanceof WidgetConfigList ) {
				$this->setWidgetConfigList( $value );
			} else if ( is_array($value) ) {
				$this->setWidgetConfigList( new WidgetConfigList($value) );
			} else {
				$this->setParam($key, $value);
			}
        }
	}
	
	/*
	 * function to add the an WidgetConfigList object to the list
	 */  
	public function setWidgetConfigList(WidgetConfigList $list)
	{
		$this->setParam('widgetconfiglist', $list);
	}
	
	/*
	 * function returns WidgetConfigList object
	 */  
	public function getWidgetConfigList()
	{
		return $this->getParam('widgetconfiglist');
	}
}

/*
 * DashboardColumnList class is a list of DashboardColumn objects
 */
class DashboardColumnList extends JsonObject
{
	public function __construct($column = null)
	{
		parent::__construct($column);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{	
		foreach ($datastructure AS $key => $value) 
		{
            //print(memory_get_usage() . "\n");
			if( $value instanceof DashboardColumn ) {
				$this->append( $value );
			} else {
				$this->append( new DashboardColumn($value) );
			}	
        }
	}
	
	/*
	 * function to add the an DashboardColumn object to the list
	 */  
	public function addItem(DashboardColumn $obj)
	{
		$this->append( $obj );
	}
	
	/*
	 * function deletes a DashboardColumn object specified by index from the list
	 */  
	public function delItem($indexOnList)
	{
		$this->delParam($indexOnList);
	}

	/*
	 * function returns DashboardColumn object for a given index
	 * otherwise null
	 */  
	public function getItem($indexOnList)
	{
		if(! empty($this[$indexOnList]) ) {
			return $this[$indexOnList];
		} else {
			return NULL;
		} 
	}
}

/*
 * DashboardTab class defines a content of the tab in the dashboard
 */
class DashboardTab extends JsonObject
{
	public function __construct($tab = null)
	{
		parent::__construct($tab);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{	
		foreach ($datastructure AS $key => $value) 
		{
            //print(memory_get_usage() . "\n");
			if( $value instanceof DashboardColumnList ) {
				$this->setDashboardColumnList( $value );
			} else if ( is_array($value) ) {
				$this->setDashboardColumnList( new DashboardColumnList($value) );
			} else {
				$this->setParam($key, $value);
			}
        }
	}
	
	/*
	 * function returns tab name
	 */
	public function getName()
	{
		return $this->getParam('name');
	}
	
	/*
	 * function sets tab name
	 */
	public function setName($tabName)
	{
		$this->setParam('name', $tabName);
	}
	
	/*
	 * function to add the an DashboardColumnList object to the list
	 */  
	public function setDashboardColumnList(DashboardColumnList $list)
	{
		$this->setParam('dashboardcolumnlist', $list);
	}
	
	/*
	 * function returns DashboardColumnList object
	 */  
	public function getDashboardColumnList()
	{
		return $this->getParam('dashboardcolumnlist');
	}
}

/*
 * DashboardTabList class is a list of DashboardTabs objects
 */
class DashboardTabList extends JsonObject
{
	public function __construct($column = null)
	{
		parent::__construct($column);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{	
		foreach ($datastructure AS $key => $value) 
		{
            //print(memory_get_usage() . "\n");
			if( $value instanceof DashboardTab ) {
				$this->append( $value );
			} else {
				$this->append( new DashboardTab($value) );
			}	
        }
	}
	
	/*
	 * function to add the an DashboardTab object to the list
	 */  
	public function addItem(DashboardTab $obj)
	{
		$this->append( $obj );
	}
	
	/*
	 * function deletes a DashboardTab object specified by index from the list
	 */  
	public function delItem($indexOnList)
	{
		$this->delParam($indexOnList);
	}

	/*
	 * function returns DashboardTab object for a given index
	 * otherwise null
	 */  
	public function getItem($indexOnList)
	{
		if(! empty($this[$indexOnList]) ) {
			return $this[$indexOnList];
		} else {
			return NULL;
		} 
	}
}

?>
