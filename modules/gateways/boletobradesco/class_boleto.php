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

class boleto {
	
	public function modulo_10($num) { 
		$numtotal10 = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num,$i-1,1);
            $temp = $numeros[$i] * $fator; 
            $temp0=0;
            foreach (preg_split('//',$temp,-1,PREG_SPLIT_NO_EMPTY) as $k=>$v){ $temp0+=$v; }
            $parcial10[$i] = $temp0;
            $numtotal10 += $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            } else {
                $fator = 2;
            }
        }
        $resto = $numtotal10 % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }
        return $digito;
	}

	public function modulo_11($num, $base=9, $r=0)  {
	    $soma = 0;
	    $fator = 2;
	    for ($i = strlen($num); $i > 0; $i--) {
	        $numeros[$i] = substr($num,$i-1,1);
	        $parcial[$i] = $numeros[$i] * $fator;
	        // Soma dos digitos
	        $soma += $parcial[$i];
	        if ($fator == $base) {
	            $fator = 1;
	        }
	        $fator++;
	    }
	    if ($r == 0) {
	        $soma *= 10;
	        $digito = $soma % 11;
	        if ($digito == 10) {
	            $digito = 0;
	        }
	        return $digito;
	    } elseif ($r == 1){
	        $resto = $soma % 11;
	        return $resto;
	    }
	}
	
	function formata_numero($numero,$loop,$insert,$tipo = "geral") {
		if ($tipo == "geral") {
			$numero = str_replace(",","",$numero);
			while(strlen($numero)<$loop){
				$numero = $insert . $numero;
			}
		}
		if ($tipo == "valor") {
			$numero = str_replace(",","",$numero);
			while(strlen($numero)<$loop){
				$numero = $insert . $numero;
			}
		}
		if ($tipo == "convenio") {
			while(strlen($numero)<$loop){
				$numero = $numero . $insert;
			}
		}
		return $numero;
	}
	
	public function geraCodigoBanco($numero) {
	    $parte1 = substr($numero, 0, 3);
	    $parte2 = $this->modulo_11($parte1);
	    return $parte1 . "-" . $parte2;
	}
	
	function esquerda($entra,$comp){
		return substr($entra,0,$comp);
	}
	
	function direita($entra,$comp){
		return substr($entra,strlen($entra)-$comp,$comp);
	}
	
	public function fbarcode($valor){

		$fino 		= 1;
		$largo 		= 3;
		$altura 	= 50;
		$barcodes 	= array("00110","10001","01001","11000","00101","10100","01100","00011","10010","01010");

		for($f1=9;$f1>=0;$f1--) { 
			for($f2=9;$f2>=0;$f2--) {  
				$f = ($f1 * 10) + $f2;
				$texto = "";
				for($i=1;$i<6;$i++){ 
					$texto .=  substr($barcodes[$f1],($i-1),1) . substr($barcodes[$f2],($i-1),1);
				}
				$barcodes[$f] = $texto;
			}
		}
		$img_html = "<img src=\"imagens/p.png\" width=\"" . $fino . "\" height=\"" . $altura . "\" border=\"0\">".
				 	"<img src=\"imagens/b.png\" width=\"" . $fino . "\" height=\"" . $altura . "\" border=\"0\">".
					"<img src=\"imagens/p.png\" width=\"" . $fino . "\" height=\"" . $altura . "\" border=\"0\">".
					"<img src=\"imagens/b.png\" width=\"" . $fino . "\" height=\"" . $altura . "\" border=\"0\">";
		
	 	$texto = $valor;
		
	 	if((strlen($texto) % 2) <> 0){
	 		$texto = "0" . $texto;
	 	}
		
		while (strlen($texto) > 0) {
			$i = round($this->esquerda($texto,2));
			$texto = $this->direita($texto,strlen($texto)-2);
			$f = $barcodes[$i];
			for($i=1;$i<11;$i+=2){
				if (substr($f,($i-1),1) == "0") {
					$f1 = $fino ;
				} else {
					$f1 = $largo ;
				}
		    	$img_html .= "<img src=\"imagens/p.png\" width=\"" . $f1 . "\" height=\"" . $altura . "\" border=\"0\">";

			    if (substr($f,$i,1) == "0") {
			      $f2 = $fino ;
			    }else{
			      $f2 = $largo ;
			    }
				$img_html .= "<img src=\"imagens/b.png\" width=\"" . $f2 . "\" height=\"" . $altura . "\" border=\"0\">";
			}
		}
		$img_html .= "<img src=\"imagens/p.png\" width=\"" . $largo . "\" height=\"" . $altura . "\" border=\"0\">".
				 	 "<img src=\"imagens/b.png\" width=\"" . $fino . "\" height=\"" . $altura . "\" border=\"0\">".
					 "<img src=\"imagens/p.png\" width=\"1\" height=\"" . $altura . "\" border=\"0\">";
					
		return $img_html;
	}
	
	function fator_vencimento($data) {
		$data = explode("/",$data);
		$ano = $data[2];
		$mes = $data[1];
		$dia = $data[0];
	    return(abs(($this->_dateToDays("1997","10","07")) - ($this->_dateToDays($ano, $mes, $dia))));
	}

	function _dateToDays($year,$month,$day) {
	    $century = substr($year, 0, 2);
	    $year = substr($year, 2, 2);
	    if ($month > 2) {
	        $month -= 3;
	    } else {
	        $month += 9;
	        if ($year) {
	            $year--;
	        } else {
	            $year = 99;
	            $century --;
	        }
	    }
	    return (floor((146097 * $century)/4)+floor((1461 * $year)/4)+floor((153*$month+ 2)/5)+$day+1721119);
	}
	
}
?>