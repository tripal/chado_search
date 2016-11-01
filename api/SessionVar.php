<?php

namespace ChadoSearch;

class SessionVar {

  public static function setSessionVar ($search_id, $var, $value) {
    if ($value) {
      $form_build_id = $_POST['form_build_id'];
      $key = "$search_id-$form_build_id-$var";
      if (function_exists('session_status') && session_status() == PHP_SESSION_NONE) {
        session_start();
      }
      $_SESSION[$key] = $value;
      // session_write_close();
      // dpm('SET ' . $key . ' TO ' . $value);
    }
  }
  
  public static function getSessionVar ($search_id, $var) {
    $form_build_id = $_POST['form_build_id'];
    $key = "$search_id-$form_build_id-$var";
    $value = key_exists($key, $_SESSION) ? $_SESSION[$key] : NULL;
    // dpm('RECIEVED ' . $key . ' AS ' . $value);
    return $value;
  }
}