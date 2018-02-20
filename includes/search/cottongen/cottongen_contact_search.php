<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_contact_search_form ($form) {
  $form->addTextFilter(
      Set::textFilter()
      ->id('lname')
      ->title('Last Name')
      ->labelWidth(130)
      ->newline()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('fname')
      ->title('First Name')
      ->labelWidth(130)
      ->newline()
      );
  $form->addTextFilter(
      Set::textFilter()
      ->id('institution')
      ->title('Institution')
      ->labelWidth(130)
      ->newline()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('country')
      ->title('Country')
      ->table('chado_search_contact_search')
      ->column('country')
      ->labelWidth(130)
      ->newline()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('keywords')
      ->title('Research Interests')
      ->labelWidth(130)
      ->newline()
      );
  $form->addSubmit();
  $form->addReset();  
  $form->addFieldset(
      Set::fieldset()
      ->id('top-level')
      ->startWidget('lname')
      ->endWidget('reset')
      );
  return $form;
}

// Submit the form
function chado_search_contact_search_form_submit ($form, &$form_state) {
    $where = array();
    $where [] = Sql::textFilter('lname', $form_state, 'lname');
    $where [] = Sql::textFilter('fname', $form_state, 'fname');
    $where [] = Sql::textFilter('institution', $form_state, 'institution');
    $where [] = Sql::selectFilter('country', $form_state, 'country');
    $where [] = Sql::textFilter('keywords', $form_state, 'keywords');
  // Get base sql
  $sql = chado_search_contact_search_base_query();
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_contact_search_table_definition')
    ->execute($form, $form_state);
}

function chado_search_contact_search_base_query () {
  $sql = "SELECT * FROM {chado_search_contact_search}";
  return $sql;
}
/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_contact_search_table_definition () {
  $headers = array(
      'name:s:chado_search_link_contact:contact_id' => 'Name',
      'institution:s' => 'Institution',
      'address:s' => 'Address',
      'email:s' => 'Email',
      'keywords:s' => 'Research Interests'
  );
  return $headers;
}
