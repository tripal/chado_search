<?php

namespace ChadoSearch\sql;

// Create an SQL condition that filters text from uploaded file on multiple columns instead of just one
class FileMultiColumnsCond extends Statement {

  public function __construct($columns, $file, $case_sensitive, $contains_word, $convert_to ) {
    $this->statement = "";
    foreach ($columns AS $col) {
      if ($this->statement != "") {
        $this->statement .= " OR ";
      }
      $cond = new FileCond($file, $col, $case_sensitive, $contains_word);
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
    return $this->statement;
  }
}