<?php

use ChadoSearch\ChadoSearch;
use ChadoSearch\result\Pager;
use ChadoSearch\result\Download;
use ChadoSearch\result\Fasta;

$GLOBALS['chado_search_conf_file'] = '/file/settings.conf';

/*
 * Get the search settings from the file
*/
function chado_search_settings($file, $enabled_only = TRUE) {
  $file_handle = fopen($file, "r");
  $enabledSearch = array();
  $search = NULL;
  while (!feof($file_handle)) {
    $line = trim(fgets($file_handle));
    if (!preg_match('/^#/', $line)) { // Ignore comments
      if (preg_match('/^\[(.+)\]$/', trim($line), $matches)) {
        // Save search
        if ($search) {
          if ($enabled_only && $search['enabled']) {
            array_push($enabledSearch, $search);
          }
          else if (!$enabled_only) {
            array_push($enabledSearch, $search);
          }
          $search = array();
          $ajax = array();
          $search['title'] = $matches[1];
          // Create a new search
        } else {
          $search = array();
          $ajax = array();
          $search['title'] = $matches[1];
        }
      } else if (preg_match('/^id=(.+)$/', $line, $matches)) {
        $search['id'] = $matches[1];
      } else if (preg_match('/^path=(.+)$/', $line, $matches)) {
        $search['path'] = $matches[1];
      } else if (preg_match('/^file=(.+)$/', $line, $matches)) {
        $search['file'] = $matches[1];
      } else if (preg_match('/^ajax=(.+):(.+)$/', $line, $matches)) {
        $ajax[$matches[1]] = $matches[2];
        $search['ajax'] = $ajax;
      } else if (preg_match('/^mview_name=(.+)$/', $line, $matches)) {
        $search['mview_name'] = $matches[1];
      } else if (preg_match('/^mview_file=(.+)$/', $line, $matches)) {
        $search['mview_file'] = $matches[1];
      } else if (preg_match('/^mview_callback=(.+)$/', $line, $matches)) {
        $search['mview_callback'] = $matches[1];
      } else if (preg_match('/^summary_title=(.+)$/', $line, $matches)) {
        $search['summary_title'] = $matches[1];
      } else if (preg_match('/^enabled=(.+)$/', $line, $matches)) {
        $search['enabled'] = $matches[1];
      } else if (preg_match('/^num_per_page=(.+)$/', $line, $matches)) {
        $search['num_per_page'] = $matches[1];
      } else if (preg_match('/^summary_allowed=(.+)$/', $line, $matches)) {
        $search['summary_allowed'] = $matches[1];
      }
    }
  }
  // The last search
  if ($enabled_only) {
    if ($search && $search['enabled']) {
      array_push($enabledSearch, $search);
    }
  }
  else {
    if ($search) {
      array_push($enabledSearch, $search);
    }
  }
  fclose($file_handle);
  return $enabledSearch;
}

/*
 * Get enabled search
 */
function chado_search_get_enabled_searches ($silent = TRUE) {
  return chado_search_get_all_searches($silent, TRUE);
}

/*
 * Get all search
 */
function chado_search_get_all_searches ($silent = TRUE, $enabled_only = FALSE) {
  global $chado_search_conf_file;
  $file = drupal_get_path('module', 'chado_search') . $chado_search_conf_file;
  if (file_exists($file)) {
    return chado_search_settings($file, $enabled_only);
  } else {
    if (!$silent) {
      drupal_set_message("Fatal Error: Chado Search conf file 'chado_search$chado_search_conf_file not found. ", 'error');
    }
    return array();
  }
}

/*
 * Get search setting by id
 */
function chado_search_get_setting_by_id ($search_id, $setting) {
  $searches = chado_search_get_enabled_searches();
  for ($i = 0; $i < count($searches); $i ++) {
    if (isset($searches[$i]['id']) && $searches[$i]['id'] == $search_id) {
      if (key_exists($setting, $searches[$i])) {
      return $searches[$i][$setting];
      }
    }
  }
  return NULL;
}

/*
 * Set search setting by id
 */
function chado_search_set_setting_by_id ($search_id, $setting) {
  global $chado_search_conf_file;
  $file = drupal_get_path('module', 'chado_search') . $chado_search_conf_file;
  $writable = is_writable($file);
  if (file_exists($file) && $writable) {
    $handle = fopen($file, 'r');
    $content = array();
    $idx_start = 0;
    $idx_end = 0;
    $idx_id = 0;
    $index = 0;
    $found = FALSE;
    while (($line = fgets($handle)) !== FALSE) {
      if (preg_match('/^\[(.+)\]$/', trim($line))) {
        if ($idx_id) {
          $found = TRUE;
          $idx_end = $idx_end > $idx_start ? $idx_end : $index;
        }
        else {
          if (!$found) {
            $idx_start = $index;
            $idx_end = $index;
          }
        }
      } else if (trim($line) == 'id=' . $search_id) {
        $idx_id = $index;
      }
      $content[$index] = $line;
      $index ++;
    }
    $idx_end = $idx_end == $idx_start ? $index : $idx_end;
    $key_value = explode('=', $setting);    
    for($i = $idx_start; $i < $idx_end ; $i ++) {
      $line = explode('=', $content[$i]);
      if (trim($line[0]) == $key_value[0]) {
       $content[$i] = $setting . "\n";
      }
    }
    
    $succeed = file_put_contents($file, $content);
    return $succeed;
  } else {
    drupal_set_message('File \'' . $chado_search_conf_file . '\' is not writable. Nothing changed.', 'error');
  }
  return FALSE;
}

