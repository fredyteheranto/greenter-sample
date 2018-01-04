<?php

use Greenter\Model\Client\Client;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/../vendor/autoload.php';

// Cliente
$client = new Client();
$client->setTipoDoc('6')
    ->setNumDoc('20000000001')
    ->setRznSocial('EMPRESA 1');

// Venta
$invoice = new Invoice();
$invoice->setTipoDoc('01')
    ->setSerie('F001')
    ->setCorrelativo('123')
    ->setFechaEmision(new DateTime())
    ->setTipoMoneda('PEN')
    ->setClient($client)
    ->setMtoOperGravadas(200)
    ->setMtoOperExoneradas(0)
    ->setMtoOperInafectas(0)
    ->setMtoIGV(36)
    ->setMtoImpVenta(2236.43)
    ->setCompany(Util::getCompany());

$item1 = new SaleDetail();
$item1->setCodProducto('C023')
    ->setCodUnidadMedida('NIU')
    ->setCtdUnidadItem(2)
    ->setDesItem('PROD 1')
    ->setMtoIgvItem(18)
    ->setTipAfeIgv('10')
    ->setMtoValorVenta(100)
    ->setMtoValorUnitario(50)
    ->setMtoPrecioUnitario(56);

$item2 = new SaleDetail();
$item2->setCodProducto('C02')
    ->setCodUnidadMedida('NIU')
    ->setCtdUnidadItem(2)
    ->setDesItem('PROD 2')
    ->setMtoIgvItem(18)
    ->setTipAfeIgv('10')
    ->setMtoValorVenta(100)
    ->setMtoValorUnitario(50)
    ->setMtoPrecioUnitario(56);

$legend = new Legend();
$legend->setCode('1000')
    ->setValue('SON CIEN CON 00/100 SOLES');

$invoice->setDetails([$item1, $item2])
    ->setLegends([$legend]);

// Envio a SUNAT.
$see = new \Greenter\See();
$see->setService(SunatEndpoints::FE_BETA);
$see->setCertificate(file_get_contents(__DIR__.'/../resources/cert.pem'));
$see->setCredentials('20000000001MODDATOS', 'moddatos');

$res = $see->send($invoice);
Util::writeXml($invoice, $see->getFactory()->getLastXml());

if ($res->isSuccess()) {
    /**@var $res \Greenter\Model\Response\BillResult*/
    $cdr = $res->getCdrResponse();
    Util::writeCdr($invoice, $res->getCdrZip());

    echo '<h2>Respuesta SUNAT:</h2><br>';
    echo '<b>ID:</b> ' . $cdr->getId().'<br>';
    echo '<b>CODE:</b> ' . $cdr->getCode().'<br>';
    echo '<b>DESCRIPTION:</b> ' . $cdr->getDescription().'<br>';
} else {
    var_dump($res->getError());
}

