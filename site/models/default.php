<?php

use Kirby\Cms\Page;
use Kirby\Cms\Structure;

class DefaultPage extends Page
{
    /**
     * Ritorna SEMPRE una Structure (mai NULL) con le categorie del parent.
     * Se non c'è parent o il field è vuoto/mancante, torna una Structure vuota.
     *
     * Se sul parent il field ha un altro nome, cambia $fieldName.
     */
    public function categoriesOptions(): Structure
    {
        $parent = $this->parent();
        if (!$parent) {
            return new Structure([]);
        }

        // Nome del campo structure sul parent
        $fieldName = 'parent_category_manager';

        $field = $parent->content()->get($fieldName);
        if (!$field || $field->isEmpty()) {
            return new Structure([]);
        }

        try {
            return $field->toStructure();
        } catch (\Throwable $e) {
            return new Structure([]);
        }
    }
}
