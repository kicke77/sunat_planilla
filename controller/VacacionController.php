<?php

session_start();
//header("Content-Type: text/html; charset=utf-8");

$op = $_REQUEST["oper"];
if ($op) {
    //Empleador
    require_once '../util/funciones.php';
    require_once '../dao/AbstractDao.php';
    require_once '../dao/EmpleadorDao.php';
    //
    require_once '../dao/TrabajadorDao.php';
    require_once '../dao/DetallePeriodoLaboralDao.php';
    require_once '../dao/VacacionDao.php';
    //IDE_EMPLEADOR_MAESTRO
    require_once '../controller/ideController.php';
}

$responce = NULL;

if ($op == "cargar_tabla_trabajador") {
    $ESTADO = $_REQUEST['estado'];
    //echo $ESTADO;
    $responce = cargar_tabla_trabajador($ESTADO);
} else if ($op == "add") {
    echoo($_REQUEST);
    $responce = addVacacion();
}

echo (!empty($responce)) ? json_encode($responce) : '';

//DUPLICADO trabajador
function cargar_tabla_trabajador($ESTADO) {

    $dao_trabajador = new TrabajadorDao();

    $page = $_GET['page'];
    $limit = $_GET['rows'];
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = $_GET['sord']; // get the direction

    $WHERE = "";

    if (isset($_GET['searchField']) && ($_GET['searchString'] != null)) {

        $operadores["eq"] = "=";
        $operadores["ne"] = "<>";
        $operadores["lt"] = "<";
        $operadores["le"] = "<=";
        $operadores["gt"] = ">";
        $operadores["ge"] = ">=";
        $operadores["cn"] = "LIKE";
        if ($_GET['searchOper'] == "cn")
            $WHERE = "AND " . $_GET['searchField'] . " " . $operadores[$_GET['searchOper']] . " '%" . $_GET['searchString'] . "%' ";
        else
            $WHERE = "AND " . $_GET['searchField'] . " " . $operadores[$_GET['searchOper']] . "'" . $_GET['searchString'] . "'";
    }


    if (!$sidx)
        $sidx = 1;

    $count = $dao_trabajador->cantidadTrabajador(ID_EMPLEADOR_MAESTRO, $ESTADO, $WHERE);

    // $count = $count['numfilas'];
    if ($count > 0) {
        $total_pages = ceil($count / $limit); //CONTEO DE PAGINAS QUE HAY
    } else {
        //$total_pages = 0;
    }
    //valida
    if ($page > $total_pages)
        $page = $total_pages;

    // calculate the starting position of the rows
    $start = $limit * $page - $limit; // do not put $limit*($page - 1)
    //valida
    if ($start < 0)
        $start = 0;

    //llena en al array
    $lista = array();

    //$dao_trabajador->actualizarStock();

    $lista = $dao_trabajador->listarTrabajador(ID_EMPLEADOR_MAESTRO, $ESTADO, $WHERE, $start, $limit, $sidx, $sord);

// CONTRUYENDO un JSON
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $i = 0;

    // ----- Return FALSE no hay Productos
    if ($lista == null || count($lista) == 0) {
        return $responce;  /* break; */
    }
    //$lista = $lista[0];
    foreach ($lista as $rec) {

        $param = $rec["id_trabajador"];

        //--- ASK
        //__01__


        $array_fecha = getFechaVacacionCalc($param);
        //echo $fecha_vacacion;  fecha_calc
        //--- ASK!
       // $anio_futuro = date("Y");
        
     
         
     




        $_01 = $rec["nombre_tipo_documento"];
        $_02 = $rec["num_documento"];
        $_03 = $rec["apellido_paterno"];
        $_04 = $rec["apellido_materno"];

        $_05 = $rec["nombres"];
        //$_06 = $array_fecha['fecha_inicio'];
        $_07 = $array_fecha['fecha_calc'];
        
        
        $name = "DNI : ".$rec["num_documento"]." ".$rec["apellido_paterno"]." ".$rec["apellido_materno"]." ".$rec["nombres"];
        $js = "javascript:verVacacion('" . $param . "','".$name."')";
         $opciones = '<div id="divEliminar_Editar">				
          <span  title="Editar"  >
          <a class="divEditar" href="' . $js . '"></a>
          </span>
          &nbsp;
          </div>';  
        
        //$_06 = $rec["fecha_nacimiento"];        
        //$_07 = $rec["estado"];
        //$js = "javascript:cargar_pagina('sunat_planilla/view/edit_personal.php?id_persona=" . $param . "','#CapaContenedorFormulario')";
        
        //$opciones_1 = '<a href="' . $js . '">Modificar</a>';
        //$opciones_2 = '<a href="' . $js2 . '">Eliminar</a>';
        //$opciones = $rec['reporte'];
        //hereee

        $responce->rows[$i]['id'] = $param;
        $responce->rows[$i]['cell'] = array(
            $param,
            $_01,
            $_02,
            $_03,
            $_04,
            $_05,
            $_06,
            $_07,
            $opciones,
        );

        $i++;
    }

    return $responce;  //RETORNO A intranet.js
}

