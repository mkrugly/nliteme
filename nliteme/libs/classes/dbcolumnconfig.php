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
* DbColumnConfig class defines database column parameters
* constructor argument can be an array or json string with relevant params
*
*/

class DbColumnConfig extends DbColumn
{
	public function __construct($columnSettings)
	{
		parent::__construct($columnSettings);
	}
	
	/*
	 * function returns HTML field type to be used in a form
	 */	
	public function setFieldType($fieldtype = FIELDTYPES::_SS)
	{
		$this->setParam('fieldtype', $fieldtype);
	}

	/*
	 * function returns HTML field type to be used in a form
	 */	
	public function setValueType($fieldtype = VALUETYPES::_ABS)
	{
		$this->setParam('valuetype', $fieldtype);
	}
    
	/*
	 * function sets HTML field type to be used in a searcher
	 */	
	public function setSearcherFieldType($fieldtype = FIELDTYPES::_IT_SM)
	{
		$this->setParam('searcherfieldtype', $fieldtype);
	}
	
	/*
	 * function sets array with predefined values 
	 */	
	public function setColumnPredefinedValues(array &$valueArray)
	{
		if( is_array($valueArray) && !empty($valueArray) ) {
			$this->setParam('values', $valueArray);
		} 
	}
	
	/*
	 * function returns index column name correspodning to this column e.g. tcid for tcname
	 */	
	public function setColumnIndex($indexColumnName)
	{
		$this->setParam('index', $indexColumnName);
	}	

	/*
	 * function returns true if column is to be used in the main search form
	 */
	public function is_enabled()
	{
		$temp = $this->getParam('enabled');
		if( ! empty($temp) && $temp === 'yes' ) {
			return True;
		} else {
			return False;
		}
	}

	/*
	 * function returns true if column is to be used in the main search form
	 */
	public function is_searchable()
	{
		$temp = $this->getParam('searchable');
		if( ! empty($temp) && $temp === 'yes' ) {
			return True;
		} else {
			return False;
		}
	}
	
	/*
	 * function returns true if column is to be show in the main table
	 */
	public function is_showable()
	{	
		$temp = $this->getParam('showable');
		if( ! empty($temp) && $temp === 'yes' ) {
			return True;
		} else {
			return False;
		}
	}
	
	/*
	 * function returns true if column can be iterated in the loop
	 */
	public function is_iteratable()
	{	
		$temp = $this->getParam('iteratable');
		if( ! empty($temp) && $temp === 'yes' ) {
			return True;
		} else {
			return False;
		}
	}
	
	/*
	 * function returns true if column can have predefined values in preferences table
	 */
	public function is_predefined()
	{	
		$temp = $this->getParam('predefined');
		if( ! empty($temp) && $temp === 'yes' ) {
			return True;
		} else {
			return False;
		}
	}
	
	/*
	 * function returns index column name corresponding to this column e.g. tcid for tcname
	 */	
	public function getColumnIndex()
	{
		return $this->getParam('index');
	}

	/*
	 * function returns table name to be joined
	 */	
	public function getColumnJoinTab()
	{
		return $this->getParam('jointab');
	}	
    
	/*
	 * function returns type of join for joining table
	 */	
	public function getColumnJoinType()
	{
        return $this->getParam('jointype');
	}
    
	/*
	 * function returns dbcolumn realname for the intermediate join configuration
	 */	
	public function getColumnJoinVia()
	{
		return $this->getParam('joinvia');
	}	    
    
	/*
	 * function returns array with predefined values in form index=>value
	 */	
	public function getColumnPredefinedValues()
	{
		$temp = $this->getParam('values');
		if( is_array($temp) && !empty($temp) ) {
			return $temp;
		} else {
			return null;
		}
	}
	
	/*
	 * function returns array with predefined values 
	 */	
	public function getColumnPredefinedValuebyIndex($index)
	{
		$temp = $this->getParam('values');
		if( is_array($temp) && !empty($temp) && $temp[$index] !== '' ) {
			return $temp[$index];
		} else {
			return null;
		}
	}
	
	/*
	 * function returns true if there are predefined values, else false
	 */	
	public function hasPredefinedValues()
	{
		$temp = $this->getParam('values');
		if( !empty($temp) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/*
	 * function returns true if there are predefined values, else false
	 */	
	public function hasPredefinedValue($valueToCheck)
	{
		$values = $this->getColumnPredefinedValues();
		if(! empty($values)) {
			return(in_array($valueToCheck, $values));
		} else {
			return false;
		}
	}
	
	/*
	 * function returns array key for a given predefined values, or FALSE is not found
	 */	
	public function getIndexOfPredefinedValue($valueToCheck)
	{
		$values = $this->getColumnPredefinedValues();
		if(! empty($values)) {
			return(array_search($valueToCheck, $values));
		} else {
			return false;
		}
	}
	
	/*
	 * function returns HTML field type to be used in a forms or NULL if not set
	 */	
	public function getFieldType()
	{
		$temp = $this->getParam('fieldtype');
		if( !empty($temp) ) {
			return $temp;
		} else {
			return null;
		}
	}

 	/*
	 * function returns HTML field type to be used in a forms or NULL if not set
	 */	
	public function getValueType()
	{
		$temp = $this->getParam('valuetype');
		if( !empty($temp) ) {
			return $temp;
		} else {
			return VALUETYPES::_ABS;
		}
	}
    
	/*
	 * function returns HTML field type to be used in a searcher or NULL if not set
	 */	
	public function getSearcherFieldType()
	{
		$temp = $this->getParam('searcherfieldtype');
		if( !empty($temp) ) {
			return $temp;
		} else {
			return null;
		}
	}
}

 /**
  *  SQL JOIN enum emulation 
  */
class FIELDTYPES
{
    const _SM = 'SELECT_MULTIPLE';
    const _SS = 'SELECT_SINGLE';
    const _IT = 'INPUT_TEXT';
    const _DP = 'DATE_PICKER';
    const _HL = 'HYPER_LINK';
}

class VALUETYPES
{
    const _ABS = 'ABSOLUTE';
    const _REL = 'RELATIVE';
}
 
 ?>
