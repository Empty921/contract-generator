<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Шаблонизатор договоров</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    height:100vh;
    display:flex;
    background:white;
}

/* Левое меню */

.sidebar{
    width:10%;
    min-width:180px;
    background:#ede4ff;
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:15px;
}

.menu-item{
    background:white;
    border-radius:10px;
    padding:15px;
    text-align:center;
    cursor:pointer;
    font-weight:bold;
    transition:.2s;
}

.menu-item:hover{
    background:#f5f0ff;
}

/* Правая часть */

.main{
    width:90%;
    display:flex;
    flex-direction:column;
}

/* Верхняя панель */

.topbar{
    height:10vh;
    min-height:80px;
    display:flex;
    align-items:center;
    padding-left:30px;
    border-bottom:1px solid #eee;
}

.topbar h1{
    color:#8b5cf6;
}

/* Контент */

.content{
    flex:1;
    overflow:auto;
    padding:30px;
}

/* Блок загрузки */

.upload-card{
    max-width:700px;
    border:1px solid #ddd;
    border-radius:12px;
    padding:25px;
}

.upload-card h2{
    margin-bottom:20px;
}

.upload-card input[type=file]{
    margin-bottom:20px;
}

/* Карточки шаблонов */

.template-card{
    border:1px solid #ddd;
    border-radius:12px;
    padding:20px;
    margin-bottom:15px;
}

.template-card h3{
    color:#8b5cf6;
    margin-bottom:10px;
}

.template-card p{
    margin-bottom:8px;
}

.btn{
    display:inline-block;
    background:#8b5cf6;
    color:white;
    padding:10px 15px;
    text-decoration:none;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

.btn:hover{
    opacity:.9;
}

.hidden{
    display:none;
}

.search{
    margin-bottom:20px;
}

.search input{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:8px;
}

</style>
</head>
<body>

<div class="sidebar">

    <div class="menu-item" onclick="showSection('templates')">
        Шаблоны
    </div>

    <div class="menu-item" onclick="showSection('upload')">
        Загрузка
    </div>

</div>

<div class="main">

    <div class="topbar">
        <h1 id="pageTitle">Шаблоны</h1>
    </div>

    <div class="content">

        <!-- ШАБЛОНЫ -->

        <div id="templates-section">

            <div class="search">
                <input
                    type="text"
                    id="templateSearch"
                    placeholder="Поиск шаблона..."
                    onkeyup="filterTemplates()"
                >
            </div>

            @forelse($templates as $template)

                <div class="template-card template-item">

                    <h3>{{ $template->name }}</h3>

                    <p>
                        Формат:
                        {{ strtoupper($template->format) }}
                    </p>

                    <p>
                        Переменных:
                        {{ $template->variables()->count() }}
                    </p>

                    <a
                        href="{{ route('templates.show',$template) }}"
                        class="btn"
                    >
                        Создать документ
                    </a>

                </div>

            @empty

                <p>Шаблонов пока нет.</p>

            @endforelse

        </div>

        <!-- ЗАГРУЗКА -->

        <div id="upload-section" class="hidden">

            <div class="upload-card">

                <h2>Загрузка нового шаблона</h2>

                <form
                    action="{{ route('templates.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf

                    <input
                        type="file"
                        name="template"
                        required
                    >

                    <br>

                    <button class="btn">
                        Загрузить
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<script>

function showSection(section)
{
    document.getElementById('templates-section')
        .classList.add('hidden');

    document.getElementById('upload-section')
        .classList.add('hidden');

    if(section === 'templates')
    {
        document.getElementById('templates-section')
            .classList.remove('hidden');

        document.getElementById('pageTitle')
            .innerText = 'Шаблоны';
    }

    if(section === 'upload')
    {
        document.getElementById('upload-section')
            .classList.remove('hidden');

        document.getElementById('pageTitle')
            .innerText = 'Загрузка';
    }
}

function filterTemplates()
{
    let input =
        document.getElementById('templateSearch')
            .value.toLowerCase();

    let cards =
        document.querySelectorAll('.template-item');

    cards.forEach(card => {

        let text =
            card.innerText.toLowerCase();

        card.style.display =
            text.includes(input)
                ? 'block'
                : 'none';
    });
}

</script>

</body>
</html>