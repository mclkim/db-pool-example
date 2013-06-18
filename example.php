<?php
require_once('DB.php');

$db = DB::getFactory()->getConnection('write');
// Columns are given seperately in order to append only the data you want from the data object
$db->insert((object)array('table' => 'foo', 'columns' => 'bar1,bar2', 'data' => (object)array('bar1' => 'item1', 'bar2' => 'item2')));
$db->update((object)array('table' => 'foo', 'columns' => 'bar1,bar2', 'where' => 'bar1=:example', 'data' => (object)array('example' => 'item1', 'bar1' => 'item1changed', 'bar2' => 'item2changed')));
$db->delete((object)array('table' => 'foo', 'where' => 'bar1=:example', 'data' => array(':example' => 'item1changed')));

?>
