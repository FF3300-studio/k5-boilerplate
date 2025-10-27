
<?php /** @var \Kirby\Cms\Block $block */ ?><?php
$classi = array("zero_uno", "zero_due", "zero_quattro", "zero_cinque", "zero_sei", "zero_sette");

// Assicurati che l'input sia trattato come UTF-8
$input = mb_convert_encoding($block->text(), 'UTF-8', 'auto');

// Pulisci eventuali <br> e applica l'output senza convertire i caratteri in entità HTML
$parole = explode(' ', str_replace("<br>", " ", $input));
?>

<h2 class="title" 
    style="justify-content: flex-start!important;
           max-width: 100%; 
           flex-grow: 1;
           min-width: fit-content;
           display:flex!important; 
           flex-direction: row; 
           flex-wrap: wrap;"
>
    <div style="justify-content: flex-start; 
                display: flex!important; 
                width: fit-content;
                flex-wrap: wrap;
                max-width: 90%;
                flex-grow: 1;"
    >
        <?php foreach($parole as $parola): ?>
        <?php $lettere = preg_split('//u', $parola, -1, PREG_SPLIT_NO_EMPTY); ?> 
            <div style="margin-right: 2.5%; 
                        display: flex!important; 
                        flex-wrap: nowrap; 
                        min-width: fit-content;
                        flex-direction: row;"
                 class="freak  
                    <?php if(isset($big) AND $big == true AND $page->parent() !== NULL): ?>
                        big
                    <?php endif; ?>
                "    
            >
            <?php foreach($lettere as $lettera): ?>
                <?php $classe = array_rand(array_flip($classi), 1);?>
                <span class="<?= $classe ?>">
                <?= htmlspecialchars(str_replace("_", " ", $lettera), ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</h2>

