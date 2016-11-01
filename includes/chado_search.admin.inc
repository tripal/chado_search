<?php
/**
 * Launch page for chado_search administration.
 *
 */
function chado_search_admin_form() {
  
  $form = array();
  $searches = chado_search_get_all_searches (FALSE);

  $headers = array('Title', 'ID', 'URL', 'PHP', 'Enabled', 'Action');
  $rows = array();
  foreach ($searches AS $search) {
    $rows[] = array(
      $search['title'],
      $search['id'],
      $search['enabled'] ? l($search['path'], $search['path']) : $search['path'],
      $search['file'],
      $search['enabled'] ? 'Yes' : 'No',
      $search['enabled'] ? l('Disable', '/admin/mainlab/chado_search/action/disable/' . $search['id']) : l('Enable', '/admin/mainlab/chado_search/action/enable/' . $search['id']),
    );
  }
  if (count($rows) == 0) {
    $rows[] = array('No search available.', array('colspan' => 6));
  }

  $table = array(
    'header' => $headers,
    'rows' => $rows,
    'attributes' => array(),
    'sticky' => FALSE,
    'caption' => '',
    'colgroups' => array(),
    'empty' => '',
  );
  $output = theme_table($table);

  $form['instructions'] = array(
    '#type' => 'item',
    '#markup' => t('List of configured search interfaces')
  );
  $form['table'] = array(
    '#markup' => $output
  );
  return $form;
}

/**
 * chado_search form validation.
 *
 */
function chado_search_admin_form_validate($form, &$form_state) {
}

function chado_search_admin_action($action, $search_id) {
  if ($action == 'enable') {
    $succeed = chado_search_set_setting_by_id($search_id, 'enabled=1');
  }
  else if ($action == 'disable'){
    $succeed = chado_search_set_setting_by_id($search_id, 'enabled=0');
  }
  if ($succeed) {
      menu_rebuild();
  }
  drupal_goto('/admin/mainlab/chado_search/settings');
}