<?php

namespace ChadoSearch\sql;

class Statement {

  // In the sub-class, generate the the SQL in its constructor and store it in $this->statement
  // so the SQL statement can be returned when getStatement() is called
  public $statement;
  
  // Return a SQL statement
  public function getStatement () {
    return $this->statement;
  }
  
  // Rewrite SQL, replace all place holders with supplied variables
  public static function rewrite($sql) {
    $args = func_get_args();
    array_shift($args);
    $sql = db_prefix_tables($sql);
    if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
      $args = $args[0];
    }
    _db_query_callback($args, TRUE);
    $sql = preg_replace_callback(DB_QUERY_REGEXP, '_db_query_callback', $sql);
    return $sql;
  }
  
  // Pair the SQL for two arrays that have the same elements by 'AND' or 'OR'.
  // Return an array with paired SQL conditions
  public static function pairConditions ($arr1, $arr2, $concatbyOR) {
    $conditions = array();
    $conj = 'AND';
    if ($concatbyOR) {
      $conj = 'OR';
    }
    $con = "";
    for ($i = 0; $i < count($arr1); $i ++) {
      if ($arr1[$i]) {
        $con = "(" . $arr1[$i];
        if ($arr2[$i]) {
          $con .= " $conj " . $arr2[$i];
        }
        $con .= ")";
      }
      if ($con) {
        $conditions[$i] = $con;
        $con = null;
      }
    }
    return $conditions;
  }
}