<?php

$op = $_REQUEST["oper"];
if ($op) {
    session_start();
    require_once '../util/funciones.php';
    require_once '../dao/AbstractDao.php';
    // IDE_EMPLEADOR_MAESTRO
    require_once '../controller/ideController.php';

    //Actualizar PLAME   
    require_once '../model/TrabajadorPdeclaracion.php';
    require_once '../dao/TrabajadorPdeclaracionDao.php';
    require_once '../dao/DeclaracionDconceptoDao.php';

    require_once '../model/DeclaracionDconcepto.php';

    // AFP
    require_once '../model/ConfAfp.php';
    require_once '../dao/ConfAfpDao.php';
    require_once '../controller/ConfAfpController.php';
    // IDE CONFIGURACION 
    require_once '../dao/ConfAsignacionFamiliarDao.php';
    require_once '../dao/ConfSueldoBasicoDao.php';
    require_once '../dao/ConfEssaludDao.php';
    require_once '../dao/ConfOnpDao.php';
    require_once '../dao/ConfUitDao.php';




    // POR UNICA VEZ UTILIZAMOS  librerias  calcularSegudaQuincena
    require_once '../controller/EtapaPagoController.php';
    //ETAPA PAGO
    require_once '../dao/EtapaPagoDao.php';
    require_once '../model/EtapaPago.php';

    require_once '../dao/PlameDeclaracionDao.php';
    require_once '../dao/PlameDao.php';
    require_once '../dao/PlameDetalleConceptoEmpleadorMaestroDao.php';

    //PAGO
    require_once '../dao/PagoDao.php';
    require_once '../model/Pago.php';

    //EPAGO TRABAJADOR
    require_once '../dao/PeriodoRemuneracionDao.php';

    // Renta de QUINTA
    require_once '../controller/IR5Controller.php';
    require_once '../dao/PlameDetalleConceptoAfectacionDao.php';
    //mass ++ + 5ta essalud, onp ,afp
    require_once '../dao/RegistroPorConceptoDao.php';
    require_once '../controller/ConfConceptosFController.php';
    require_once '../controller/ConfConceptosController.php';

    //
    //
    //CONFIGURACION DE 28 Y NAVIDAD
    require_once '../dao/VacacionDao.php';
    
    // reporte txt
    require_once '../dao/EstablecimientoDao.php';
    require_once '../dao/EmpresaCentroCostoDao.php';
    require_once '../dao/EstablecimientoDireccionDao.php';
    require_once '../dao/PersonaDireccionDao.php';
    require_once '../dao/DetalleRegimenPensionarioDao.php';
    
    //reporte tabla
    
    
        //ZIP
    require_once '../util/zip/zipfile.inc.php';
}




$response = NULL;
if ($op == "add") {
    //$response = add_PtrabajadorPdeclaracion();
} else if ($op == "generar_declaracion") {
    $ID_PDECLARACION = $_REQUEST['id_pdeclaracion'];
    $estado = generarConfiguracion($ID_PDECLARACION);

    if ($estado == true) {
        generarDeclaracionPlanillaMensual($ID_PDECLARACION);
    }
} else if ($op == "cargar_tabla_2") {

    $response = listar_trabajadorPdeclaracion();
} else if ($op == "grid_lineal") {

    $response = cargar_tabla_grid_lineal();
} else if ($op == "del") {
    $response = eliminar_trabajadorPdeclaracion();
} else if ($op == "recibo30") {
    $ID_PDECLARACION = $_REQUEST['id_pdeclaracion'];
    $estado = generarConfiguracion($ID_PDECLARACION);
 if ($estado == true) {
    generarBoletaTxt($ID_PDECLARACION);
 }
}


echo (!empty($response)) ? json_encode($response) : '';

/**
 *
 * @param type $ID_PDECLARACION 
 * 
 * Configuracion...
 */
function generarConfiguracion($ID_PDECLARACION) {

    $daox = new PlameDeclaracionDao();
    $datax = $daox->buscar_ID($ID_PDECLARACION);
    $periodo = $datax['periodo'];

//--- SUELDO BASE = SB
    $dao_1 = new ConfSueldoBasicoDao();
    $SB = $dao_1->vigenteAux($periodo);

//--- ASIGNACION FAMILIAR = AF
    $dao_2 = new ConfAsignacionFamiliarDao();
    $T_AF = $dao_2->vigenteAux($periodo);

//--- TASA ESSALUD = T_ ESSALUD
    $dao_3 = new ConfEssaludDao();
    $T_ESSALUD = $dao_3->vigenteAux($periodo);

//--- TASA ONP = T_ONP
    $dao_4 = new ConfOnpDao();
    $T_ONP = $dao_4->vigenteAux($periodo);

//--- UIT
    $dao_5 = new ConfUitDao();
    $UIT = $dao_5->vigenteAux($periodo);

// Valores Fijos
    $ESSALUD_MAS = 5.00;
    $SNP_MAS = 5.00;
    /*
      echo "8888888888888888888888888888888888888888888888";

      echo "SB =".$SB;
      echo "T_AF =".$T_AF;
      echo "T_ESSALUD =".$T_ESSALUD;
      echo "T_ONP =".$T_ONP;
      echo "UIT =".$UIT;

      echo "8888888888888888888888888888888888888888888888";
     */


    // DEFINE
    if (is_null($SB) || is_null($T_AF) || is_null($T_ESSALUD) || is_null($T_ONP) || is_null($UIT)) {
        //header($string, $replace)
        //header('Location: www.google.com');

        $SB = 0;
        $T_AF = 0;
        $T_ESSALUD = 0;
        $T_ONP = 0;
        $UIT = 0;

        $ESSALUD_MAS = 0;
        $SNP_MAS = 0;
    } else {
        define('SB', $SB);
        define('T_AF', $T_AF);
        define('T_ESSALUD', $T_ESSALUD); // ojoooooooooooooooooooooooooooooo? xq habili?=
        define('T_ONP', $T_ONP);
        define('UIT', $UIT);

        define('ESSALUD_MAS', $ESSALUD_MAS);
        define('SNP_MAS', $SNP_MAS);

        return true;
    }
}

//EtapaPagoController
function calcularSegudaQuincena($ID_PDECLARACION) {
    $COD_PERIODO_REMUNERACION = 2;
    //$COD_PERIODO_REMUNERACION = $_REQUEST['cod_periodo_remuneracion'];
    $ids_trabajador = $_REQUEST['ids'];
    //========================================================================//
    $daoPlame = new PlameDeclaracionDao();
    $data_d = $daoPlame->buscar_ID($ID_PDECLARACION);

    $FECHA_PERIODO = $data_d['periodo'];
    $FECHAX = getFechasDePago($FECHA_PERIODO);
    $FECHA = array();
    //========================================================================//
    //---
    if (/* count($data_id_etapa_pago) == 1 */true) { //Segunda QUINCENA SI o SI
        $FECHA['inicio'] = $FECHAX['second_weeks_mas1']; //16/01/2012 a 31/01/2012
        $FECHA['fin'] = $FECHAX['last_day'];
        //================================
        $dao = new EtapaPagoDao();
        $id_etapa_pago = $dao->buscarEtapaPago_ID($ID_PDECLARACION, 2, 2);

        if (is_null($id_etapa_pago)) {

            $model = new EtapaPago();
            $model->setId_pdeclaracion($ID_PDECLARACION);
            $model->setCod_periodo_remuneracion($COD_PERIODO_REMUNERACION);
            $model->setFecha_inicio($FECHA['inicio']);
            $model->setFecha_fin($FECHA['fin']);
            $model->setGlosa("Segunda Quincena");
            $model->setTipo("2");
            $model->setFecha_creacion(date("Y-m-d"));

            $id_etapa_pago = $dao->registrar($model);
        }
        //--------------------------------
        registrar_15($id_etapa_pago, $FECHA['inicio'], $FECHA['fin']/* , $ids_trabajador */);

        //--------------------------------
        // registrar_15($id_etapa_pago, $FECHA['inicio'], $FECHA['fin']);
    } else {
        echo "CASO INCONTROLABLE";
    }
}

