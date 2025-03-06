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
* WidgetConfig class defines widget parameters
* constructor argument can be an array or json string with relevant params
*/
class WidgetConfig extends JsonObject
{
	public function __construct($config)
	{
		parent::__construct($config);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{
		foreach ($datastructure AS $key => $value) 
		{
			if($key === 'widgetparams' && !($value instanceof WidgetParams)) {
				$this->setParam($key, new WidgetParams($value));
			} else if($key === 'widgethiddenparams' && !($value instanceof WidgetParams)) {
				$this->setParam($key, new WidgetParams($value));
			} else {
				$this->setParam($key, $value);
			}
        }
	}
	
	/*
	 * function returns widget name
	 */
	public function getName()
	{
		return $this->getParam('name');
	}
	
	/*
	 * function sets widget name
	 */
	public function setName($widgetName)
	{
		$this->setParam('name', $widgetName);
	}

	/*
	 * function returns widget name
	 */
	public function getTitle()
	{
		return $this->getParam('title');
	}
	
	/*
	 * function sets widget name
	 */
	public function setTitle($widgetTitle)
	{
		$this->setParam('title', $widgetTitle);
	}
		
	/*
	 * function returns widget URL
	 */
	public function getUrl()
	{
		return $this->getParam('url');
	}
	
	/*
	 * function sets widget URL
	 */
	public function setUrl($widgetUrl)
	{
		$this->setParam('url', $widgetUrl);
	}
	
	/*
	 * function returns widget URL
	 */
	public function getUseIframe()
	{
		return $this->getParam('useiframe');
	}
	
	/*
	 * function sets useIFrame flag to a given value
	 */
	public function setUseIframe($yesNo)
	{
		$this->setParam('useiframe', $yesNo);
	}
	
	/*
	 * function returns true if useIFrame is set to "yes", false otherwise
	 */
	public function is_useiframe()
	{
		$temp = $this->getUseIframe();
		if( ! empty($temp) && $temp === 'yes' ) {
			return True;
		} else {
			return False;
		}
	}
	
	/*
	 * function returns WidgetParams object containg widget specific parameters 
	 * i.e. which are allowed to be modified by the user
	 */	
	public function getWidgetParams()
	{
		return $this->getParam('widgetparams');
	}
	
	/*
	 * WidgetParams object containg widget specific parameters
	 */
	public function setWidgetParams(WidgetParams $widgetparams)
	{
		$this->setParam('widgetparams', $widgetparams);
	}
	
	/*
	 * function returns WidgetParams object containg widget specific hidden parameters
	 * hidden parameters are the ones which are not to be configurable by the user
	 */		
	public function getWidgetHiddenParams()
	{
		return $this->getParam('widgethiddenparams');
	}
	
	/*
	 * function sets WidgetParams object containg widget specific hidden parameters
	 */
	public function setWidgetHiddenParams(WidgetParams $widgetparams)
	{
		$this->setParam('widgethiddenparams', $widgetparams);
	}	
}

/*
 * WidgetConfigList class is a list of WidgetConfig objects
 */
class WidgetConfigList extends JsonObject
{
	public function __construct($widgetlist = null)
	{
		parent::__construct($widgetlist);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{	
		foreach ($datastructure AS $key => $value) 
		{
            //print(memory_get_usage() . "\n");
			if( $value instanceof WidgetConfig ) {
				$this->append( $value );
			} else {
				$this->append( new WidgetConfig($value) );
			}	
        }
	}
	
	/*
	 * function to add the an WidgetConfig object to the list
	 */  
	public function addItem(WidgetConfig $widgetObj)
	{
		$this->append( $widgetObj );
	}
	
	/*
	 * function deletes a WidgetConfig object specified by index from the list
	 */  
	public function delItem($widgetIndexOnList)
	{
		$this->delParam($widgetIndexOnList);
	}

	/*
	 * function returns WidgetConfig object for a given index
	 * otherwise null
	 */  
	public function getItem($widgetIndexOnList)
	{
		if(! empty($this[$widgetIndexOnList]) ) {
			return $this[$widgetIndexOnList];
		} else {
			return NULL;
		} 
	}
	
	/*
	 * function returns WidgetConfig object for a given name if exists
	 * otherwise null
	 */  
	public function getItemByName($name)
	{
		$iterator = $this->getIterator();
		while( $iterator->valid() )
		{
			if( $iterator->current()->getName() == $name )
			{
				return $iterator->current();
			}
			$iterator->next();
		}
		return NULL;
		
		/* TBD when widgetConfigList is assoc list of WidgerConfig objects identified by WidgetConfig Name
		if(! empty($this[$name]) ) {
			return $this[$name];
		} else {
			return NULL;
		} 
		*/ 
	}	
}

/*
 * WidgetParams class contains a widget specific parameters, that can be used by widget controler
 */
class WidgetParams extends JsonObject
{
	public function __construct($params)
	{
		parent::__construct($params);
	}
	
	/*
	 * function defines how the object will look like
	 */ 
	protected function set($datastructure)
	{
		foreach ($datastructure AS $key => $value) 
		{
			$this->setParam($key, $value);
        }
	}
}

?>
