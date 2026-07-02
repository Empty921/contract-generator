<?php

require __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

// Test MPDF generation
try {
    $html = '<h1>Тестовый документ</h1><p>Привет, мир!</p><p>Имя: {name}</p>';
    
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P'
    ]);
    
    $mpdf->WriteHTML($html);
    $mpdf->Output('test_mpdf.pdf', \Mpdf\Output\Destination::FILE);
    
    if (file_exists('test_mpdf.pdf')) {
        echo "MPDF created successfully\n";
        echo "File size: " . filesize('test_mpdf.pdf') . " bytes\n";
    } else {
        echo "MPDF file not created\n";
    }
} catch (Exception $e) {
    echo "MPDF error: " . $e->getMessage() . "\n";
}

echo "\nMPDF test completed!\n";
