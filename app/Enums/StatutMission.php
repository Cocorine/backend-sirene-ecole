<?php

namespace App\Enums;

enum StatutMission: string
{
    case EN_ATTENTE = 'en_attente';
    case ACCEPTEE = 'acceptee';
    case RETIREE = 'retiree';
    case REFUSEE = 'refusee';
    case TERMINEE = 'terminee';
}