// Drop specified MView
function chado_search_drop_mview($view_name) {
  $mview_id = tripal_get_mview_id($view_name);
  if($mview_id){
    tripal_delete_mview($mview_id);
  }
}

/*************************************************************
 * Entry point callback
*/
function chado_search_callback ($search_id, $url, $num_per_page, $show_result = NULL){
  $form = drupal_get_form('chado_search_wrapper_form', $search_id, $url, $num_per_page, $show_result);
  return $form;
}

// Form
function chado_search_wrapper_form ($form, &$form_state, $search_id, $url, $num_per_page, $show_result = NULL) {
  $search = ChadoSearch::init($search_id, $url, $num_per_page);
  $form = $search->prepareForm($form_state);
  $func = 'chado_search_' . $search_id . '_form';
  if (function_exists($func)) {
    $set_form = $func ($form);
    $final_form = $set_form->getForm();
  }
  $final_form['#search_id'] = $search_id;
  $final_form['#search_url'] = $url;
  $final_form['#number_per_page'] = $num_per_page;

  if (isset($final_form['#custom_output-replace_star_with_selection'])) {
    $form_state['#custom_output-replace_star_with_selection'] = $final_form['#custom_output-replace_star_with_selection'];
  }
  
  $allowed = chado_search_get_setting_by_id($search_id, 'summary_allowed');
  if (($show_result == 'summary' || $show_result == 'list') && $allowed) {
    $title = chado_search_get_setting_by_id($search_id, 'summary_title');
    if ($title) {
      drupal_set_title($title);
    }
    $form_state['#show_all_results'] = TRUE;
    $form_state['#show_all_results_scroll'] = FALSE;
    if (key_exists('scroll', $_GET)) {
      $form_state['#show_all_results_scroll'] = TRUE;
    }
    $hasResult = $search_id . "-result";
    $noResult = $search_id . "-no-result";
    if ($show_result == 'summary') {
      $final_form['wait']['#prefix'] = 
        "<style type=\"text/css\">
            body {display: none;}
         </style>
         <script type=\"text/javascript\">
           (function ($) {;
              $(document).ready(function(){
                \$('.chado_search-widget,.chado_search-fieldset').hide();
                if(!document.getElementById('$hasResult') && !document.getElementById('$noResult')){
                  document.getElementById('chado_search-id-submit').click();
                }
              })
           })(jQuery);
          </script>";
    } else if ($show_result == 'list') {
      $final_form['wait']['#prefix'] =
        "<style type=\"text/css\">
            body {display: none;}
          </style>
          <script type=\"text/javascript\">
            (function ($) {;
               $(document).ready(function(){
                 if(!document.getElementById('$hasResult') && !document.getElementById('$noResult')){
                   document.getElementById('chado_search-id-submit').click();
                 }
               })
             })(jQuery);
           </script>";
      unset ($form_state['build_info']['args'][3]); // unset 'list' so the page will not be hidden when resubmitting the form
    }
  }
  return $final_form;
}

// Form Validate
function chado_search_wrapper_form_validate ($form, &$form_state) {
  $search_id = $form['#search_id'];
  $val_func = 'chado_search_' . $search_id . '_form_validate';
  if (function_exists($val_func)) {
    $val_func($form, $form_state);
  }
}

// Form submit
function chado_search_wrapper_form_submit ($form, &$form_state) {
  // To allow getting values from $_GET
  $inputs = $form_state['input'];
  foreach ($inputs AS $k => $input) {
    if ($k != 'form_build_id' && $k != 'form_id' && $k != 'form_token') {
      if (key_exists($k, $_GET)) {
        $form_state['values'][$k] = check_plain($_GET[$k]);
      }
    }
  }
  $search_id = $form['#search_id'];
  $url = $form['#search_url'];
  $num_per_page = $form['#number_per_page'];
  $search = ChadoSearch::init($search_id, $url, $num_per_page);
  $submit_func = 'chado_search_' . $search_id . '_form_submit';
  if (function_exists($submit_func)) {
    $submit_func($form, $form_state, $search);
  }
}

// Create AJAX pager
function chado_search_ajax_pager($page, $search_id, $url, $num_per_page) {
  $search = ChadoSearch::init($search_id, $url, $num_per_page);
  return drupal_json_output(Pager::switchPage($search_id, $page, $num_per_page));
}

// Create AJAX download
function chado_search_ajax_download ($search_id, $url, $num_per_page) {
  $func = 'chado_search_' . $search_id . '_download_definition';
  $func_alt = 'chado_search_' . $search_id . '_table_definition';
  if (function_exists($func)) {
    $headers = $func();
  } else if (function_exists($func_alt)) { // Try using the table difinition if download difinition does not exist.
    $tmp = $func_alt();
    $headers = array();
    foreach ($tmp AS $k => $v) {
      $key = explode(":", $k);
      $headers[$key[0]] = $v;
    }
  }
  $search = ChadoSearch::init($search_id, $url, $num_per_page);
  return drupal_json_output(Download::createDownload($search_id, $url, $headers));
}

