<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'Pengaduan Sarpras') ?> - STIKOM Tunas Bangsa</title>
<meta name="description" content="Sistem Pengaduan Fasilitas Sarana Prasarana STIKOM Tunas Bangsa">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="auth-page">
<?= $this->renderSection('content') ?>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
