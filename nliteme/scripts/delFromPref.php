<?php
/* Example script to delete the column's predefined values in the table column preferences
 * Note. This can be used for table columns configured to holds predefined values
 */
   require '../libs/include.php';
   
   
   $valsToDel = array('H1','H3','S1','Q3');
   $column = Config::getInstance()->getColumnConfig()->getDbColumnByRealName('extracolumn_0');
   if(! empty($column) && $column->is_predefined() === true && $column->is_enabled() === true) {
			print_r($column);
			$newPredefs = array();
			foreach($column->getColumnPredefinedValues() as $index => $value)
			{
				if(!in_array($value,$valsToDel))
				{
					$newPredefs[$index] = $value;
				}
			}
			if(!empty($newPredefs))
			{
				$column->setColumnPredefinedValues($newPredefs);
				//Config::getInstance()->updateColumnInColumnConfig($column);
				print_r(Config::getInstance()->getColumnConfig()->getDbColumnByRealName('extracolumn_0'));
			}
	}
?>
