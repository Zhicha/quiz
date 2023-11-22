<?php
include_once "config.php";
// var_dump($_GET);
// var_dump($_POST);

// удаляем данные
function delete_data() {
  global $db;
  //$id = $db->real_escape_string($id);
  $res = db_query("DELETE FROM questionnaire_data");
  return $res;  
}
  
// Проверка количества строк в таблице
function getCountField($date){
  $result=[];
  global $db;
  $id = $db->real_escape_string($id);
  $res = db_query( "SELECT 'date' FROM `questionnaire_data` WHERE `questionnaire_data`.`date` = '$date'");  
  while ($row = $res->fetch_assoc()) $result[] = $row['date'];
  return count($result);
}

// удаление одной строки
function delete_field($id) {
    global $db;
    $id = $db->real_escape_string($id);
    $res = db_query( "DELETE FROM `questionnaire_data` WHERE `questionnaire_data`.`id` = '$id'");  
    return $res;

    
}


// удаления блока фио
function delete_blank($data) {
    global $db;
    $data = $db->real_escape_string($data);
    $res = db_query( "DELETE FROM `questionnaire_data` WHERE `questionnaire_data`.`date` = '$data'");  
    
    return $res;
   
}


  // // Обновления строки
  // function updateData($id, $value) {  
  //   global $db;
  //   $id = $db->real_escape_string($id);
  //   $value = $db->real_escape_string($value);
  //   $res = db_query("UPDATE questionnaire_data SET value='{$value}' WHERE id='{$id}'");  
  //   return $res;
  // }
  


if($_GET['type']==='delete'){
  delete_data();
  exit();
}


  if ($_GET['id']&&$_GET['date']){
    $countFild = getCountField($_GET['date']);
    if($countFild>2){
    delete_field($_GET['id']);
    }else{
    delete_blank($_GET['date']);    
    }
   exit();
  }

  if ($_GET['date']){
    delete_blank($_GET['date']);
    exit();
  }


// if ($_GET['type'] === 'update') {
//   updateData($_GET['id'], $_GET['value']);
//   exit();
// }
 ?>


