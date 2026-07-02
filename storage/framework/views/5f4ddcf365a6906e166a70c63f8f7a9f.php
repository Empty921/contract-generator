<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Шаблонизатор договоров</title>

    <style>
        /* Кнопки с эффектом нажатия */
        .btn {
            display: inline-block;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: bold;
            color: white;
            background: #6366f1;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.1s ease, background 0.2s ease;
            position: relative;
            overflow: hidden;
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

        /* Удаление */
        .btn-delete {
            background: #ef4444;
        }
        .btn-delete:hover {
            background: #dc2626;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            background: white;
        }

        /* Левое меню */
        .sidebar {
            width: 10%;
            min-width: 180px;
            background: #ede4ff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .menu-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }

        .menu-item:hover {
            background: #f5f0ff;
        }

        /* Правая часть */
        .main {
            width: 90%;
            display: flex;
            flex-direction: column;
        }

        /* Верхняя панель */
        .topbar {
            height: 10vh;
            min-height: 80px;
            display: flex;
            align-items: center;
            padding-left: 30px;
            border-bottom: 1px solid #eee;
        }

        .topbar h1 {
            color: #8b5cf6;
            font-size: 26px;
        }

        /* Контент */
        .content {
            flex: 1;
            overflow: auto;
            padding: 30px;
        }

        /* Блок загрузки */
        .upload-card {
            max-width: 700px;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 25px;
            font-size: 18px;
        }

        .upload-card h2 {
            margin-bottom: 20px;
            font-size: 26px;
        }

        .upload-card input[type="file"] {
            margin-bottom: 20px;
        }

        /* Карточки шаблонов */
        .template-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .template-card h3 {
            color: #8b5cf6;
            margin-bottom: 10px;
        }

        .template-card p {
            margin-bottom: 8px;
        }

        .btn {
            display: inline-block;
            background: #8b5cf6;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;

            position: relative;
            overflow: hidden;

            transition:
                transform .12s ease,
                opacity .12s ease;
        }

        .btn:hover {
            opacity: .95;
        }

        .btn:active {
            transform: scale(.94);
        }

        .btn::after {
            content: "";

            position: absolute;

            left: 50%;
            top: 50%;

            width: 0;
            height: 0;

            border-radius: 50%;

            background: rgba(
                255,
                255,
                255,
                0.45
            );

            transform:
                translate(-50%, -50%);
        }

        .btn:active::after {
            animation: ripple .4s ease;
        }

        @keyframes ripple {

            0% {
                width: 0;
                height: 0;
                opacity: 1;
            }

            100% {
                width: 300px;
                height: 300px;
                opacity: 0;
            }
        }

        .btn:hover {
            opacity: 0.9;
        }

        .hidden {
            display: none;
        }

        .search {
            margin-bottom: 20px;
        }

        .search input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .error {
            background: #ffe5e5;
            color: #c0392b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        /* Стили для истории */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .history-table th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .history-table tr:hover {
            background: #f9fafb;
        }

        .history-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .history-card h3 {
            color: #8b5cf6;
            margin-bottom: 15px;
        }

        .history-card p {
            margin-bottom: 10px;
        }

        .variables-list {
            background: #f9fafb;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
        }

        .variables-list ul {
            margin-left: 20px;
        }

        .variables-list li {
            margin-bottom: 5px;
        }

        .btn-preview {
            background: #10b981;
        }
        .btn-preview:hover {
            background: #059669;
        }

        .preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .preview-modal-content {
            background: white;
            padding: 20px;
            border-radius: 12px;
            max-width: 90%;
            max-height: 90vh;
            overflow: auto;
            position: relative;
        }

        .preview-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            font-size: 24px;
        }
    </style>
</head>
<body>

    <!-- Левое меню -->
    <div class="sidebar">
        <div class="menu-item" onclick="showSection('templates')">Шаблоны</div>
        <div class="menu-item" onclick="showSection('upload')">Загрузка</div>
        <div class="menu-item" onclick="showSection('history')">История</div>
    </div>

    <!-- Основная часть -->
    <div class="main">

        <!-- Верхняя панель -->
        <div class="topbar">
            <h1 id="pageTitle">Шаблоны</h1>
        </div>

        <!-- Контент -->
        <div class="content">

            <!-- Раздел: Шаблоны -->
            <div id="templates-section">
                <div class="search">
                    <input type="text" id="templateSearch" placeholder="Поиск шаблона..." onkeyup="filterTemplates()">
                </div>

                <?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="template-card template-item">
                        <h3><?php echo e($template->name); ?></h3>
                        <p>Формат: <?php echo e(strtoupper($template->format)); ?></p>
                        <p>Переменных: <?php echo e($template->variables()->count()); ?></p>
                        <a href="<?php echo e(route('templates.show', $template)); ?>" class="btn">Заполнить</a>
                        <button class="btn btn-preview" onclick='openTemplatePreview(<?php echo json_encode($template->variables->pluck("variable_name")->values(), 15, 512) ?>, <?php echo json_encode(asset("storage/" . $template->file_path), 15, 512) ?>, <?php echo json_encode($template->format, 15, 512) ?>)'>Предпросмотр шаблона</button>
                        <form action="<?php echo e(route('templates.destroy', $template)); ?>" method="POST" style="display:inline-block;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-delete" onclick="return confirm('Удалить шаблон «<?php echo e($template->name); ?>»?');">Удалить</button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p>Шаблонов пока нет.</p>
                <?php endif; ?>
            </div>

            <!-- Раздел: Загрузка -->
            <div id="upload-section" class="hidden">
                <div class="upload-card">
                    <h2>Загрузка нового шаблона</h2>

                    <?php if($errors->any()): ?>
                        <div class="error">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><?php echo e($error); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('templates.store')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div style="margin-bottom:15px">
                            <label>Название шаблона</label><br>
                            <input type="text" name="name" required style="width:100%; padding:10px; margin-top:5px;">
                        </div>

                        <div style="margin-bottom:15px">
                            <label>DOCX или PDF файл</label><br>
                            <input type="file" name="template_file" accept=".docx,.pdf" required>
                        </div>

                        <button class="btn">Загрузить</button>
                    </form>
                </div>
            </div>

            <!-- Раздел: История -->
            <div id="history-section" class="hidden">
                <h2 style="color:#8b5cf6; margin-bottom:20px;">История сгенерированных документов</h2>
                
                <?php if($generatedDocuments->isEmpty()): ?>
                    <p>История пуста.</p>
                <?php else: ?>
                    <?php $__currentLoopData = $generatedDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="history-card">
                            <h3><?php echo e($document->template->name ?? 'Удаленный шаблон'); ?></h3>
                            <p><strong>Дата:</strong> <?php echo e($document->created_at->format('d.m.Y H:i')); ?></p>
                            <p><strong>Формат:</strong> <?php echo e(strtoupper($document->output_format)); ?></p>
                            <div class="variables-list">
                                <strong>Переменные:</strong>
                                <ul>
                                    <?php $variables = is_array($document->variables_json) ? $document->variables_json : (json_decode($document->variables_json, true) ?: []); ?>
                                    <?php $__currentLoopData = $variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><strong>$<?php echo e($name); ?>:</strong> <?php echo e($value); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                            <button class="btn btn-preview" onclick="openPreview('<?php echo e(route('documents.preview', $document)); ?>', '<?php echo e($document->output_format); ?>')">Предпросмотр</button>
                            <a href="<?php echo e(route('documents.download', $document)); ?>" class="btn" style="background:#3b82f6">Скачать</a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Модальное окно для предпросмотра -->
    <div id="previewModal" class="preview-modal" onclick="if(event.target === this) closePreview();">
        <div class="preview-modal-content">
            <span class="preview-modal-close" onclick="closePreview()">&times;</span>
            <div id="previewContent"></div>
        </div>
    </div>

    <script>
        function showSection(section) {
            document.getElementById('templates-section').classList.add('hidden');
            document.getElementById('upload-section').classList.add('hidden');
            document.getElementById('history-section').classList.add('hidden');

            if (section === 'templates') {
                document.getElementById('templates-section').classList.remove('hidden');
                document.getElementById('pageTitle').innerText = 'Шаблоны';
            }

            if (section === 'upload') {
                document.getElementById('upload-section').classList.remove('hidden');
                document.getElementById('pageTitle').innerText = 'Загрузка';
            }

            if (section === 'history') {
                document.getElementById('history-section').classList.remove('hidden');
                document.getElementById('pageTitle').innerText = 'История';
            }
        }

        function filterTemplates() {
            let input = document.getElementById('templateSearch').value.toLowerCase();
            let cards = document.querySelectorAll('.template-item');

            cards.forEach(card => {
                let text = card.innerText.toLowerCase();
                card.style.display = text.includes(input) ? 'block' : 'none';
            });
        }

        function openPreview(url, format) {
            const modal = document.getElementById('previewModal');
            const content = document.getElementById('previewContent');

            if (format === 'pdf') {
                content.innerHTML = '<iframe src="' + url + '" width="100%" height="600px" style="border:none;"></iframe>';
            } else {
                content.innerHTML = '<iframe src="' + url + '" width="100%" height="600px" style="border:none;"></iframe>';
            }

            modal.style.display = 'flex';
        }

        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
        }
        function openTemplatePreview(variables, url, format) {
            const modal = document.getElementById('previewModal');
            const content = document.getElementById('previewContent');
            const list = variables.length
                ? '<ul style="margin:15px 0 0 25px;">' + variables.map(name => '<li><code>${' + name + '}</code></li>').join('') + '</ul>'
                : '<p>Плейсхолдеры не найдены.</p>';

            content.innerHTML = '<h2 style="color:#8b5cf6; margin-bottom:10px;">Плейсхолдеры шаблона</h2>'
                + '<p><strong>Формат:</strong> ' + format.toUpperCase() + '</p>'
                + list
                + '<div style="margin-top:20px;"><a href="' + url + '" class="btn" target="_blank">Открыть исходный файл</a></div>';

            modal.style.display = 'flex';
        }
    </script>

</body>
</html>
<?php /**PATH D:\Practika\contract-generator\resources\views/dashboard.blade.php ENDPATH**/ ?>