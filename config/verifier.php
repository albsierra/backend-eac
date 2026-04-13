<?php

return [
    'base_url'       => env('VERIFIER_BASE_URL', 'https://verifier.vfds.example.org'),
    'client_id'      => env('VERIFIER_CLIENT_ID', 'backend-eac'),
    'algorithm'      => env('VERIFIER_JWT_ALGORITHM', 'RS256'),
    'role_claim'     => env('VERIFIER_ROLE_CLAIM', 'realm_access.roles'),
    'jwks_cache_ttl' => (int) env('VERIFIER_JWKS_CACHE_TTL', 3600),

    /*
     * URL del endpoint JWKS derivada automáticamente del realm.
     * Formato estándar OIDC. Puedes sobreescribirla si el despliegue
     * usa una ruta personalizada.
     */
    'jwks_uri' => env(
        'VERIFIER_JWKS_URI',
        env('VERIFIER_BASE_URL', 'https://verifier.vfds.example.org')
        . '/'
        . env('VERIFIER_JWKS', '.well-known/jwks')
    ),

    /*
     * URL del issuer esperado en el claim "iss" del JWT.
     * Debe coincidir exactamente con el valor que emite Verifier.
     */
    'issuer' => env(
        'VERIFIER_ISSUER',
        env('VERIFIER_BASE_URL', 'https://verifier.vfds.example.org')
    ),
];
