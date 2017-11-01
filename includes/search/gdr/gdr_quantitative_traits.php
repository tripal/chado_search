<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */

// GDR created MViews manually so we unforunately need to hard code the mviews to use.
function chado_search_quantitative_traits_mviews () {
  $mviews = array(
    'Apple (RosBREED)' => 'chado_search_quantitative_traits_apple_crs',
    'Apple (WA Apple Breeding)' => 'chado_search_quantitative_traits_apple_ke',
    'Sweet Cherry (RosBREED)' => 'chado_search_quantitative_traits_sweet_cherry_crs',
    'Tart Cherry (RosBREED)' => 'chado_search_quantitative_traits_tart_cherry_crs',
    'Strawberry (RosBREED)' => 'chado_search_quantitative_traits_strawberry_crs',
    'Peach (RosBREED)' => 'chado_search_quantitative_traits_peach_crs'
  );
  return $mviews;
}

function chado_search_quantitative_traits_get_mview ($key) {
  $mviews = chado_search_quantitative_traits_mviews();
  return $mviews[$key];
}

// Search form
function chado_search_quantitative_traits_form ($form) {  
  $form->addTabs(
      Set::tab()
      ->id('trait_search_tabs')
      ->items(array('/search/qualitative_traits' => 'Qualitative Trait', '/search/quantitative_traits' => 'Quantitative Trait'))
  );
  $opts = array_keys(chado_search_quantitative_traits_mviews());
  $form->addSelectOptionFilter(
      Set::selectOptionFilter()
      ->id('organism')
      ->title('Species')
      ->options($opts)
      ->newline()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('trait1')
      ->title('Trait1')
      ->dependOnId('organism')
      ->callback('chado_search_quantitative_traits_ajax_dynamic_trait')
  );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('value1a')
      ->title('between')
      ->id2('value1b')
      ->title2( 'and')
      ->newLine()
  );
  $form->addDynamicTextFields(
      Set::dynamicTextFields()
      ->id('trait1')
      ->targetIds(array('value1a', 'value1b'))
      ->callback('chado_search_quantitative_traits_ajax_dynamic_textfield')
      ->resetOnChagne('organism')
  );
  $form->addSelectOptionFilter(
      Set::selectOptionFilter()
      ->id('op1')
      ->options(array('and' => 'AND', 'or' => 'OR'))
      ->required(TRUE)
      ->newLine()
  );
  
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('trait2')
      ->title('Trait2')
      ->dependOnId('organism')
      ->callback('chado_search_quantitative_traits_ajax_dynamic_trait')
      );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('value2a')
      ->title('between')
      ->id2('value2b')
      ->title2('and')
      ->newLine()
  );
  $form->addDynamicTextFields(
      Set::dynamicTextFields()
      ->id('trait2')
      ->targetIds(array('value2a', 'value2b'))
      ->callback('chado_search_quantitative_traits_ajax_dynamic_textfield')
      ->resetOnChagne('organism')
  );
  $form->addSelectOptionFilter(
      Set::selectOptionFilter()
      ->id('op2')
      ->options(array('and' => 'AND', 'or' => 'OR'))
      ->required(TRUE)
      ->newLine()
  );
  
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('trait3')
      ->title('Trait3')
      ->dependOnId('organism')
      ->callback('chado_search_quantitative_traits_ajax_dynamic_trait')
      );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('value3a')
      ->title('between')
      ->id2('value3b')
      ->title2('and')
  );
  $form->addDynamicTextFields(
      Set::dynamicTextFields()
      ->id('trait3')
      ->targetIds(array('value3a', 'value3b'))
      ->callback('chado_search_quantitative_traits_ajax_dynamic_textfield')
      ->resetOnChagne('organism')
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('topLevel')
      ->startWidget('organism')
      ->endWidget('value3b')
  );

  $form->addSubmit();
  $form->addReset();

  return $form;
}

// Validate the form
function chado_search_quantitative_traits_form_validate ($form, &$form_state) {
  $org = $form_state['values']['organism'];
  if (!$org) {
    form_set_error('organism', 'Please select a species.');
  }
}

