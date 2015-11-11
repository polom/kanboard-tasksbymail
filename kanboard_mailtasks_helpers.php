<?php
function do_debug($message) {
  global $is_debug;
  if ($is_debug === true) {
    echo $message."\n";
  }
}
?>
