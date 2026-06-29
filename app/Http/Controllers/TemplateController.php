<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\TemplateProcessor;

class TemplateController extends Controller
{
    public function create()
    {
        return view('templates.create');
    }
    public function dashboard()
    {
        $templates = Template::with('variables')
            ->latest()
            ->get();

        return view('dashboard', compact('templates'));
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
            'name' => 'required',
            'template_file' => 'required|file|mimes:docx,pdf'
        ]);

        $path = $request->file('template_file')->store('templates');

        $extension = strtolower(
            $request->file('template_file')->getClientOriginalExtension()
        );

        $template = Template::create([
            'name' => $request->name,
            'file_path' => $path,
            'format' => $extension
        ]);

        try {

            $fullPath = Storage::path($path);

            $text = '';

            if ($extension === 'docx') {

                $zip = new \ZipArchive();

                if ($zip->open($fullPath) === true) {

                    $xml = $zip->getFromName('word/document.xml');

                    $zip->close();

                    $text = strip_tags($xml);
                }

            } elseif ($extension === 'pdf') {

                $parser = new Parser();

                $pdf = $parser->parseFile($fullPath);

                $text = $pdf->getText();
            }

            if (trim($text) === '') {

                $template->delete();

                return back()->withErrors([
                    'template_file' => 'Не удалось прочитать содержимое файла.'
                ]);
            }

            preg_match_all('/\$\{(.*?)\}/', $text, $matches);

            $variables = array_unique($matches[1]);

            if (count($variables) === 0) {

                $template->delete();

                return back()->withErrors([
                    'template_file' => 'В шаблоне не найдено ни одной переменной вида {{name}}.'
                ]);
            }

            foreach ($variables as $variable) {

                $variable = trim($variable);

                if ($variable === '') {

                    $template->delete();

                    return back()->withErrors([
                        'template_file' => 'Обнаружена пустая переменная {{}}.'
                    ]);
                }

                TemplateVariable::create([
                    'template_id' => $template->id,
                    'variable_name' => $variable
                ]);
            }

        } catch (\Exception $e) {

            $template->delete();

            return back()->withErrors([
                'template_file' => 'Ошибка обработки файла: ' . $e->getMessage()
            ]);
        }

        return redirect()->route('templates.index')
            ->with('success', 'Шаблон успешно загружен');
    }
    public function generate(Request $request, $id)
    {
        $template = Template::with('variables')->findOrFail($id);

        if ($template->format !== 'docx') {

            return back()->withErrors([
                'error' => 'Генерация пока поддерживается только для DOCX.'
            ]);
        }

        $sourcePath = Storage::path($template->file_path);

        $processor = new TemplateProcessor($sourcePath);

        foreach ($template->variables as $variable) {

            $value = $request->input($variable->variable_name);

            $processor->setValue(
                $variable->variable_name,
                $value
            );
        }

        $fileName = 'generated_' . time() . '.docx';

        $savePath = storage_path('app/' . $fileName);

        $processor->saveAs($savePath);

        return response()->download($savePath)->deleteFileAfterSend(true);
    }
}