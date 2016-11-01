<?php

namespace ChadoSearch\form\control;

// This class is modeled by using Drupal 7 form elements
class Element {
  
  // Required attributes
  public $id;
  public $type;
  
  // Optional attributes
  public $search_name;
  
  // Computed attributes
  public $eid;
  public $prefix;
  public $suffix;
  
  // Drupal attributes which will be added to the form if it's not NULL
  public $access;
  public $after_build;
  public $ajax;
  public $array_parents;
  public $attached;
  public $attributes;
  public $autocomplete_path;
  public $collapsed;
  public $collapsible;
  public $cols;
  public $default_tab;
  public $default_value;
  public $delta;
  public $description;
  public $disabled;
  public $element_validate;
  public $empty;
  public $empty_option;
  public $empty_value;
  public $field_prefix;
  public $field_suffix;
  public $group;
  public $header;
  public $js_select;
  public $maxlength;
  public $multiple;
  public $options;
  public $parents;
  public $post_render;
  public $pre_render;
  public $process;
  public $required;
  public $resizable;
  public $return_value;
  public $rows;
  public $size;
  public $states;
  public $theme;
  public $theme_wrappers;
  public $title;
  public $title_display;
  public $tree;
  public $value_callback;
  public $weight;
  public $action;
  public $button_type;
  public $executes_submit_callback;
  public $limit_validation_errors;
  public $markup;
  public $method;
  public $name;
  public $src;
  public $submit;
  public $validate;
  public $value;
  
  public $newline;
  
  public function __construct($search_name, $id, $type) {
    $this->search_name = $search_name;
    $this->id = $id;
    $this->type = $type;

    // Generate values for computed fields
    $this->eid = 'chado_search-id-' . $id;
    $this->prefix = "<div id=\"chado_search-$search_name-$type-$id\" class=\"chado_search-$search_name-$type chado_search-$type chado_search-widget\">";
    $this->suffix = '</div>';
  }
  
  // Allow the sub-class to override or add new attributes to the form
  // If the subclass has its own veriable(s) to be used to set the form, you have to
  // use this function to add those changes
  public function setForm (&$form, &$form_state) {}
  
