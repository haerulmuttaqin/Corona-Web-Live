<?php
$message = preg_replace('/(<\/?p>)+/', ' ', $message);
throw new Exception("Database error occured with message : {$message}");

?>