// Submit the form
function chado_search_quantitative_traits_form_submit ($form, &$form_state) {
  $org = $form_state['values']['organism'];
  $mview = chado_search_quantitative_traits_get_mview($org);
  $t[0] = Sql::selectFilter('trait1', $form_state, 'trait_descriptor'); 
  $v[0] = Sql::betweenFilter('value1a', 'value1b', $form_state, 'trait_value', 'trait_value', TRUE);
  $t[1] = Sql::selectFilter('trait2', $form_state, 'trait_descriptor');
  $v[1] = Sql::betweenFilter('value2a', 'value2b', $form_state, 'trait_value', 'trait_value', TRUE);
  $t[2] = Sql::selectFilter('trait3', $form_state, 'trait_descriptor');
  $v[2] = Sql::betweenFilter('value3a', 'value3b', $form_state, 'trait_value', 'trait_value', TRUE);
  $op = array();
  $op[1] = $form_state['values']['op1'];
  $op[2] = $form_state['values']['op2'];
  $conditions =Sql::pairConditions($t, $v);
    // Read from $conditions and generate the SQL
    $first_con = true;
    $append = "";
    $changeHeaders = "";
    foreach ($conditions AS $index => $c) {
       if ($first_con) {
         $sql = "SELECT * FROM (SELECT stock_id, variety_name, organism_id, organism, trait_descriptor AS trait$index, trait_value AS value$index, project_name FROM {". $mview . "}";
         $append .= "WHERE $c) T$index";
         $first_con = false;
         $first_table = $index;
       } else {
         if ($op[$index] == 'OR') {
           $append .= " FULL OUTER JOIN (SELECT stock_id, variety_name, organism_id, organism, trait_descriptor AS trait$index, trait_value AS value$index FROM {" . $mview . "} WHERE $c) T$index USING(stock_id, variety_name, organism_id, organism)";
         }
         else {
           $append .= " INNER JOIN (SELECT variety_name AS variety_name$index,  trait_descriptor AS trait$index, trait_value AS value$index FROM {" . $mview . "} WHERE $c) T$index ON (T$first_table.variety_name = T$index.variety_name$index";
           if ($op[1] == 'OR' && $index = 2) {
             $append .= " OR T1.variety_name = T$index.variety_name$index)";
           }
           else {
             $append .= ")";
           }
         }
       }
    }
    // If there is no $condition, use a different SQL to group stocks
    if (!$conditions) {
      $disabledCols = "value0;value1;value2";
      $sql = "SELECT first(stock_id) AS stock_id, variety_name, first(organism_id) AS organism_id, first(organism) AS organism, string_agg(trait_descriptor || ' = ' || trait_value, '; ') AS all_traits, string_agg(DISTINCT project_name, ', ') AS project_name FROM {" . $mview . "} GROUP BY variety_name";
    } else { // If there is $condition, dynamically determine which columns to show
      $disabledCols = "all_traits";
      foreach ($t AS $idx => $enabled) {
        if (!$enabled) {
          $disabledCols .= ";value$idx";
        } else {
          $title = explode('=', $enabled);
          $t = trim($title[1], ' \'');
          $changeHeaders .= "value$idx=$t;";
        }
      }
    }
  Set::result()
    ->sql($sql)
    ->tableDefinitionCallback('chado_search_quantitative_traits_table_definition')
    ->append($append)
    ->disableCols($disabledCols)
    ->changeHeaders($changeHeaders)
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_quantitative_traits_table_definition () {
  $headers = array(      
    'variety_name:s:chado_search_quantitative_traits_link_stock:stock_id' => 'Germplasm',
    'organism:s:chado_search_quantitative_traits_link_organism:organism_id' => 'Species',
    'all_traits:s' => 'All Traits',
    'value0:s' => 'Trait1',
    'value1:s' => 'Trait2',
    'value2:s' => 'Trait3',
    'project_name:s' => 'Dataset'
  );
  return $headers;
}

// Define call back to link the stock to its  node for the result table
function chado_search_quantitative_traits_link_stock ($stock_id) {
  return chado_search_link_entity('stock', $stock_id);
}

// Define call back to link the featuremap to its  node for the result table
function chado_search_quantitative_traits_link_organism ($organism_id) {
  return chado_search_link_entity('organism', $organism_id);
}

/*************************************************************
 * AJAX callbacks
*/
function chado_search_quantitative_traits_ajax_dynamic_textfield ($value, $id) {
  $data = '';
  $org = isset($_POST['organism']) ? $_POST['organism'] : NULL;
  $mview = chado_search_quantitative_traits_get_mview($org);
  if ($org && $value && preg_match('/^value\d+a$/', $id)) {
    $sql = "SELECT min (trait_value) FROM {" . $mview . "} WHERE trait_descriptor = :descriptor";
    $data = chado_query($sql, array(':descriptor' => $value))->fetchField();
  }
  else if ($org && $value && preg_match('/^value\d+b$/', $id)) {
    $sql = "SELECT max(trait_value) FROM {" . $mview . "} WHERE trait_descriptor = :descriptor";
    $data = chado_query($sql, array(':descriptor' => $value))->fetchField();
  }
  return $data;
}

function chado_search_quantitative_traits_ajax_dynamic_trait ($value) {
  if ($value) {
    $mview = chado_search_quantitative_traits_get_mview($value);
    $sql = "SELECT DISTINCT trait_descriptor FROM {" . $mview . "}";
    return chado_search_bind_dynamic_select(array($value), 'trait_descriptor', $sql);
  }
}
