<?php

namespace ChadoSearch\sql;

// Create an SQL condition that filters the result on specified column.
// Delimiter is used to chop the input into keywords and append to the
// condition SQL statement by 'OR' operator 
class ColumnCond extends Statement {

  public function __construct($column, $op, $values, $case_sensitive, $delimiter = NULL, $append_op = 'OR') {
    if (trim($values)) {
      $values = str_replace("'", "''", $values); // escape the single quote
      $vals = array();
      if ($delimiter == NULL) {
        $vals = array($values);
      }
      else {
        $vals = explode($delimiter, $values);
      }
      $this->statement = "(";
      $counter = 0;
      foreach ($vals as $value) {
        $value = trim($value);        
        if ($case_sensitive) {
          if ($op == 'contains') {
            $this->statement .= "$column like '%%" . $value . "%%'";
          } else if ($op == 'exactly') {
            $this->statement .= "$column = '" . $value . "'";
          } else if ($op == 'starts') {
            $this->statement .= "$column like '" . $value . "%%'";
          } else if ($op == 'ends') {
            $this->statement .= "$column like '%%" . $value . "'";
          }
        } else {
          if ($op == 'contains') {
            $this->statement .= "lower($column) like lower('%%" . $value . "%%')";
          } else if ($op == 'exactly') {
            $this->statement .= "lower($column) = lower('" . $value . "')";
          } else if ($op == 'starts') {
            $this->statement .= "lower($column) like lower('" . $value . "%%')";
          } else if ($op == 'ends') {
            $this->statement .= "lower($column) like lower('%%" . $value . "')";
          }
        }
        if ($counter < count($vals) - 1) {
          $this->statement .= " $append_op ";
        }
        $counter ++;
      }
      $this->statement .= ")";
    }
  }
}