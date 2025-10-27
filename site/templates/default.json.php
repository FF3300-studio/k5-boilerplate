<?php

use Kirby\Toolkit\Str;

function formatDataItaliano($data) {
    $fmt = new IntlDateFormatter('it_IT', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    $fmt->setPattern("d MMM Y"); // Es. "3 mag 2025"
    return strtoupper($fmt->format(strtotime($data))); // "3 MAG 2025"
}

function serializePage($page, $site) {

    // Calcola il numero massimo di iscrizioni
    $max = $page->num_max()->isNotEmpty() ? (int)$page->num_max()->value() : null;

    // Conta le risposte al form
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
        'url' => $page->url(),
        'title' => $page->title()->value() ?? null,
        'num_max' => $max,
        'risposte_form' => $count,
        'descrizione' => $page->descrizione()->isNotEmpty() ? $page->descrizione()->value() : null,
        'dove' => $page->dove()->isNotEmpty()
            ? $page->dove()->toPages()->map(fn($p) => $p->title())->values()
            : null,
        'immagine' => $page->immagine()->isNotEmpty() ? $page->immagine()->toFile()->url() : null,
        'thumb' => $page->thumbnail()->isNotEmpty() ? $page->thumbnail()->toFile()->url() : null,
        'child_category_selector' => $page->child_category_selector()->isNotEmpty() ? $page->child_category_selector()->value() : null,
        'appuntamenti' => $page->appuntamenti()->isNotEmpty()
            ? array_map(function ($appuntamento) {
                return [
                    'giorno' => formatDataItaliano($appuntamento['giorno']),
                    'orario_inizio' => substr($appuntamento['orario_inizio'], 0, 5),
                    'orario_fine' => substr($appuntamento['orario_fine'], 0, 5),
                ];
            }, $page->appuntamenti()->yaml())
            : null,
        'deadline' => $page->deadline()->isNotEmpty()
            ? formatDataItaliano($page->deadline()->value())
            : null,
        'correlati' => $page->correlati()->isNotEmpty()
            ? $page->correlati()->toPages()->map(fn($p) => $p->title())->values()
            : null,
        'team' => $page->team()->isNotEmpty()
            ? $page->team()->toStructure()->map(function($entry) {
                return [
                    'persona' => $entry->persona()->value(),
                    'ruolo' => $entry->ruolo()->value(),
                ];
            })->values()
            : null,
        'locator' => $page->locator()->isNotEmpty() ? $page->locator()->yaml() : null,
        'zoom_mappa' => $page->zoom_mappa()->isNotEmpty() ? $page->zoom_mappa()->value() : null,
        'centro_mappa' => $page->centro_mappa()->isNotEmpty() ? $page->centro_mappa()->value() : null,

    ];
}

$children = $page->children()->listed();
$data = [];

if ($children->isNotEmpty()) {
    foreach ($children as $child) {
        $data[] = serializePage($child, $site);
    }
} else {
    $data[] = serializePage($page, $site);
}

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="pagina-' . $page->slug() . '.json"');
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);