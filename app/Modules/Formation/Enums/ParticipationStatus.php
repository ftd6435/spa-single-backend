<?php

namespace App\Modules\Formation\Enums;

enum ParticipationStatus: string
{
    case EnAttente = 'en_attente';
    case Validee = 'validee';
    case Abandonnee = 'abandonnee';
    case Terminee = 'terminee';
}
