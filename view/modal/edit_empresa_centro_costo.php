<?php 
//session_start();

require_once('../../util/funciones.php');
require_once('../../dao/AbstractDao.php');

require_once ('../../dao/EstablecimientoCentroCostoDao.php');
require_once ('../../controller/EstablecimientoCentroCostoController.php');

$id_establecimiento = $_REQUEST['id_establecimiento'];


$data = listarEstablecimientoCentroCosto($id_establecimiento,"A");

//echo "<pre>";
//print_r($data);
//echo "</pre>";


?>
<script type="text/javascript">
    //INICIO HISTORIAL
    $(document).ready(function(){						   
        //demoApp = new Historial();                  
        $( "#tabs").tabs();
		
	});
	
</script>

<div>

<form action="hola.php" method="get" name="formEditEmpresaCentroCosto"
id="formEditEmpresaCentroCosto">

<div class="ocultar">
<input name="oper" type="text" value="edit" />
<input  type="text" name="id_establecimiento" id="id_establecimiento"
value="<?php echo $id_establecimiento ?>" />
</div>

  <table width="350" border="1" id="">
 
 <?php
 if(count($data)>0):
 ?>

    <tr>
      <td width="35">ID</td>
      <td width="202">Descripcion</td>
      <td width="91"> <input type="checkbox" name="checkbox" id="" 
      onClick="estadoCheck(this,'formEditEmpresaCentroCosto')">
      Seleccione</td>
    </tr>
 
    
<?php for($i=0; $i<count($data); $i++): ?>    
    
    
    <tr>
      <td>
        <input name="id_establecimiento_centro_costo[]" type="text" 
        value="<?php  echo $data[$i]['id_establecimiento_centro_costo']; ?>" size="2" />
      </td>
      
      
      <td><input name="id_empresa_centro_costo[]" type="hidden"  size="2"
      value="<?php  echo $data[$i]['id_empresa_centro_costo']; ?>" />        <?php echo  $data[$i]['descripcion']; ?></td>
      
      
      <td><input type="checkbox" name="seleccionado[]" 
      value="<?php  echo $data[$i]['id_establecimiento_centro_costo']; ?>"
      
      <?php echo ($data[$i]['seleccionado'] == '1') ? ' checked="checked"' : ''; ?>
      
       >
      </td>
    </tr>
    
<?php 
endfor;
else:
 echo "<h1>No existe Centro De costo</h1>";
endif;


 ?>    
    
    
  </table>

</form>  
  
</div>