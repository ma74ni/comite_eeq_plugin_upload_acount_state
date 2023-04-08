<?php

  /**
   * Plugin Name: Subir Estados de Cuenta
   * Plugin URI: http://digytalscript.com
   * Description: Subir los estados de cuenta
   * Author: Many
   * Version: 1.1
   * Author URI: http://www.holamany.com
   */

  function handlePost(): void
  {
    if ($_POST) {
      global $wpdb;

      $anio = $_POST['anio'];
      $mes = $_POST['mes'];
      $quincena = $_POST['quincena'];
      $table = $wpdb -> prefix . 'users_states_account';
      $arrayMeses = array(
        "1" => array(
          "1" => "01", "2" => "02",
        ), "2" => array(
          "1" => "03", "2" => "04",
        ), "3" => array(
          "1" => "05", "2" => "06",
        ), "4" => array(
          "1" => "07", "2" => "08",
        ), "5" => array(
          "1" => "09", "2" => "10",
        ), "6" => array(
          "1" => "11", "2" => "12",
        ), "7" => array(
          "1" => "13", "2" => "14",
        ), "8" => array(
          "1" => "15", "2" => "16",
        ), "9" => array(
          "1" => "17", "2" => "18",
        ), "10" => array(
          "1" => "19", "2" => "20",
        ), "11" => array(
          "1" => "21", "2" => "22",
        ), "12" => array(
          "1" => "23", "2" => "24",
        )
      );
      $numUsaQuincena = $anio . $arrayMeses[$mes][$quincena];
      $dataCount = $wpdb -> get_var(
        "SELECT COUNT(*) FROM `wp_users_states_account`
                WHERE usa_quincena = '$numUsaQuincena'");

      handleDelete($dataCount, $numUsaQuincena, $table);

      $file = $_FILES['upload']['name'];
      $filetype = wp_check_filetype($file, null);
      $extension = $filetype['ext'];

      if (!$extension == 'csv') {
        echo '<div class="m-alert-warning">Algo salió mal, el archivo no se pudo subir</div>';
      }
      $filename = $_FILES['upload']['tmp_name'];

      if (($handle = fopen($filename, 'r')) !== false) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
          $row++;
          if ($row == 1) {
            continue;
          }
          $userLogin = formatId($data[14]);
          $solicitud = htmlspecialchars($data[8], ENT_QUOTES, 'UTF-8');
          $codigo = $data[0];
          $usaQuincena = $numUsaQuincena;
          $habilitado = '0';
          $empresa = $data[4];
          $tipo = $data[5];
          $subTipo = $data[6];
          $producto = $data[7];
          $saldoAnterior = $data[9];
          $capital = $data[10];
          $interes = $data[11];
          $dcto = $data[12];
          $saldoAct = $data[13];
          $sql = $wpdb -> prepare("INSERT INTO `$table` VALUES (
                            0,
                            '$userLogin',
                            '$solicitud',
                            '$codigo',
                            '$usaQuincena',
                            '$habilitado',
                            '$empresa',
                            '$tipo',
                            '$subTipo',
                            '$producto',
                            '$saldoAnterior',
                            '$capital',
                            '$interes',
                            '$dcto',
                            '$saldoAct',
                            '$anio',
                            '$mes',
                            '$quincena',
                            '1'
                            )
                            ");
          $wpdb -> query($sql);
        }
        error_log($sql);
        echo '<div class="m-alert-success">Datos subidos con éxito</div>';
        fclose($handle);
      }
    }
  }

  function subirData()
  {
    $user = wp_get_current_user();
    if ($user) {
      handlePost();
      $anios = getAnios();
      $meses = getMeses();

      $template = '
      <div class="m-container my-5">
        <h2>Subir Estados de Cuenta</h2>
        <p>Seleccione un archivo .csv para subir.</p>
        <form id="upload_form" action="" enctype="multipart/form-data" method="post" target="messages">
          <div class="m-form-group">
            <label for="anio">AÑO</label>
            <select class="m-form-control" id="anio" name="anio">
              <option value=""> -SELECCIONE-</option>';
      $template .= cargarOptions($anios, 'anio');
      $template .= '
          </select>
        </div>
        <div class="m-form-group">
          <label for="mes">MES</label>
           <select class="m-form-control" id="mes" name="mes">
            <option value=""> -SELECCIONE-</option>';
      $template .= cargarOptions($meses, 'mes');
      $template .= '
          </select>
        </div>
        <div class="m-form-group">
          <label for="quincena">QUINCENA</label>
           <select class="m-form-control" id="quincena" name="quincena">
            <option value=""> -SELECCIONE-</option>
            <option value="1">1</option>
            <option value="2">2</option>
          </select>
        </div>
        <div class="m-form-group">
            <input name="upload" id="upload" type="file" />
        </div>
        <button class="btn btn-success" id="btnSubmit" type="submit">Subir</button>
      </form>
    </div>
    ';
      return $template;
    }

  }

  add_shortcode("upload_est_cta", "subirData");

  function getAnios()
  {
    global $wpdb;
    return $wpdb -> get_results("SELECT * FROM `wp_anios` WHERE state_anio = '1'");
  }

  function getMeses()
  {
    global $wpdb;
    return $wpdb -> get_results("SELECT * FROM `wp_meses`");
  }

  function cargarOptions($array, $type)
  {
    $template = '';
    foreach ($array as $item) {
      if ($type == 'anio') {
        $template .= '<option value="' . $item -> name_anio . '">' . $item -> name_anio . '</option>';
      }
      if ($type == 'mes') {
        $template .= '<option value="' . $item -> mes_id . '">' . $item -> mes_nombre . '</option>';
      }
    }
    return $template;
  }

  function handleDelete($array, $numUsaQuincena, $table)
  {
    global $wpdb;

    if ($array > 0) {
      $delete = $wpdb -> prepare("DELETE FROM `$table`
            WHERE usa_quincena = '$numUsaQuincena'");
      $wpdb -> query($delete);
    }
  }

  function formatId($id)
  {
    return strlen($id) < 10? str_pad($id, 10, "0", STR_PAD_LEFT) : $id;
  }