function generarDeclaracionPlanillaMensual($ID_PDECLARACION) {
    /* OJO Para controlar mejor :
     * 01 :: listado de todos los trabajadores activos con su padre Persona.
     * 02 :: Preguntar sii pertenece al periodo N.
     * 03 :: listar con certesa. 
     */

//==============================================================================
    //$ID_PDECLARACION = $_REQUEST['id_pdeclaracion'];
    $ids = $_REQUEST['ids'];

    calcularSegudaQuincena($ID_PDECLARACION);

    echo "**************** paso de CALC segunda quincena 15";
    ECHO " ID_PDECLARACION   :::" . $ID_PDECLARACION;

    //DAO (workers list of declaracion)
    $dao = new PlameDeclaracionDao();
    $data_traa = $dao->listarDeclaracionEtapa($ID_PDECLARACION);


    //...PERIODO DECLARACION...!
    //$dao->lis

    echo "<pre> data_traa";
    print_r($data_traa);
    echo "</pre>";

    //TRABAJADORES YA REGISTRADOS (0 si no hay registrados aun)
    $dao_trapdecla = new TrabajadorPdeclaracionDao();
    $_data_id_trabajador = $dao_trapdecla->listar($ID_PDECLARACION, "id_trabajador");

    if (count($data_traa) == count($_data_id_trabajador)) {
        // echo "DATOS YA SON IGUALES NO PUEDE seguir registrando MAS. [TRUNCADO-MENSUAL]! ";
        return false;
    }

    /* --------------filtro de  id_trabajadores ------------- */

    for ($i = 0; $i < count($_data_id_trabajador); $i++) {
        for ($j = 0; $j < count($data_traa); $j++) {
            if ($_data_id_trabajador[$i]['id_trabajador'] == $data_traa[$j]['id_trabajador']) {
                unset($data_traa[$j]);
                break; //??
            }
        }
    }

    $data = array_values($data_traa);

    /* --------------filtro de  id_trabajadores ------------- */

    echo "<pre>  IDDDSSSSDDSSS";
    print_r($ids);
    echo "</pre>";
    if (isset($ids)) {
        //filtro//
        $ids_tra = array();
        for ($i = 0; $i < count($ids); $i++) {
            for ($j = 0; $j < count($data); $j++) {
                if ($ids[$i] == $data[$j]['id_trabajador']) {

                    $ids_tra[]['id_trabajador'] = $data[$j]['id_trabajador']; //$data[$j];
                    break; //
                }
            }
        }
        $data = null;
        $data = $ids_tra; //array_values($data_traa);  
    }

    // paso 01 :: Get todos los -> id_trabajador
    $ID_TRABAJADOR = array();
    for ($i = 0; $i < count($data); $i++) {
        $ID_TRABAJADOR[] = $data[$i]['id_trabajador'];
    }
    $data = null;
//==============================================================================
    //paso 02 :: Registrar [trabajadores_pdeclaraciones]

    echo "<pre> Insert  list trabajadores";
    print_r($ID_TRABAJADOR);
    echo "</pre>";

    //DAO
    $dao_rpc = new RegistroPorConceptoDao();


    for ($i = 0; $i < count($ID_TRABAJADOR); $i++) {




        //REGISTRAMOS TRABAJADOR (declaracion Mensual)
        // ..... anes Genero la Seguna Quincenaaaaa
        $dao_pago = new PagoDao();
        $data_sum = $dao_pago->dosQuincenas($ID_PDECLARACION, $ID_TRABAJADOR[$i]);

        $obj = new TrabajadorPdeclaracion();
        $obj->setId_pdeclaracion($ID_PDECLARACION);
        $obj->setId_trabajador($ID_TRABAJADOR[$i]);
        $obj->setDia_laborado($data_sum['dia_laborado']);
        $obj->setDia_total($data_sum['dia_laborado']);
        $obj->setOrdinario_hora($data_sum['ordinario_hora']);
        $obj->setOrdinario_min($data_sum['ordinario_min']);
        $obj->setSobretiempo_hora($data_sum['sobretiempo_hora']);
        $obj->setSobretiempo_min($data_sum['sobretiempo_min']);
        $obj->setSueldo($data_sum['sueldo']);
        $obj->setSueldo_neto($data_sum['sueldo_neto']);
        $obj->setEstado(0);
        $obj->setFecha_creacion(date("Y-m-d H:i:s"));

        //   Configurar sueldo Automatico
        //
          $data_sum['sueldo'] = sueldoDefault($data_sum['sueldo']);
        //
        //
          //  Configurar sueldo Automatico
        // LISTADO DE trabajadores ya registrados en el periodo o mes ejem: mm/yyyy
        //echo "id_trabajador_pdeclaracion = " . $id_trabajador_pdeclaracion;
        //....................................................................//
        // Otras utilidades
        $dao = new TrabajadorPdeclaracionDao();
        $DATA_TRA = $dao->buscar_ID_trabajador($ID_TRABAJADOR[$i]);

        //Registrar datos adicionales del Trabajador
        $obj->setIngreso_5ta_categoria(0);
        $obj->setCod_tipo_trabajador($DATA_TRA['cod_tipo_trabajador']);
        $obj->setCod_regimen_pensionario($DATA_TRA['cod_regimen_pensionario']);
        $obj->setCod_regimen_aseguramiento_salud($DATA_TRA['cod_regimen_aseguramiento_salud']);
        $obj->setCod_situacion($DATA_TRA['cod_situacion']);
        //ADD 22/09/2012
        $obj->setCod_ocupacion_p($DATA_TRA['cod_ocupacion_p']);
        $obj->setCod_ocupacion_p($DATA_TRA['id_empresa_centro_costo']);
        

        $id_trabajador_pdeclaracion = $dao->registrar($obj);
// --- Comment end 
//....................................................................//
        // paso 03 :: Consultar Conceptos
        // INGRESOS
        ECHO "\n\n\nREMUNERACION VACACIONAL  0118";
        $bandera = concepto_0118($id_trabajador_pdeclaracion, $ID_PDECLARACION, $ID_TRABAJADOR[$i], $data_sum['sueldo']);
        if ($bandera == false) {
            ECHO "SEULDO BASICO";
            concepto_0121($id_trabajador_pdeclaracion, $data_sum['sueldo']);
        }





        //Asignacion familiar
        ECHO "ASIGNACION FAMILIAR";
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C201);
        if (isset($data_val['valor'])) {
            concepto_0201($id_trabajador_pdeclaracion, $data_sum['sueldo']);
        }
        // DESCUENTOS - ADELANTO
        ECHO "DESCUENTO -ADELANTO ";
        concepto_0701($ID_TRABAJADOR[$i], $ID_PDECLARACION, $id_trabajador_pdeclaracion);

        // paso 04 :: Preguntar si el trabajador cumple:
        // TRIBUTOS Y APORTACIONES
        // Regimen de Salud
        if ($DATA_TRA['cod_regimen_aseguramiento_salud'] == '00') { //ok Regimen de Salud Regular
            concepto_0804($id_trabajador_pdeclaracion, $ID_PDECLARACION, $ID_TRABAJADOR[$i]);
        } else {
            // null
        }
        // Regimen Pensionario
        //AFP

        echo "<pre>DAtAA...";
        print_r($DATA_TRA);
        echo "</pre>";

        if ($DATA_TRA['cod_regimen_pensionario'] == '02') { //ONP
            concepto_0607($id_trabajador_pdeclaracion, $ID_PDECLARACION, $ID_TRABAJADOR[$i]);
        } else if ($DATA_TRA['cod_regimen_pensionario'] == '21') { //Integra
            concepto_AFP($id_trabajador_pdeclaracion, '21', $ID_PDECLARACION, $ID_TRABAJADOR[$i]);
        } else if ($DATA_TRA['cod_regimen_pensionario'] == '22') { //horizonte
            concepto_AFP($id_trabajador_pdeclaracion, '22', $ID_PDECLARACION, $ID_TRABAJADOR[$i]);
        } else if ($DATA_TRA['cod_regimen_pensionario'] == '23') { //Profuturo
            concepto_AFP($id_trabajador_pdeclaracion, '23', $ID_PDECLARACION, $ID_TRABAJADOR[$i]);
        } else if ($DATA_TRA['cod_regimen_pensionario'] == '24') { //Prima
            concepto_AFP($id_trabajador_pdeclaracion, '24', $ID_PDECLARACION, $ID_TRABAJADOR[$i]);
        } else {
            //null
        }


        //Otra utilidades
        // ESSALUD_MAS
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C604);
        if (intval($data_val['valor']) == 1) {
            concepto_0604($id_trabajador_pdeclaracion);
        }

        //ASEGURA PENSION_MAS
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C612);
        if (intval($data_val['valor']) == 1) {
            concepto_0612($id_trabajador_pdeclaracion);
        }
        //-----------------------------------------------------------
        // 0105 = TRABAJO EN SOBRETIEMPO (HORAS EXTRAS) 25%
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C105);
        if (isset($data_val['valor'])) {
            concepto_0105($id_trabajador_pdeclaracion, $data_sum['sueldo'], $data_val['valor']);
        }


        // 0106 = TRABAJO EN SOBRETIEMPO (HORAS EXTRAS) 35%
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C106);
        if (isset($data_val['valor'])) {
            concepto_0106($id_trabajador_pdeclaracion, $data_sum['sueldo'], $data_val['valor']);
        }


        // 0107 = TRABAJO EN DÍA FERIADO O DÍA DE DESCANSO
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C107);
        if (isset($data_val['valor'])) {
            concepto_0107($id_trabajador_pdeclaracion, $data_sum['sueldo'], $data_val['valor']);
        }

        // 0115 = REMUNERACIÓN DÍA DE DESCANSO Y FERIADOS (INCLUIDA LA DEL 1° DE MAYO)
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C115);
        $estado = intval($data_val['valor']);

        echo "ESTADO*-*-*-*-*-*-*--*-- =[" . $estado . "]*-*-*-*-*-*-*--*--";
        if ($estado == 1) {
            concepto_0115($id_trabajador_pdeclaracion, $data_sum['sueldo']);
        }



        // 0304 = BONIFICACIÓN POR RIESGO DE CAJA
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C304);
        if (isset($data_val['valor'])) {
            concepto_0304($id_trabajador_pdeclaracion, $data_val['valor']);
        }


        // 0703 = DESCUENTO AUTORIZADO U ORDENADO POR MANDATO JUDICIAL
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C703);
        if (isset($data_val['valor'])) {
            concepto_0703($id_trabajador_pdeclaracion, $data_val['valor']);
        }


        // 0704 = TARDANZAS
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C704);
        if (isset($data_val['valor'])) {
            concepto_0704($id_trabajador_pdeclaracion, $data_sum['sueldo'], $data_val['valor']);
        }

        // 0705 = INASISTENCIAS
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C705);
        if (isset($data_val['valor'])) {
            concepto_0705($id_trabajador_pdeclaracion, $data_sum['sueldo'], $data_val['valor']);
        }


        // 0909 = MOVILIDAD SUPEDITADA A ASISTENCIA Y QUE CUBRE SÓLO EL TRASLADO
        $data_val = array();
        $data_val = $dao_rpc->buscar_RPC_PorTrabajador($ID_TRABAJADOR[$i], C909);
        if (isset($data_val['valor'])) {
            concepto_0909($id_trabajador_pdeclaracion, $data_val['valor']);
        }

        //CALCULO AUTOMATICO DE:
        // - 28 de julio
        // - Navidad
        // - Bonificacion Extraordinaria... ()opcional se desabilita !!!!!!!!:  -_-|-_-
        //PlameDeclaracionDao::


        ECHO "entro a  fUNCION gratificaccion de JULIO Y DICIEMBRE";
        concepto_28_Navidad_LEY_29351($id_trabajador_pdeclaracion, $ID_PDECLARACION, $ID_TRABAJADOR[$i], $data_sum['sueldo']);


// --- Comment end 



        /**
         * Calculo Renta de Quinta
         */
        calcular_IR5_concepto_0605($id_trabajador_pdeclaracion, $ID_PDECLARACION, $ID_TRABAJADOR[$i], $data_sum['sueldo']);
        //concepto_0605($id_trabajador_pdeclaracion, $monto);
    }//ENDFOR
}

/**
 *
 * @param type $id
 * @param type $monto
 * @param type $horas No debe ser Mayor a dos
 * @return type 
 */
function concepto_0105($id, $monto = 0, $horas = 0) {

    $sueldo_por_hora = sueldoMensualXHora($monto);
    $nuevo_sueldo_por_hora = $sueldo_por_hora * 1.25;

    $neto = $nuevo_sueldo_por_hora * $horas;

    $neto = roundTwoDecimal($neto);
    //registrar
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($neto);
    $model->setMonto_pagado($neto);
    $model->setCod_detalle_concepto(C105);

    $dao = new DeclaracionDconceptoDao();

    return $dao->registrar($model);
}

