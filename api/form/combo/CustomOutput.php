<?php

namespace ChadoSearch\form\combo;

class CustomOutput extends Filter {
  
  public $options;
  public $defaults;
  public $title;
  public $desc;
  public $groupby_selection;
  public $replace_star;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
    $title = $this->title ? $this->title : 'Customize output';
    $desc = $this->desc ? $this->desc : 'Select columns to display in the result table.';
    $options = $this->options;
    $columns = array('row-counter' => 'Row counter');
    foreach ($options AS $k => $v) {
      $key = explode(':', $k);
      $col = $key[0];
      $columns[$col] = $v;
    }
    $defaults =  array('row-counter');
    $defaults = is_array($this->defaults) ? array_merge($defaults, $this->defaults) : array_merge($defaults, array_keys($columns));
    $form[$id] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => $title,
      '#description' => $desc,
      '#prefix' => "<div id=\"chado_search-$search_name-$id-widget\" class=\"chado_search-custom_output-widget chado_search-widget\">",
      '#suffix' => '</div>'
    );
    $form[$id]['custom_output_options'] = array(
      '#type' => 'checkboxes',
      '#options' => $columns,
      '#default_value' => $defaults,
    );
    
    if ($this->groupby_selection) {
      $form['#custom_output-groupby_selection'] = $this->groupby_selection;
    }
    
    $form['#custom_output-replace_star_with_selection'] = $this->replace_star;
  }
}