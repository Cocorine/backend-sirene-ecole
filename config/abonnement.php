<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Abonnement Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for subscription management
    |
    */

    // Montant annuel de l'abonnement (en FCFA)
    'montant_annuel' => env('ABONNEMENT_MONTANT_ANNUEL', 50000),

    // Nombre de sirènes autorisées par défaut
    'sirenes_default' => env('ABONNEMENT_SIRENES_DEFAULT', 1),

    // Durée de l'abonnement en mois
    'duree_mois' => env('ABONNEMENT_DUREE_MOIS', 12),

    // Délai de grâce après expiration (en jours)
    'grace_period_days' => env('ABONNEMENT_GRACE_PERIOD', 7),

    // Tarifs par nombre de sirènes
    'tarifs' => [
        1 => 50000,
        2 => 90000,
        3 => 120000,
        4 => 150000,
        5 => 175000,
        // Au-delà de 5 sirènes: 25000 FCFA par sirène supplémentaire
    ],

    // Prix par sirène supplémentaire (au-delà de 5)
    'prix_sirene_supplementaire' => 25000,
];
