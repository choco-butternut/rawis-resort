<?php
// defaults
$showImage = $showImage ?? true;
$showText  = $showText  ?? true;
?>

<div class="logo">

    <?php if ($showImage): ?>
        <img src="/rawis-resort/assets/logo.png" alt="Rawis logo">   
    <?php endif; ?>

    <?php if ($showText): ?>
        <div class="logo-text">
            <h1>Rawis</h1>
            <h2>Resort Hotel</h2>
        </div>
    <?php endif; ?>

</div>
