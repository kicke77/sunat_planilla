<?php
//*******************************************************************//
require_once('../view/ide.php');
//*******************************************************************//
require_once("../util/funciones.php");


$periodo = $_REQUEST['periodo'];
$cod_periodo_remuneracion = $_REQUEST['cod_periodo_remuneracion'];

$mes = getFechaPatron($periodo, "m");
$anio = getFechaPatron($periodo, "Y");
$ID_DECLARACION = $_REQUEST['id_declaracion'];

//-------------------------------------------
//echo "<pre>";
//echo print_r(getFechasDePago($periodo));
//echo "</pre>";

$FECHAX = getFechasDePago($periodo);

$FECHA =array();
if($premunerativo == 2){ //quincena 2
    //UNO
    $FECHA[0]['inicio'] = getFechaPatron($FECHAX['first_day'],"d/m/Y");
    $FECHA[0]['fin'] = getFechaPatron($FECHAX['second_weeks'],"d/m/Y");
    $FECHA[0]['tipo'] = "1";
    //DOS
    $FECHA[1]['inicio'] = getFechaPatron($FECHAX['second_weeks'],"d/m/Y");
    $FECHA[1]['fin'] = getFechaPatron($FECHAX['las_day'],"d/m/Y");
    $FECHA[0]['tipo'] = "2";
    
}






?>

<script type="text/javascript">
    $(document).ready(function(){
                  
        $( "#tabs").tabs();
		
    });
	
	var cod_periodo_remuneracion = document.getElementById('cod_periodo_remuneracion').value;
	var id_declaracion = document.getElementById('id_declaracion').value;
cargarTabla_Etapa(id_declaracion,cod_periodo_remuneracion);
    //--------------------------
	
    function atrasEtapa02(){
        var cod_periodo_remuneracion = document.getElementById('cod_periodo_remuneracion').value;
        cod_periodo_remuneracion = parseInt(cod_periodo_remuneracion);
        var periodo = document.getElementById('periodo').value
		var id_declaracion = document.getElementById('id_declaracion').value;
	

        var url = "sunat_planilla/view-empresa/new_etapaPago.php";
        url+="?periodo="+periodo+"&cod_periodo_remuneracion="+cod_periodo_remuneracion+"&id_declaracion="+id_declaracion;		
        cargar_pagina(url,'#CapaContenedorFormulario');
		

    }
	
</script>


<div class="demo" align="left">
    <div id="tabs">
        <ul>
            <li><a href="#tabs-1">Etapa Declaracion</a></li>			

        </ul>
        <div id="tabs-1">
<div class="ocultar">id_declaracion
  <input name="id_declaracion" id="id_declaracion" type="text" value="<?php echo $ID_DECLARACION; ?>">
  <br />
  cod_periodo_remuneracionn
  <input type="text" name="cod_periodo_remuneracion" id="cod_periodo_remuneracion" value="<?php echo $cod_periodo_remuneracion; ?>" />
  <br />
  periodo
  <input type="text" name="periodo" id="periodo" value="<?php echo $periodo; ?>" />
</div>
<h2>Adelanto 
          <input name="adelanto" type="text" id="adelanto" 
                       value="<?php echo getNameMonth($mes); ?>" size="10" />
                del 
                <input name="anio" type="text" id="anio" value="<?php echo $anio; ?>" size="5" />
</h2>
          <h2>03 Seleccionde Operacion</h2>
            
            
            
            
            
          <p><!--  onclick="registrarEtapa()" -->
            <input type="button" name="adelanto_01" id="adelanto_01" value="01 Adelanto Individual">
            <input type="button" name="adelanto_02" id="adelanto_02" value="02 Adelanto Total" />
            <br />
            lista de trabajadores dentro del periodo: centro costo.          </p>
          <table id="list">
            </table>
            <div id="pager">          
            </div>    

            <p>&nbsp;</p>
            <hr />          
            <input type="button" name="btnAtras"  value="&lt;&lt; Atras"
                   onclick="atrasEtapa02()" />
            <input type="button" name="btnAdelante" id="btnAdelante" value="SIGUIENTE &gt;&gt;" disabled="disabled"
                   onclick="" /></p>
        </div>
    </div>

</div>