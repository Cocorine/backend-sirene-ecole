<?php

namespace App\DTO;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="SireneRequest",
 *     title="Sirene Request",
 *     description="Sirene details for site creation",
 *     required={"numero_serie"},
 *     @OA\Property(
 *         property="numero_serie",
 *         type="string",
 *         description="Serial number of the sirene",
 *         example="SN123456789"
 *     )
 * )
 */
/**
 * @OA\Schema(
 *     schema="SitePrincipalRequest",
 *     title="Main Site Request",
 *     description="Main site details for school registration",
 *     required={"adresse", "ville_id", "sirene"},
 *     @OA\Property(
 *         property="nom",
 *         type="string",
 *         nullable=true,
 *         description="Name of the main site",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="adresse",
 *         type="string",
 *         description="Address of the main site",
 *         maxLength=500
 *     ),
 *     @OA\Property(
 *         property="ville_id",
 *         type="string",
 *         format="uuid",
 *         description="ID of the city for the main site"
 *     ),
 *     @OA\Property(
 *         property="latitude",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         description="Latitude of the main site",
 *         minimum=-90,
 *         maximum=90
 *     ),
 *     @OA\Property(
 *         property="longitude",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         description="Longitude of the main site",
 *         minimum=-180,
 *         maximum=180
 *     ),
 *     @OA\Property(
 *         property="sirene",
 *         ref="#/components/schemas/SireneRequest"
 *     )
 * )
 */
/**
 * @OA\Schema(
 *     schema="SitesAnnexeRequest",
 *     title="Annex Site Request",
 *     description="Annex site details for school registration",
 *     required={"nom", "sirene"},
 *     @OA\Property(
 *         property="nom",
 *         type="string",
 *         description="Name of the annex site",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="adresse",
 *         type="string",
 *         nullable=true,
 *         description="Address of the annex site",
 *         maxLength=500
 *     ),
 *     @OA\Property(
 *         property="ville_id",
 *         type="string",
 *         format="uuid",
 *         nullable=true,
 *         description="ID of the city for the annex site"
 *     ),
 *     @OA\Property(
 *         property="latitude",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         description="Latitude of the annex site",
 *         minimum=-90,
 *         maximum=90
 *     ),
 *     @OA\Property(
 *         property="longitude",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         description="Longitude of the annex site",
 *         minimum=-180,
 *         maximum=180
 *     ),
 *     @OA\Property(
 *         property="sirene",
 *         ref="#/components/schemas/SireneRequest"
 *     )
 * )
 */