function concepto_0106($id, $monto = 0, $horas = 0) {

    $sueldo_por_hora = sueldoMensualXHora($monto);
    $nuevo_sueldo_por_hora = $sueldo_por_hora * 1.35;
    $neto = $nuevo_sueldo_por_hora * $horas;
    $neto = roundTwoDecimal($neto);

    //registrar
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($neto);
    $model->setMonto_pagado($neto);
    $model->setCod_detalle_concepto(C106);

    $dao = new DeclaracionDconceptoDao();

    return $dao->registrar($model);
}

function concepto_0107($id, $monto = 0, $dias = 0) {

    $sueldo_por_dia = sueldoMensualXDia($monto);
    $nuevo_sueldo_por_dia = $sueldo_por_dia * 2; //DOBLE SUELDO
    $neto = $nuevo_sueldo_por_dia * $dias;
    $neto = roundTwoDecimal($neto);

    //registrar
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($neto);
    $model->setMonto_pagado($neto);
    $model->setCod_detalle_concepto(C107);

    $dao = new DeclaracionDconceptoDao();

    return $dao->registrar($model);
}

// REMUNERACIÓN DÍA DE DESCANSO Y FERIADOS (INCLUIDA LA DEL 1° DE MAYO)
// Se aplica cuando el 1 DE mayo ('dia del trabajador') cae domingo o
// o dia de trabajao para el empleado.
// En este caso se le pafara el dia al trabajador.... xq necesariamente es un dia
// de descanzo.
function concepto_0115($id, $sueldo) {

    $neto = sueldoMensualXDia($sueldo);
    //registrar
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($neto);
    $model->setMonto_pagado($neto);
    $model->setCod_detalle_concepto(C115);

    $dao = new DeclaracionDconceptoDao();

    return $dao->registrar($model);
}

// 0118 = REMUNERACION VACACIONAL
function concepto_0118($id, $id_pdeclaracion, $id_trabajador, $sueldo) {
    $rpta = false;
    //__ 01 Listar Periodo
    $dao_pdeclaracion = new PlameDeclaracionDao();
    $data = $dao_pdeclaracion->buscar_ID($id_pdeclaracion);

    $anio_periodo = getFechaPatron($data['periodo'], "Y");
    $mes_periodo = getFechaPatron($data['periodo'], "m");

    //__ 00 Listar Vacacion Trabajador
    $dao_vac = new VacacionDao();

    echo "\nid_trabajador = " . $id_trabajador;
    echo "\nanio_periodo = " . $anio_periodo;
    echo "\n";

    $data_vacacion = $dao_vac->fechaVacacionProgramada($id_trabajador, $anio_periodo);

    var_dump($data_vacacion);
    echoo($data_vacacion);
    echo "entrara a iff..";

    if (is_array($data_vacacion)) {

        //--------------------------------------------
        // - Condicion de Lapso valido de 11 meses. para Establecer Vacaciones
        // - Xq si pasa los 12 meses Se le pagaria indemnizacion.        
        //--------------------------------------------
        //variable fecha mas 11 mes
        $fecha_max_vacacion_programada = crearFecha($data_vacacion['fecha'], 0, 11, 0);

        /* if( $data_vacacion['fecha_programada']<=$fecha_max_vacacion_programada ){             
          } */



        echo "\nF.PROGRAMADA = " . $data_vacacion['fecha_programada'];
        echo "\nfecha_max_vacacion_programada =" . $fecha_max_vacacion_programada;

        //fecha programada  ES MENOR O IGUAL  :fecha_calc + 11 meses    //OK
        //:: Validar Tambien Con Javascript......................................................................................
        if ($data_vacacion['fecha_programada'] <= $fecha_max_vacacion_programada /* isset($data_vacacion['fecha_programada']) */) {
            echo "entro condicion 0000 1\n";
            $anio_vacacion = getFechaPatron($data_vacacion['fecha_programada'], "Y");
            $mes_vacacion = getFechaPatron($data_vacacion['fecha_programada'], "m");



            echo "anio_vacacion =$anio_vacacion\n";
            echo "anio_periodo =$anio_periodo\n\n\n\n";

            echo "mes_vacacion =$mes_vacacion\n";
            echo "mes_periodo =$mes_periodo\n\n\n\n";



            if (($anio_vacacion == $anio_periodo) && ($mes_vacacion == $mes_periodo)) {
                echo "\n\n\n\n\n Vacacion se Dara Correctamente en el mes y anio Correcto INSERT\n";
                //---
                $model = new DeclaracionDconcepto();
                $model->setId_trabajador_pdeclaracion($id);
                $model->setMonto_devengado($sueldo);
                $model->setMonto_pagado($sueldo);
                $model->setCod_detalle_concepto(C118);

                $dao = new DeclaracionDconceptoDao();
                $dao->registrar($model);
                //---
                $rpta = true;
            }
        } else if ($data_vacacion['fecha_programada'] > $fecha_max_vacacion_programada) {

            $rpta->mensaje = "fecha Programada, No puede ser Mayor A la Fecha de mas 11 meses.\n";
            echo "fecha Programada, No puede ser Mayor A la Fecha de mas 11 meses.";
        } else {
            $rpta->mensaje = "Error Critico";
            echo "Error Critico";
        }
    }

    return $rpta;
}

// Sueldo Basico
function concepto_0121($id_trabajador_pdeclaracion, $monto_remuneracion) {

    //SUELDO BASICO
    $SB = null;
    if ($monto_remuneracion < SB) {
        $SB = SB;
    } else {
        $SB = $monto_remuneracion;
    }

    //$SUELDO_BASE = $monto_remuneracion;
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id_trabajador_pdeclaracion);
    $model->setMonto_devengado($SB);
    $model->setMonto_pagado($SB);
    $model->setCod_detalle_concepto('0121');

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

// ASIGNACION FAMILIAR
function concepto_0201($id_trabajador_pdeclaracion, $monto_remuneracion) {
    //SUELDO BASICO
    $SB = null;
    //if ($monto_remuneracion < SB) {
    $SB = SB;
    //} else {
    //    $SB = $monto_remuneracion;
    //}
    //$SB = $monto_remuneracion;

    $CAL_AF = $SB * (T_AF / 100);
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id_trabajador_pdeclaracion);
    $model->setMonto_devengado($CAL_AF);
    $model->setMonto_pagado($CAL_AF);
    $model->setCod_detalle_concepto('0201');

    $dao = new DeclaracionDconceptoDao();
    $dao->registrar($model);
    return true;
}

// BONIFICACION POR RIESGO DE CAJA.
function concepto_0304($id, $monto) {

    //$neto = $monto;    
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($monto);
    $model->setMonto_pagado($monto);
    $model->setCod_detalle_concepto(C304);

    $dao = new DeclaracionDconceptoDao();
    $dao->registrar($model);
    return true;
}

// Adelanto en este caso la suma de las 2 QUINCENAS ?????????? DUDAAA!!! 
function concepto_0701($ID_TRABAJADOR, $ID_PDECLARACION, $id_trabajador_pdeclaracion) {

    // 01 :: = Consultar Trabajador
    $dao_1 = new PlameDeclaracionDao();
    $ADELANTO = $dao_1->PrimerAdelantoMensual($ID_TRABAJADOR, $ID_PDECLARACION);


    // 02 ::    
    //ADELANTO    
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id_trabajador_pdeclaracion);
    $model->setMonto_devengado($ADELANTO);
    $model->setMonto_pagado($ADELANTO);
    $model->setCod_detalle_concepto('0701');

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

