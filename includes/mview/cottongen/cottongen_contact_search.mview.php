<?php
// Create 'germplasm_search' MView
function chado_search_create_species_mview() {
  $view_name = 'chado_search_contact_search';
  chado_search_drop_mview($view_name);
  $schema = array(
  'table' => $view_name,
  'fields' => array(
    'contact_id' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'name' => array (
      'type' => 'varchar',
      'length' => '255'
    ),
    'description' => array (
      'type' => 'varchar',
      'length' => '255'
    ),
    'fname' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'lname' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'email' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'phone' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'fax' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'address' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'title' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'lab' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'institution' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'country' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'url' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'source' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'alias' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'name_code' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'keywords' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'last_update' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
  )
);
  $sql = "
SELECT 
  contact_id,
  name,
  description,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'first_name') AND CP.contact_id = C.contact_id) AS FNAME,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'last_name') AND CP.contact_id = C.contact_id) AS LNAME,	
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'email') AND CP.contact_id = C.contact_id) AS EMAIL,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'phone') AND CP.contact_id = C.contact_id) AS PHONE,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'fax') AND CP.contact_id = C.contact_id) AS FAX,	
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'address') AND CP.contact_id = C.contact_id) AS ADDRESS,	
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'title') AND CP.contact_id = C.contact_id) AS TITLE,	
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'lab') AND CP.contact_id = C.contact_id) AS LAB,	
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'institution') AND CP.contact_id = C.contact_id) AS INSTITUTION,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'country') AND CP.contact_id = C.contact_id) AS COUNTRY,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'url') AND CP.contact_id = C.contact_id) AS URL,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'source') AND CP.contact_id = C.contact_id) AS SOURCE,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'alias') AND CP.contact_id = C.contact_id) AS ALIAS,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'name_code') AND CP.contact_id = C.contact_id) AS NAMECODE,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'keywords') AND CP.contact_id = C.contact_id) AS KEYWORDS,
  (SELECT string_agg(value, ',') FROM contactprop CP WHERE type_id IN 
    (SELECT cvterm_id FROM cvterm WHERE name = 'last_update') AND CP.contact_id = C.contact_id) AS LASTUPDATE
FROM contact C 
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
