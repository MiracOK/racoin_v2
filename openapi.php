<?php

namespace Documentation;

require __DIR__ . '/vendor/autoload.php';

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Racoin API",
    description: "Documentation de l'API de Racoin générée avec Swagger-PHP."
)]
#[OA\Server(
    url: "/api",
    description: "Serveur Principal"
)]
class OpenApiDocs
{
    #[OA\Get(
        path: "/annonces",
        summary: "Récupère la liste des annonces"
    )]
    #[OA\Response(
        response: 200,
        description: "Une liste d'annonces"
    )]
    public function annonces() {}

    #[OA\Get(
        path: "/annonce/{id}",
        summary: "Récupère une annonce spécifique par son ID"
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Détails de l'annonce"
    )]
    #[OA\Response(
        response: 404,
        description: "Annonce non trouvée"
    )]
    public function annonceById() {}

    #[OA\Get(
        path: "/categories",
        summary: "Récupère les catégories"
    )]
    #[OA\Response(
        response: 200,
        description: "Une liste de catégories"
    )]
    public function categories() {}
}