// Get AJAX download progress
function chado_search_ajax_download_progress ($search_id, $url, $num_per_page) {
  $progress = variable_get('chado_search-' . session_id() . '-' . $search_id . '-download-progress', 0);
  return drupal_json_output( array('progress' => $progress));
}

// Create AJAX Fasta download directly from the feature table
function chado_search_ajax_download_fasta_feature ($search_id, $url, $num_per_page) {
  $func = 'chado_search_' . $search_id . '_download_fasta_definition';
  if (function_exists($func)) {
    $feauture_id_column = $func();
  } else {
    $feauture_id_column = 'feature_id';
  }
  $search = ChadoSearch::init($search_id, $url, $num_per_page);
  return drupal_json_output(Fasta::createFasta($search_id, $url, $feauture_id_column));
}

// Provide an API function for updating AJAX form elements
function chado_search_ajax_form_update($form, &$form_state) {
  $update = $form_state['triggering_element']['#attribute']['update'];
  if (is_array($update)) {
    $cmd = array();
    foreach ($update AS $id => $u) {
      if (isset($u['value'])) {
        $form[$id]['#value'] = $u['value'];
      }
      if (isset($u['wrapper'])) {
        array_push($cmd, ajax_command_replace('#' . $u['wrapper'], render($form[$id])));
      }
    }
    $return =  array (
      '#type' => 'ajax',
      '#commands' => $cmd
    );
    return $return;
  }
  else {
    return $form[$update];
  }
}

// Link to node by nid. If nid is unavailable return NULL
function chado_search_link_node ($nid) {
  if ($nid) {
    return "/node/$nid";
  } else {
    return NULL;
  }
}

function chado_search_link_entity ($base_table, $record_id) {
  $link = NULL;
  // tripal v2 link (node)
  $nid = chado_get_nid_from_id ($base_table, $record_id);
  if ($nid) {
    $link = "/node/$nid";
  }
  // tripal v3 link (entity)
  if (function_exists('tripal_get_chado_entity_id')) {
    $entity_id = tripal_get_chado_entity_id ($base_table, $record_id);
    if ($entity_id) {
      $link = "/bio_data/$entity_id";
    }
  }
  return $link;
}

// Link to node by nid. If nid is unavailable return NULL
function chado_search_link_url ($url) {
  if ($url) {
    return $url;
  } else {
    return NULL;
  }
}

// Bind unique values of a certain column to the ComputedTextFields element
// This function is usually called by an AJAX function to populate the values in a select box
function chado_search_bind_dynamic_textfields($value, $column, $sql) {
  try {
    foreach($value AS $k => $v) {
      $value[$k] = urldecode($v);
    }
    $result = chado_query ($sql, $value)->fetchObject();
    $data = array ();
    if ($result) {
      array_push ($data, $result->$column);
    }
    return $data;
  } catch (\PDOException $e) {
    drupal_set_message('Unable to bind DynamicTextFields form element. Please check your SQL statement in the AJAX callback. ' . $e->getMessage(), 'error');
  }
}

// Bind unique values of a certain column to the DynamicSelect element
// This function is usually called by an AJAX function to populate the values in a select box
function chado_search_bind_dynamic_select($value, $column, $sql) {
  try {
    $data = array(0 => 'Any');
    $key = array_shift(array_keys($value));
    if (count($value[$key]) > 0) {
    $result = chado_query($sql, $value);
      while ($obj = $result->fetchObject()) {
        if ($obj->$column) {
          $data[$obj->$column] = $obj->$column;
        }
      }
    }
    return $data;
  } catch (\PDOException $e) {
    drupal_set_message('Unable to bind DynamicSelectFilter form element. Please check your SQL statement in the AJAX callback. ' . $e->getMessage(), 'error');
  }
}

function chado_search_get_class($obj = NULL) {
  $namespace = explode('\\', get_class($obj));
  $class = $namespace[count($namespace) - 1];
  return $class;
}

/**
 * This function fixes a Drupal 7 bug when a form contains multi-select and a file upload,
 * the values were concatenated during an AJAX call which results in an illegal choice in the
 * select box
 * 
 * @return string[]|array[]|unknown
 */
function chado_search_ajax_form_callback() {
  $select = $_POST['_triggering_element_name'];
  $select_1 = explode(",", $_POST[$select][0]);
  if ( count($select_1) > 1) {
    unset($_POST[$select]);
    $_POST[$select] = $select_1;
  }
  
  list($form, $form_state) = ajax_get_form();
  drupal_process_form($form['#form_id'], $form, $form_state);
  
  if (!empty($form_state['triggering_element'])) {
    $path = $form_state['triggering_element']['#ajax']['path'];
  }
  if (!empty($path)) {
    return chado_search_ajax_form_update($form, $form_state); // call to generate the second dropdown
  }
}


