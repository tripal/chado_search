<?php

namespace ChadoSearch\sql;

// Create an SQL condition that filters the result for Select widget
class SelectCond extends Statement {

  public function __construct($column, $value) {
    $this->statement = '';
    if (is_array($value)) {
      if (key_exists(0, $value)) {
        $this->statement = ''; // If 'Any' is selected, return.
        return;
      }
      $this->statement = "(";
      $counter = 0;
      foreach ($value AS $v) {
        $v = str_replace("'", "''", $v); // escape the single quote
        $this->statement .= "$column = '$v'";
        if ($counter < count($value) - 1) {
          $this->statement .= " OR ";
        }
        $counter ++;
      }
      $this->statement .= ")";
      if ($this->statement == "()") {
        $this->statement = ''; // If nothing is selected, return.
      }
    } else {
      if ($value) {
        $value = str_replace("'", "''", $value); // escape the single quote
        $this->statement = "";
        $this->statement .= "$column = '$value'";
      }
    }
  }
}