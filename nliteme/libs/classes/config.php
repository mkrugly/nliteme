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

* Config class is a singleton that contains complete configuration read from the database
* as well as some basic database db queries as functions
* 
 */

class Config
{
	private static $instance;
	private static $columnlist = array();
	private static $settings;
	private static $preferences = array();
	private function __construct() {}
	private function __clone() {}

    /*
     * function returns a reference to Config singleton instance 
     */ 
	public static function getInstance()
	{
        if (! isset(self::$instance) ) {
            self::$instance = new Config();
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
		$arr = array();
		self::$preferences = array();
		$selectQuery = new DbQueryBuilder(TABLE_PREFIX."preferences");
		$selectQuery->addColumns(array('name','params'));
		$result = $selectQuery->executeQuery();
		if($result)
		{
			foreach($result->GetRows() AS $row)
			{
			  if(isset($row['params']))
			  {
				if( $row['name'] == COLUMNCONFIG_PREFIX.'testresults' 
					 || $row['name'] == COLUMNCONFIG_PREFIX.'builds'
					 || $row['name'] == COLUMNCONFIG_PREFIX.'build_increments'
					 || $row['name'] == COLUMNCONFIG_PREFIX.'testsuites'
					 || $row['name'] == COLUMNCONFIG_PREFIX.'testcases'
					 || $row['name'] == COLUMNCONFIG_PREFIX.'testlines'
					 || $row['name'] == COLUMNCONFIG_PREFIX.'features')
				{
					self::$columnlist[$row['name']] = new DbColumnConfigList($row['params']);
				} else if ($row['name'] == 'settings') {
					self::$settings = new Settings($row['params']);
				} else {
					self::$preferences[$row['name']] = $row['params'];
				}
			  }
			}
		}
	}
	
	/*
	 * function returns an DbColumnList object containing columns configuration
	 */ 
	public function getColumnConfig($tablename=null)
	{
		if( empty( $tablename) ) { $tablename='testresults'; } // for backwards compatibility
		$recordname=COLUMNCONFIG_PREFIX.$tablename;
		if(isset(self::$columnlist[$recordname])) {
			return self::$columnlist[$recordname];
		} else {
			return null;
		}
	}
	
	/*
	 * function returns an associative array holding general configuration settings
	 */ 
	public function getSettings()
	{
		return self::$settings;
	}

	/*
	 * function returns a preference value for a given preference's name
	 */ 
	public function getPreference($name)
	{
		if(!empty($name) && isset(self::$preferences[$name])) {
			return self::$preferences[$name];
		} else {
			return null;
		}	
	}
	
	/*
	 * function returns a preference value for a given preference's name
	 */ 
	public function getPreferences()
	{
		return self::$preferences;	
	}
	
	/*
	 * function to modify ColumnConfiguration in preferences
	 * returns TRUE if update successfull, error string/list otherwise
	 */
	public function updateColumnConfig(DbColumnConfigList $columnConfig, $tablename=null)
	{
		if( empty( $tablename) ) { $tablename='testresults'; } // for backwards compatibility
		$recordname=COLUMNCONFIG_PREFIX.$tablename;
		if(! empty($columnConfig) ) {
			if(! empty(self::getColumnConfig($tablename))) {
				$result = self::updateRecord($recordname, $columnConfig->encode() );
			} else {
				$result = self::insertRecord($recordname, $columnConfig->encode() );
			}
		} else {
			$result = get_class()."::".__FUNCTION__.' '.Text::_('NULL_AGRUMENT');
			Tracer::getInstance()->log($result, LOGLEV_ERROR);
		}
		return $result;
	}
	
	/*
	 * function to modify ColumnConfiguration in preferences
	 * returns TRUE if update successfull, error string/list otherwise
	 */
	public function updateColumnInColumnConfig(DbColumnConfig $column, $tablename=null)
	{
		if(! empty($column) ) {
			$realname = $column->getColumnRealName();
			if (! empty($realname)) {
				self::getColumnConfig($tablename)->addColumn($column);
				return self::updateColumnConfig(self::getColumnConfig($tablename), $tablename);
			} else {
				$result = get_class()."::".__FUNCTION__.' '.Text::_('NULL_REALNAME');
				Tracer::getInstance()->log($result, LOGLEV_ERROR);
			}

		} else {
			$result = get_class()."::".__FUNCTION__.' '.Text::_('NULL_AGRUMENT');
			Tracer::getInstance()->log($result, LOGLEV_ERROR);
		}
		return $result;
	}

	/*
	 * function to modify Settings in preferences
	 * returns TRUE if update successfull, error string/list otherwise
	 */
	public function updateSettings(Settings $settings)
	{
		if(! empty($settings) ) {
			$result = self::updateRecord('settings', $settings->encode() );
		} else {
			$result = get_class()."::".__FUNCTION__.' '.Text::_('NULL_AGRUMENT');
			Tracer::getInstance()->log($result, LOGLEV_ERROR);
		}
		return $result;
	}

	/*
	 * function to add/modify preference entries in the DB
	 * returns TRUE if update successfull, error string/list otherwise
	 * Note. $value has to be provided in the final format e.g. json string etc.
	 */
	public function setPreference($name, $value)
	{
		if(!empty($name) && !empty($value)) {
			if(isset(self::$preferences[$name])) {
				$result = self::updateRecord($name, $value );
			} else {
				$result = self::insertRecord($name, $value );
			}
		} else {
			$result = get_class()."::".__FUNCTION__.' '.Text::_('NULL_AGRUMENT');
			Tracer::getInstance()->log($result, LOGLEV_ERROR);
		}
		return $result;
	}

	/*
	 * function to remove preference entries in the DB
	 * returns TRUE if update successfull, error string/list otherwise
	 */
	public function unsetPreference($name)
	{
		if(!empty($name) && isset(self::$preferences[$name])) {
			$result = self::deleteRecord($name);
		} else {
			$result = get_class()."::".__FUNCTION__.' '.Text::_('NULL_AGRUMENT');
			Tracer::getInstance()->log($result, LOGLEV_ERROR);
		}
		return $result;
	}
	
	private function updateRecord($recordName, $recordParams)
	{
		$rec = new ConfigRecord($recordName, $recordParams);
		$ret = $rec->update();
		if($ret === true)
		{
			self::reinitialize();
		}
		return $ret;
	}
	
	private function insertRecord($recordName, $recordParams)
	{
		$rec = new ConfigRecord($recordName, $recordParams);
		$ret = $rec->insert();
		if($ret === true)
		{
			self::reinitialize();
		}
		return $ret;
	}
	
	private function deleteRecord($recordName)
	{
		$rec = new ConfigRecord($recordName);
		$ret = $rec->delete();
		if($ret === true)
		{
			self::reinitialize();
		}
		return $ret;
	}	
}

/*
 * Class Settings hold the general preferences of the website read from preferences table
 * e.g. Number Of build to return 
 * 
 */ 
class Settings extends JsonObject
{
	public function __construct($settings = null)
	{
		parent::__construct($settings);
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

class ConfigRecord extends DbRecord
{
	public function __construct($prefName, $prefValue = null)
	{
		parent::__construct(TABLE_PREFIX.'preferences', 'name', null);
		// calculate md5 hash value if 'build' column value set
		$this->setName($prefName);
		if(! empty($prefValue) )
		{
			$this->setValue($prefValue);
		}
	}

	/*
	 * function defines how the object will look like
	 */ 
	//protected function set($dbColumnList)
	//{
	//	parent::set($dbColumnList);
	//}
	
	/*
	 * fake function to fullfil DbRecord Class requirements (abstarct function)
	 */
	public function getId()
	{
	}

	/*
	 * fake function to fullfil DbRecord Class requirements (abstarct function)
	 */	
	public function getIdColumnName()
	{
	}	
	
	/*
	 * fake function to fullfil DbRecord Class requirements (abstarct function)
	 */
	public function getHash()
	{
	}
	
	/*
	 * fake function to fullfil DbRecord Class requirements (abstarct function)
	 */
	public function getIdColumn()
	{
	}
	
	/*
	 * fake function to fullfil DbRecord Class requirements (abstarct function)
	 */
	public function getNameColumn()
	{
	}

	/*
	 * fake function to fullfil DbRecord Class requirements (abstarct function)
	 */	
	public function getNameColumnName()
	{
	}	
	
	/*
	 * function returns a list of columns that can be defined for modification of DB table
	 */
	public function getAllowedColumns()
	{
		$allowedColumns = array('name', 'params');
		return $allowedColumns;
	}

	/*
	 * function returns the list of ignored columns i.e. columns that shall not be 
	 * added to this DB table, but may be needed in the object for other purposes e.g. for updating another object
	 */ 
	public function getIgnoredColumns()
	{
		$ignoredColumns = array();
		return $ignoredColumns;
	}

	/*
	 * function validates if the Build exists in db
	 */
	public function is_inDb()
	{
		$selectQuery = new DbQueryBuilder($this->getTableName(), array('name'));
		$selectQuery->addCondition("name='".$this->getName()."'");
		$rs = $selectQuery->executeQuery();
		if(! $rs->EOF ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function setName($value)
	{
		$this->setDbColumnValue('name', $value);
	}
	
	public function setValue($value)
	{
		$this->setDbColumnValue('params', $value);
	}
	
	public function getName()
	{
		return $this->getDbColumnValue('name');
	}
	
	public function getValue()
	{
		return $this->getDbColumnValue('params');
	}
}

?>
