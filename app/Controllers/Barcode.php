<?php namespace App\Controllers;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Barcode extends BaseController
{
    public function generate($text)
    {
        $qrCode = new QrCode($text);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        header('Content-Type: '.$result->getMimeType());
        echo $result->getString();
        exit;
    }
}