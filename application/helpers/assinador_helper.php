<?php
Class Assinador{
    private $seed = '23ka2020';
    private $assinatura;
    public function assinar($item,$valor,$emitente,$status=""){
        $item = hash('sha256',$item);
        $valor = hash('sha256',$valor);
        $status = hash('sha256',$status);
        $this->assinatura =  hash('haval128,3',$item.$this->seed.$valor.$emitente);
    }
    public function getAsssinatura(){
        return $this->assinatura;
    }
}