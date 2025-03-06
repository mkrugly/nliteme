<?php
/*
 * Example script to modify the names of existing test cases
 * It opens and reads input file with lines containing collon delimited old_name and new_name
 * e.g.
 * tc_1;TC_1
 * tc_2;TC_2 
 * and updates the test case databse records accordingly
 */
	require '../libs/include.php';

	$namesHash = array();
	if($argc > 1 && file_exists($argv[1]) )
	{
		$namesHash = getNames($argv[1]);
		if( !empty($namesHash) )
		{
			// create testcase model
			$tcDetailsModel = new TestCaseModel();
			// prepare find query
			$tcListQuery = new DbQueryBuilder(TABLE_PREFIX.'testcases');
			$tcListQuery->addColumns(array('tcid','tcname'));
			$tcListQuery->addCondition('tcname=?');   
			$tcListQuery->prepareQuery();
			foreach ($namesHash AS $oldName => $newName) 
			{
				print($oldName." => ".$newName."\n");
				$rs = $tcListQuery->executeQuery(array($oldName));
				if(! $rs->EOF ) 
				{
					$rec = $rs->FetchRow();
					$rec['tcname'] = $newName;
					print_r($rec);
					/*
					if(	$tcDetailsModel->saveDetails($rec) === false )
					{
						print('update failed for tcid(old tcname): '.$rec['tcid'].' ('.$oldName.')\n');
						print_r($tcDetailsModel->getMessages());
						print('\n');
					}
				*/
				}
				print("\n");
			}
		}
	} 
 
// reads input file, returns assocciative array oldName=>newName
function getNames($filename)
{
	$arr = array();
	if ($fh = fopen($filename, "r")) 
	{
		while (!feof($fh)) 
		{
			$lineArr = explode(';', trim(fgets($fh)));
			if( count($lineArr) >= 2 )
			{
			   $arr[trim($lineArr[0])] = trim($lineArr[1]);
			}
		}
	}
	return $arr;
}   

?>
