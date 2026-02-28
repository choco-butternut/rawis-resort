<?php
// defaults
$showImage = $showImage ?? true;
$showText  = $showText  ?? true;
?>

<div class="logo">

    <?php if ($showImage): 
        $base = defined('BASE_URL') ? BASE_URL : '';
        $logoSrc = ($base !== '' ? $base . '/assets/logo.png' : '/assets/logo.png');
    ?>
        <img src="<?= htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Rawis logo">
    <?php endif; ?>

    <?php if ($showText): ?>
        <div class="logo-text">
            <h1>Rawis</h1>
            <h2>Resort Hotel</h2>
        </div>
    <?php endif; ?>

</div>
