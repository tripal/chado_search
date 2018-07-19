<?php
require_once 'citrus_biodata_download.mviews.php';

use ChadoSearch\Set;
use ChadoSearch\Sql;
use ChadoSearch\SessionVar;

use ChadoSearch\result\Download;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_biodata_download_form ($form) {
  
  $form_state = $form->form_state;  

  // Data Type
  $options = chado_search_biodata_download_get_mview_settings('name');
  asort($options);
  $form->addSelectOptionFilter(
      Set::selectOptionFilter()
      ->id('datatype')
      ->title('Data Type')
      ->options($options)
      ->noKeyConversion(TRUE)
      ->newLine()
  );
  
  // Status & Counter
  $form->addDynamicMarkup(
    Set::dynamicMarkup()
      ->id('status')
      ->dependOnId('datatype')
      ->callback('chado_search_biodata_download_status')
      ->newline()
  );
  
  // Settings for the Filter & Attribute lists (hide if datatype not selected)
  $setttings_filter =
    Set::dynamicFieldset()
    ->id('data_filters')
    ->title('Data Filters')
    ->dependOnId('datatype')
    ->callback('chado_search_biodata_download_ajax')
    ->width('56%');
  $setttings_attr =
    Set::dynamicFieldset()
    ->id('data_attributes')
    ->title('Downloadable Attributes')
    ->dependOnId('datatype')
    ->callback('chado_search_biodata_download_ajax')
    ->width('40%');
    if(!isset($form_state['values']['datatype']) || $form_state['values']['datatype'] == '0') {
    $setttings_filter->display('none');
    $setttings_attr->display('none');
  }
    
  // Filter List fieldset
  $form->addDynamicFieldset($setttings_filter);
  
  // Attribute List fieldset
  $form->addDynamicFieldset($setttings_attr);
  
  if(isset($form_state['values']['datatype'])) {
    $mview = $form_state['values']['datatype'];
    $mv_def = chado_search_biodata_download_get_mview($mview, 'title');
    if ($mv_def) {
      $filter_settings = chado_search_biodata_download_get_mview($mview, 'filter');
      // Populate filter list
      $button_added = false;
      $col_opts = array();
      foreach($mv_def AS $col => $title) {
        // Get filter type
        if (key_exists($col, $filter_settings)) {
          $ftype = $filter_settings[$col]['type'];
          if ($ftype == 'select') {
            $options = array('0' => 'Any');
            $opt_table = $filter_settings[$col]['opt_table'];
            $sql_opt = "SELECT DISTINCT $col FROM $opt_table";
            $results = chado_query($sql_opt);
            while ($obj = $results->fetchObject()) {
              $options [$obj->$col] = $obj->$col;
            }            
            $select_settings = 
              Set::select()
              ->id('csfilter--'  . $mview . '-'  . $col)
              ->title($title)
              ->options($options)
              ->fieldset('data_filters');
            if (isset($form_state['values']['csfilter--' . $mview . '-' . $col]) && trim($form_state['values']['csfilter--' . $mview . '-' . $col])) {
              $select_settings->defaultValue(trim($form_state['values']['csfilter--' . $mview . '-' . $col]));
            }
            $form->addSelect($select_settings);
          }
        }
        else {
          // Textfield
          $text_settings = 
            Set::textField()
            ->id('csfilter--'  . $mview . '-'  . $col)
            ->title($title)
            ->fieldset('data_filters');
            if (isset($form_state['values']['csfilter--' . $mview . '-' . $col]) && trim($form_state['values']['csfilter--' . $mview . '-' . $col])) {
              $text_settings->defaultValue(trim($form_state['values']['csfilter--' . $mview . '-' . $col]));
            }
            $form->addTextfield($text_settings);
        }
        if (!$button_added) {
          $form->addButton(
              Set::button()
              ->id('apply_filters')
              ->fieldset('data_filters')
              ->value('Apply Filters')
          );
          $button_added = true;
        }
        $col_opts [$col] = $title;
      }

      // Populate attribuet list
      $form->addCheckboxes(
        Set::checkBoxes()
          ->id('attribute_checkboxes')
          ->options($col_opts)
          ->fieldset('data_attributes')
          ->defaultValue(array_keys($col_opts))
      );
    }
  }

  $form->addSubmit(
    Set::submit()
      ->value('Download')
  );
  // Reset Button
  $form->addReset();
  
  // Wrap form in Fieldset
  $form->addFieldset(
      Set::fieldset()
      ->id('biodata_download_fields')
      ->startWidget('datatype')
      ->endWidget('reset')
  );
  return $form;
}

