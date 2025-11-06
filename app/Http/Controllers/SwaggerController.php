<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Sirène Automatique API",
 *     version="1.0.0",
 *     description="API complète pour la gestion du système de sirènes d'écoles avec paiement CinetPay",
 *     @OA\Contact(
 *         email="contact@celeriteholding.com",
 *         name="Celerite Holding"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Serveur de développement local"
 * )
 *
 * @OA\Server(
 *     url="https://api.sirene-ecole.com",
 *     description="Serveur de production"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Utilisez le token Bearer reçu lors de la connexion"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints pour l'authentification (OTP, Login, Logout)"
 * )
 *
 * @OA\Tag(
 *     name="Écoles",
 *     description="Gestion des écoles et inscription"
 * )
 *
 * @OA\Tag(
 *     name="Abonnements",
 *     description="Gestion des abonnements des écoles"
 * )
 *
 * @OA\Tag(
 *     name="Paiements",
 *     description="Gestion des paiements et transactions"
 * )
 *
 * @OA\Tag(
 *     name="CinetPay",
 *     description="Intégration avec l'agrégateur de paiement CinetPay"
 * )
 *
 * @OA\Tag(
 *     name="Sirènes",
 *     description="Gestion des sirènes et affectations"
 * )
 *
 * @OA\Tag(
 *     name="Calendriers & Jours Fériés",
 *     description="Gestion des calendriers scolaires et jours fériés"
 * )
 *
 * @OA\Tag(
 *     name="Programmations",
 *     description="Gestion des programmations de sonneries"
 * )
 *
 * @OA\Tag(
 *     name="Pannes & Interventions",
 *     description="Gestion des pannes et interventions techniques"
 * )
 */
class SwaggerController extends Controller
{
    //
}
