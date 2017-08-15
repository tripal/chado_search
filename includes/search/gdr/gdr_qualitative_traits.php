<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_qualitative_traits_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('trait_search_tabs')
      ->items(array('/search/qualitative_traits' => 'Qualitative Trait', '/search/quantitative_traits' => 'Quantitative Trait'))
  );
  $form->addSelectOptionFilter(
      Set::selectOptionFilter()
      ->id('organism')
      ->title('Species')
      ->options(array('apple', 'peach'))
      ->newline()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('trait1')
      ->title('Trait1')
      ->dependOnId('organism')
      ->callback('chado_search_qualitative_traits_ajax_dynamic_trait')
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('value1')
      ->title('Value1')
      ->dependOnId('trait1')
      ->resetOnChagne('organism')
      ->callback('chado_search_qualitative_traits_ajax_values')
      ->newLine()
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
      ->callback('chado_search_qualitative_traits_ajax_dynamic_trait')
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('value2')
      ->title('Value2')
      ->dependOnId('trait2')
      ->resetOnChagne('organism')
      ->callback('chado_search_qualitative_traits_ajax_values')
      ->newLine()
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
      ->callback('chado_search_qualitative_traits_ajax_dynamic_trait')
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('value3')
      ->title('Value3')
      ->dependOnId('trait3')
      ->resetOnChagne('organism')
      ->callback('chado_search_qualitative_traits_ajax_values')
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('topLevel')
      ->startWidget('organism')
      ->endWidget('value3')
  );

  $form->addSubmit();
  $form->addReset();

  return $form;
}

// Validate the form
function chado_search_qualitative_traits_form_validate ($form, &$form_state) {
  $org = $form_state['values']['organism'];
  if (!$org) {
  form_set_error('organism', 'Please select a species.');
  }
}

// Submit the form
function chado_search_qualitative_traits_form_submit ($form, &$form_state) {
  $org = $form_state['values']['organism'];
  $t[0] = Sql::selectFilter('trait1', $form_state, 'trait_descriptor'); 
  $v[0] = Sql::selectFilter('value1', $form_state, 'trait_value');
  $t[1] = Sql::selectFilter('trait2', $form_state, 'trait_descriptor');
  $v[1] = Sql::selectFilter('value2', $form_state, 'trait_value');
  $t[2] = Sql::selectFilter('trait3', $form_state, 'trait_descriptor');
  $v[2] = Sql::selectFilter('value3', $form_state, 'trait_value');
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
         $sql = "SELECT * FROM (SELECT stock_id, variety_name, organism_id, organism, trait_descriptor AS trait$index, trait_value AS value$index FROM {chado_search_qualitative_traits_" . $org . "_crs}";
         $append .= "WHERE $c) T$index";
         $first_con = false;
         $first_table = $index;
       } else {
         if ($op[$index] == 'OR') {
           $append .= " FULL OUTER JOIN (SELECT  stock_id, variety_name, organism_id, organism, trait_descriptor AS trait$index, trait_value AS value$index FROM {chado_search_qualitative_traits_" . $org . "_crs} WHERE $c) T$index USING(stock_id, variety_name, organism_id, organism)";
         }
         else {
           $append .= " INNER JOIN (SELECT variety_name AS variety_name$index, trait_descriptor AS trait$index, trait_value AS value$index FROM {chado_search_qualitative_traits_" . $org . "_crs} WHERE $c) T$index ON (variety_name$index = T$first_table.variety_name";
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
      $sql = "SELECT first(stock_id) AS stock_id, variety_name, first(organism_id) AS organism_id, first(organism) AS organism, string_agg(trait_descriptor || ' = ' || trait_value, '; ') AS all_traits FROM {chado_search_qualitative_traits_" . $org . "_crs} GROUP BY variety_name";
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
    ->tableDefinitionCallback('chado_search_qualitative_traits_table_definition')
    ->append($append)
    ->disableCols($disabledCols)
    ->changeHeaders($changeHeaders)
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_qualitative_traits_table_definition () {
  $headers = array(      
    'variety_name:s:chado_search_qualitative_traits_link_stock:stock_id' => 'Germplasm',
    'organism:s:chado_search_qualitative_traits_link_organism:organism_id' => 'Species',
      'all_traits:s' => 'All Traits',
      'value0:s' => 'Trait1',
      'value1:s' => 'Trait2',
      'value2:s' => 'Trait3',
  );
  return $headers;
}

// Define call back to link the stock to its  node for the result table
function chado_search_qualitative_traits_link_stock ($stock_id) {
  return chado_search_link_entity('stock', $stock_id);
}

// Define call back to link the featuremap to its  node for the result table
function chado_search_qualitative_traits_link_organism ($organism_id) {
  return chado_search_link_entity('organism', $organism_id);
}

/*************************************************************
 * AJAX callbacks
*/
function chado_search_qualitative_traits_ajax_values ($val) {
  $org = isset($_POST['organism']) ? $_POST['organism'] : NULL;
  if ($org) {
  $sql = "SELECT distinct trait_value FROM {chado_search_qualitative_traits_" . $org . "_crs} WHERE trait_descriptor = :descriptor ORDER BY trait_value";
  return chado_search_bind_dynamic_select(array(':descriptor' => $val), 'trait_value', $sql);
  }
}

function chado_search_qualitative_traits_ajax_dynamic_trait ($value) {
  if ($value) {
    $sql = "SELECT DISTINCT trait_descriptor FROM {chado_search_qualitative_traits_" . $value . "_crs}";
    return chado_search_bind_dynamic_select(array($value), 'trait_descriptor', $sql);
  }
}
