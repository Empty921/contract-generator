<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateVariable;
use App\Models\GeneratedDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Smalot\PdfParser\Parser as PdfParser;
use Exception;
use ZipArchive;

class TemplateController extends Controller
{
    public function create()
    {
        return view('templates.create');
    }

    public function dashboard()
    {
        $templates = Template::with('variables')->latest()->get();
        $generatedDocuments = GeneratedDocument::with('template')->latest()->get();
        return view('dashboard', compact('templates', 'generatedDocuments'));
    }

    public function index()
    {
        $templates = Template::with('variables')->latest()->get();
        return view('templates.index', compact('templates'));
    }

    public function show($id)
    {
        $template = Template::with('variables')->findOrFail($id);
        return view('templates.show', compact('template'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'template_file' => 'required|mimes:docx,pdf|max:10240',
        ]);

        $file = $request->file('template_file');
        $originalName = $file->getClientOriginalName();
        $format = strtolower($file->getClientOriginalExtension());
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '_' . uniqid() . '.' . $format;
        $path = $file->storeAs('templates', $safeName, 'local');

        $template = Template::create([
            'name' => $request->input('name'),
            'file_path' => $path,
            'format' => $format,
        ]);

        $variables = [];

        try {
            $variables = $this->extractVariables(Storage::path($path), $format);

            if (count($variables) === 0) {
                $template->delete();
                Storage::disk('local')->delete($path);
                return back()->withErrors(['template_file' => 'В шаблоне не найдено переменных вида ${variable}.']);
            }

            foreach ($variables as $varName => $maxLength) {
                TemplateVariable::create([
                    'template_id' => $template->id,
                    'variable_name' => $varName,
                    'max_length' => $maxLength,
                ]);
            }

        } catch (Exception $e) {
            Storage::disk('local')->delete($path);
            return back()->withErrors(['template_file' => 'Ошибка: ' . $e->getMessage()]);
        }

        return redirect()->route('dashboard')
                        ->with('success', 'Шаблон загружен! Доступно ' . count($variables) . ' переменных.');
    }

    public function generate(Request $request, $id)
    {
        $template = Template::with('variables')->findOrFail($id);

        $request->validate(['output_format' => 'required|in:docx,pdf']);
        $outputFormat = $request->input('output_format');

        if (!$this->isOutputFormatSupported($template->format, $outputFormat)) {
            return back()->withInput()->withErrors([
                'output_format' => $this->unsupportedFormatMessage($template->format, $outputFormat),
            ]);
        }

        $variableValues = [];
        foreach ($template->variables as $variable) {
            $value = trim((string)$request->input($variable->variable_name));
            if ($value === '') {
                return back()->withInput()->withErrors([$variable->variable_name => 'Поле не заполнено.']);
            }
            if ($variable->max_length && mb_strlen($value) > $variable->max_length) {
                return back()->withInput()->withErrors([$variable->variable_name => 'Максимум ' . $variable->max_length . ' символов.']);
            }
            $variableValues[$variable->variable_name] = $value;
        }

        try {
            $sourcePath = Storage::path($template->file_path);
            if (!file_exists($sourcePath)) {
                return back()->withErrors(['error' => 'Файл шаблона не найден.']);
            }

            $generatedDir = storage_path('app/generated');
            if (!file_exists($generatedDir)) mkdir($generatedDir, 0775, true);

            $fileName = 'generated_' . date('Ymd_His') . '_' . uniqid();
            $generatedFilePath = $this->generateDocumentFile($template, $sourcePath, $variableValues, $outputFormat, $generatedDir, $fileName);

            GeneratedDocument::create([
                'template_id' => $template->id,
                'output_format' => $outputFormat,
                'variables_json' => json_encode($variableValues),
                'file_path' => 'generated/' . basename($generatedFilePath),
            ]);

            return response()->download($generatedFilePath, $fileName . '.' . $outputFormat)
                ->deleteFileAfterSend(false);
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Ошибка: ' . $e->getMessage()]);
        }
    }

    public function history()
    {
        $documents = GeneratedDocument::with('template')->latest()->get();
        return view('history', compact('documents'));
    }

    public function previewDocument(GeneratedDocument $document)
    {
        $path = Storage::path($document->file_path);
        if (!file_exists($path)) {
            return response('Файл предпросмотра не найден. Возможно, он был удалён из хранилища.', 404);
        }

        if ($document->output_format === 'pdf') {
            return response()->file($path, ['Content-Type' => 'application/pdf']);
        }

        $phpWord = IOFactory::load($path);
        return response($this->phpWordToHtml($phpWord));
    }