function concepto_0703($id, $monto) {// 10/09/2012
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($monto);
    $model->setMonto_pagado($monto);
    $model->setCod_detalle_concepto(C703);

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

// TARDANZAS : DESCUENTOS
function concepto_0704($id, $monto, $hora) {

    $sueldo_x_hora = sueldoMensualXHora($monto);

    $neto = $sueldo_x_hora * $hora;

    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($neto);
    $model->setMonto_pagado($neto);
    $model->setCod_detalle_concepto(C703);

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

// 0705 INASISTENCIAS : 
function concepto_0705($id, $monto, $dias) {

    $sueldo_x_dia = sueldoMensualXDia($monto);

    $neto = $sueldo_x_dia * $dias;

    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($neto);
    $model->setMonto_pagado($neto);
    $model->setCod_detalle_concepto(C703);

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

// RENTA DE QUINTA CATEGORIA
function concepto_0605($id_trabajador_pdeclaracion, $monto) {
    /*
      $CAL_AF = SB * (T_AF/100);
      $model = new DeclaracionDconcepto();
      $model->setId_trabajador_pdeclaracion($id_trabajador_pdeclaracion);
      $model->setMonto_devengado($CAL_AF);
      $model->setMonto_pagado($CAL_AF);
      $model->setCod_detalle_concepto('0201');

      $dao = new DeclaracionDconceptoDao();
      echo "<pre>";
      print_r($model);
      echo "</pre>";
      $dao->registrar($model);
     */
}

// SNP [ONP = 02]
function concepto_0607($id, $id_pdeclaracion, $id_trabajador) {

    //====================================================   
    $all_ingreso = get_SNP_Ingresos($id_pdeclaracion, $id_trabajador);
    //====================================================

    $CALC = (floatval($all_ingreso)) * (T_ONP / 100);

    //echo "T_ONP  = " . T_ONP;
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    //$model->setMonto_devengado($CALC);
    $model->setMonto_pagado($CALC);
    $model->setCod_detalle_concepto('0607');
    //dao
    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

//-----------------------------------------------------------------------------
//FUNCION AYUDA  getSumaTodosIngresosTrabajador
function arrayConceptosIngresos() {
    $dao = new PlameDetalleConceptoEmpleadorMaestroDao();
    $conceptos = array('100', '200', '300', '400', '500', '900');
    $data_concepto = $dao->view_listarConcepto(ID_EMPLEADOR_MAESTRO, $conceptos);

    $concepto_ingresos = array();
    for ($x = 0; $x < count($data_concepto); $x++) {
        $concepto_ingresos[] = $data_concepto[$x]['cod_detalle_concepto'];
    }
    return $concepto_ingresos;
}

/**
 * // SUMA DE TODOS LOS INGRESOS DEL TRABAJADOR
 * listado de todos los conceptos que se encuentran seleccionado por el
 * empleador Maestro = ID_PDECLARACION = SOLO EN JUNIO junio.
 */
function getSumaTodosIngresosTrabajador($id_trabajador, $id_pdeclaracion) {

    $dao_dconcepto = new DeclaracionDconceptoDao();
    $data_dconcepto = $dao_dconcepto->listarTrabajadorPorDeclaracion($id_trabajador, $id_pdeclaracion);
    $concepto_ingresos = arrayConceptosIngresos();

    $sum = 0.00;
    for ($z = 0; $z < count($data_dconcepto); $z++) {
        if (in_array($data_dconcepto[$z]['cod_detalle_concepto'], $concepto_ingresos)) {
            $sum = $sum + $data_dconcepto[$z]['monto_pagado'];
        }
    }
    return $sum;
}

/**
 *  Utilizado para calcular de Enero a Junio = 
 *  Utilizado para Gratificacion de 28 de Julio
 * ---
 * @param type $id_trabajador
 * @param type $periodo 
 */
function getSumaTodosIngresosDeDiasFeriadosTrabajador($id_trabajador, $periodo, $break = null) {
    $mes = date("m", strtotime($periodo));
    $anio = date("Y", strtotime($periodo));
    $dia = date("d", strtotime($periodo));

    $dao_pd = new PlameDeclaracionDao();
    $data_pd = $dao_pd->listar(ID_EMPLEADOR_MAESTRO, $anio);

//..............................................................................
    // 0107 = TRABAJO EN DÍA FERIADO O DÍA DE DESCANSO
    // 0115 = REMUNERACIÓN DÍA DE DESCANSO Y FERIADOS (INCLUIDA LA DEL 1° DE MAYO)
    $conceptos_afectos = array('0107', '0115');
    //$conceptos_afectos = arrayConceptosAfectos_5ta();
//.............................................................................. 

    $dao_dconcepto = new DeclaracionDconceptoDao();
    $sum_jbasico = 0.00;
    $contador_estado = 0; //solo si tiene 3 dias feriados devuelve EL valor SINO = 0; En el estos meses Imposible 2 juntos Feriados JAJA.
    for ($i = ($mes - 1); $i > 0; $i--) {

        $periodo_lab = "$anio-$i-$dia";
        $periodo_lab = getFechaPatron($periodo_lab, "Y-m-d");

        //BUSCAR ID_PDECLARACION
        $id_pdeclaracion_lab = null;

        for ($j = 0; $j < count($data_pd); $j++) {

            if ($data_pd[$j]['periodo'] == $periodo_lab) {
                $id_pdeclaracion_lab = $data_pd[$j]['id_pdeclaracion'];

                $data_dconcepto = $dao_dconcepto->listarTrabajadorPorDeclaracion($id_trabajador, $id_pdeclaracion_lab);
                for ($z = 0; $z < count($data_dconcepto); $z++) {
                    if (in_array($data_dconcepto[$z]['cod_detalle_concepto'], $conceptos_afectos)) {
                        $contador_estado = $contador_estado + 1;
                        //echo "contador_estado=" . $contador_estado . "   ===== " . $data_dconcepto[$z]['cod_detalle_concepto'];
                        $sum_jbasico = $sum_jbasico + $data_dconcepto[$z]['monto_pagado'];
                    }
                }
            }
        }


        if ($i == $break) { // TERMINA DE RECORRER EL 7 = JULIO Y SALIR DEL BUCLE.
            //echo "break en 7ete";
            break;
        }
    }

    //echo "SUM_JBASICO = ".$sum_jbasico;
    $rpta = null;
    if ($contador_estado >= 3) {
        $rpta = $sum_jbasico;
    } else {
        $rpta = 0.00;
    }

    return $rpta;
//--------------------
}

// 0406	= GRATIFICACIONES DE FIESTAS PATRIAS Y NAVIDAD – LEY 29351
function concepto_28_Navidad_LEY_29351($id, $ID_PDECLARACION, $id_trabajador, $monto) { //0406
    $dao_pdeclaracion = new PlameDeclaracionDao();
    $data = $dao_pdeclaracion->buscar_ID($ID_PDECLARACION);

    $anio_periodo = getFechaPatron($data['periodo'], "Y");
    $mes_periodo = getFechaPatron($data['periodo'], "m");
    //..........................................................................
    $daoPlame = new PlameDao();
    $trabajador = array();
    $trabajador = $daoPlame->listarTrabajadorPeriodo(ID_EMPLEADOR_MAESTRO, $id_trabajador);



    /**
     * C O N F I G U R A R   Gratificacion Proporcional. 
     * 
     * 
     */
    //$trabajador['fecha_fin'] = '2012-06-30';
    //$trabajador['fecha_inicio'] = '2012-01-05';
    /**
     * C O N F I G U R A R   Gratificacion Proporcional. 
     * 
     * 
     * 
     */
    if ($mes_periodo == '07' || $mes_periodo == '7') {
        echo "\nENTRO JULIO EN " . __FILE__;

        $fecha = getRangoJulio($anio_periodo);
        $fj_inicio = $fecha['inicio'];
        $fj_fin = $fecha['fin'];

        $periodo_junio = $anio_periodo . "-06-01"; // MES DE JUNIO = 2012-06-01
        $data_pdeclaracion = $dao_pdeclaracion->Buscar_IDPeriodo(ID_EMPLEADOR_MAESTRO, $periodo_junio);

        // OBTENER Los Ingresos solo Mes = JUNIO.         
        $monto_ingresos_junio = getSumaTodosIngresosTrabajador($id_trabajador, $data_pdeclaracion['id_pdeclaracion']);

        // Suma De Periodos        
        $monto_dias_feriados = getSumaTodosIngresosDeDiasFeriadosTrabajador($id_trabajador, $data['periodo']);


        //|--------------------------------------------------------------------
        //| f = suma(ingreos) + suma(feriados)
        //|--------------------------------------------------------------------
        // GRATIFICACIONES DE FIESTAS PATRIAS – LEY 29351
        if (($trabajador['fecha_inicio'] <= $fj_inicio ) && (($trabajador['fecha_fin'] >= $fj_fin) || ($trabajador['fecha_fin'] == null) )) {
            echo "\n\nINSERT -> 0406 = GRATIFICACION COMPLETA 28 julio y NAVIDAD\n\n";
            // falta el  9 %
            $model = new DeclaracionDconcepto();
            $model->setId_trabajador_pdeclaracion($id);
            //$model->setMonto_devengado($monto_ingresos_junio);
            $model->setMonto_pagado($monto_ingresos_junio);
            $model->setCod_detalle_concepto(C406);
            //dao
            $dao = new DeclaracionDconceptoDao();
            $dao->registrar($model);



            echo "INSERT ->0312=BONIFICACIÓN EXTRAORDINARIA TEMPORAL – LEY 29351\n";
            echo __FILE__;
            ECHO __LINE__;
            echo "\nmonto_dias_feriados " . $monto_dias_feriados;
            // 6 = 6 meses que pasaron
            // 9% = tasa extraordinaria.   
            // 001 = SI dias feriados >=3 : se le suma todos los dias feriados trabajados /6
            $bextraordinario = ($monto_dias_feriados / 6);
            $bextraordinario = ($bextraordinario + $monto_ingresos_junio) * 0.09;

            $model = new DeclaracionDconcepto();
            $model->setId_trabajador_pdeclaracion($id);
            //$model->setMonto_devengado($bextraordinario);
            $model->setMonto_pagado($bextraordinario);
            $model->setCod_detalle_concepto(C312);
            //dao
            $dao = new DeclaracionDconceptoDao();
            $dao->registrar($model);
        } else if ($trabajador['fecha_inicio'] > $fj_inicio) {


            // $monto_ingresos_junio ->>Gratificacion no es = a ALl ingresos de junio
            //SINO es el proporcional ->gratificacion proporcional q se le calcula

            echo "\n\n0407=GRATIFICACIONES PROPORCIONAL – LEY 29351\n\n";

            //GRATIFICACION PROPORCIONAL
            $mesx = getFechaPatron($trabajador['fecha_inicio'], "m");
            $diax = getFechaPatron($trabajador['fecha_inicio'], "d");

            $mes_q_falta = null;

            if (intval($diax) == 1) { //DIA EMPEZO 01-mes-anio = OK NORMAL
                $mes_q_falta = numMesQueFalta($mesx, getFechaPatron($fj_fin, "m"));
            } else if (intval($diax) > 1) { //SE CALCULA DEL SIGUIENTE MES.                
                //asume que empezo diaz despues. .:. + 1 siguiente Mes                
                $mesx = intval($mesx) + 1;
                $mes_q_falta = numMesQueFalta($mesx, getFechaPatron($fj_fin, "m"));
            }


            $bextraordinario = 0.00;
            $bextraordinario = ($monto_dias_feriados / 6) * $mes_q_falta; //---------------------------------->mes q falta OK
            $bextraordinario = ($bextraordinario + $monto_ingresos_junio) * 0.09; // ESSALUD = 9%
            //X1 -------------------------- 01 ---------------------------------
            $model = new DeclaracionDconcepto();
            $model->setId_trabajador_pdeclaracion($id);
            //$model->setMonto_devengado($monto_ingresos_junio);
            //Calculo proporcional ->> a los meses q entroooo
            echo "\nUSTED EMPEZO A TRABAJAR DESPUES DE  $fj_inicio \n";
            echo "\n\nmes que faltaaaaaaaaaaaa " . $mes_q_falta;
            echo "\n\n MONTO DE SOLO JUNIO afectos" . $monto_ingresos_junio;

            $nuevo = ($monto_ingresos_junio / 6) * $mes_q_falta;
            //
            echo "\nformula ";
            echo "\n ($monto_ingresos_junio/6) * $mes_q_falta  = " . $nuevo;
            echo "\n nuevo = " . $nuevo;
            //
            $model->setMonto_pagado($nuevo); /* $monto_ingresos_junio */
            $model->setCod_detalle_concepto(C407);
            //$dao
            $dao = new DeclaracionDconceptoDao();
            $dao->registrar($model);

            echo "INSERT ->0312=BONIFICACIÓN EXTRAORDINARIA TEMPORAL – LEY 29351\n";
            // BONIFIACACION EXTRAORDINARIAA ..... esaluda 9% de su sueldo
            //X2 $monto_ingresos_junio--------- 02 -----------------------------
            $model = new DeclaracionDconcepto();
            $model->setId_trabajador_pdeclaracion($id);
            //$model->setMonto_devengado($bextraordinario);            
            $model->setMonto_pagado($bextraordinario);
            $model->setCod_detalle_concepto(C312);
            //dao
            $dao = new DeclaracionDconceptoDao();
            $dao->registrar($model);
        }



        //END GRATIFICACION DE JULIO !!!!!!!!!!
    } else if ($mes_periodo == '12') {
        echo "ENTRO DICIEMBRE EN " . __FILE__;

        $fecha = getRangoDiciembre($anio_periodo); //RPTA Enero a Noviembre
        $fj_inicio = $fecha['inicio'];
        $fj_fin = $fecha['fin'];


        $periodo_diciembre = $anio_periodo . "-11-01"; // MES DE NOVIEMBRE = 2012-11-01
        $data_pdeclaracion = $dao_pdeclaracion->Buscar_IDPeriodo(ID_EMPLEADOR_MAESTRO, $periodo_diciembre);

        //------------------------------------------------------------------------------
        // OBTENER Los Ingresos solo Mes = NOVIEMBRE.  ------------------------------------------->ok dato de noviembre       
        $monto_ingresos_noviembre = getSumaTodosIngresosTrabajador($id_trabajador, $data_pdeclaracion['id_pdeclaracion']);

        // Suma De Periodos        function (id_trabajador,mes=12,break=7)
        $monto_dias_feriados = getSumaTodosIngresosDeDiasFeriadosTrabajador($id_trabajador, $data['periodo'], 7);


        //|--------------------------------------------------------------------
        //| f = suma(ingreos) + suma(feriados)
        //|--------------------------------------------------------------------
        // GRATIFICACIONES DE FIESTAS PATRIAS – LEY 29351  -->ABAJO OK
        if (($trabajador['fecha_inicio'] <= $fj_inicio ) && (($trabajador['fecha_fin'] >= $fj_fin) || ($trabajador['fecha_fin'] == null) )) {
            echo "\n\nINSERT -> 0406 = GRATIFICACION COMPLETA NAVIDAD\n\n";
            // falta el  9 %
            ECHO "\nALL INGRESOS DE NOVIEMBRE = " . $monto_ingresos_noviembre;
            echo"\n TRABAJADOR se Encuentra para GRATIFICACACION DE DICIEMBRE";
            echo "\n $fj_inicio  al $fj_fin o null";

            $model = new DeclaracionDconcepto();
            $model->setId_trabajador_pdeclaracion($id);
            $model->setMonto_pagado($monto_ingresos_noviembre);
            $model->setCod_detalle_concepto(C406);
            //dao
            $dao = new DeclaracionDconceptoDao();
            $dao->registrar($model);


            echo "INSERT ->0312=BONIFICACIÓN EXTRAORDINARIA TEMPORAL – LEY 29351\n";
            // 6 = 6 meses que pasaron
            // 9% = tasa extraordinaria.   
            // 001 = SI dias feriados >=3 : se le suma todos los dias feriados trabajados /6
            $bextraordinario = ($monto_dias_feriados / 6);
            $bextraordinario = ($bextraordinario + $monto_ingresos_noviembre) * 0.09; //ESSALUD

            echo "\nbonificacion extraordiraria 9% DE ESALUD es  =" . $bextraordinario;

            $model = new DeclaracionDconcepto();
            $model->setId_trabajador_pdeclaracion($id);
            $model->setMonto_pagado($bextraordinario);
            $model->setCod_detalle_concepto(C312);
            //dao
            $dao = new DeclaracionDconceptoDao();
            $dao->registrar($model);
        } else if ($trabajador['fecha_inicio'] > $fj_inicio) {

            echo "\n\n0407=GRATIFICACIONES PROPORCIONAL – LEY 29351\n\n";

            //GRATIFICACION PROPORCIONAL
            $mesx = getFechaPatron($trabajador['fecha_inicio'], "m");
            $diax = getFechaPatron($trabajador['fecha_inicio'], "d");

            $mes_q_falta = null;

            if (intval($diax) == 1) { //DIA EMPEZO 01-mes-anio = OK NORMAL
                $mes_q_falta = numMesQueFalta($mesx, getFechaPatron($fj_fin, "m"));
            } else if (intval($diax) > 1) { //SE CALCULA DEL SIGUIENTE MES.                
                //asume que empezo diaz despues. .:. + 1 siguiente Mes                
                $mesx = intval($mesx) + 1;

                $mes_q_falta = numMesQueFalta($mesx, getFechaPatron($fj_fin, "m"));
            }

            echo "\nMES QUE FALTA = " . $mes_q_falta;
            echo "\n\n\n";

            echo __FILE__;
            echo __LINE__;
            echo "\nmonto_dias_feriados " . $monto_dias_feriados;


            $bextraordinario = 0.00;
            $bextraordinario = ($monto_dias_feriados / 6) * $mes_q_falta;
            $bextraordinario = ($bextraordinario + $monto_ingresos_noviembre) * 0.09;

            //X1 
            $model = new DeclaracionDconcepto();
            $model->setId_trabajador_pdeclaracion($id);
            $model->setMonto_pagado($monto_ingresos_noviembre);
            $model->setCod_detalle_concepto(C406);
            //$daodao
            $dao = new DeclaracionDconceptoDao();
            $dao->registrar($model);

            echo "INSERT ->0312=BONIFICACIÓN EXTRAORDINARIA TEMPORAL – LEY 29351\n";

            //X2 $monto_ingresos_junio
            $model = new DeclaracionDconcepto();
            $model->setId_trabajador_pdeclaracion($id);
            //$model->setMonto_devengado($bextraordinario);
            $model->setMonto_pagado($bextraordinario);
            $model->setCod_detalle_concepto(C312);
            //dao
            $dao = new DeclaracionDconceptoDao();
            $dao->registrar($model);
        }



        //END GRATIFICACION DE JULIO !!!!!!!!!!        
//------------------------------------------------------------------------------    
    } else {
        echo "    OTROS MESES !!  SIN GRATIFICACION  ";
    }
}

function sueldoDefault($sueldo) {
    $sueldo = floatval($sueldo);
    $new_sueldo = 0.00;
    if ($sueldo < SB) {
        $new_sueldo = SB;
    } else {
        $new_sueldo = $sueldo;
    }
    return $new_sueldo;
}

// AFP o SPP 
// 0601 = Comision afp porcentual
// 0606 = Prima de suguro AFP
// 0608 = SPP aportacion obligatoria
function concepto_AFP($id, $cod_regimen_pensionario, $id_pdeclaracion, $id_trabajador) {

    $dao = new PlameDeclaracionDao();
    $data_pd = $dao->buscar_ID($id_pdeclaracion);
    $periodo = $data_pd['periodo'];    
    
    //====================================================  
    $all_ingreso = get_AFP_Ingresos($id_pdeclaracion, $id_trabajador);    
    //====================================================    
    //echo "all_ingreso =" . $all_ingreso;
    
    $dao_afp = new ConfAfpDao();
    $afp = $dao_afp->vigenteAfp($cod_regimen_pensionario, $periodo);
    //----

    $A_OBLIGATORIO = floatval($afp['aporte_obligatorio']);
    $COMISION = floatval($afp['comision']);
    $PRIMA_SEGURO = floatval($afp['prima_seguro']);


    // UNO = comision porcentual
    $_601 = (floatval($all_ingreso)) * ($COMISION / 100);

    // DOS prima de seguro
    $_606 = (floatval($all_ingreso)) * ($PRIMA_SEGURO / 100);

    // TRES = aporte obligatorio
    $_608 = (floatval($all_ingreso)) * ($A_OBLIGATORIO / 100);


    // uno DAO
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    //$model->setMonto_devengado($CALC);
    $model->setMonto_pagado($_601);
    $model->setCod_detalle_concepto('0601');
    $dao = new DeclaracionDconceptoDao();
    $dao->registrar($model);


    // dos DAO
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    //$model->setMonto_devengado($CALC);
    $model->setMonto_pagado($_606);
    $model->setCod_detalle_concepto('0606');
    $dao = new DeclaracionDconceptoDao();
    $dao->registrar($model);

    // tres DAO
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    //$model->setMonto_devengado($CALC);
    $model->setMonto_pagado($_608);
    $model->setCod_detalle_concepto('0608');
    $dao = new DeclaracionDconceptoDao();
    $dao->registrar($model);

    return true;
}

// 604 ESSALUD + VIDA
function concepto_0604($id_trabajador_pdeclaracion) {

    $CALC = ESSALUD_MAS;
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id_trabajador_pdeclaracion);
    //$model->setMonto_devengado($CALC);
    $model->setMonto_pagado($CALC);
    $model->setCod_detalle_concepto('0604');

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

// 612 SNP ASEGURA TU PENSIÓN +
function concepto_0612($id_trabajador_pdeclaracion) {
    $CALC = SNP_MAS;
    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id_trabajador_pdeclaracion);
    // $model->setMonto_devengado($CALC);
    $model->setMonto_pagado($CALC);
    $model->setCod_detalle_concepto('0612');

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

// 804 ESSALUD trabajador
function concepto_0804($id, $id_pdeclaracion, $id_trabajador) {
    //==================================================== 
    $all_ingreso = get_ESSALUD_REGULAR_Ingresos($id_pdeclaracion, $id_trabajador);
    //====================================================
    /*    $dao = new PlameDeclaracionDao();
      $arreglo = $dao->buscar_ID($id_pdeclaracion);

      //--- TASA ESSALUD = T_ ESSALUD
      //$dao_3 = new ConfEssaludDao();
      //$T_ESSALUD = $dao_3->vigenteAux($arreglo['periodo']); //2012-01-01
      echo "<pre>";
      print_r($arreglo);
      echo "</pre>";
     */

    echo " T_ESSALUD " . T_ESSALUD;
    var_dump(T_ESSALUD);
    echo "herehereherhe SALIO ? tasa esalud <<<--- ******************** ";

    $CALC = floatval($all_ingreso) * (T_ESSALUD / 100);
    //$CALC = 

    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($CALC);
    $model->setMonto_pagado($CALC);
    $model->setCod_detalle_concepto('0804');

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

function concepto_0909($id, $monto) {

    $model = new DeclaracionDconcepto();
    $model->setId_trabajador_pdeclaracion($id);
    $model->setMonto_devengado($monto);
    $model->setMonto_pagado($monto);
    $model->setCod_detalle_concepto(C909);

    $dao = new DeclaracionDconceptoDao();
    return $dao->registrar($model);
}

//-----------------------------------------------------------------------------//
//.............................................................................//
//-----------------------------------------------------------------------------//

function listar_trabajadorPdeclaracion() {

    $ID_PDECLARACION = $_REQUEST['id_pdeclaracion'];

    $dao = new TrabajadorPdeclaracionDao();

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

    $lista = array();
    $lista = $dao->listar($ID_PDECLARACION, null, $WHERE);
//echo "<pre>";
//var_dump($lista);
//echo "</pre>";
    $count = count($lista);

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

// CONTRUYENDO un JSON

    $response->page = $page;
    $response->total = $total_pages;
    $response->records = $count;
    $i = 0;

    // ----- Return FALSE no hay Productos
    if ($lista == null || count($lista) == 0) {
        return $response;
    }
//print_r($lista);

    foreach ($lista as $rec) {

        $param = $rec["id_trabajador_pdeclaracion"];

        $_01 = $rec['id_trabajador'];
        $_02 = $rec['cod_tipo_documento'];
        $_03 = $rec['num_documento'];
        $_04 = $rec['apellido_paterno'];
        $_05 = $rec['apellido_materno'];
        $_06 = $rec['nombres'];
        $_07 = $rec['dia_laborado'];
        $_08 = $rec['sueldo'];





        // $js = "javascript:cargar_pagina('sunat_planilla/view-empresa/edit_pago.php?id_etapa_pago=" . $param . "&id_pdeclaracion=" . $_00 . "','#CapaContenedorFormulario')";

        $js = "javascript:cargar_pagina('sunat_planilla/view-plame/detalle_declaracion/edit_trabajador.php?id_trabajador_pdeclaracion=" . $param . "&id_trabajador=" . $_01 . "','#detalle_declaracion_trabajador')";


        $js2 = "javascript:eliminarTrabajadorPdeclaracion('" . $param . "')";
        $opciones = '<div id="divEliminar_Editar">				
		<span  title="Editar"   >
		<a href="' . $js . '" class="divEditar" ></a>
		</span>              
                
		<span  title="Editar"   >
		<a href="' . $js2 . '" class="divEliminar" ></a>
		</span>

		</div>';

        $response->rows[$i]['id'] = $param;
        $response->rows[$i]['cell'] = array(
            $param,
            //$_01,
            $_02,
            $_03,
            $_04,
            $_05,
            $_06,
            $_07,
            $_08,
            $opciones
        );
        $i++;
    }
    /*
      echo "<pre>";
      print_r($response);
      echo "</pre>"; */
    return $response;
}

// GRID SIN PIE 
function cargar_tabla_grid_lineal() {
    $ID = $_REQUEST['id'];

    $dao = new TrabajadorPdeclaracionDao();

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

    $lista = array();
    $lista = $dao->buscar_ID_GRID_LINEAL($ID);

    $count = count($lista);

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

// CONTRUYENDO un JSON
    $response->page = $page;
    $response->total = $total_pages;
    $response->records = $count;
    $i = 0;

    // ----- Return FALSE no hay Productos
    if ($lista == null || count($lista) == 0) {
        //return $response;
    }
//print_r($lista);


    foreach ($lista as $rec) {
        $param = $rec["id_trabajador_pdeclaracion"];
        $dia_total = $rec['dia_total'];


        /*
          $dao1 = new PdiaSubsidiadoDao();
          $dia_subsidiado = $dao1->busacar_IdPago($param,"SUMA");

          $dao2 =new PdiaNoSubsidiadoDao();
          $dia_NOsubsidiado = $dao2->buscar_IdPago($param,"SUMA");


          $dia_laborado_calc = $dia_total - ($dia_subsidiado +$dia_NOsubsidiado);
         */
        //$_00 = $rec['id_trabajador'];
        $_01 = $rec['cod_tipo_documento'];
        $_02 = $rec['num_documento'];
        $_03 = $rec['apellido_paterno'];
        $_04 = $rec['apellido_materno'];
        $_05 = $rec['nombres'];
        $_06 = $dia_laborado_calc;
        $_07 = $rec['sueldo']; //INGRESOS
        $_08 = $rec['descuento']; //$rec['descuento']; 
        $_09 = $rec['sueldo_neto']; //$rec['valor_neto'];
        $_10 = $rec['estado'];

        $js = "javascript:cargar_pagina('sunat_planilla/view-empresa/detalle_etapa_pago/editar_trabajador.php?id_pago=" . $param . "&id_trabajador=" . $_00 . "','#detalle_declaracion_trabajador')";

        // $js2 = "javascript:eliminarPersona('" . $param . "')";		
        $opciones = '<div id="divEliminar_Editar">				
		<span  title="Editar" >
		<a href="' . $js . '"><img src="images/edit.png"/></a>
		</span>
		</div>';

        //hereee
        $response->rows[$i]['id'] = $param;
        $response->rows[$i]['cell'] = array(
            $param,
            $_01,
            $_02,
            $_03,
            $_04,
            $_05,
            $_06,
            $_07,
            $_08,
            $_09,
            $_10
                //$opciones*/
        );
        $i++;
    }

//echo "<pre>";
//print_r($response);
//echo "</pre>";
    return $response;
}

// view-plame
function buscar_ID_TrabajadorPdeclaracion($id) {
    //$id = $_REQUEST[''];
    $dao = new TrabajadorPdeclaracionDao();
    $data = $dao->buscar_ID($id);
    $model = new TrabajadorPdeclaracion();
    $model->setId_trabajador_pdeclaracion($data['id_trabajador_pdeclaracion']);
    $model->setId_pdeclaracion($data['id_pdeclaracion']);
    $model->setId_trabajador($data['id_trabajador']);
    $model->setDia_laborado($data['dia_laborado']);
    $model->setDia_total($data['dia_total']);
    $model->setOrdinario_hora($data['ordinario_hora']);
    $model->setOrdinario_min($data['ordinario_min']);
    $model->setSobretiempo_hora($data['sobretiempo_hora']);
    $model->setSobretiempo_min($data['sobretiempo_min']);
    $model->setSueldo($data['sueldo']);
    $model->setSueldo_neto($data['sueldo_neto']);
    $model->setEstado($data['estado']);
    $model->setFecha_creacion($data['fecha_creacion']);
    $model->setFecha_modificacion($data['fecha_modificacion']);
    $model->setIngreso_5ta_categoria($data['ingreso_5ta_categoria']);
    $model->setCod_tipo_trabajador($data['cod_tipo_trabajador']);
    $model->setCod_regimen_pensionario($data['cod_regimen_pensionario']);
    $model->setCod_regimen_aseguramiento_salud($data['cod_regimen_aseguramiento_salud']);
    $model->setCod_situacion($data['cod_situacion']);

    return $model;
}

function eliminar_trabajadorPdeclaracion() {
    $id = $_REQUEST['id'];

    $dao = new TrabajadorPdeclaracionDao();
    return $dao->eliminar($id);
}

//|---------------------------------------------------------------------------//
//| REPORTES TXT
//|---------------------------------------------------------------------------//

function generarBoletaTxt($id_pdeclaracion) {
 // id de trabajador a generar recibo x quincena.
    //null
    $ids = $_REQUEST['ids'];
    //$id_pdeclaracion = $_REQUEST['id_pdeclaracion'];
    $id_etapa_pago = $_REQUEST['id_etapa_pago'];
//---------------------------------------------------
// Variables secundarios para generar Reporte en txt
    $master_est = /*null;*/1;
    $master_cc = /*null;*/2;

    if ($_REQUEST['todo'] == "todo") {
        $cubo_est = "todo";
        $cubo_cc = "todo";
    }

    $id_est = $_REQUEST['id_establecimientos'];
    $id_cc = $_REQUEST['cboCentroCosto'];

    if (!is_null($id_est)) {
        $master_est = $id_est;
    } else {
        //$cubo_est = "todo";
    }

    if (!is_null($id_cc)) {
        $master_cc = $id_cc;
    } else {
        //$cubo_cc = "todo";
    }
    //
    $dao = new PlameDeclaracionDao();
    $data_pd = $dao->buscar_ID($id_pdeclaracion);
    $fecha = $data_pd['periodo'];

    $nombre_mes = getNameMonth(getFechaPatron($fecha, "m"));
    $anio = getFechaPatron($fecha, "Y");


    $file_name = NAME_COMERCIAL . '-BOLETA PAGO.txt';

    $BREAK = chr(13) . chr(10);
    $BREAK2 = chr(13) . chr(10). chr(13) . chr(10);
    $LINEA = str_repeat('-', 80);
//..............................................................................
// Inicio Exel
//..............................................................................
    $fp = fopen($file_name, 'w');

    
        // paso 01 Listar ESTABLECIMIENTOS del Emplearo 'Empresa'
    $dao_est = new EstablecimientoDao();
    $est = array();
    $est = $dao_est->listar_Ids_Establecimientos(ID_EMPLEADOR);

    // paso 02 listar CENTROS DE COSTO del establecimento.    
    if (is_array($est) && count($est) > 0) {
        //DAO
        $dao_cc = new EmpresaCentroCostoDao();
        $dao_pago = new TrabajadorPdeclaracionDao(); //[OK]
        $dao_estd = new EstablecimientoDireccionDao();
        $dao_rp = new DetalleRegimenPensionarioDao(); //[OK]
        $dao_pdireccion = new PersonaDireccionDao(); //[OK]

        // -------- Variables globales --------//        
        $SUM_TOTAL_CC = array();
        $SUM_TOTAL_EST = array();



        for ($i = 0; $i < count($est); $i++) { // ESTABLECIMIENTO
            fwrite($fp, $BREAK2);
            fwrite($fp, "Conteo de eSTABLECIMNETO = i = $i");
            fwrite($fp, $BREAK2);
            //echo " i = $i establecimiento    ID=".$est[$i]['id_establecimiento'];
            //echo "<br>";
            //$SUM_TOTAL_EST[$i]['establecimiento'] = strtoupper("Establecimiento X ==" . $est[$i]['id_establecimiento']);
            $bandera_1 = false;
            if ($est[$i]['id_establecimiento'] == $master_est) {
                $bandera_1 = true;
            } else if ($cubo_est == "todo") {
                $bandera_1 = true;
            }

            if ($bandera_1) {
                //$SUM_TOTAL_EST[$i]['monto'] = 0;
                
                //Establecimiento direccion Reniec
                $data_est_direc = $dao_estd->buscarEstablecimientoDireccionReniec($est[$i]['id_establecimiento']);

                //$SUM_TOTAL_EST[$i]['establecimiento'] = $data_est_direc['ubigeo_distrito'];
                
                
                //
                $ecc = array();
                $ecc = $dao_cc->listar_Ids_EmpresaCentroCosto($est[$i]['id_establecimiento']);
                // paso 03 listamos los trabajadores por Centro de costo 
                fwrite($fp, $BREAK2);
                fwrite($fp, "NUM DE establecimiento y cuantos CENTROS  COSTOS TIENEN   =".count($ecc));
                 
                 fwrite($fp, $BREAK2);
                for ($j = 0; $j < count($ecc); $j++) {
                    fwrite($fp, $BREAK2);
                    fwrite($fp, "entra a for j = $j ");
                   
                    
                    $bandera_2 = false;
                    if ($ecc[$j]['id_empresa_centro_costo'] == $master_cc) {
                        $bandera_2 = true;
                    } else if ($cubo_est == "todo") {
                        $bandera_2 = true;
                    }

                    if ($bandera_2) {
                        $SUM_TOTAL_CC[$i][$j]['establecimiento'] = $data_est_direc['ubigeo_distrito'];
                        $SUM_TOTAL_CC[$i][$j]['centro_costo'] = strtoupper($ecc[$j]['descripcion']);
                        $SUM_TOTAL_CC[$i][$j]['monto'] = 0;
                        
                        // LISTA DE TRABAJADORES
                        $data_tra = array();
                        $data_tra = $dao_pago->listar_2($id_pdeclaracion, $est[$i]['id_establecimiento'], $ecc[$j]['id_empresa_centro_costo']);
                        
                        for ($k=0;$k<count($data_tra);$k++){

                        //fwrite($fp, $LINEA);
                        fwrite($fp, $BREAK);
                        fwrite($fp, str_pad("BOLETA DE PAGO",136," ",STR_PAD_BOTH));
                        fwrite($fp, $BREAK);
                        fwrite($fp, str_pad("D.S. 020-2008-TR DEL 17-01-2008",136," ",STR_PAD_BOTH));
                        fwrite($fp, $BREAK2);
                        
                        fwrite($fp, NAME_EMPRESA);
                        fwrite($fp, $BREAK);
                        
                        
                        $direccion =$data_est_direc['ubigeo_nombre_via']." ".$data_est_direc['nombre_via'];
                        $direccion .=" ".$data_est_direc['numero_via']." - ".$data_est_direc['ubigeo_distrito'];
                        
                        fwrite($fp, str_pad($direccion,49," ",STR_PAD_RIGHT));                        
                        fwrite($fp, str_pad("CODIGO: ".$data_tra[$k]['num_documento'],44," ",STR_PAD_RIGHT));                        
                        fwrite($fp, str_pad("DNI: ".$data_tra[$k]['num_documento'],44," ",STR_PAD_RIGHT));
                        fwrite($fp, $BREAK);
                        
                        $nombre_c = $data_tra[$k]['apellido_paterno']." ".$data_tra[$k]['apellido_materno']." ".$data_tra[$k]['nombres'];
                        fwrite($fp, str_pad("R.U.C. ".RUC ,49," ",STR_PAD_RIGHT));                        
                        fwrite($fp, str_pad("NOMBRE Y APELLIDOS: ".$nombre_c ,88," ",STR_PAD_RIGHT));
                        fwrite($fp, $BREAK);                        
                        
                        fwrite($fp,str_pad("Reo.Pat. 2010033861100000",49," ",STR_PAD_RIGHT));
                        fwrite($fp,str_pad("CARNET DE ESSALUD : -",44," ",STR_PAD_RIGHT));
                        fwrite($fp,str_pad("FECHA INGRESO : 00/00/000", 44," ",STR_PAD_RIGHT));
                        fwrite($fp, $BREAK);
                        
                        //......................................................                                                
                        $afp_carnet_value =null;
                        $afp_nombre_value = null;
                        
                        if($data_tra[$k]['cod_regimen_pensionario']=='02'){ //ONP                            
                            $afp_nombre_value = "R.P. : ".$data_tra[$k]['nombre_afp'];                            
                        }else{ //AFP                            
                            //dao                            
                            $arreglo_data_rp = $dao_rp->buscarDetalleRegimenPensionario($data_tra[$k]['id_trabajador']);
                            $afp_nombre_value = "A.F.P. : ".$data_tra[$k]['nombre_afp'];
                            $afp_carnet_value = "NRO.CARNET AFP : ".$arreglo_data_rp['cuspp']; 
                        }                       
                        
                        //......................................................
                        
                       
                        fwrite($fp,str_pad(" ",49," ",STR_PAD_RIGHT));                        
                        fwrite($fp,str_pad($afp_carnet_value,44," ",STR_PAD_RIGHT));
                        fwrite($fp,str_pad($afp_nombre_value,44," ",STR_PAD_RIGHT));
                        fwrite($fp, $BREAK);
                        
                        
                        $num_mes = intval( getFechaPatron($data_pd['periodo'], "m") );
                        $fecha_0 = getNameMonth($num_mes);
                        $fecha_1 = getFechaPatron($data_pd['periodo'], "d.Y");
                        $cadena_fecha = "MES : ".$fecha_0." DE : ".$fecha_1;
                        fwrite($fp,str_pad($cadena_fecha, 49," ",STR_PAD_RIGHT));
                        fwrite($fp,str_pad("CARGO : ".$data_tra[$k]['nombre_ocupacion'],44," ",STR_PAD_RIGHT));
                        fwrite($fp,str_pad("SECCION : ".$data_tra[$k]['nombre_centro_costo'],44," ",STR_PAD_RIGHT));
                        fwrite($fp, $BREAK);
                        
                        
                        //......................................................
                        $data_direccion = array();
                        $data_direccion = $dao_pdireccion->listarPersonaDirecciones($data_tra[$k]['id_persona']);                        
                        //foreach ($lista as $rec) {
                        $cadena = '';
                        for($a=0;$a< 1/*count($lista)*/;$a++){
                            $rec = $data_direccion[$a];
                            //$param = $rec["id_persona_direccion"];
                            //$id_persona = $rec['id_persona'];
                            //here
                            $ubigeo_nombre_via = $rec["ubigeo_nombre_via"];
                            $nombre_via = $rec['nombre_via'];
                            $numero_via = $rec['numero_via'];

                            $ubigeo_nombre_zona = $rec['ubigeo_nombre_zona'];
                            $nombre_zona = $rec['nombre_zona'];
                            $etapa = $rec['etapa'];
                            $manzana = $rec['manzana'];
                            $blok = $rec['blok'];
                            $lote = $rec['lote'];

                            $departamento = $rec['departamento'];
                            $interior = $rec['interior'];

                            $kilometro = $rec['kilometro'];

                            $ubigeo_departamento = str_replace('DEPARTAMENTO', '', $rec['ubigeo_departamento']);
                            $ubigeo_provincia = $rec['ubigeo_provincia'];
                            $ubigeo_distrito = $rec['ubigeo_distrito'];

                            
                        $cadena .= ($ubigeo_nombre_via != "-") ? " ".$ubigeo_nombre_via : '';
                        $cadena .= (!empty($nombre_via)) ? " ".$nombre_via : '';
                        $cadena .= (!empty($numero_via)) ? " ".$numero_via : '';

                        $cadena .= ($ubigeo_nombre_zona != "-") ? $ubigeo_nombre_zona : '';
                        $cadena .= (!empty($nombre_zona)) ? " ".$nombre_zona : '';
                        $cadena .= (!empty($etapa)) ? " ".$etapa : '';

                        $cadena .= (!empty($manzana)) ? ' MZA. ' . $manzana : '';
                        $cadena .= (!empty($blok)) ? " ".$blok : '';
                        $cadena .= (!empty($etapa)) ? " ".$etapa : '';
                        $cadena .= (!empty($lote)) ? ' LOTE. ' . $lote : '';

                        $cadena .= (!empty($departamento)) ? " ".$departamento : '';
                        $cadena .= (!empty($interior)) ? " ".$interior : '';
                        $cadena .= (!empty($kilometro)) ? " ".$kilometro : '';

                        $cadena .= ($ubigeo_departamento != "-") ? $ubigeo_departamento."-" : '';
                        $cadena .= ($ubigeo_provincia != "-") ? $ubigeo_provincia."-" : '';
                        $cadena .= ($ubigeo_distrito != "-") ? $ubigeo_distrito : '';

                        $cadena = strtoupper($cadena);

                            $cadena = strtoupper($cadena);
                        }                        
                        
                        //......................................................
                        
                        $cadena_dialab = $data_tra[$k]['dia_laborado']." DIAS TRAB. ".$data_tra[$k]['ordinario_hora']." HORAS TRAB.";
                       
                        fwrite($fp,str_pad($cadena_dialab,49," ",STR_PAD_RIGHT));                        
                        fwrite($fp,str_pad("DIRECCION : ".$cadena,88," ",STR_PAD_RIGHT));
                        
                        fwrite($fp, $BREAK);
                        
                        generarBotletaTabla($fp,$data_tra[$k]['id_trabajador_pdeclaracion'],$data_tra[$k]['cod_regimen_pensionario'],$data_pd['periodo'],$BREAK,$BREAK2);
                        
                        //fwrite($fp, str_pad("FECHA : ", 47, " ", STR_PAD_LEFT));
                                               

                        // LISTA DE DECLARACIONES CONCEPTOS
                        //$data_tra = array();
                        //$data_tra = $dao_pago->listar_2($id_etapa_pago, $est[$i]['id_establecimiento'], $ecc[$j]['id_empresa_centro_costo']);

                        
                        }//enfFor $k 
                        
                    }
                }//END FOR CCosto



                fwrite($fp, $BREAK . $BREAK . $BREAK . $BREAK . $BREAK . $BREAK . $BREAK . $BREAK . $BREAK);
                //fwrite($fp, $BREAK . $BREAK . $BREAK . $BREAK . $BREAK . $BREAK . $BREAK . $BREAK . $BREAK);
            }
        }//END FOR Est


    }//END IF
    
    
    fclose($fp); 
    
    
    
    
    
    
    
    
   
    
    
    $file = array();
    $file[] = $file_name;
    //$file[] = ($file_name2);
    ////generarRecibo15_txt2($id_pdeclaracion, $id_etapa_pago);


    $zipfile = new zipfile();
    $carpeta = "file-" . date("d-m-Y") . "/";
    $zipfile->add_dir($carpeta);

    for ($i = 0; $i < count($file); $i++) {
        $zipfile->add_file(implode("", file($file[$i])), $carpeta . $file[$i]);
        //$zipfile->add_file( file_get_contents($file[$i]),$carpeta.$file[$i]);
    }

    header("Content-type: application/octet-stream");
    header("Content-disposition: attachment; filename=zipfile.zip");

    echo $zipfile->file();
    
    
}



function generarBotletaTabla($fp,$id_trabajador_pdeclaracion,$cod_regimen_pensionario,$periodo,$BREAK,$BREAK2){
    
    //..............................................................................
    $cod_conceptos_ingresos = array('100','200','300','400','500','900');

    $cod_conceptos_descuentos = array('600','700');

    $cod_conceptos_aportes = array(/*'600',*/'800');
    //..............................................................................
    
    $dao_ddc = new DeclaracionDconceptoDao();
    $dao_pdcem = new  PlameDetalleConceptoEmpleadorMaestroDao();    
    
    
   $LINEA = str_repeat('-', 135);
   $PUNTO = "*";
   $BORDE_R = str_pad('',3," ",STR_PAD_RIGHT); // $BORDER
   $BORDE_L = str_pad('',3," ",STR_PAD_LEFT); // $BORDER
   fwrite($fp, $LINEA);
   fwrite($fp, $BREAK);
   
   fwrite($fp, $PUNTO);
   fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("R E M U N E R A C I O N E S".$cadena,38," ",STR_PAD_BOTH));
   fwrite($fp, $BORDE_L);
   
   fwrite($fp, $PUNTO);
   //fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("R E T E N C I O N E S / D E S C U E N T O S".$cadena,44," ",STR_PAD_BOTH));
   //fwrite($fp, $BORDE_L);
   
   fwrite($fp, $PUNTO);
   fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("A P O R T A C I O N E S".$cadena,37," ",STR_PAD_BOTH));
   fwrite($fp, $BORDE_L);
   fwrite($fp, $PUNTO);
   fwrite($fp, $BREAK);
   
   fwrite($fp, $PUNTO);
   fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("DESCRIPCION".$cadena,19," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad("IMPORTE".$cadena,19," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L); 
   
   fwrite($fp, $PUNTO);
   fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("DESCRIPCION".$cadena,19," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad("IMPORTE".$cadena,19," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L);
   fwrite($fp, $PUNTO);
   
   fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("DESCRIPCION".$cadena,19," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad("IMPORTE".$cadena,18," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L); 
   fwrite($fp, $PUNTO);
   
   fwrite($fp, $BREAK);
   fwrite($fp, $LINEA);
   fwrite($fp, $BREAK);  
   

    // ----- INICIO CUERPO 
        
    //conceptos calculados
    $calc = array();        
    $calc = $dao_ddc->buscar_ID_TrabajadorPdeclaracion_2($id_trabajador_pdeclaracion);

    // 01 lista de todos conceptos Ingresos
    $c_pingreso = array();
    $c_pingreso = $dao_pdcem->view_listarConcepto(ID_EMPLEADOR_MAESTRO, $cod_conceptos_ingresos,1);
       
    // armado de array
    $array_ingreso = array();
    for ($i = 0; $i < count($c_pingreso); $i++) {        
        $array_ingreso[] = $c_pingreso[$i]['cod_detalle_concepto'];
    }
    
$ingresos= array();
$x=0;
$sum_i = 0.00;
for ($o = 0; $o < count($calc); $o++):    
if (in_array($calc[$o]['cod_detalle_concepto'], $array_ingreso)):     
    $ingresos[$x]['descripcion'] = $calc[$o]['descripcion'];
    $ingresos[$x]['descripcion_abreviada'] = $calc[$o]['descripcion_abreviada'];
    //$ingresos[$x]['cod_detalle_concepto'] = $calc[$o]['cod_detalle_concepto'];     
    $ingresos[$x]['monto_pagado'] = $calc[$o]['monto_pagado']; 
    $sum_i = $sum_i + $calc[$o]['monto_pagado'];
    $x++;
endif;
endfor;


//------------------------------------------------------------------------------
// DESCUENTOS

// 01 lista de todos conceptos Ingresos
$c_pdescuento = array();
$c_pdescuento = $dao_pdcem->view_listarConcepto(ID_EMPLEADOR_MAESTRO, $cod_conceptos_descuentos,$seleccionado = array(0,1));

// armado de array
$array_descuento = array();
for ($i = 0; $i < count($c_pdescuento); $i++) {        
    $array_descuento[] = $c_pdescuento[$i]['cod_detalle_concepto'];
}


$descuentos = array();
$x=0;
$sum_d = 0.00;
for ($o = 0; $o < count($calc); $o++):    
if (in_array($calc[$o]['cod_detalle_concepto'], $array_descuento)):     
    $descuentos[$x]['descripcion'] = $calc[$o]['descripcion'];
    $descuentos[$x]['descripcion_abreviada'] = $calc[$o]['descripcion_abreviada'];
    $descuentos[$x]['cod_detalle_concepto'] = $calc[$o]['cod_detalle_concepto']; 
    $descuentos[$x]['monto_pagado'] = $calc[$o]['monto_pagado'];
    $sum_d = $sum_d + $calc[$o]['monto_pagado'];
    $x++;
endif;
endfor;


//------------------------------------------------------------------------------

// 01 lista de todos conceptos Ingresos
$c_paporte = array();
$c_paporte = $dao_pdcem->view_listarConcepto(ID_EMPLEADOR_MAESTRO, $cod_conceptos_aportes,0);

// armado de array
$array_aporte = array();
for ($i = 0; $i < count($c_paporte); $i++) {        
    $array_aporte[] = $c_paporte[$i]['cod_detalle_concepto'];
}


$aportes = array();
$x=0;
$sum_a = 0.00;
for ($o = 0; $o < count($calc); $o++):    
if (in_array($calc[$o]['cod_detalle_concepto'], $array_aporte)):     
    $aportes[$x]['descripcion'] = $calc[$o]['descripcion'];
    $aportes[$x]['descripcion_abreviada'] = $calc[$o]['descripcion_abreviada'];
    $aportes[$x]['monto_pagado'] = $calc[$o]['monto_pagado'];
    $sum_a = $sum_a + $calc[$o]['monto_pagado'];
    $x++;
endif;//?????????????
endfor;

   
//----------------------------PINTAR EN TABLA-----------------------------------
    $cnt_ingreso = count($ingresos);
    $cnt_descuento = count($descuentos);
   // $cnt_aporte = count($aportes);
    
    $mayor = null;
   if($cnt_ingreso > $cnt_descuento){
       $mayor = $cnt_ingreso;
   }else if($cnt_descuento > $cnt_ingreso ) {
       $mayor = $cnt_descuento;
   }
      
   
   for($i =0;$i<7;$i++):

   fwrite($fp, $PUNTO);
   fwrite($fp, $BORDE_R);
   $descripcion_1 =($ingresos[$i]['descripcion_abreviada']=="") ? $ingresos[$i]['descripcion'] : $ingresos[$i]['descripcion_abreviada'];
   
   fwrite($fp, str_pad($descripcion_1, 29," ",STR_PAD_RIGHT));
   fwrite($fp, str_pad($ingresos[$i]['monto_pagado'],9," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L);
   fwrite($fp, $PUNTO);
   
   fwrite($fp, $BORDE_R);
   $descripcion_2 =($descuentos[$i]['descripcion_abreviada']=="") ? $descuentos[$i]['descripcion'] : $descuentos[$i]['descripcion_abreviada']." ";
   
   //,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,
    $dao_afp = new ConfAfpDao();
    $afp = $dao_afp->vigenteAfp($cod_regimen_pensionario, $periodo);
   
   if($cod_regimen_pensionario == '02'){ //ONP
          
   }else{ //AF --Q ESTA AFILIADO
       
       if($descuentos[$i]['cod_detalle_concepto']== '0601'){
           
           $descripcion_2 .= $afp['comision']."%";
       }else if($descuentos[$i]['cod_detalle_concepto']== '0606'){
           $descripcion_2 .= $afp['prima_seguro']."%";
           
       }else if($descuentos[$i]['cod_detalle_concepto']== '0608'){
           $descripcion_2 .= $afp['aporte_obligatorio']."%";
           
       }       
       
   }
    //,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,   
   
   
   //$descripcion_2 = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
   fwrite($fp, str_pad($descripcion_2, 29," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad($descuentos[$i]['monto_pagado'],9," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L);
   fwrite($fp, $PUNTO);
   
   fwrite($fp, $BORDE_R);
   $descripcion_3 = ($aportes[$i]['descripcion_abreviada']=="")? $aportes[$i]['descripcion'] : $aportes[$i]['descripcion_abreviada'];
//   $descripcion_3 = "aaaaaaaaaaaaaaaaaaaaaaaaaaaa";
   fwrite($fp, str_pad($descripcion_3,28," ",STR_PAD_RIGHT));
   fwrite($fp, str_pad($aportes[$i]['monto_pagado'],9," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L);
   fwrite($fp, $PUNTO);
   fwrite($fp, $BREAK);
   
   endfor;
//----------------------------PINTAR EN TABLA-----------------------------------
// ----- FIN CUERPO
   
   
    fwrite($fp, $LINEA);
    fwrite($fp, $BREAK);

   fwrite($fp, $PUNTO);
   fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("TOTAL REMUNERACIONES ",29," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad($sum_i,9," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L); 


   fwrite($fp, $PUNTO);
   fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("TOTAL RETENC./DESCUENTOS ",29," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad($sum_d,9," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L); 

   fwrite($fp, $PUNTO);
   fwrite($fp, $BORDE_R);   
   fwrite($fp,str_pad("TOTAL APORTACIONES",28," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad($sum_a,9," ",STR_PAD_LEFT));
   fwrite($fp, $BORDE_L); 
   fwrite($fp, $PUNTO);
   fwrite($fp, $BREAK);
   fwrite($fp,$LINEA);
   fwrite($fp, $BREAK2);
   
   
      
   
    //---------  
   $linea_caja =str_repeat('-', 29);
   fwrite($fp, $linea_caja);
   fwrite($fp, $BREAK);
    
   fwrite($fp, $PUNTO);
   fwrite($fp, str_pad('',1," ",STR_PAD_RIGHT));   
   fwrite($fp,str_pad("REDONDEO",13," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad(": 11,000.00 ",12," ",STR_PAD_LEFT));
   fwrite($fp, str_pad('',1," ",STR_PAD_LEFT)); 
   fwrite($fp, $PUNTO);
   fwrite($fp, $BREAK);
   fwrite($fp, $linea_caja);
   
   
   fwrite($fp, $BREAK);   
   fwrite($fp, $PUNTO);
   fwrite($fp, str_pad('',1," ",STR_PAD_RIGHT));   
   fwrite($fp,str_pad("NETO A PAGAR",13," ",STR_PAD_RIGHT));
   fwrite($fp,str_pad(": 11,000.00 ",12," ",STR_PAD_LEFT));  
   fwrite($fp, str_pad('',1," ",STR_PAD_LEFT)); 
   fwrite($fp, $PUNTO);
   
   fwrite($fp, str_pad('_________________________', 53, " ", STR_PAD_LEFT));  fwrite($fp, str_pad('_________________________', 43, " ", STR_PAD_LEFT));
   fwrite($fp, $BREAK);
   fwrite($fp, $linea_caja);
   fwrite($fp, str_pad('P.'.NAME_EMPRESA, 49, " ", STR_PAD_LEFT)); //VO
   fwrite($fp, str_pad('RECIBI CONFORME', 43, " ", STR_PAD_LEFT));   
   fwrite($fp, $BREAK2);  ????

    
    
    
}
