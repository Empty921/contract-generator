<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>{{ $template->name }}</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    background:#f8f8fb;
    padding:40px;
}

.card{
    max-width:900px;
    margin:auto;
    background:white;
    border-radius:16px;
    padding:30px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

h1{
    color:#8b5cf6;
    margin-bottom:10px;
}

.subtitle{
    color:#666;
    margin-bottom:30px;
}

.form-group{
    margin-bottom:20px;
}

label{
    display:block;
    margin-bottom:8px;
    font-weight:bold;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:8px;
}

button{
    background:#8b5cf6;
    color:white;
    border:none;
    padding:12px 25px;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
}

button:hover{
    opacity:.9;
}

.back{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
    color:#8b5cf6;
    font-weight:bold;
}

.error{
    background:#ffe5e5;
    color:#c0392b;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}

</style>
</head>
<body>

<div class="card">

    <a href="{{ route('dashboard') }}" class="back">
        ← Назад
    </a>

    <h1>{{ $template->name }}</h1>

    <p class="subtitle">
        Заполните данные для генерации документа
    </p>

    @if($errors->any())

        <div class="error">

            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach

        </div>

    @endif

    <form method="POST"
          action="{{ route('templates.generate', $template->id) }}">

        @csrf

        @foreach($template->variables as $variable)

            <div class="form-group">

                <label>
                    {{ $variable->variable_name }}
                </label>

                <input
                    type="text"
                    name="{{ $variable->variable_name }}"
                    required
                >

            </div>

        @endforeach

        <button type="submit">
            Скачать документ
        </button>

    </form>

</div>

</body>
</html>