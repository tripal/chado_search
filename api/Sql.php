<?php
namespace ChadoSearch;

use ChadoSearch\sql\BetweenCond;
use ChadoSearch\sql\ColumnCond;
use ChadoSearch\sql\FileCond;
use ChadoSearch\sql\FileMultiColumnsCond;
use ChadoSearch\sql\MultiColumnsCond;
use ChadoSearch\sql\SelectCond;
use ChadoSearch\sql\Statement;

/**
 * A class to help generate SQL for form elements
 *
 */

class Sql {

  /****************************************************
   * SQL statement builders
   */
  
  // SQL for LabeledFilter
  static function labeledFilter ($filter_id, &$form_state, $column, $case_sensitive = FALSE) {
    $value = $form_state['values'][$filter_id];
    $s = new ColumnCond($column, 'exactly', $value, $case_sensitive);
    return $s->getStatement();
  }
  
  // SQL for TextFilter
  static function textFilter ($filter_id, &$form_state, $column, $case_sensitive = FALSE) {
    $op = $form_state['values'][$filter_id . '_op'];
    $value = $form_state['values'][$filter_id];
    $s = new ColumnCond($column, $op, $value, $case_sensitive);
    return $s->getStatement();
  }
  
  // SQL for TextareaFilter. $delimiter can not be a single quote '
  static function textareaFilter ($filter_id, &$form_state, $column, $case_sensitive = FALSE, $delimiter = "\n", $append_op = 'OR') {
    $op = $form_state['values'][$filter_id . '_op'];
    $values = $form_state['values'][$filter_id];
    $s = new ColumnCond($column, $op, $values, $case_sensitive, $delimiter, $append_op);
    return $s->getStatement();
  }
  
  // SQL for TextFilter. Search the field on multiple columns
  // '$convert_to' will convert the matched columns into another column, typically an ID or primary key. It takes the format of '<column>:<table>'. Useful when the results were grouped.
  static function textFilterOnMultipleColumns ($filter_id, &$form_state, $columns = array(), $case_sensitive = FALSE, $convert_to = NULL) {
    $value = $form_state['values'][$filter_id];
    $op = $form_state['values'][$filter_id . '_op'];
    $s = new MultiColumnsCond($columns, $op, $value, $case_sensitive, $convert_to);
    return $s->getStatement();
  }
  
  // SQL for FileFilter. Search the field on multiple columns
  static function fileOnMultipleColumns ($filter_id, $columns = array(), $case_sensitive = FALSE, $contains_word = FALSE, $convert_to = NULL) {
    $file = $_FILES['files']['tmp_name'][$filter_id];
    $s = new FileMultiColumnsCond($columns, $file, $case_sensitive, $contains_word, $convert_to);
    return $s->getStatement();
  }
  
  // SQL for SelectFilter
  static function selectFilter ($filter_id, &$form_state, $column) {
    $value = $form_state['values'][$filter_id];
    $s = new SelectCond($column, $value);
    return $s->getStatement();
  }
  
  //SQL for BetweenFilter
  static function betweenFilter ($filter1_id, $filter2_id, &$form_state, $column1, $column2, $cast2real = FALSE) {
    $value1 = $form_state['values'][$filter1_id];
    $value2 = $form_state['values'][$filter2_id];
    $s = new BetweenCond($column1, $value1, $column2, $value2, $cast2real);
    return $s->getStatement();
  }
  
  // SQL for File
  static function file ($filter_id, $column, $case_sensitive = FALSE, $contains_word = FALSE) {
    $file = $_FILES['files']['tmp_name'][$filter_id];
    $s = new FileCond($file, $column, $case_sensitive, $contains_word);
    return $s->getStatement();
  }
  // Pair the SQL for two arrays that have the same elements by 'AND' or 'OR'.
  // Return an array with paired SQL conditions
  static function pairConditions ($arr1, $arr2, $concatbyOR = FALSE) {
    return Statement::pairConditions($arr1, $arr2, $concatbyOR);
  }
  
}