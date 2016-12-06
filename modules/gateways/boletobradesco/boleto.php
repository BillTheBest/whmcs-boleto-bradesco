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

define("CLIENTAREA",true);
define("FORCESSL",true);

require("../../../init.php");
$whmcs->load_function('gateway');
$whmcs->load_function('client');
$whmcs->load_function('invoice');

require_once(dirname(__FILE__) . "/class_boleto.php");
require_once(dirname(__FILE__) . "/class_bradesco.php");

$GATEWAY = getGatewayVariables("boletobradesco");
if (!$GATEWAY["type"]) die("Module Not Activated");

if (is_numeric($invoiceid) && $invoiceid > 0) {

	$result = full_query("SELECT * FROM `tblinvoices` WHERE id = " . $invoiceid);
	$data = mysql_fetch_array($result);
	$id = $data["id"];
	$userid = $data["userid"];
	$date = $data["date"];
	$duedate = $data["duedate"];
	$subtotal = $data["subtotal"];
	$credit = $data["credit"];
	$tax = $data["tax"];
	$taxrate = $data["taxrate"];
	$total = $data["total"];

	if ( $id && $userid && ( isset($_SESSION['adminid']) || $_SESSION["uid"]==$userid ) ) {} else {
		die("Invalid Access Attempt");
	}

	$clientsdetails = getClientsDetails($userid);

	$year = substr($duedate,0,4);
	$month = substr($duedate,5,2);
	$day = substr($duedate,8,2);

	$taxa_boleto 						= $GATEWAY["taxa"];
	$valor_cobrado 						= $total;
	$valor_cobrado 						= str_replace(",", ".",$valor_cobrado);
	$valor_boleto						= number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] 		= $invoiceid;  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] 	= $invoiceid;	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] 	= date("d/m/Y",mktime(0,0,0,$month,$day,$year)); // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] 		= date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] 	= date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] 		= number_format($valor_cobrado+$taxa_boleto, 2, ',', ''); 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] 			= "";
	$dadosboleto["valor_unitario"] 		= "";
	$dadosboleto["aceite"] 				= "N";		
	$dadosboleto["especie"] 			= "R$";
	$dadosboleto["especie_doc"] 		= "DM";
	$dadosboleto["conta"] 				= $GATEWAY["conta_cedente"];	// Num da conta, sem digito
	$dadosboleto["conta_dv"] 			= $GATEWAY["conta_cedente_dv"]; 	// Digito do Num da conta
	$dadosboleto["agencia"]				= substr("0000" . $GATEWAY["agencia"],-4);
	$dadosboleto["agencia_dv"]			= $GATEWAY["agenciadv"];
	$dadosboleto["carteira"] 			= $GATEWAY["convenio"];  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157
	$dadosboleto["identificacao"] 		= $GATEWAY["identificacao"];
	$dadosboleto["cpf_cnpj"] 			= $GATEWAY["cpfcnpj"];
	$dadosboleto["endereco"] 			= $GATEWAY["endereco"];
	$dadosboleto["cidade_uf"] 			= $GATEWAY["cidade_uf"];
	$dadosboleto["cedente"] 			= $GATEWAY["cedente"];

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] 				= $clientsdetails["firstname"]." ".$clientsdetails["lastname"];
	$dadosboleto["endereco1"] 			= $clientsdetails["address1"];
	$dadosboleto["endereco2"] 			= $clientsdetails["city"].", ".$clientsdetails["state"].", ".$clientsdetails["postcode"]; 

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] 		= $dadosboleto["identificacao"];
	$dadosboleto["demonstrativo2"] 		= "Referente ao pedido " . $invoiceid;
	$dadosboleto["demonstrativo3"] 		= ($taxa_boleto > 0) ? "Taxa bancária - R$ ".number_format($taxa_boleto, 2, ',', '') : "&nbsp;";
	$dadosboleto["instrucoes1"] 		= $GATEWAY["instrucoes1"];
	$dadosboleto["instrucoes2"] 		= $GATEWAY["instrucoes2"];
	$dadosboleto["instrucoes3"] 		= $GATEWAY["instrucoes3"];
	$dadosboleto["instrucoes4"] 		= $GATEWAY["instrucoes4"];
	$dadosboleto["urllogo"] 			= $GATEWAY["logo"];
	
	$boleto = new boleto_bradesco();
	$link = $boleto->Link($dadosboleto);
} else {
	header("Location: ../../../clientarea.php");
}
?>