<?php
/**
 * Plugin Name: Subir Estados de Cuenta
 * Plugin URI: http://digytalscript.com
 * Description: Subir los estados de cuenta
 * Author: Many
 * Version: 0.1
 * Author URI: http://www.holamany.com
 */

function subir_data() {
  //provides access to WP environment
  require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  


 $user = wp_get_current_user();
  if($user) {

    global $wpdb;

  if($_POST){
    $anio = $_POST['anio'];
    $mes = $_POST['mes'];
    $quincena = $_POST['quincena'];
    $table = $wpdb->prefix.'users_states_account';
    $array_meses = array (
      "1" => array (
        "1" => "01",
        "2" => "02",
      ),
      "2" => array (
        "1" => "03",
        "2" => "04",
      ),
      "3" => array (
        "1" => "05",
        "2" => "06",
      ),
      "4" => array (
        "1" => "07",
        "2" => "08",
      ),
      "5" => array (
        "1" => "09",
        "2" => "10",
      ),
      "6" => array (
        "1" => "11",
        "2" => "12",
      ),
      "7" => array (
        "1" => "13",
        "2" => "14",
      ),
      "8" => array (
        "1" => "15",
        "2" => "16",
      ),
      "9" => array (
        "1" => "17",
        "2" => "18",
      ),
      "10" => array (
        "1" => "19",
        "2" => "20",
      ),
      "11" => array (
        "1" => "21",
        "2" => "22",
      ),
      "12" => array (
        "1" => "23",
        "2" => "24",
      )
    );
    $num_usa_quincena = $anio . $array_meses[$mes][$quincena];
    $data_count = $wpdb->get_var( "SELECT COUNT(*) FROM `wp_users_states_account` WHERE usa_quincena = '$num_usa_quincena'");

    if($_FILES['upload']['name']) {
      $file = $_FILES['upload']['name'];
      $filetype = wp_check_filetype( $file, null );
      $extension = $filetype['ext'];
      if( $data_count > 0) {
        $delete = $wpdb -> prepare(
            "DELETE FROM `$table`
            WHERE usa_quincena = '$num_usa_quincena'"
          );
        $wpdb->query($delete);
      }
      if($extension == 'csv') {
        $filename = $_FILES['upload']['tmp_name'];
        $handle = fopen($filename, "r");
        $row = 0;
        while( ($data = fgetcsv($handle, 1000, ";") ) !== FALSE ){
          $row++;
          if ($row == 1) { continue; }
          $sql = $wpdb -> prepare(
            "INSERT INTO `$table` 
            (`user_login`,`usa_solicitud`,`usa_codigo`,`usa_quincena`,`usa_habilitado`,`usa_empresa`,`usa_tipo`,`usa_subtipo`,`usa_producto`,`usa_saldo_ant`,`usa_capital`,`usa_interes`,`usa_dcto`,`usa_saldo_act`,`usa_anio`,`usa_mes`,`usa_num_quincena`) 
            VALUES ('$data[15]', '$data[9]', '$data[0]', '$data[1]', '$data[4]', '$data[5]', '$data[6]', '$data[7]', '$data[8]', '$data[10]', '$data[11]', '$data[12]', '$data[13]', '$data[14]', '$anio', '$mes', '$quincena')"
          );
          $wpdb->query($sql);
        }
        echo '<div class="m-alert-success">Datos subidos con éxito</div>';
         fclose($handle);
      } else {
        echo '<div class="m-alert-warning">Algo salió mal, el archivo no se pudo subir</div>';
        //wp_die();
      }
    }
  }

    $anios = $wpdb->get_results(
      "SELECT * 
      FROM `wp_anios` WHERE state_anio = '1'");

    $meses = $wpdb->get_results(
      "SELECT * 
      FROM `wp_meses`");
      
    $template = '
    <div class="m-container my-5">
      <h2>Subir Estados de Cuenta</h2>
      <p>Seleccione un archivo .csv para subir.</p>
      <form id="upload_form" action="" enctype="multipart/form-data" method="post" target="messages">
        <div class="m-form-group">
          <label for="anio">AÑO</label>
          <select class="m-form-control" id="anio" name="anio">
            <option value=""> -SELECCIONE-</option>';
            foreach($anios as $anio) {
              $template .= '<option value="'. $anio->name_anio .'">'. $anio->name_anio .'</option>';
            }
          $template .= '
          </select>
        </div>
        <div class="m-form-group">
          <label for="mes">MES</label>
           <select class="m-form-control" id="mes" name="mes">
            <option value=""> -SELECCIONE-</option>';
            foreach($meses as $mese) {
              $template .= '<option value="'. $mese->mes_id .'">'. $mese->mes_nombre .'</option>';
            }
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
 add_shortcode("upload_est_cta", "subir_data");