    public function downloadDocument(GeneratedDocument $document)
    {
        $path = Storage::path($document->file_path);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, 'document_' . $document->id . '.' . $document->output_format);
    }

    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        TemplateVariable::where('template_id', $template->id)->delete();

        if ($template->file_path && Storage::exists($template->file_path)) {
            Storage::delete($template->file_path);
        }

        foreach ($template->generatedDocuments as $document) {
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        }

        $template->delete();
        return redirect()->route('dashboard')->with('success', 'Шаблон удалён.');
    }

    private function extractVariables(string $filePath, string $format): array
    {
        if ($format === 'docx') {
            return array_fill_keys($this->extractDocxVariables($filePath), null);
        }

        $parser = new PdfParser();
        $text = $parser->parseFile($filePath)->getText();

        return array_fill_keys($this->extractValidVariablesFromText($text), null);
    }

    private function extractDocxVariables(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception('Не удалось открыть DOCX-файл для проверки плейсхолдеров.');
        }

        $text = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('/^word\/(document|header\d+|footer\d+)\.xml$/', $name)) {
                $xml = $zip->getFromIndex($i);
                $text .= html_entity_decode(strip_tags((string) $xml), ENT_QUOTES | ENT_XML1, 'UTF-8') . "\n";
            }
        }
        $zip->close();

        return $this->extractValidVariablesFromText($text);
    }

    private function extractValidVariablesFromText(string $text): array
    {
        if (preg_match('/\$\{\s*\}/u', $text)) {
            throw new Exception('В шаблоне найден пустой плейсхолдер ${}. Укажите имя переменной, например ${CLIENT_NAME}.');
        }

        preg_match_all('/\$\{/u', $text, $openMatches);
        preg_match_all('/\}/u', $text, $closeMatches);
        if (count($openMatches[0]) !== count($closeMatches[0])) {
            throw new Exception('В шаблоне есть непарные скобки плейсхолдера. Проверьте разметку вида ${NAME}.');
        }

        preg_match_all('/\$\{([^}]*)\}/u', $text, $matches);
        $variables = [];

        foreach ($matches[1] ?? [] as $rawName) {
            $name = trim($rawName);
            if ($name === '') {
                throw new Exception('В шаблоне найден пустой плейсхолдер ${}. Укажите имя переменной.');
            }
            if (str_contains($name, '${') || str_contains($name, '}')) {
                throw new Exception('В шаблоне найдена вложенная или сломанная разметка плейсхолдера. Используйте только простой формат ${NAME}.');
            }
            if (!preg_match('/^[\p{L}\p{N}_\-\.]+$/u', $name)) {
                throw new Exception('Некорректное имя плейсхолдера «' . $name . '». Разрешены буквы, цифры, точка, дефис и подчёркивание.');
            }

            $variables[] = $name;
        }

        if (str_contains($text, '${') && count($variables) !== count($openMatches[0])) {
            throw new Exception('В шаблоне найдена некорректная разметка плейсхолдера. Используйте формат ${NAME} без вложенных конструкций.');
        }

        return array_values(array_unique($variables));
    }

    private function generateDocumentFile(Template $template, string $sourcePath, array $variableValues, string $outputFormat, string $generatedDir, string $fileName): string
    {
        if ($template->format === 'docx') {
            if ($outputFormat !== 'docx') {
                throw new Exception($this->unsupportedFormatMessage($template->format, $outputFormat));
            }

            $docxPath = $generatedDir . DIRECTORY_SEPARATOR . $fileName . '.docx';
            $processor = new TemplateProcessor($sourcePath);

            foreach ($variableValues as $name => $value) {
                $processor->setValue($name, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }

            $processor->saveAs($docxPath);

            if (!file_exists($docxPath)) {
                throw new Exception('Файл не был создан.');
            }

            return $docxPath;
        }

        throw new Exception($this->unsupportedFormatMessage($template->format, $outputFormat));
    }

    private function isOutputFormatSupported(string $templateFormat, string $outputFormat): bool
    {
        return $templateFormat === 'docx' && $outputFormat === 'docx';
    }

    private function unsupportedFormatMessage(string $templateFormat, string $outputFormat): string
    {
        if ($templateFormat === 'docx' && $outputFormat === 'pdf') {
            return 'PDF-экспорт из DOCX временно недоступен: на сервере нет LibreOffice/полноценного DOCX→PDF-конвертера. DOCX можно скачать без потери структуры.';
        }

        if ($templateFormat === 'pdf') {
            return 'Генерация из PDF-шаблонов отключена: проект не содержит надёжного редактора существующего PDF/AcroForm, а пересборка из текста ломает внешний вид. Используйте DOCX-шаблон или PDF с поддержанной формой после добавления PDF-редактора.';
        }

        return 'Выбранная конвертация ' . strtoupper($templateFormat) . ' → ' . strtoupper($outputFormat) . ' недоступна.';
    }

    private function htmlToPdf(string $html, string $pdfPath): void
    {
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8']);
        $mpdf->WriteHTML($html);
        $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);
    }

    private function htmlToDocx(string $html, string $docxPath): string
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
        IOFactory::createWriter($phpWord, 'Word2007')->save($docxPath);

        return $docxPath;
    }

    private function phpWordToHtml(\PhpOffice\PhpWord\PhpWord $phpWord): string
    {
        $html = '<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,Arial,sans-serif;line-height:1.5;}</style></head><body>';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $html .= $this->phpWordElementToHtml($element);
            }
        }

        return $html . '</body></html>';
    }

    private function phpWordElementToHtml($element): string
    {
        if (method_exists($element, 'getText')) {
            return '<p>' . e($element->getText()) . '</p>';
        }

        if (method_exists($element, 'getElements')) {
            $html = '<p>';
            foreach ($element->getElements() as $child) {
                $html .= method_exists($child, 'getText') ? e($child->getText()) : '';
            }
            return $html . '</p>';
        }

        return '';
    }
}
