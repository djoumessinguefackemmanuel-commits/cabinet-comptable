<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Entry;

class ApiEntryController extends Controller
{
    public function show(string $id): void
    {
        $entry = Entry::findWithLines((int) $id);
        if (!$entry) {
            $this->json(['error' => 'Écriture introuvable.'], 404);
            return;
        }

        $this->json(['entry' => $entry]);
    }
}
