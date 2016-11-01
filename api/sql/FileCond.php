<?php

namespace ChadoSearch\sql;

// Create an SQL condition that filters the result for the File widget
class FileCond extends Statement {

  public function __construct($file, $column, $case_sensitive, $contains_word) {
    $this->statement = '';
    // get the file upload content if one has been provided
    if ($file) {
      if ($case_sensitive) {
        if ($contains_word) {
          $this->statement = "(";
        } else {
          $this->statement = "$column IN (";
        }
      } else {
        if ($contains_word) {
          $this->statement = "(";
        } else {
          $this->statement = "lower($column) IN (";
        }
      }
      $handle = fopen($file, 'r');
      while ($line = fgets($handle)) {
        $name = trim($line);
        if ($name != "") {
          $name = str_replace("'", "''", $name); // escape the single quote
          if ($case_sensitive) {
            if ($contains_word) {
              if ($this->statement != "(") {
                $this->statement .= " OR ";
              }
              $this->statement .= "$column like '%%" . $name . "%%'";
            } else {
              $this->statement .= "'$name', ";
            }
          } else {
            if ($contains_word) {
              if ($this->statement != "(") {
                $this->statement .= " OR ";
              }
              $this->statement .= "lower($column) like lower('%%" . $name . "%%')";
            } else {
              $this->statement .= "lower('$name'), ";
            }
          }
        }
      }
      fclose($handle);
      if ($this->statement != "$column IN (" && $this->statement != "(") {
        if (!$contains_word) {
          $this->statement = substr($this->statement, 0, strlen($this->statement) - 2);
        }
        $this->statement .= ")";
      }
    }
  }
}