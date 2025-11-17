<?php
// calcola dati specifici del form per questa thumb (o pagina)
$formData = $formData($formPage ?? $page); 

// Calcolo percentuale
$percent = 0;
if ($formData['max'] && $formData['max'] > 0) {
    $raw = ($formData['count'] / $formData['max']) * 100;
    $percent = $formData['count'] > 0 ? max(5, min(100, $raw)) : 0;
}
?>

<?php if ($formData['max']): ?>
    <br>
    <p style="min-width: 100%!important; display: flex; flex-direction: row; justify-content: space-between; margin-bottom: 5px;">
        <span style="min-width: auto!important; font-size: 14px;"><?= $formData['count'] ?> iscrizioni</span> 
        <?php if ($formData['available'] !== null): ?>
        <span style="min-width: auto!important; font-size: 14px;"><?= $formData['available'] ?> posti disponibili</span>
        <?php endif; ?>
    </p>

    <svg width="100%" height="10" style="display: block;">
        <!-- sfondo -->
        <rect x="0" y="0" width="100%" height="10" fill="#eee" rx="4" ry="4" />

        <!-- barra avanzamento -->
        <rect x="0" y="0" width="<?= $formData['percent'] ?>%" height="10" fill="#B6D71D" rx="4" ry="4" />

        <!-- tacche -->
        <?php if ($formData['count'] > 0 && $formData['max'] > 0): ?>
            <?php for ($i = 1; $i <= $formData['count']; $i++): ?>
                <?php $pos = ($i / $formData['max']) * 100; ?>
                <line x1="<?= $pos ?>%" y1="0" x2="<?= $pos ?>%" y2="20" stroke="#ffffff88" stroke-width="1" />
            <?php endfor; ?>
        <?php endif; ?>
    </svg>

    <?php if ($formData['available'] === 0): ?>
        <p style="color: red; font-weight: bold; margin-top: 5px;">⚠️ Posti esauriti</p>
    <?php endif; ?>
<?php endif; ?>
