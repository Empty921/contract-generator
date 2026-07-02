<?php

require __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

// Create a simple PDF for testing
$pdfContent = "%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>
endobj
4 0 obj
<< /Length 44 >>
stream
BT /F1 12 Tf 72 720 Td (Hello PDF Test) Tj ET
endstream
endobj
5 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000203 00000 n
0000000284 00000 n
trailer
<< /Size 6 /Root 1 0 R >>
startxref
364
%%EOF";

file_put_contents('test_input.pdf', $pdfContent);

// Test PDF parsing
try {
    $parser = new Parser();
    $pdf = $parser->parseFile('test_input.pdf');
    $text = $pdf->getText();
    echo "PDF parsed successfully\n";
    echo "Text: $text\n";
} catch (Exception $e) {
    echo "PDF parse error: " . $e->getMessage() . "\n";
}

echo "\nPDF parsing test completed!\n";
