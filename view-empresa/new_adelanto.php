<?php 
require_once('../dao/AbstractDao.php');
require_once('../dao/PeriodoRemuneracionDao.php');
require_once('../controller/PeriodoRemuneracionController.php');

$p_remuneracion = listarPeriodoRemuneracion();

?>
<script type="text/javascript">
    $(document).ready(function(){                  
        $( "#tabs").tabs();
		
	});
</script>


<div class="demo" align="left">
    <div id="tabs">
        <ul>
            <li><a href="#tabs-1">Adelanto</a></li>			

        </ul>
        <div id="tabs-1">
        
    <div class="ayuda">
    Ayuda...
    Adelanto Individual</div>
        
        <label>mes</label>
        <input type="text" name="mes" id="mes" size="5" /> 
        a&ntilde;o
        
        <input name="adelanto_anio" type="text" id="adelanto_anio" size="5" /> 
        
        <label for="cbo_periodo remuneracion">periodo Remuneracion</label>
        <select name="cbo_periodo remuneracion" id="cbo_periodo remuneracion">
            <option value="">-</option>
              <?php 
        
        foreach ($p_remuneracion  as $indice) {
            if( $indice['cod_periodo_remuneracion'] == 1 ){
        
                $html = '<option value="'. $indice['cod_periodo_remuneracion'] .'" selected="selected" >' . $indice['descripcion'] . '</option>';
        
            }else {
                $html = '<option value="'. $indice['cod_periodo_remuneracion'] .'" >' . $indice['descripcion'] . '</option>';
            }
            echo $html;
        } 
        ?>
        
        </select>
        <input type="submit" name="button" id="button" value="Listar Trabajadores">
<h2>Lista de Trabajadores</h2>
        
        
        
        </div>
        
        
        
</div>

</div>

