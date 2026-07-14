<?php

namespace App\Modules\Formation\Enums;

enum FormationStatus: string
{
    case EnAttente = 'en_attente';
    case EnCours = 'en_cours';
    case Terminee = 'terminee';
    case Annulee = 'annulee';
}
