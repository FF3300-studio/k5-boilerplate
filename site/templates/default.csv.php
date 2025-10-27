<?php

use Kirby\Toolkit\Str;

function formatDataItaliano($data) {
    $fmt = new IntlDateFormatter('it_IT', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    $fmt->setPattern("d MMM Y");
    return strtoupper($fmt->format(strtotime($data)));
}

function serializePage($page, $site) {

    $max = $page->num_max()->isNotEmpty() ? (int)$page->num_max()->value() : '';

    $responses = $site->index(true)->filter(function ($p) use ($page) {
        return $p->intendedTemplate()->name() === 'formrequest'
            && Str::startsWith($p->id(), $page->id());
    });

    // Conta solo risposte già lette
    $responsesRead = $responses->filter(function ($p) {
        return $p->content()->get('read')->isFalse();
    });
    $count = $responsesRead->count();

    return [
        'url'   => $page->url(),
        'title' => $page->title()->value() ?? '',
        'descrizione' => $page->descrizione()->isNotEmpty() ? $page->descrizione()->value() : '',
        'dove' => $page->dove()->isNotEmpty()
            ? implode(' | ', $page->dove()->toPages()->map(fn($p) => $p->title())->values())
            : '',
        'immagine' => $page->immagine()->isNotEmpty() ? $page->immagine()->toFile()->url() : '',
        'thumb' => $page->thumbnail()->isNotEmpty() ? $page->thumbnail()->toFile()->url() : '',
        'child_category_selector' => $page->child_category_selector()->isNotEmpty() ? $page->child_category_selector()->value() : '',
        'appuntamenti' => $page->appuntamenti()->isNotEmpty()
            ? implode(' | ', array_map(function ($appuntamento) {
                return formatDataItaliano($appuntamento['giorno']) . ' ' .
                    substr($appuntamento['orario_inizio'], 0, 5) . '-' .
                    substr($appuntamento['orario_fine'], 0, 5);
            }, $page->appuntamenti()->yaml()))
            : '',
        'deadline' => $page->deadline()->isNotEmpty()
            ? formatDataItaliano($page->deadline()->value())
            : '',
        'correlati' => $page->correlati()->isNotEmpty()
            ? implode(' | ', $page->correlati()->toPages()->map(fn($p) => $p->title())->values())
            : '',
        'team' => $page->team()->isNotEmpty()
            ? implode(' | ', $page->team()->toStructure()->map(function($entry) {
                return $entry->persona()->value() . ' (' . $entry->ruolo()->value() . ')';
            })->values())
            : '',
        'locator' => $page->locator()->isNotEmpty()
            ? json_encode($page->locator()->yaml())
            : '',
        'zoom_mappa' => $page->zoom_mappa()->isNotEmpty() ? $page->zoom_mappa()->value() : '',
        'centro_mappa' => $page->centro_mappa()->isNotEmpty() ? $page->centro_mappa()->value() : '',
        'num_max' => $max,
        'risposte_form' => $count,
    ];
}

$rows = [];
$children = $page->children()->listed();

if ($children->isNotEmpty()) {
    foreach ($children as $child) {
        $rows[] = serializePage($child, $site);
    }
} else {
    $rows[] = serializePage($page, $site);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="pagina-' . $page->slug() . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, array_keys($rows[0]));

foreach ($rows as $row) {
    fputcsv($output, array_values($row));
}

fclose($output);
exit;