<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($template->name); ?></title>

    <style>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f8f8fb;
            padding: 40px;
        }

        .card {
            max-width: 900px;
            margin: auto;
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        h1 {
            color: #8b5cf6;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            background: #8b5cf6;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            opacity: .9;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #8b5cf6;
            font-weight: bold;
        }

        .error {
            background: #ffe5e5;
            color: #c0392b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .preview-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="card">

    <a href="<?php echo e(route('dashboard')); ?>" class="back">
        ← Назад
    </a>

    <h1><?php echo e($template->name); ?></h1>

    <p class="subtitle">
        Заполните данные для генерации документа
    </p>

    <?php if($errors->any()): ?>
        <div class="error">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('templates.generate', $template->id)); ?>">
        <?php echo csrf_field(); ?>

        <?php $__currentLoopData = $template->variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="form-group">
                <label>
                    <?php echo e($variable->variable_name); ?>

                </label>
                <input
                    type="text"
                    name="<?php echo e($variable->variable_name); ?>"
                    value="<?php echo e(old($variable->variable_name)); ?>"
                    required
                >
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="form-group">
            <label>Формат результата</label>
            <select name="output_format">
                <option value="docx" <?php echo e(old('output_format', 'docx') == 'docx' ? 'selected' : ''); ?>>DOCX</option>
                <option value="pdf" <?php echo e(old('output_format') == 'pdf' ? 'selected' : ''); ?>>PDF</option>
            </select>
        </div>

        <button type="submit">
            Скачать документ
        </button>
    </form>

    <div class="preview-box">
        <p><strong>Тип шаблона:</strong> <?php echo e(strtoupper($template->format)); ?></p>
        <p><strong>Переменных:</strong> <?php echo e(count($template->variables)); ?></p>
        <p><small>Пример заполнения: <code>$<?php echo e($template->variables->first()->variable_name ?? '...'); ?></code></small></p>
    </div>

</div>

</body>
</html><?php /**PATH D:\Practika\contract-generator\resources\views/templates/show.blade.php ENDPATH**/ ?>