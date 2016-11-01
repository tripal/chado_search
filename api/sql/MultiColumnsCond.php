<?php

namespace ChadoSearch\sql;

// Create an SQL condition that filters text on multiple columns instead of just one
class MultiColumnsCond extends Statement {

  public function __construct($columns, $op, $value, $case_sensitive, $convert_to ) {
    $this->statement = "";
    foreach ($columns AS $col) {
      if ($this->statement != '') {
        $this->statement .= " OR ";
      }
      $cond = new ColumnCond($col, $op, $value, $case_sensitive);
      $this->statement .= $cond->getStatement();
    }
    if ($this->statement != "") {
      if ($convert_to) {
        $cvrt = explode(":", $convert_to);
        $cvrt_column = $cvrt[0];
        $cvrt_table = $cvrt[1];
        $this->statement = "(" . $cvrt_column . " IN (SELECT $cvrt_column FROM { $cvrt_table } WHERE ($this->statement)))";
      } else {
        $this->statement = "(" . $this->statement . ")";
      }
    }
  }
}