// Recursivo.
function getFechaVacacionCalc($id_trabajador) { //id por defautl is Activo
    // ALERT!!!
    // ALERT!!!
    // CALCULO DE VACACIONES importante la fecha del Servidor !   
    //__00 _buscar
    $dao = new DetallePeriodoLaboralDao();
    $data = $dao->buscarDetallePeriodoLaboral($id_trabajador);
    //echo "entrroorror\n";
      /* echo "<pre>";
      echo print_r($data);
      echo "<pre>";
     */
    //__01__
    $daov = new VacacionDao();

    $data_v = $daov->listarUltimaFechaVacacion($id_trabajador);
    //variables
    $fecha_i = null;

    //echo "todoodododo".var_dump($data_v);

    if ($data_v['fecha'] == null) {
        // AUN NO SE HA REGISTRADO NINGUNO , establece trabajador
        // segun la fecha de ingreso.
        //DATA = trabajador registrado      
        // - 2000
        // - 2012

        if (getFechaPatron($data['fecha_inicio'], "Y") < date("Y")) { //fecha < 2012
            $dia_past = getFechaPatron($data['fecha_inicio'], "d");
            $mes_past = getFechaPatron($data['fecha_inicio'], "m");
            //$anio_past = getFechaPatron($data['fecha_inicio'], "Y");


            $anio_now = date("Y");
            //$resta = ($anio_now - $anio_past) - 1;
            $fecha_i = ($anio_now - 1) . "-" . $mes_past . "-" . $dia_past;


            $biciesto_0 = date("L", strtotime($fecha_i));
            $num_0 = ($biciesto_0 == 1) ? 1 : 0;
            /* echo "biciento dentro = ".$data['fecha_inicio'];
              echo "\n\n\n";
              echo $num_0;
              echo "\n\n\n";
             */

            //echo "\nFECHA TRABAJADOR es Menorrrr\n";
        } else {
            //ES MAYOR !  eh igual.
            $fecha_i = "FECHA ES MAYO ! Eh igual";
            //echo "INESSSSPERADO ? ";
            echo $fecha_i;
        }

        //$fecha_i = $data['fecha_inicio'];
    } else {
        $fecha_i = $data_v['fecha']; //fecha Calculada.. before insert.
    }

    //echo " \nfuera IF\nfecha_i = ".$fecha_i;
    //echo "\n\n\n";
    $biciesto = date("L", strtotime(date("Y-m-d")));
    $num = ($biciesto == 1) ? 1 : 0;
    $num_global = $num;
    //echo "\nnum global = ".$num_global;
    // SE SUMA UN 1 dia // CAda 4 años = 366
    $rpta = array();
    $rpta['fecha_inicio']= $data['fecha_inicio'];
    $rpta['fecha_calc'] = crearFecha($fecha_i, (365 + $num_global), 0, 0);
    //echo " \nRETURN  == ".$rpta;
    //echo "\n";
    //var_dump($rpta);


    return $rpta;
}

function addVacacion() {

    //$post = $_REQUEST;
    $f_calculado = getFechaPatron($_REQUEST['fv_calculado'], "Y-m-d");
    $f_programado = null;
    //$f_programado = getFechaPatron($post['fv_programado'], "Y-m-d");


    if (isset($_REQUEST['fv_programado'])) {
        //echo "isset";
        $f_programado = $f_calculado;
    } else {
        //echo "else";
        $f_programado == null;
    }

    $dao = new VacacionDao();
    $dao->add($_REQUEST['id_trabajador'], $f_calculado, $f_programado);
    /*
      echo "FINALL\N";
      echoo($f_calculado);
      echo "dd";
      echoo($f_programado);
     */
    return true;
}



?>