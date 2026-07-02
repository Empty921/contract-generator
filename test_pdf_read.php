<?php

require __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

// Попробуем создать реальный PDF через MPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P'
]);

$html = '<h1>Тестовый документ</h1><p>Здесь будут переменные: {name}, {date}, {amount}</p>';
$mpdf->WriteHTML($html);
$mpdf->Output('test_real.pdf', \Mpdf\Output\Destination::FILE);

echo "Created test_real.pdf\n";

// Теперь попробуем прочитать его
try {
    $parser = new Parser();
    $pdf = $parser->parseFile('test_real.pdf');
    $text = $pdf->getText();
    echo "PDF text:\n$text\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
