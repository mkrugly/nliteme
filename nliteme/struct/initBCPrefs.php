<?php

   require '../libs/include.php';


$bcPref = Config::getInstance()->getPreference('buildcompare');
if(empty($bcPref))
{
   print("BuildCompare preferences are not set\n");
} else {
   print("BuildCompare preferences are set. Removing existing.\n");
   Config::getInstance()->unsetPreference('buildcompare');
}

$bcC = new BuildCompareConfig();
// $bcC->setSortingColumns(array('tcname','tlname','tsname','fname','extracolumn_0'));
// $bcC->setGroupingColumns(array('increment','build','tsname','tlname','fname', 'extracolumn_0'));

$bcCByList = new BuildCompareCompareByConfigList();

$bcCBy = new BuildCompareCompareByConfig();
$bcCBy->setCompareByCfgKey('tcverdict');
$bcCBy->setCompareByColumn('tcverdict');
$bcCBy->setSortingColumns(array('tcname','tlname','tsname','fname','extracolumn_0'));
$bcCBy->setGroupingColumns(array());
$bcCByList->setCompareByConfig($bcCBy);

$bcCBy = new BuildCompareCompareByConfig();
$bcCBy->setCompareByCfgKey('tstcverdict');
$bcCBy->setCompareByColumn('tcverdict');
$bcCBy->setSortingColumns(array('tcname','tlname', 'tsname','extracolumn_0'));
$bcCBy->setGroupingColumns(array());
$bcCByList->setCompareByConfig($bcCBy);

$bcCBy = new BuildCompareCompareByConfig();
$bcCBy->setCompareByCfgKey('featuretcverdict');
$bcCBy->setCompareByColumn('tcverdict');
$bcCBy->setSortingColumns(array('tcname','tlname', 'fname','extracolumn_0'));
$bcCBy->setGroupingColumns(array());
$bcCByList->setCompareByConfig($bcCBy);

$bcCBy = new BuildCompareCompareByConfig();
$bcCBy->setCompareByCfgKey('duration');
$bcCBy->setCompareByColumn('duration');
$bcCBy->setSortingColumns(array('tcname','tlname','tsname','fname','extracolumn_0'));
$bcCBy->setGroupingColumns(array());
$bcCByList->setCompareByConfig($bcCBy);

$bcCBy = new BuildCompareCompareByConfig();
$bcCBy->setCompareByCfgKey('passrate');
$bcCBy->setCompareByColumn('passrate');
$bcCBy->setSortingColumns(array('tlname','extracolumn_0','fname','tsname'));
$bcCBy->setGroupingColumns(array('increment','build','fname','tsname','tlname','extracolumn_0'));
$bcCByList->setCompareByConfig($bcCBy);

$bcC->setCompareByConfigList($bcCByList);

//print_r($bcC);
//print("\n");
//print_r($bcC->encode());
//print("\n");

//$bcCString = '{"sortingcolumns":{"0":"tcname","1":"tlname","2":"tsname","3":"extracolumn_0"},"groupingcolumns":{"0":"increment","1":"build","2":"tsname","3":"tlname","4":"extracolumn_0"},"comparebycolumns":{"tcverdict":{"comparebycolumn":"tcverdict","sortingcolumns":{"0":"tcname","1":"tlname","2":"tsname","3":"extracolumn_0"},"groupingcolumns":{}},"duration":{"comparebycolumn":"duration","sortingcolumns":{"0":"tcname","1":"tlname","2":"tsname","3":"extracolumn_0"},"groupingcolumns":{}},"passrate":{"comparebycolumn":"passrate","sortingcolumns":{},"groupingcolumns":{"0":"increment","1":"build","2":"tsname","3":"tlname","4":"extracolumn_0"}}}}';
//$bcC1 = new BuildCompareConfig($bcCString);
//print_r($bcC1);
//print("\n");
//print_r($bcC1->encode());
//print("\n");

Config::getInstance()->setPreference('buildcompare',$bcC->encode());

$bcPref = Config::getInstance()->getPreference('buildcompare');

if(empty($bcPref))
{
   print("BuildCompare preferences are not set\n");
} else {
   print_r($bcPref);
}


print("\n");
?>
