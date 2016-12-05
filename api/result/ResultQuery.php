<?php

namespace ChadoSearch\result;

use ChadoSearch\SessionVar;
use ChadoSearch\SearchDatabase;

class ResultQuery{
  public $search_id;
  public $sql;
  private $dl_sql;
  
  function __construct($search_id, $sql) {
    $this->search_id = $search_id;
    $this->sql = $sql;
  }
  
  function addWhere ($where) {
    if ($where != NULL) {
      // Re-arrange $where to make sure the indice are in order
      $tmpwhere = array();
      foreach ($where AS $item) {
        if (trim($item)) {
          array_push($tmpwhere, $item);
        }
      }
      $where = $tmpwhere;
      $con = " WHERE ";
      for ($i = 0; $i < count($where); $i ++) {
        if ($where[$i] != "") {
          if ($i > 0 && $con != " WHERE ") {
            $con .= " AND ";
          }
          $con .= $where[$i];
        }
      }
      if ($con != ' WHERE ') {
        $this->sql .= $con;
      }
    }
    return $this;
  }
  
  function addGroupBy ($groupby) {
    $separator = '';
    if ($groupby != NULL) {
      $gb = explode(":", $groupby);
      $gcol = $gb[0];
      $gtable = $gb [1];
      $separator = "; ";
      if (count ($gb) > 2) {
        $separator = $gb [2];
      }
      $mycols = SearchDatabase::getColumns($gtable);
      $gbsql = "";
      foreach ($mycols AS $col) {
        if (!in_array($col->column_name, explode(',', $gcol))) {
          if ($col->data_type == 'text' || $col->data_type == 'character varying') { // Aggregate if the data type is text or character varing
            $gbsql .= "string_agg(distinct $col->column_name, '$separator') AS $col->column_name, ";
          } else { // get the max value for other data types
            $gbsql .= "max($col->column_name) AS $col->column_name, ";
          }
        }
      }    

      $gbsql = rtrim($gbsql, ', ');
      // Find the first top level * (i.e. the one that's not in a pair of parenthese) and replace it with aggregated column expressions
      $arr_sql = str_split($this->sql);
      $replace_sql = "";
      $parenthses = array();
      $notchanged = TRUE;
      $counter = 0;
      foreach ($arr_sql AS $char) {
        if ($char == '(') {
          array_push($parenthses, 1);
          $replace_sql .= $char;
        } else if ($char == ')') {
          array_pop($parenthses);
          $replace_sql .= $char;
        } else if ($char == '*' && $notchanged && count ($parenthses) == 0) {
          $replace_sql .= $gbsql;
          $notchanged = FALSE;
        } else {
          $replace_sql .= $char;
        }
        $counter ++;
      }
      $this->sql = $replace_sql;
      $this->sql .= " GROUP BY $gcol";
      if ($separator == "</br>") {
        $this->dl_sql = str_replace('</br>', '; ', $this->sql);
      }
    }
    return $this;
  }
  
  function appendSQL($append) {
    if ($append) {
      $this->sql .= ' ' . $append;
    }
    return $this;
  }
  
  function getSQL () {
    SessionVar::setSessionVar($this->search_id, 'sql', $this->sql);
    if ($this->dl_sql) {
      SessionVar::setSessionVar($this->search_id, 'download', $this->dl_sql);
    }
    return $this->sql;
  }
  
  function getCountSQL () {
    $csql = "SELECT count (*) FROM ($this->sql) BASE";
    return $csql;
  }
  
  function count() {
    $count_sql = $this->getCountSQL();
    $total = chado_query($count_sql)->fetchField();
    return $total;
  }
}