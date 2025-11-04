<?php

namespace App\Traits;

use App\Enums\PrioritePanne;
use App\Enums\StatutPanne;
use App\Models\Panne;
use Illuminate\Support\Str;

trait HasPannes
{
    public function declarerPanne(string $description, string $priorite = 'moyenne'): Panne
    {
        return Panne::create([
            'sirene_id' => $this->id,
            'ecole_id' => $this->ecole_id,
            'site_id' => $this->site_id,
            'numero_panne' => $this->generateNumeroPanne(),
            'description' => $description,
            'priorite' => $priorite,
            'statut' => StatutPanne::DECLAREE,
            'date_signalement' => now(),
            'date_declaration' => now(),
        ]);
    }

    public function getPannesEnCours()
    {
        return $this->pannes()
            ->whereIn('statut', [
                StatutPanne::DECLAREE,
                StatutPanne::VALIDEE,
                StatutPanne::ASSIGNEE,
                StatutPanne::EN_COURS
            ])
            ->get();
    }

    public function hasPanneEnCours(): bool
    {
        return $this->pannes()
            ->whereIn('statut', [
                StatutPanne::DECLAREE,
                StatutPanne::VALIDEE,
                StatutPanne::ASSIGNEE,
                StatutPanne::EN_COURS
            ])
            ->exists();
    }

    private function generateNumeroPanne(): string
    {
        do {
            $numero = 'PAN-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Panne::where('numero_panne', $numero)->exists());

        return $numero;
    }
}
