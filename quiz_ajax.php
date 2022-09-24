<?php
include_once 'config.php';
// удаляем данные
function delete_data() {
    global $db;
    //$id = $db->real_escape_string($id);
    $res = db_query("DELETE FROM questionnaire_data");

    return $res;
}

  delete_data();

 ?>
