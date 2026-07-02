<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Шаблоны</title>
    <style>
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            min-height: 36px;
            font-size: 14px;
            line-height: 1;
            font-weight: bold;
            color: white;
            background: #6366f1;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.1s ease, background 0.2s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            vertical-align: middle;
        }

        .actions-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .actions-row form {
            display: flex;
            margin: 0;
        }

        .btn:hover {
            background: #4f46e5;
        }

        .btn:active {
            transform: scale(0.95);
            background: #4338ca;
        }

        .btn::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        .btn:active::after {
            width: 200px;
            height: 200px;
            opacity: 0;
        }

        .btn-delete {
            background: #ef4444;
        }

        .btn-delete:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>

<h1>Список шаблонов</h1>

<a href="{{ route('templates.create') }}" class="btn">
    Загрузить новый шаблон
</a>

<hr>

@foreach($templates as $template)
    <div style="margin-bottom:20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">

        <h3>{{ $template->name }}</h3>

        <p>
            Формат: {{ strtoupper($template->format) }}
        </p>

        <p>
            Переменных: {{ $template->variables->count() }}
        </p>

        <div class="actions-row">
            <a href="{{ route('templates.show', $template->id) }}" class="btn">Создать</a>
            <form action="{{ route('templates.destroy', $template->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-delete" onclick="return confirm('Вы уверены, что хотите удалить шаблон «{{ $template->name }}»?');">Удалить</button>
            </form>
        </div>

    </div>
@endforeach

</body>
</html>
