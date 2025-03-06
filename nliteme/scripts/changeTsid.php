<?php
/*
 * Example script to modify the test suite association for exisitng test results
 *
 * In the given example all the test results with test suite id 1 will be associated with a test suite id 4
 * and the test results records with test suite id 2 will be associated with a test suite id 5
 * Note. The test suite records with the given test suite ids have to exist in the data base
 */
   require '../libs/include.php';

   $trListQuery = new DbQueryBuilder(TABLE_PREFIX.'testresults');
   $trListQuery->addColumns(array('id','tsid'));
   $trListQuery->addCondition('tsid=?');
   $trListQuery->addLimit(0,10);
   $tsids = array('1'=>'4','2'=>'5');
   
   foreach ($tsids AS $key => $value)
   {
	   $resultSet = $trListQuery->executeQuery(array($key));
	   modTsid($resultSet, $value);
   }
   
function modTsid($resultSet, $newTsId)   
{
   print("New TSID: ".$newTsId."\n");
   foreach ($resultSet AS $record) 
   {
       print_r($record);
   }
   print("\n");
}

?>