// Validate the form
function chado_search_biodata_download_form_validate ($form, &$form_state) {
  $values = $form_state['values'];
  $mview = $values['datatype'];  
  // Make sure datatype is selected
  if ($mview == '0') {
    form_set_error('datatype', 'Please select a data type');
    return;
  }
  
  // Create download file if Download button is clicked
  if (isset($form_state['triggering_element']['#id']) && $form_state['triggering_element']['#id'] == 'chado_search-id-submit') {
    $mview = $values['datatype'];
    $where = chado_search_biodata_download_filter_sql($form_state);
    $sql ="SELECT * FROM {" . $mview . "}" . $where;
    SessionVar::setSessionVar('biodata_download','download', $sql);
    $sql_total = "SELECT count(*) FROM {" . $mview . "}" . $where;
    $total = chado_query($sql_total)->fetchField();
    // Make sure there is at least one result
    if ($total == 0) {
      form_set_error('data_filters', 'The query returns no result. Please adjust the filters and try again');
      return;
    }
    else {
      SessionVar::setSessionVar('biodata_download','total-items', $total);
    }
    // Limit download attributes
    $headers = chado_search_biodata_download_get_mview($mview, 'title');
    $checkboxes = $values['attribute_checkboxes'];
    foreach ($checkboxes AS $key => $val) {
      if ($val == '0') {
        unset($headers[$key]);
      }
    }
    //Make sure at least one attribute is selected
    if (count($headers) == 0) {
      form_set_error('attribute_checkboxes', 'No attribute is selected. Please try again');
      return;
    }
    
    // If all checks passed, create the download file
    $dl = new Download('biodata_download', 'download/biodata', FALSE);
    $results = $dl->createDownload($headers);
    drupal_goto($results['path']);
  }
}

function chado_search_biodata_download_ajax($val) {
  return;
}

// Update Status on the form with record counts
function chado_search_biodata_download_status($val, $form, $form_state) {
  $message = NULL;
  if ($val != '0') {
    $applyfilters = isset($form_state['triggering_element']['#id']) && $form_state['triggering_element']['#id'] == 'chado_search-id-apply_filters';
    $counter = 0;
    if (chado_table_exists($val)) {
      $dist = chado_search_biodata_download_get_mview($val, 'distinct');
      $sql_dist = "SELECT count(DISTINCT " . key($dist) . ") FROM {" . $val . "}";
      $sql_total = "SELECT count(*) FROM {" . $val . "}";
      // apply filters
      $where = '';
      if ($applyfilters) {
        $where = chado_search_biodata_download_filter_sql($form_state);
      }
      // Run SQL
      $unique = chado_query($sql_dist . $where)->fetchField();
      $total = chado_query($sql_total . $where)->fetchField(); 
      SessionVar::setSessionVar('biodata_download','total-items', $total);
    }
    $message = "<strong>$total</strong> records with <strong>$unique</strong> unique ".  array_pop($dist);
  }
  return $message;
}

// Generate SQL for the filters
function chado_search_biodata_download_filter_sql($form_state) {
  // apply filters
  $filters = chado_search_biodata_download_get_filters($form_state);
  $num_filters = count($filters);
  $where = '';
  if ($num_filters > 0) {
    $where .= ' WHERE ';
    $index = 0;
    foreach ($filters AS $f => $v) {
      $where .= "lower($f) LIKE '%" . strtolower($v) . "%'";
      if ($index < $num_filters - 1) {
        $where .= " AND ";
      }
      $index ++;
    }
  }
  return $where;
}

// Get applied filters form $form_state['values']
function chado_search_biodata_download_get_filters ($form_state) {
  $values = $form_state['values'];
  $mview = $values['datatype'];
  $filters = array();
  foreach ($values AS $key => $value) {
    if (preg_match('/^csfilter--' . $mview . '-.+/', $key)) {
      $token = explode('--', $key);
      $mv = explode('-', $token[1]);
      $column = $mv[1];
      if (trim($value)) {
        $filters [$column] = $value;
      }
    }
  }
  return $filters;
}


// Get a specific MView
function chado_search_biodata_download_get_mview($mview, $setting = NULL) {
  $def = chado_search_biodata_download_mview_definition();
  if (isset($def[$mview])) {
    if ($setting && isset($def[$mview][$setting])) {
      return $def[$mview][$setting];
    }
    else if (!$setting) {
      return $def[$mview];
    }
  }
  return NULL;
}

// Get specific settings for all MViews
function chado_search_biodata_download_get_mview_settings ($name) {
  $def = chado_search_biodata_download_mview_definition();
  $settings = array();
  foreach ($def AS $key => $val) {
    $settings[$key] = isset($val[$name]) ? $val[$name] : NULL;
  }
  return $settings;
}