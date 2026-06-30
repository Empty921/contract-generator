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
        $templates = Template::with('variables')
            ->latest()
            ->get();

        return view('templates.index', compact('templates'));
    }

    public function show($id)
    {
        $template = Template::with('variables')
            ->findOrFail($id);

        return view('templates.show', compact('template'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'template_file' => 'required|file|mimes:docx,pdf'
        ]);

        $path = $request->file('template_file')->store('templates');

        $extension = strtolower(
            $request->file('template_file')->getClientOriginalExtension()
        );

        $template = Template::create([
            'name'      => $request->name,
            'file_path' => $path,
            'format'    => $extension
        ]);

        try {

            $fullPath = Storage::path($path);

            $text = '';

            if ($extension === 'docx') {

                $zip = new \ZipArchive();

                if ($zip->open($fullPath) !== true) {

                    throw new \Exception(
                        'Не удалось открыть DOCX файл.'
                    );
                }

                $xml = $zip->getFromName('word/document.xml');

                $zip->close();

                if (!$xml) {
                    throw new \Exception(
                        'Не удалось прочитать document.xml.'
                    );
                }

                $text = strip_tags($xml);
            }

            if ($extension === 'pdf') {

                $parser = new Parser();

                $pdf = $parser->parseFile($fullPath);

                $text = $pdf->getText();
            }

            if (trim($text) === '') {

                $template->delete();

                return back()->withErrors([
                    'template_file' =>
                        'Не удалось получить текст из документа.'
                ]);
            }

            preg_match_all(
                '/\$\{([^}]+)\}/',
                $text,
                $matches
            );

            $variables = [];

            if (isset($matches[1])) {

                foreach ($matches[1] as $item) {

                    $item = trim($item);

                    if ($item !== '') {
                        $variables[] = $item;
                    }
                }
            }

            $variables = array_unique($variables);

            if (count($variables) === 0) {

                $template->delete();

                return back()->withErrors([
                    'template_file' =>
                        'В документе не найдено переменных вида ${variable}.'
                ]);
            }

            foreach ($variables as $variable) {
                TemplateVariable::create([
                    'template_id'   => $template->id,
                    'variable_name' => $variable
                ]);
            }

        } catch (\Throwable $e) {

            $template->delete();

            return back()->withErrors([
                'template_file' =>
                    'Ошибка обработки файла: ' .
                    $e->getMessage()
            ]);
        }

        return redirect()
            ->route('templates.show', $template->id)
            ->with(
                'success',
                'Шаблон успешно загружен.'
            );
    }

    public function generate(Request $request, $id)
    {
        $template = Template::with('variables')
            ->findOrFail($id);

        if ($template->format !== 'docx') {

            return back()->withErrors([
                'error' =>
                    'Генерация поддерживается только для DOCX.'
            ]);
        }

        try {

            foreach ($template->variables as $variable) {

                $value = trim(
                    (string) $request->input(
                        $variable->variable_name
                    )
                );

                if ($value === '') {

                    return back()
                        ->withInput()
                        ->withErrors([
                            $variable->variable_name =>
                                'Поле "' .
                                $variable->variable_name .
                                '" не заполнено.'
                        ]);
                }
            }

            $sourcePath = Storage::path(
                $template->file_path
            );

            if (!file_exists($sourcePath)) {

                return back()->withErrors([
                    'error' =>
                        'Файл шаблона не найден.'
                ]);
            }

            $processor = new TemplateProcessor(
                $sourcePath
            );

            foreach ($template->variables as $variable) {

                $value = trim(
                    (string) $request->input(
                        $variable->variable_name
                    )
                );

                $processor->setValue(
                    $variable->variable_name,
                    htmlspecialchars(
                        $value,
                        ENT_QUOTES,
                        'UTF-8'
                    )
                );
            }

            $generatedDir = storage_path(
                'app/generated'
            );

            if (!file_exists($generatedDir)) {

                mkdir(
                    $generatedDir,
                    0777,
                    true
                );
            }

            $fileName =
                'generated_' .
                date('Ymd_His') .
                '_' .
                uniqid() .
                '.docx';

            $savePath =
                $generatedDir .
                DIRECTORY_SEPARATOR .
                $fileName;

            $processor->saveAs($savePath);

            if (!file_exists($savePath)) {

                throw new \Exception(
                    'Файл не был создан.'
                );
            }

            return response()
                ->download(
                    $savePath,
                    $fileName
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->withErrors([
                    'error' =>
                        'Ошибка генерации: ' .
                        $e->getMessage()
                ]);
        }
    }
    public function destroy($id)
    {
        $template = Template::findOrFail($id);

        TemplateVariable::where(
            'template_id',
            $template->id
        )->delete();

        if (
            $template->file_path &&
            Storage::exists($template->file_path)
        ) {
            Storage::delete($template->file_path);
        }

        $template->delete();

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Шаблон удалён.'
            );
    }
}