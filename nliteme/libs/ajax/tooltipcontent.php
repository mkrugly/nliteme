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
 * used for displaying autocomplete hints in the input text form fields
 * searches through the database table for $_GET['field'] LIKE CONCAT($_GET['term'],'%') and returns 1st four values
 * 
 * TBD. when sessions are used store precompiled query in the session to speed up
 */ 

    require_once(dirname(__FILE__).'/../dbconnect.php');
    require_once(dirname(__FILE__).'/../classes/dbconnectionfactory.php');
    require_once(dirname(__FILE__).'/../classes/dbquerybuilder.php');

	if( ! empty($_GET))
	{
		if( isset($_GET['id']) )
		{
			$id = $_GET['id'];
			$value = null;
			$table='';
			$field = "description";
			if( preg_match('/^(\w+)_(\d+)_/',$id,$matches) ) 
			{
				$key = $matches[1];
				$value = $matches[2];
				$queryCond = 'id=?';
				switch ($key) 
				{
					case 'buildid':
						$table=TABLE_PREFIX.'builds_description';
						break;
					case 'incid':
						$table=TABLE_PREFIX.'build_increments_description';
						break;							
					case 'tlid':
						$table=TABLE_PREFIX.'testlines_description';
						break;	
					case 'tsid':
						$table=TABLE_PREFIX.'testsuites_description';
						break;	
					case 'tcid':
						$table=TABLE_PREFIX.'testcases_description';
						break;	
					case 'fid':
						$table=TABLE_PREFIX.'features_description';
						break;
					case 'id':
						$table=TABLE_PREFIX.'testresults_description';
						break;													
				}
			}
			// if table exists perform search
			if( !empty($table) && !empty($value)) 
			{
				$query = new DbQueryBuilder($table, array($field));
				$query->addCondition($queryCond);
				$query->prepareQuery();
				$rs = $query->executeQuery(array($value));
				if(! $rs->EOF ) {
					$row = $rs->FetchRow();
					$str = current($row);
					if(! empty($str)) {
						$str = gzuncompress($str);
						// since row is an array with one element (can associative) get it's 1st element
						echo "<pre>".$str."</pre>";
					} else {
						echo "";
					}
				}
			} 
		}
	} 
?>
