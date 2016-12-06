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

function boletobradesco_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"Boleto Bradesco"),
	 "logo" => array("FriendlyName" => "Logo", "Type" => "text", "Size" => "40", "Description" => "Url do logo para ser exibido no boleto"),
     "taxa" => array("FriendlyName" => "Taxa", "Type" => "text", "Size" => "10", ),
     "agencia" => array("FriendlyName" => "Agencia", "Type" => "text", "Size" => "20", ),
     "agenciadv" => array("FriendlyName" => "Agencia Digito", "Type" => "text", "Size" => "20", ),
     "conta_cedente" => array("FriendlyName" => "Conta Cedente", "Type" => "text", "Size" => "20", "Description" => "ContaCedente do Cliente, sem digito (Somente Números)" ),
     "conta_cedente_dv" => array("FriendlyName" => "Conta Cedente DV", "Type" => "text", "Size" => "20", "Description" => "Digito da ContaCedente do Cliente" ),
     "convenio" => array("FriendlyName" => "Convenio / Carteira", "Type" => "dropdown", "Options" => "09,06,03", "Size" => "20", ),
	 "identificacao" => array("FriendlyName" => "Identificação", "Type" => "text", "Size" => "50", ),
	 "cpfcnpj" => array("FriendlyName" => "CPF/Cnpj", "Type" => "text", "Size" => "50", ),
	 "endereco" => array("FriendlyName" => "Endereço", "Type" => "text", "Size" => "50", ),
	 "cidade_uf" => array("FriendlyName" => "Cidade / UF", "Type" => "text", "Size" => "50", ),
	 "cedente" => array("FriendlyName" => "Razão Social", "Type" => "text", "Size" => "50", ),
	 "instrucoes1" => array("FriendlyName" => "Instruções 1", "Type" => "text", "Size" => "50", ),
	 "instrucoes2" => array("FriendlyName" => "Instruções 2", "Type" => "text", "Size" => "50", ),
	 "instrucoes3" => array("FriendlyName" => "Instruções 3", "Type" => "text", "Size" => "50", ),
	 "instrucoes4" => array("FriendlyName" => "Instruções 4", "Type" => "text", "Size" => "50", ),
    );

	return $configarray;
}

function boletobradesco_link($params) {

	$code = '<input type="button" value="'.$params['langpaynow'].'" onClick="window.location=\''.$params['systemurl'].'/modules/gateways/boletobradesco/boleto.php?invoiceid='.$params['invoiceid'].'\'" />';
	return $code;
	
}
?>