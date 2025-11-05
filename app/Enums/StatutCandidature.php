<?php

namespace App\Enums;

enum StatutCandidature: string
{
    case SOUMISE = 'soumise';
    case ACCEPTEE = 'acceptee';
    case REFUSEE = 'refusee';
    case RETIREE = 'retiree';
}
