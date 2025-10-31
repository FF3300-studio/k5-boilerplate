

<?php
use Kirby\Toolkit\Str;

$responses = $page->index(true)->filter(function ($p) use ($page) {
    return $p->intendedTemplate()->name() === 'formrequest'
        && ($p->isDescendantOf($page) || Str::startsWith($p->id(), $page->id()));
});

echo "Risposte trovate: " . $responses->count();
?> 

<!-- <?php if ($responses->count()): ?>
    <ul>
        <?php foreach ($responses as $response): ?>
            <?php
            // Decodifica il campo JSON "formdata"
            $data = json_decode($response->formdata()->value(), true);
            ?>
            <li>
                <?= $data['name'] ?? 'Nome non disponibile' ?> 
                (<?= $data['email'] ?? 'Email non disponibile' ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?> -->

