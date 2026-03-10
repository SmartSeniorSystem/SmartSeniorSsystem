<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>" id="mainBody">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Noto+Sans+Arabic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', 'Noto Sans Arabic', sans-serif; background-color: #f8fafc; }
        .english-text { display: <?= ($lang == 'en' ? 'block' : 'none') ?>; }
        .arabic-text { display: <?= ($lang == 'ar' ? 'block' : 'none') ?>; }
        .transition-all { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="text-slate-800">