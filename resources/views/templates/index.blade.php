<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Шаблоны</title>
</head>
<body>

<h1>Список шаблонов</h1>

<a href="{{ route('templates.create') }}">
    Загрузить новый шаблон
</a>

<hr>

@foreach($templates as $template)

    <div style="margin-bottom:20px">

        <h3>{{ $template->name }}</h3>

        <p>
            Формат: {{ strtoupper($template->format) }}
        </p>

        <p>
            Переменных: {{ $template->variables->count() }}
        </p>

        <a href="{{ route('templates.show', $template->id) }}">
            Создать документ
        </a>

    </div>

@endforeach

</body>
</html>