<?php
    date_default_timezone_set ("Europe/Moscow");
    //include_once 'logWriter.php';

    // establish mySQLi connection & database selection
    $host = $_SERVER['HTTP_HOST'];
    $db = new mysqli('localhost', "", "","");    

    // db query settings
    $db->set_charset('utf8');
    if ($db->connect_errno) die('Could not connect: '.$mysqli->connect_error);

    // make a query to the database
    function db_query ($query) {
      global $db;
      $res=$db->query ($query);
      if (!$res) throw new Exception ($db->error);
      return $res;
    }

    function db_multiQuery ($query) {
        global $db;
        $res=$db->multi_query ($query);
        if (!$res) throw new Exception ($db->error);
        return $res;
    }
