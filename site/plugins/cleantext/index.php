<?php

Kirby::plugin('my/cleantext', [
    'fieldMethods' => [
        'cleanText' => function ($field) {
            // Converti markdown in HTML usando il helper markdown()
            $html = markdown($field->value);

            // Rimuovi i tag HTML
            $text = strip_tags($html);

            // Decodifica entità HTML eventualmente rimaste
            $text = html_entity_decode($text);

            // Rimuovi eventuali spazi extra
            return trim($text);
        }
    ]
]);