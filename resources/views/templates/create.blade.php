<!DOCTYPE html>
<html>
<head>
    <title>Загрузка шаблона</title>
</head>
<body>

<h1>Загрузка шаблона договора</h1>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<form action="/templates" method="POST" enctype="multipart/form-data">
    @csrf

    <div>
        <label>Название шаблона:</label>
        <input type="text" name="name">
    </div>

    <br>

    <div>
        <label>DOCX файл:</label>
        <input type="file" name="template_file">
    </div>

    <br>

    <button type="submit">
        Загрузить
    </button>
</form>

</body>
</html>