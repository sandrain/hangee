<?
  session_start();

  try {
  	 require "php/init.php";
  	 include "php/controller.php";
  } catch (Exception $e) {
  	 echo $e;
  	 //handleException($e);
  }
  
?>