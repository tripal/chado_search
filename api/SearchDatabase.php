<?php

namespace ChadoSearch;

class SearchDatabase {
  
  // Get all column names for a table
  public static function getColumns ($table) {
    $sql = "SELECT column_name, data_type FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :table";
    $result = db_query($sql, array(':table' => $table));
    return SearchDatabase::resultToArray($result, 'column_name');
  }
  
  // Convert the specified column of a result table into an array
  public static function resultToArray($result) {
    $arr = array();
    while ($obj = $result->fetchObject()) {
      array_push($arr, $obj);
    }
    return $arr;
  }
}