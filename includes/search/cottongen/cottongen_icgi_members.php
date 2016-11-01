<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_icgi_members_form ($form) {
  $form->addTextFilter(
      Set::textFilter()
      ->id('lname')
      ->title('Last Name')
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('organization')
      ->title('Organization')
      ->labelWidth(90)
  );
  $options = array();
  $sql = "SELECT distinct value from profile_value where fid = (SELECT fid from profile_field WHERE name = 'profile_country') AND value <> ''";
  $result = db_query($sql);
  while ($opt = $result->fetchField()) {
    array_push($options, $opt);
  }
  sort($options);
  $form->addSelectOptionFilter(
      Set::selectOptionFilter()
      ->id('country')
      ->title('Country')
      ->options($options)
  );
  $form->addSubmit();
  return $form;
}

// Submit the form
function chado_search_icgi_members_form_submit ($form, &$form_state) {
    $where = array();
    $where [0] = Sql::textFilter('lname', $form_state, 'lname');
    $where [1] = Sql::textFilter('organization', $form_state, 'organization');
    $where [2] = Sql::selectFilter('country', $form_state, 'country');
  // Get base sql
  $sql = chado_search_icgi_members_base_query();
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_icgi_members_table_definition')
    ->execute($form, $form_state);
}

function chado_search_icgi_members_base_query () {
  $sql = "
    SELECT 
      lname || ', ' || fname AS name,
      case 
        when org_website = '' 
        then position || '<br>' || organization 
        else position || '<br>' || '<a href=' || org_website ||'>' || organization || '</a>'
        end AS affiliation,
      address1 || address2 || city || country AS address,
      '<a href=mailto:' || mail || '>' || mail || '</a>' AS email,
      *
      FROM (
      SELECT 
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_last_name')) AS lname,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_first_name')) AS fname,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_position')) AS position,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_organization')) AS organization,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_org_website')) AS org_website,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_street_address_1')) AS address1,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_street_address_2')) AS address2,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_city')) AS city,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_country')) AS country,
        U.mail,
        (select value from profile_value where uid = U.uid and fid = (select fid from profile_field where name = 'profile_alt_email')) AS alt_email
        FROM users U 
        INNER JOIN users_roles UR ON U.uid = UR.uid
        INNER JOIN role R ON R.rid = UR.rid
        WHERE R.name = 'icgi member'
        AND U.uid <> 0
        ORDER BY lname
      ) chado_search_icgi_members
      ";
  return $sql;
}
/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_icgi_members_table_definition () {
  global $user;
  if(in_array('icgi member', $user->roles)) {
  $headers = array(
      'name:s' => 'Name',
    'affiliation:s' => 'Affiliation',
      'address:s' => 'Address',
      'email:s' => 'Email'
  );
  } else {
    $headers = array(
      'name:s' => 'Name',
      'affiliation:s' => 'Affiliation'
    );
  }
  return $headers;
}

// Define call back to link the icgi_members to its  node for the result table
function chado_search_icgi_members_link_icgi_members ($icgi_members_id) {
  $nid = chado_get_nid_from_id('icgi_members', $icgi_members_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the project to its  node for the result table
function chado_search_icgi_members_link_parent ($stock_id) {
  $nid = chado_get_nid_from_id('stock', $stock_id);
  return chado_search_link_node ($nid);
}