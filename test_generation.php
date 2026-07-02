<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

// Test DOCX creation
try {
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();
    $section->addText('Hello {name}');
    $phpWord->save('test.docx', 'Word2007');
    echo "DOCX created successfully\n";
} catch (Exception $e) {
    echo "DOCX error: " . $e->getMessage() . "\n";
}

// Test DOCX reading and template processing
try {
    $phpWord = IOFactory::load('test.docx');
    $textElements = $phpWord->getSections();
    
    foreach ($textElements as $section) {
        foreach ($section->getElements() as $element) {
            $text = method_exists($element, 'getText') ? $element->getText() : '';
            echo "Found text: $text\n";
        }
    }
    echo "DOCX read successfully\n";
} catch (Exception $e) {
    echo "DOCX read error: " . $e->getMessage() . "\n";
}

// Test TemplateProcessor
try {
    $processor = new TemplateProcessor('test.docx');
    $processor->setValue('name', 'World');
    $processor->save('test_output.docx');
    echo "TemplateProcessor works\n";
} catch (Exception $e) {
    echo "TemplateProcessor error: " . $e->getMessage() . "\n";
}

// Test PDF generation with DomPDF
try {
    $html = '<h1>Hello PDF</h1><p>This is a test.</p>';
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('test.pdf', ['Attachment' => false]);
    echo "DomPDF works\n";
} catch (Exception $e) {
    echo "DomPDF error: " . $e->getMessage() . "\n";
}

echo "\nAll tests completed!\n";
