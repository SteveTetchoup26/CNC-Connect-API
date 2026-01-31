<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Documentation API Laravel",
 *      description="Documentation de l'API avec Swagger",
 *      @OA\Contact(
 *          email="contact@example.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Serveur API"
 * )
 *
 *  * @OA\Tag(
 *     name="Authentification",
 *     description="Endpoints pour la gestion de l'authentification des utilisateurs"
 * )
 *
 */
abstract class Controller
{
    
}
