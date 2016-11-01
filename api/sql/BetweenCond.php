<?php

namespace ChadoSearch\sql;

// Create an SQL condition that filters the result for the Between widget
class BetweenCond extends Statement {

  public function __construct($column1, $value1, $column2, $value2, $cast2real = FALSE) {
    $this->statement = '';
    if ($value1 != "" && !is_numeric($value1)) {
      drupal_set_message ("'$value1' is not a numeric value. Condition not applied due to invalid input.", 'error');
      $this->statement = '';
    }
    if ($value2 != "" && !is_numeric($value2)) {
      drupal_set_message ("'$value2' is not a numeric value. Condition not applied due to invalid input.", 'error');
      $this->statement = '';
    }
    if ($cast2real) {
      if (trim($value1) != "" && trim($value2) != "") {
        $this->statement = "($column1 >= CAST ($value1 AS real) AND $column2 <= CAST ($value2 AS real))";
      } else if (trim($value1) != "") {
        $this->statement = "$column1 >= CAST($value1 AS real)";
      } else if (trim($value2) != "") {
        $this->statement = "$column2 <= CAST($value2 AS real)";
      }
    }
    else {
      if (trim($value1) != "" && trim($value2) != "") {
        $this->statement = "($column1 >= $value1 AND $column2 <= $value2)";
      } else if (trim($value1) != "") {
        $this->statement = "$column1 >= $value1";
      } else if (trim($value2) != "") {
        $this->statement = "$column2 <= $value2";
      }  
    }
    
  }
}