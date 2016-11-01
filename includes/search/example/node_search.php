<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * hook_form()  
 */
function chado_search_node_search_form ($form) {
  $form->addTextFilter(
      Set::textFilter()
      ->id('title')
      ->title('Title')
  );
  $form->addSubmit();    
  return $form;
}

/*************************************************************
 * hook_form_submit()
 */
 function chado_search_node_search_form_submit ($form, &$form_state) {
  $sql = "SELECT nid, title FROM node";
  $where [0] = Sql::textFilter('title', $form_state, 'title');
  Set::result()
  ->sql($sql)
  ->where($where)
  ->execute($form, $form_state);
}
