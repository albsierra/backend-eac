<?php
// app/Console/Commands/VerifierDiagnosticCommand.php

namespace App\Console\Commands;

use App\Services\VerifierJwksService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VerifierDiagnosticCommand extends Command
{
    protected $signature   = 'verifier:diagnostic {--token= : JWT a verificar (opcional)}';
    protected $description = 'Verifica la conectividad con Verifier y valida un JWT de prueba';

    public function handle(VerifierJwksService $jwksService): int
    {
        $this->info('─── Diagnóstico de Verifier ────────────────────────────────');

        // 1. Configuración
        $this->line('');
        $this->line('<fg=cyan>Configuración:</>');
        $this->table(['Parámetro', 'Valor'], [
            ['Base URL',    config('verifier.base_url')],
            ['Client ID',   config('verifier.client_id')],
            ['Algorithm',   config('verifier.algorithm')],
            ['Issuer',      config('verifier.issuer')],
            ['JWKS URI',    config('verifier.jwks_uri')],
            ['JWKS TTL',    config('verifier.jwks_cache_ttl') . ' s'],
        ]);

         // 2. Conectividad con OIDC Discovery
        $this->line('<fg=cyan>Conectividad OIDC Discovery:</>');
        $discoveryUrl = config('verifier.jwks_uri');

        try {
            $response = Http::timeout(5)->get($discoveryUrl);

            if ($response->successful()) {
                $this->line("  ✅ <fg=green>OK</> — {$discoveryUrl}");
            } else {
                $this->error("  ❌ HTTP {$response->status()} — {$discoveryUrl}");
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error("  ❌ Error de conexión: " . $e->getMessage());
            $this->line("     ¿Está el servidor Verifier accesible desde este host?");
            return self::FAILURE;
        }

        // 3. Descarga de claves JWKS
        $this->line('');
        $this->line('<fg=cyan>Claves JWKS:</>');

        try {
            $keys = $jwksService->refreshKeys();
            $this->line("  ✅ <fg=green>" . count($keys) . " clave(s) cargada(s)</>");
            foreach (array_keys($keys) as $kid) {
                $this->line("     kid: {$kid}");
            }
        } catch (\Throwable $e) {
            $this->error("  ❌ No se pudieron cargar las claves JWKS: " . $e->getMessage());
            return self::FAILURE;
        }

        // 4. Verificación de un token concreto (opcional)
        $token = $this->option('token');

        if ($token) {
            $this->line('');
            $this->line('<fg=cyan>Verificación del token proporcionado:</>');

            try {
                $algorithm = config('verifier.algorithm');

                $keySet    = $jwksService->getKeys();

                $payload = JWT::decode($token, $keySet);

                $this->line("  ✅ <fg=green>Firma válida</>");
                $this->table(['Claim', 'Valor'], [
                    ['iss',   $payload->iss   ?? '—'],
                    ['sub',   $payload->sub   ?? '—'],
                    ['aud',   is_array($payload->aud ?? '') ? implode(', ', $payload->aud) : ($payload->aud ?? '—')],
                    ['email', $payload->verifiableCredential?->credentialSubject?->email ?? '—'],
                    ['exp',   $payload->exp   ? date('Y-m-d H:i:s', $payload->exp) . ' UTC' : '—'],
                    ['iat',   $payload->iat   ? date('Y-m-d H:i:s', $payload->iat) . ' UTC' : '—'],
                    ['vc_type', $payload->verifiableCredential?->type ?? '—'],
                    ['roles',   implode(', ', array_merge(...array_map(
                        fn ($r) => (array) ($r->names ?? []),
                        $payload->verifiableCredential?->credentialSubject?->roles ?? []
                    )))],
                ]);

                // Comprobar si el issuer y audience son los esperados
                if (($payload->iss ?? '') !== config('verifier.issuer')) {
                    $this->warn("  ⚠️  El issuer del token no coincide con el configurado.");
                    $this->warn("     Esperado: " . config('verifier.issuer'));
                    $this->warn("     Recibido: " . ($payload->iss ?? 'ausente'));
                }

                if (config('verifier.client_id')) {
                    $aud = is_array($payload->aud ?? '') ? $payload->aud : [$payload->aud ?? ''];
                    if (! in_array(config('verifier.client_id'), $aud)) {
                        $this->warn("  ⚠️  El audience del token no incluye el client_id configurado.");
                        $this->warn("     Esperado en audience: " . config('verifier.client_id'));
                        $this->warn("     Audience actual: " . implode(', ', $aud));
                    }
                }
            } catch (\Firebase\JWT\ExpiredException) {
                $this->warn("  ⚠️  El token ha expirado (la firma era válida).");
            } catch (\Throwable $e) {
                $this->error("  ❌ Token inválido: " . $e->getMessage());
            }
        }

        $this->line('');
        $this->info('Diagnóstico completado.');

        return self::SUCCESS;
    }
}
