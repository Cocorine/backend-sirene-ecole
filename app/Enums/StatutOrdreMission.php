<?php

namespace App\Enums;

enum StatutOrdreMission: string
{
    case EN_ATTENTE = 'en_attente';
    case EN_COURS = 'en_cours';
    case TERMINE = 'termine';
    case CLOTURE = 'cloture';
}
