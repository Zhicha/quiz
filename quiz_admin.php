<?php
include_once "config.php";

// получаем количество ответов
function checkLimits($id) {
    global $db;
    $id = $db->real_escape_string($id);
    $result = '';
    $res = db_query("SELECT COUNT(`id_list`) AS count_id FROM questionnaire_data WHERE `id_list`='$id'");
    while ($row = $res->fetch_assoc()) $result = $row['count_id'];
    return $result;
}

// получаем анкету
function getQuestionnaire() {
    $result = [];
    $res = db_query("SELECT q.id, q.name, q.header, q.comment,
      ql.id AS ql_id, ql.id_blank, ql.name AS ql_name, ql.type, ql.sort, ql.limits, ql.required
      FROM questionnaire AS q
      INNER JOIN questionnaire_list ql ON ql.id_blank = q.id
      WHERE 1 ORDER BY ql.sort");
    while ($row = $res->fetch_assoc()) $result[] = $row;
    return $result;
}

// получаем ответы из текстовых полей
function getIdTextField($id, $group=false) {
  global $db;
  $id = $db->real_escape_string($id);
  $result = []; 
  $result2 = [];
  $values = [];
  $res = db_query("SELECT `id`, `id_blank`, `name` FROM questionnaire_list WHERE `id_blank` = '$id' AND `type` = 'in' ORDER BY `id`");  
  while ($row = $res->fetch_assoc()) $result[$row['id']] = $row['name'];

  if ($group) {
   $res3 = db_query("SELECT `id`, `id_blank`, `name` FROM questionnaire_list WHERE `id_blank` = '$id' AND `type` = 'ch' ORDER BY `id`");
  while ($row = $res3->fetch_assoc()) $result2[$row['id']] = $row['name'];
  }  

  if (count($result) > 0) {
    foreach ($result as $key => $value) {
      if ($group) {
        $res2 = db_query("SELECT qd.id_list, qd.value, qd.date FROM questionnaire_data AS qd WHERE qd.id_list = '$key'");
        while ($row = $res2->fetch_assoc()) $values[$row['date']][$value] = $row['value'];
      } else {
        $res2 = db_query("SELECT `id_list`, `value`, `date`  FROM questionnaire_data WHERE `id_list` = '$key'");
        while ($row = $res2->fetch_assoc()) $values[] = [$row['id_list'], $row['value'], $row['date'], $value];
      }      
    }
  }

  if ($group) {
    if (count($result2) > 0) {
      foreach ($result2 as $key => $value) {
        $res4 = db_query("SELECT qd.id_list, qd.value, qd.date FROM questionnaire_data AS qd WHERE qd.id_list = '$key'");
        while ($row = $res4->fetch_assoc()) $values[$row['date']][$value] = $row['value'];
      }
    }
  }
  return $values;
}

$questionnaire = getQuestionnaire();
$questionnaire_name = $questionnaire[0]['name'];
$questionnaire_id = $questionnaire[0]['id'];
$header_text = $questionnaire[0]['header'];
$comment = $questionnaire[0]['comment'];
$value_text = getIdTextField($questionnaire_id);
$value_group = getIdTextField($questionnaire_id, true);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Опрос админ</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!--<link href="extensions/bootstrap_5.1.3/bootstrap.min.css" rel="stylesheet">
    <script src="extensions/bootstrap_5.1.3/bootstrap.bundle.min.js"></script>
    <script src="extensions/jquery_3.6.0/jquery.min.js"></script>-->
  </head>
  <body>
    <div class="container-sm" style="max-width: 500px;">
      <div class="row" style="font-size: 1.3em; margin: 25px 15px;">
        <h1 class="mb-3 text-center"><?php echo $questionnaire_name; ?></h1>
        <h3>По списку</h3>
        <?php foreach ($questionnaire as $key => $value):
          $point = $value['ql_name'];
          $limits = $value['limits'];
          $value['limits'] ? $limits = 'из '.$value['limits'].'.' : $limits = '';
          $total = checkLimits($value['ql_id']);
          if ($point === 'Ваши фамилия и имя') {
           $point = 'Всего';
          }
          echo "<p>{$point}: {$total} чел. {$limits}</p>";
          endforeach; ?>

          <?php
          $prev_field = '';
          foreach ($value_text as $key => $value):
          $field = $value[3];
          $value_text = $value[1];
          if ($prev_field !== $field && $field !== 'Ваши фамилия и имя') {
            echo  "<h3>{$field}</h3>";
          }
          if ($field !== 'ВВаши фамилия и имя') {
           echo "<p>{$value_text}</p>"; 
          }          
          $prev_field = $field;
          endforeach;
          echo  "<h3>Кто? Что?</h3>";
          foreach ($value_group as $key => $value) {
            echo '<div> <span>'.$value['Ваши фамилия и имя'].': </span>';
             
            foreach ($value as $key2 => $value2) {
              if ($key2 !== 'Ваши фамилия и имя') {
                echo '<span> '.$key2.' - '.$value2.'; </span>'; 
              }              
            }
            echo '</div><hr>';
          }          
          ?>
      </div>
      <button id="clear_data" class="btn btn-danger" style="margin-left: 110px;" type="button" name="button">Очистить форму</button>
    </div>
  </body>
  <script>
  $("#clear_data").click(function () {
    if (confirm("Удалить ответы из опроса?")) {
      fetch("quiz_ajax.php?type=delete")
      .then(response => response.text())
      .then(commits => {
		  location.reload();
        /*if (commits) {			
          
        }*/
      });
    }
  });
  </script>
  <style>
  .grey_text {
    font-size: 14px;
    color: gray;
    display: block;
  }
  </style>
</html>
