<?php

//require_once '../dao/AbstractDao.php';
//require_once '../model/DiaNoSubsidiado.php';

class DiaNoSubsidiadoDao extends AbstractDao {

    // singleton
    private static $instancia = null;

    static public function anb_add($obj) {

        if (self::$instancia == null) {
            try {
                self::$instancia = new DiaNoSubsidiadoDao();
                //self::$instancia->registrar($obj);               
            } catch (Exception $e) {
                throw $e;
            }
        }

        self::$instancia->registrar($obj);
    }

    static public function anb_existe($id_trabajador_pdeclaracion,$codigo){
        if (self::$instancia == null) {
            self::$instancia = new DiaNoSubsidiadoDao(); 
        }
        return self::$instancia->existe($id_trabajador_pdeclaracion,$codigo);
    }
    
    static public function anb_update($obj){
        if (self::$instancia == null) {
            self::$instancia = new DiaNoSubsidiadoDao(); 
        }
        return self::$instancia->actualizar($obj);        
    }
    
    static public function anb_diasNoSubsidiado($id_trabajador_pdeclaracion, $cod_tipo_suspen_relacion_laboral) {

        if (self::$instancia == null) {
            try {
                self::$instancia = new DiaNoSubsidiadoDao();                
            } catch (Exception $e) {
                throw $e;
            }
        }
        
        self::$instancia->diasNoSubsidiado($id_trabajador_pdeclaracion, $cod_tipo_suspen_relacion_laboral);
        
        
    }

    public function registrar($obj) {
        //ECHO "\nENTRO MAS DENTRO insert SINGLETON\n";
        //echo "\n\n";
        $model = new DiaNoSubsidiado();
        $model = $obj;
        $query = "
        INSERT INTO dias_nosubsidiados
                    (
                     id_trabajador_pdeclaracion,
                     cantidad_dia,
                     cod_tipo_suspen_relacion_laboral,
                     estado,
                     fecha_inicio,
                     fecha_fin)
        VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?);          
        ";

        $stm = $this->pdo->prepare($query);
        $stm->bindValue(1, $model->getId_trabajador_pdeclaracion());
        $stm->bindValue(2, $model->getCantidad_dia());
        $stm->bindValue(3, $model->getCod_tipo_suspen_relacion_laboral());
        $stm->bindValue(4, $model->getEstado());
        $stm->bindValue(5, $model->getFecha_inicio());
        $stm->bindValue(6, $model->getFecha_fin());

        $stm->execute();
        //$lista = $stm->fetchAll();
        $stm = null;
        return true;
    }

    public function actualizar($obj) { // ojo funcion usada ? sino PRIVATE = static

        $model = new DiaNoSubsidiado();
        $model = $obj;
        $query = "
        UPDATE dias_nosubsidiados
        SET   
          cantidad_dia = ?,
          cod_tipo_suspen_relacion_laboral = ?,
          fecha_inicio=?,
          fecha_fin=?
        WHERE id_dia_nosubsidiado = ?;  
        ";

        $stm = $this->pdo->prepare($query);
        $stm->bindValue(1, $model->getCantidad_dia());
        $stm->bindValue(2, $model->getCod_tipo_suspen_relacion_laboral());
        $stm->bindValue(3, $model->getFecha_inicio());
        $stm->bindValue(4, $model->getFecha_fin());
        $stm->bindValue(5, $model->getId_dia_nosubsidiado());

        $stm->execute();
        //$lista = $stm->fetchAll();
        $stm = null;
        return true;
    }

    public function eliminar($id) {

        $query = "
        DELETE
        FROM dias_nosubsidiados
        WHERE id_dia_nosubsidiado = ?;      
        ";

        $stm = $this->pdo->prepare($query);
        $stm->bindValue(1, $id);

        $stm->execute();
        //$lista = $stm->fetchAll();
        $stm = null;
        return true;
    }

    public function buscar_IdTrabajadorPdeclaracion($id, $SUM=null) {

        $query1 = "
        SELECT
          id_dia_nosubsidiado,
          id_trabajador_pdeclaracion,
          cantidad_dia,
          cod_tipo_suspen_relacion_laboral,
          fecha_inicio,
          fecha_fin,
          estado
        FROM dias_nosubsidiados
        WHERE id_trabajador_pdeclaracion = ?         
        ";
        $query2 = "
        SELECT        
        SUM(cantidad_dia) AS cantidad_dia
        FROM dias_nosubsidiados
        WHERE id_trabajador_pdeclaracion = ?          
        ";
        if ($SUM == "SUMA") {
            $query = $query2;
        } else {
            $query = $query1;
        }
        $stm = $this->pdo->prepare($query);
        $stm->bindValue(1, $id);
        $stm->execute();
        $lista = $stm->fetchAll();
        $stm = null;

        if ($SUM == "SUMA") {
            return $lista[0]['cantidad_dia'];
        } else {
            return $lista;
            ;
        }
    }

        
    
    //new 
    private function existe($id_trabajador_pdeclaracion,$codigo){
        
        $query ="
        SELECT 
        id_dia_nosubsidiado
        FROM dias_nosubsidiados
        WHERE id_trabajador_pdeclaracion = ?
        AND cod_tipo_suspen_relacion_laboral = ?            
        ";
        $stm = $this->pdo->prepare($query);
        $stm->bindValue(1, $id_trabajador_pdeclaracion);
        $stm->bindValue(2, $codigo);
        $stm->execute();
        $lista = $stm->fetchAll();
        $stm = null;
        return $lista[0]['id_dia_nosubsidiado'];        
    }
    
    //personalizado para boleta:
    private function diasNoSubsidiado($id_trabajador_pdeclaracion, $cod_tipo_suspen_relacion_laboral) {

        $query = "
        SELECT
          -- id_dia_nosubsidiado,          
          cantidad_dia                   
        FROM dias_nosubsidiados
        WHERE id_trabajador_pdeclaracion = ?
        AND cod_tipo_suspen_relacion_laboral = ?        
        ";

        $stm = $this->pdo->prepare($query);
        $stm->bindValue(1, $id_trabajador_pdeclaracion);
        $stm->bindValue(2, $cod_tipo_suspen_relacion_laboral);
        $stm->execute();
        $lista = $stm->fetchAll();
        $stm = null;

        return $lista[0]['cantidad_dia'];
    }


}

/*
  $obj = new DiaNoSubsidiado();
  $obj->setId_trabajador_pdeclaracion(1171);
  $obj->setCantidad_dia(5);
  $obj->setCod_tipo_suspen_relacion_laboral(23);

  DiaNoSubsidiadoDao::anb_registrar($obj);
 */
?>