  public function attach(&$form, &$form_state) {
    // Default attributes
    $form[$this->id] = array(
      '#id' => $this->eid,      
      '#type' => $this->type,
      '#prefix' => $this->prefix,
      '#suffix' => $this->suffix
    );
    
    //Optional attributes
    if ($this->access !== NULL) {
      $form[$this->id]['#access'] = $this->access;
    }
    if ($this->after_build !== NULL) {
      $form[$this->id]['#after_build'] = $this->after_build;
    }
    if ($this->ajax !== NULL) {
      $form[$this->id]['#ajax'] = $this->ajax;
    }
    if ($this->array_parents !== NULL) {
      $form[$this->id]['#array_parents'] = $this->array_parents;
    }
    if ($this->attached !== NULL) {
      $form[$this->id]['#attached'] = $this->attached;
    }
    if ($this->attributes !== NULL) {
      $form[$this->id]['#attributes'] = $this->attributes;
    }
    if ($this->autocomplete_path !== NULL) {
      $form[$this->id]['#autocomplete_path'] = $this->autocomplete_path;
    }
    if ($this->collapsed !== NULL) {
      $form[$this->id]['#collapsed'] = $this->collapsed;
    }
    if ($this->collapsible !== NULL) {
      $form[$this->id]['#collapsible'] = $this->collapsible;
    }
    if ($this->cols !== NULL) {
      $form[$this->id]['#cols'] = $this->cols;
    }
    if ($this->default_tab !== NULL) {
      $form[$this->id]['#default_tab'] = $this->default_tab;
    }
    if ($this->delta !== NULL) {
      $form[$this->id]['#delta'] = $this->delta;
    }
    if ($this->description !== NULL) {
      $form[$this->id]['#description'] = $this->description;
    }
    if ($this->disabled !== NULL) {
      $form[$this->id]['#disabled'] = $this->disabled;
    }
    if ($this->element_validate !== NULL) {
      $form[$this->id]['#element_validate'] = $this->element_validate;
    }
    if ($this->empty !== NULL) {
      $form[$this->id]['#empty'] = $this->empty;
    }
    if ($this->empty_option !== NULL) {
      $form[$this->id]['#empty_option'] = $this->empty_option;
    }
    if ($this->empty_value !== NULL) {
      $form[$this->id]['#empty_value'] = $this->empty_value;
    }
    if ($this->field_prefix !== NULL) {
      $form[$this->id]['#field_prefix'] = $this->field_prefix;
    }
    if ($this->field_suffix !== NULL) {
      $form[$this->id]['#field_suffix'] = $this->field_suffix;
    }
    if ($this->group !== NULL) {
      $form[$this->id]['#group'] = $this->group;
    }
    if ($this->header !== NULL) {
      $form[$this->id]['#header'] = $this->header;
    }
    if ($this->js_select !== NULL) {
      $form[$this->id]['#js_select'] = $this->js_select;
    }
    if ($this->maxlength !== NULL) {
      $form[$this->id]['#maxlength'] = $this->maxlength;
    }
    if ($this->multiple !== NULL) {
      $form[$this->id]['#multiple'] = $this->multiple;
    }
    if ($this->options !== NULL) {
      $form[$this->id]['#options'] = $this->options;
    }
    if ($this->parents !== NULL) {
      $form[$this->id]['#parents'] = $this->parents;
    }
    if ($this->post_render !== NULL) {
      $form[$this->id]['#post_render'] = $this->post_render;
    }
    if ($this->pre_render !== NULL) {
      $form[$this->id]['#pre_render'] = $this->pre_render;
    }
    if ($this->process !== NULL) {
      $form[$this->id]['#process'] = $this->process;
    }
    if ($this->required !== NULL) {
      $form[$this->id]['#required'] = $this->required;
    }
    if ($this->resizable !== NULL) {
      $form[$this->id]['#resizable'] = $this->resizable;
    }
    if ($this->return_value !== NULL) {
      $form[$this->id]['#return_value'] = $this->return_value;
    }
    if ($this->rows !== NULL) {
      $form[$this->id]['#rows'] = $this->rows;
    }
    if ($this->size !== NULL) {
      $form[$this->id]['#size'] = $this->size;
    }
    if ($this->states !== NULL) {
      $form[$this->id]['#states'] = $this->states;
    }
    if ($this->theme !== NULL) {
      $form[$this->id]['#theme'] = $this->theme;
    }
    if ($this->theme_wrappers !== NULL) {
      $form[$this->id]['#theme_wrappers'] = $this->theme_wrappers;
    }
    if ($this->title !== NULL) {
      $form[$this->id]['#title'] = $this->title;
    }
    if ($this->title_display !== NULL) {
      $form[$this->id]['#title_display'] = $this->title_display;
    }
    if ($this->tree !== NULL) {
      $form[$this->id]['#tree'] = $this->tree;
    }
    if ($this->value_callback !== NULL) {
      $form[$this->id]['#value_callback'] = $this->value_callback;
    }
    if ($this->weight !== NULL) {
      $form[$this->id]['#weight'] = $this->weight;
    }
    if ($this->action !== NULL) {
      $form[$this->id]['#action'] = $this->action;
    }
    if ($this->button_type !== NULL) {
      $form[$this->id]['#button_type'] = $this->button_type;
    }
    if ($this->executes_submit_callback !== NULL) {
      $form[$this->id]['#executes_submit_callback'] = $this->executes_submit_callback;
    }
    if ($this->limit_validation_errors !== NULL) {
      $form[$this->id]['#limit_validation_errors'] = $this->limit_validation_errors;
    }
    if ($this->markup !== NULL) {
      $form[$this->id]['#markup'] = $this->markup;
    }
    if ($this->method !== NULL) {
      $form[$this->id]['#method'] = $this->method;
    }
    if ($this->name !== NULL) {
      $form[$this->id]['#name'] = $this->name;
    }
    if ($this->src !== NULL) {
      $form[$this->id]['#src'] = $this->src;
    }
    if ($this->submit !== NULL) {
      $form[$this->id]['#submit'] = $this->submit;
    }
    if ($this->validate !== NULL) {
      $form[$this->id]['#validate'] = $this->validate;
    }
    if ($this->value !== NULL) {
      $form[$this->id]['#value'] = $this->value;
    }
    
    // Default value. Set only if the value exists
    if (isset($form_state['values'][$this->id])) {
      $form[$this->id]['#default_value'] = $form_state['values'][$this->id];
    }
    
    // Add a newline to the end of the element
    if ($this->newline) {
      $form[$this->id]['#suffix'] .= "<div class=\"chado_search-element-newline\"> </div>";
    }
    
    // Warn if ID is not specified
    if (!$this->id) {
      form_set_error('invalid_id', "Please specify an ID for the'" . chado_search_get_class($this) . "'.");
    }
    
    // Allow override or add more attributes
    $this->setForm ($form, $form_state);
    
    //DEBUG - show the form element
    //if ($this->type == 'file') {dpm($form[$this->id]);}
  }
}
