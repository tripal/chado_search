<?php
/**
 * Registers a drush command and constructs the full help for that command
 *
 * @return
 *   And array of command descriptions
 */
function chado_search_drush_command() {
  $items = array();
  $items['cs-settings-reload'] = array(
      'description' => dt('Reload the settings for the chado_search.'),
      'arguments'   => array(
      ),
      'examples' => array(
          'Standard example (reload settings)' => 'drush cs-settings-reload',
          'Using command alias' => 'drush csreload',
      ),
      'aliases' => array('csreload'),
  );
  $items['cs-run'] = array(
    'description' => dt('Call \'chado_search_<search_id>_drush_run\' function defined in any search.'),
    'arguments'   => array(
    ),
    'examples' => array(
      'Standard example (reload settings)' => 'drush cs-run',
      'Using command alias' => 'drush csrun',
    ),
    'aliases' => array('csrun'),
  );
  return $items;
}

/**
 * Callback for 'sbreload' or 'drush sb-reload'
 */
function drush_chado_search_cs_settings_reload() {
  // Create MViews if it's not arleady existing
  $enabledSearch = chado_search_get_enabled_searches(FALSE);
  foreach ($enabledSearch AS $search) {
    $mview = $search['mview_name'];
    if ($mview && !db_table_exists('chado.' . $mview)) {
      require_once $search['mview_file'];
      $func = $search['mview_callback'];
      $func();
    }
  }
  // Update Drupal menus
  menu_rebuild();
}

function drush_chado_search_cs_run() {
  $searches = chado_search_get_enabled_searches();
  foreach ($searches AS $search) {
    require_once $search['file'];
    $func = 'chado_search_' . $search['id'] . '_drush_run';
    if (function_exists($func)) {
      $func();
    }
  }
}