<?php
/**
 * Boleto Bradesco
 *
 * Gera boletos nas carteiras 03,06,09 sem remessa
 *
 * Distribuido via GPL baseado no boletophp.com.br
 *
 * @package    BoletoBradesco
 * @author     Weverton Velludo <wv@brasilnetwork.com.br>
 * @copyright  Copyright (c) Weverton Velludo 2015
 * @license    http://www.brasilnetwork.com.br
 * @version    $Id$
 * @link       http://www.brasilnetwork.com.br
 */

class boleto_bradesco extends boleto {

	public function Link ($dadosboleto) {
		$codigobanco 						= "237";
		$codigo_banco_com_dv 				= $this->geraCodigoBanco($codigobanco);
		$nummoeda 							= "9";
		$fator_vencimento 					= $this->fator_vencimento($dadosboleto["data_vencimento"]);
		$valor 								= $this->formata_numero($dadosboleto["valor_boleto"],10,0,"valor");
		$agencia 							= $this->formata_numero($dadosboleto["agencia"],4,0);
		$conta 								= $this->formata_numero($dadosboleto["conta"],6,0);
		$conta_dv 							= $this->formata_numero($dadosboleto["conta_dv"],1,0);
		$carteira 							= $dadosboleto["carteira"];
		$nnum 								= $this->formata_numero($dadosboleto["carteira"],2,0).$this->formata_numero($dadosboleto["nosso_numero"],11,0);
		$dv_nosso_numero 					= $this->digitoVerificador_nossonumero($nnum);
		$conta_cedente 						= $this->formata_numero($dadosboleto["conta"],7,0);
		$conta_cedente_dv 					= $this->formata_numero($dadosboleto["conta_dv"],1,0);
		$dv 								= $this->digitoVerificador_barra("$codigobanco$nummoeda$fator_vencimento$valor$agencia$nnum$conta_cedente".'0', 9, 0);
		$linha 								= "$codigobanco$nummoeda$dv$fator_vencimento$valor$agencia$nnum$conta_cedente"."0";
		$nossonumero 						= substr($nnum,0,2).'/'.substr($nnum,2).'-'.$dv_nosso_numero;
		$dadosboleto["codigo_barras"] 		= $linha;
		$dadosboleto["codigo_barras_img"]	= $this->fbarcode($linha);
		$dadosboleto["linha_digitavel"] 	= $this->monta_linha_digitavel($linha);
		$dadosboleto["agencia_codigo"] 		= $dadosboleto["agencia"] ."-".$dadosboleto["agencia_dv"] ." / ". $conta_cedente ."-". $conta_cedente_dv;
		$dadosboleto["nosso_numero"] 		= $nossonumero;
		$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
		
		require_once(dirname(__FILE__) . "/layout/bradesco.php");
	}

    public function Retorno ($arquivo) {
        if (!file_exists($arquivo)) {
			return false;
        } else {
            $arquivo = file($arquivo);
            foreach ($arquivo as $registro) {
                $retorno["identificacao"]               = substr($registro,0,1);
                $retorno["documento"]                   = substr($registro,1,2);
                $retorno["empresa"]                     = substr($registro,3,14);
                $retorno["cedente"]                     = substr($registro,20,17);
                $retorno["carteira"]                    = substr($registro,21,3);
                $retorno["agencia"]                     = substr($registro,24,5);
                $retorno["conta"]                       = substr($registro,29,8);
                $retorno["controle"]                    = substr($registro,37,25);
                $retorno["banco"]                       = substr($registro,70,12);
                $retorno["pagamento"]                   = substr($registro,110,6);
                $retorno["titulo"]                      = substr($registro,126,20) * 1;
                $retorno["vencimento"]                  = substr($registro,146,6);
                $retorno["valor"]                       = substr($registro,152,13);
                $retorno["bancocobrador"]               = substr($registro,165,3);
                $retorno["agenciacobradora"]    		= substr($registro,168,5);
                $retorno["especie"]                     = substr($registro,173,2);
                $retorno["despesas"]                    = substr($registro,175,13);
                $retorno["valorpago"]                   = substr($registro,253,13) * 1;
                $retorno["juros"]                       = substr($registro,266,13);
                $retorno["despesas"]                    = substr($registro,175,13);
                $retorno["datacredito"]                 = substr($registro,295,6);
                $retorno["sequencial"]                  = substr($registro,394,6);
                $retorno["valorpagoformatado"]  		= substr($retorno["valorpago"], 0, strlen($retorno["valorpago"])-2) . "." . substr($retorno["valorpago"], -2);
                $retorno["pedido"]                      = substr($retorno["titulo"],0,strlen($retorno["titulo"])-1);
                $valor_bb 								= $retorno["valorpago"] * 1;

                if (is_numeric($retorno["pedido"]) && $retorno["pedido"] > 0 && $valor_bb >= 1) {
					$dados[] = $retorno;
                }
            }
            return $dados;
		}
    }
	
	public function digitoVerificador_barra($numero) {
		$resto2 = $this->modulo_11($numero, 9, 1);
	     if ($resto2 == 0 || $resto2 == 1 || $resto2 == 10) {
	        $dv = 1;
	     } else {
		 	$dv = 11 - $resto2;
	     }
		 return $dv;
	}
	
	public function digitoVerificador_nossonumero($numero) {
		$resto2 = $this->modulo_11($numero, 7, 1);
	    $digito = 11 - $resto2;
	    if ($digito == 10) {
	       $dv = "P";
	    } elseif($digito == 11) {
	    	$dv = 0;
		} else {
	        $dv = $digito;
	    }
		return $dv;
	}
	
	public function monta_linha_digitavel($codigo) {
        $p1 = substr($codigo, 0, 4);
        $p2 = substr($codigo, 19, 5);
        $p3 = $this->modulo_10("$p1$p2");
        $p4 = "$p1$p2$p3";
        $campo1 = substr($p4, 0, 5).'.'.substr($p4, 5);

        $p1 = substr($codigo, 24, 10);
        $p2 = $this->modulo_10($p1);
        $p3 = "$p1$p2";
        $campo2 = substr($p3, 0, 5).'.'.substr($p3, 5);
		
        $p1 = substr($codigo, 34, 10);
        $p2 = $this->modulo_10($p1);
        $p3 = "$p1$p2";
        $campo3 = substr($p3, 0, 5).'.'.substr($p3, 5);

        $campo4 = substr($codigo, 4, 1);

		$p1 = substr($codigo, 5, 4);
		$p2 = substr($codigo, 9, 10);
		$campo5 = "$p1$p2";

        return "$campo1 $campo2 $campo3 $campo4 $campo5"; 
	}
		
}
?>