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
$confirm_data=[];
if (isset($_POST['sent'])) {
  // Добавляем ответы
  function setQuestionnaireAnswer($answers='') {
    // попробовать через filter_input_array
    global $db;

    $user_exist;
    $dates_a=[];
    if ($_POST['9']) {
      $value_check = strtolower($_POST['9']);
      $resu = db_query("SELECT qd.value FROM questionnaire_data AS qd WHERE qd.value='$value_check'");
      while ($row = $resu->fetch_assoc()) $user_exist = $row['value'];
      if ($user_exist) {
        echo '<html>
          <head>
            <title>Опрос</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
          </head>
          <body>
            <div class="container-sm" style="max-width: 500px;">
              <div class="row" style="font-size: 1.3em; margin: 25px 15px;">';
          echo "<h3>Извините вы уже выбрали:</h3>";
        if (isset($_COOKIE['confirm_data'])) {
          echo $_COOKIE['confirm_data'];          
        }
        echo '<a href="index.php">Вернуться к опросу.</a></div></div></body></html>';              
        exit();
      }
    }
    
    foreach ($_POST as $key => $value) {
      if ($value) {
        $key = $db->real_escape_string($key);
        $value = $db->real_escape_string($value);
        $limits;        
        $value_exist = checkLimits($key);
        if ($key !== 'sent') {
          $res = db_query("SELECT ql.id, ql.limits FROM questionnaire_list AS ql WHERE ql.id='$key'");
          while ($row = $res->fetch_assoc()) $limits = $row['limits'];

          if ($limits) {
            $value = strtolower($value);
            if ($limits > 0 && $value_exist < $limits) {
              $insert = db_query("INSERT INTO questionnaire_data (`id_list`, `value`) VALUES ('$key', '$value')");
              $dates_a[] = $db->insert_id;
            } else {
              echo '<html>
              <head>
                <title>Опрос</title>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
              </head>
              <body>
                <div class="container-sm" style="max-width: 500px;">
                  <div class="row" style="font-size: 1.3em; margin: 25px 15px;">';
              echo "<h3>Лимит исчерпан, попытайтесь заполнить <a href='index.php'>ещё раз</a>.</h3>";
              echo '<a href="index.php">Вернуться к опросу.</a></div></div></body></html>';              
              exit();
            }
          } else {
            $insert = db_query("INSERT INTO questionnaire_data (`id_list`, `value`) VALUES ('$key', '$value')");
            $dates_a[] = $db->insert_id;
          }          
        }
      }

    }

    $confirm_data = getQuestionnaireByUser($dates_a);    
    foreach ($confirm_data as $key => $value) {
      $text_comfirme .= "<h4>{$key} - {$value}</h4>";
    }
    return $text_comfirme;
  }
  
  $text_comfirme = setQuestionnaireAnswer($_POST);  
}
// получаем анкету
function getQuestionnaire() {
    $result = [];
    $condition = '1';    
    $res = db_query("SELECT q.id, q.name, q.header, q.comment,
      ql.id AS ql_id, ql.id_blank, ql.name AS ql_name, ql.type, ql.sort, ql.limits, ql.required
      FROM questionnaire AS q
      INNER JOIN questionnaire_list ql ON ql.id_blank = q.id
      WHERE $condition ORDER BY ql.sort");
    while ($row = $res->fetch_assoc()) $result[] = $row;
    return $result;
}

// data by user
function getQuestionnaireByUser($dates=[]) {
    $result = [];
    $condition = '';
    foreach ($dates as $key => $value) {
      if (!empty($condition)) {
       $condition .= ' OR ';
      }
      $condition .= " qd.id={$value} ";
    }    

    $res = db_query("SELECT qd.id, qd.id_list, qd.value, qd.date,
      ql.id, ql.id_blank, ql.name
      FROM questionnaire_data AS qd
      INNER JOIN questionnaire_list ql ON ql.id = qd.id_list
      WHERE $condition ORDER BY ql.id DESC");
    while ($row = $res->fetch_assoc()) $result[$row['name']] = $row['value'];
    return $result;
}

$questionnaire = getQuestionnaire();
$questionnaire_name = $questionnaire[0]['name'];
$questionnaire_id = $questionnaire[0]['id'];
$header_text = $questionnaire[0]['header'];
$comment = $questionnaire[0]['comment'];
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Опрос</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  </head>
  <body>
    <div class="container-sm" style="max-width: 500px;">
      <div class="row" style="font-size: 1.3em; margin: 25px 15px;">
        <h1 class="mb-3 text-center"><?php echo $questionnaire_name; ?></h1>
        <?php if (!isset($_POST['sent']) && !isset($_GET['stop'])): 
        $ready = false;
        ?>
        <p>Поставьте галочку, введите вашу фамилию и имя и нажмите синюю кнопку <b>«ОТПРАВИТЬ»</b>.</p>      
        <form action="index.php" class="was-validated" method="post">
        <?php foreach ($questionnaire as $key => $value):
          $id_ql = $value['ql_id'];
          $value_limits = checkLimits($id_ql);
          $name_ql = $value['ql_name'];
          $required = '';
          if ($value['required'] === 1) {
            $required = 'required';
          }
          $kol_vo = '';
          $disabled = '';
          if ($value['limits'] && $value_limits && ($value_limits >= (int)$value['limits'])) {
            $disabled = 'disabled';
            $kol_vo = "Нужное количество набрано.";
          } else {
            $kol_vo_tmp = (int)$value['limits'] - $value_limits;
            $kol_vo = "Нужно ещё {$kol_vo_tmp} чел.";
          }

          if (!$value['limits']) {
            $kol_vo = "Лимит не установлен.";
          }

          if ($value['type'] === 'ch') {
            echo "<div class='form-check mb-2'><input type='checkbox' class='form-check-input' id='check{$id_ql}' name='{$id_ql}' value='1' {$required} {$disabled}>
            <label class='form-check-label' for='check{$id_ql}'><b>{$name_ql}</b></label>
            <span class='grey_text'>{$kol_vo}</span>";
          } elseif ($value['type'] === 'in') {
            if ($ready) {              
              echo "<p>{$comment}</p>";
              echo "<div class='mb-2'><input type='text' class='input-google' id='input{$id_ql}' placeholder='{$name_ql}' name='{$id_ql}' {$required} {$disabled}>";
            }            
            if (!$ready) {
              echo "<p>{$header_text}</p>";
              echo "<div class='mb-2'><input type='text' class='input-google' id='input{$id_ql}' placeholder='{$name_ql}' name='{$id_ql}' {$required} {$disabled}>";
              $ready = true;
            }
            
          } ?>
          </div>
        <?php endforeach; ?>
        <input type="hidden" name="sent" value="<?php echo $questionnaire_id; ?>">
        <?php if ($questionnaire_id): ?>
        <span id="text_error">Выберите блюда и ведите вашу фамилию и имя.</span>
        <button type="submit" class="btn btn-primary btn-lg mt-3" disabled><b>ОТПРАВИТЬ</b></button>
        <?php endif; ?>
      </form>
      <?php endif; ?>
      <?php if (isset($_POST['sent'])): ?>
        <h3>Подождите <span class="spinner-border spinner-border-sm"></span></h3>
        <script>
        let text_comfirme = '<?php echo $text_comfirme; ?>';        
        document.cookie = "confirm_data="+text_comfirme+"; max-age=2592000";        
        setTimeout(function () {
          window.location = "index.php?stop";
        }, 1000);
        </script>
      <?php endif; ?>
      <?php if (isset($_GET['stop'])):?>
        <h3>Вы выбрали:</h3>
        <?php        
        if(isset($_COOKIE['confirm_data'])) {
          echo $_COOKIE['confirm_data'];
        }        
        ?>
        <h4>Спасибо</h4>
        <h6><a href="index.php">Вернуться к опросу.</h6>
      <?php endif; ?>
      </div>
    </div>
  </body>
  <script>
  function check_field_value() {
    let check = 0, check_name = 0;
    $("input").each(function () {
      if ($(this).attr("type") === "checkbox") {
        if ($(this).prop("checked")) {
          check++;          
        }
      } else if ($(this).attr("type") === "text") {
        if ($(this).val() && $(this).attr("placeholder") === "Другое") {
          check++;
        }
        if ($(this).val() && $(this).attr("placeholder") === "Ваши фамилия и имя") {
          check_name++;
        }
      }      
    });
    if (check === 0 || check_name === 0) {
      $(".btn-primary").attr("disabled", true);
      $("#text_error").text("Выберите блюда и ведите вашу фамилию и имя.");
    } else {
      $(".btn-primary").attr("disabled", false);
      $("#text_error").text("");
    }
  }
  $("input[type='checkbox']").change(function () {
    check_field_value();
    if ($(this).prop("checked")) {
      $("input[type='checkbox']").prop("disabled", true);
      $(this).prop("disabled", false);
    } else {
      $("input[type='checkbox']").each(function () {
        console.log($(this).next().next().text());
        if ($(this).next().next().text() === "Нужное количество набрано.") {
          $(this).prop("disabled", true);
        } else {
          $(this).prop("disabled", false);
        }
      });
      //$("input[type='checkbox']").prop("disabled", false);
    }
  });
  $("input[type='text']").keyup(function () {
    check_field_value();
  });
    // Проверка при отправки формы на лимит
  $("button[type='submit']").click(function () {
    if ($(this).prop("disabled")) {
      check_field_value();
    }
  });
  
  </script>
  <style>
  /* Стилизация полей ввода для модального окна */
  .form-check-input {
    border-radius: 0 !important;
  }
  .input-google {
    padding-left: 5px;
    width: 100%;
    border-radius: 0;
    border-top: none !important;
    border-left: none !important;
    border-right: none !important;
    border-bottom: 1px #198754 solid;
    box-shadow: none !important;
    font-weight: bold;
  }
  /* form starting stylings -------------------------------*/
  .input-google:focus {
    border-bottom: 2px #198754 solid;
    outline: none;
  }
  .grey_text {
    font-size: 14px;
    color: gray;
    display: block;
  }
  #text_error {
    color: red;
    display: block;
  }
  </style>
</html>
