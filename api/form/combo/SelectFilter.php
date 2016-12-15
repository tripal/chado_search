<?php

namespace ChadoSearch\form\combo;

use ChadoSearch\Set;

class SelectFilter extends Filter {
  
  public $title;
  public $column;
  public $table;
  public $required;
  public $multiple;
  public $column_natural_sort;
  public $optgroup;
  public $optgroup_by_pattern;
  public $cache;
  public $label_width;
  public $size;
  public $disables;
  public $only;
  
  public function setForm (&$form, &$form_state) {
    try {
      $search_name = $this->search_name;
      $id = $this->id;
      $title = $this->title;
      $column = $this->column;
      $table = $this->table;
      $required = $this->required;
      $multiple = $this->multiple;
      $column_natural_sort = $this->column_natural_sort;
      $optgroup = $this->optgroup;
      $optgroup_by_pattern = $this->optgroup_by_pattern;
      $cache = $this->cache;
      $width = '';
      if ($this->label_width) {
        $width = "style=\"width:" . $this->label_width ."px\"";
      }
      $size = $this->size;
      
      $id_label = $id . "_label";
      $options = array();
      if (!$required) {
        $options[0] = 'Any';
      }
      if (is_array($column)) {
        $col = "";
        $group = "";
        for ($i = 0; $i < count($column); $i ++) {
          $col .= $column[$i];
          $group .= $column[$i];
          if ($i < count($column) - 1) {
            $col .= " || ' ' || ";
            $group .= ", ";
          }
        }
        $sql = "SELECT $col AS key FROM {" . $table . "} GROUP BY $group ORDER BY key";
        $results = chado_query($sql);
        while ($obj = $results->fetchObject()) {
          if (trim($obj->key) != "") {
            $options[$obj->key] = $obj->key;
          }
        }
      } else if ($column && $table) {
        $tname = "chado_search_cache_" . $table . "_" . $column;
        $tname = substr($tname, 0, 63); // Unfortunately, postgres has a table name limit of 63 chars
        if ($cache) {// If cache is on, create a cache table
          if (!db_table_exists($tname)) { // If the cache table not exists, create it
            chado_query("SELECT distinct $column INTO $tname FROM {" . $table . "}");
            $lastupdate = db_query(
              "SELECT last_update FROM tripal_mviews WHERE mv_table = :mv_table",
              array(':mv_table' => $table)
            )->fetchField();
            variable_set("chado_search_cache_last_update_" . $tname . "_" . $column, $lastupdate);
          } else { // If cache table exists, check if it's up-to-date. If it's not up-to-date, recreate the cache table
            $lastupdate = db_query(
              "SELECT last_update FROM tripal_mviews WHERE mv_table = :mv_table", 
              array(':mv_table' => $table)
            )->fetchField();
            if (variable_get("chado_search_cache_last_update_" . $tname . "_" . $column, "") != $lastupdate) {
              chado_query("DROP table $tname");
              chado_query("SELECT distinct $column INTO $tname FROM {" . $table . "}");
              variable_set("chado_search_cache_last_update_" . $tname . "_" . $column, $lastupdate);
            }
          }
          $table = $tname; // Use cached table instead of the original table if cache is on.
        } else { // If cache is off, remove the cache table
          if (db_table_exists($tname)) {
            chado_query("DROP table $tname");
            variable_del("chado_search_cache_last_update_" . $tname . "_" . $column);
          }
        }
        if ($column_natural_sort) {
          $s_sql = 
            "SELECT distinct $column, 
               CASE WHEN regexp_replace($column, E'\\\D','','g') = '' 
               THEN 999999999 
               ELSE regexp_replace($column, E'\\\D','','g')::numeric 
               END AS sortkey 
             FROM {" . $table ."} 
             ORDER BY sortkey";
        } else {
          $s_sql = "SELECT distinct $column FROM {" . $table . "} ORDER BY $column";
        }
        $results = NULL;
        if ($cache) {
          $results = db_query($s_sql);
        }
        else {
          $results = chado_query($s_sql);
        }
        while ($obj = $results->fetchObject()) {
          if (trim($obj->$column) != "") {
            if (!is_array($this->disables) || !in_array($obj->$column, $this->disables)){
              if (!is_array($this->only) || in_array($obj->$column, $this->only)) {
                $options[$obj->$column] = $obj->$column;
              }
            }
          }
        }
      }
      if ($optgroup) {
        $opts ['Common Selections'] = array();
        foreach ($optgroup AS $val) {
          if ($val == 'Any') {
            $opts['Common Selections'][0] = $val;
            unset ($options[0]);
          }
          else {
            $opts['Common Selections'][$val] = $val;
          }
        }
        $opts ['All Options'] = $options;
        $options = $opts;
      } else if ($optgroup_by_pattern) {
        $groupedopt = array();
        $other = array();
        foreach ($options AS $v_op) {
          $found = 0;
          if ($v_op == 'Any') {
            $groupedopt[0] = 'Any';
            continue;
          }
          foreach ($optgroup_by_pattern AS $k_group => $v_group) {
            if (preg_match("/$v_group/", $v_op)) {
              $found = 1;
              $groupedopt[$k_group][$v_op] = $v_op;
              break;
            }
          }
          if (!$found && !key_exists('Other', $optgroup_by_pattern)) {
            $other['Other'][$v_op] = $v_op;
          }
        }
        ksort($groupedopt, SORT_STRING);
        $groupedopt = array_merge($groupedopt, $other);
        $options = $groupedopt;
      }
      if ($title) {
        $this->csform->addMarkup(Set::markup()->id($id_label)->text($title));
        $form[$id_label]['#prefix'] = 
          "<div id=\"chado_search-filter-$search_name-$id\" class=\"chado_search-filter chado_search-widget\">
             <div id=\"chado_search-filter-$search_name-$id-label\" class=\"chado_search-filter-label form-item\" $width>";
        $form[$id_label]['#suffix'] = 
          "  </div>";
        $this->csform->addSelect(Set::select()->id($id)->options($options)->multiple($multiple)->size($size));
        $form[$id]['#prefix'] = 
          "<div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter-field\">";
      } else {
        $this->csform->addSelect(Set::select()->id($id)->options($options)->multiple($multiple)->size($size));
        $form[$id]['#prefix'] = 
          "<div id=\"chado_search-filter-$search_name-$id\" class=\"chado_search-filter chado_search-widget\">
             <div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter-field\">";
      }
      $form[$id]['#suffix'] = 
        "   </div>
           </div>";
    } catch (\PDOException $e) {
      drupal_set_message('Unable to create SelectFilter form element. Please check your settings. ' . $e->getMessage(), 'error');
    }
  }
}