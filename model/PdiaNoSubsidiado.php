<?php
class PdiaNoSubsidiado {
    //put your code here
    private $id_pdia_nosubsidiado;
    private $id_pago;
    private $cantidad_dia;
    private $cod_tipo_suspen_relacion_laboral;
   
    public function __construct() {
        $this->id_pdia_nosubsidiado = null;
        $this->id_pago = null;
        $this->cantidad_dia = null;
        $this->cod_tipo_suspen_relacion_laboral = null;
    }
    
    public function getId_pdia_nosubsidiado() {
        return $this->id_pdia_nosubsidiado;
    }

    public function setId_pdia_nosubsidiado($id_pdia_nosubsidiado) {
        $this->id_pdia_nosubsidiado = $id_pdia_nosubsidiado;
    }

    public function getId_pago() {
        return $this->id_pago;
    }

    public function setId_pago($id_pago) {
        $this->id_pago = $id_pago;
    }

    public function getCantidad_dia() {
        return $this->cantidad_dia;
    }

    public function setCantidad_dia($cantidad_dia) {
        $this->cantidad_dia = $cantidad_dia;
    }

    public function getCod_tipo_suspen_relacion_laboral() {
        return $this->cod_tipo_suspen_relacion_laboral;
    }

    public function setCod_tipo_suspen_relacion_laboral($cod_tipo_suspen_relacion_laboral) {
        $this->cod_tipo_suspen_relacion_laboral = $cod_tipo_suspen_relacion_laboral;
    }






    
    
}

?>
