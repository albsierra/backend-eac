<?php
namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;
use App\Models\EcosistemaLaboral;
use App\Models\Matricula;
use App\Models\PerfilHabilitacion;
use App\Models\ResultadoAprendizaje;
use App\Models\CriterioEvaluacion;
use App\Models\Role;
use App\Models\SituacionCompetencia;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

class DocenteCalificacionControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function urlCalificacion(int $ecosistemaId, int $estudianteId): string
    {
        return "/api/v1/docente/ecosistemas/{$ecosistemaId}/calificacion/{$estudianteId}";
    }

    /**
     * Crea docente con rol asignado al ecosistema.
     */
    private function crearDocente(EcosistemaLaboral $ecosistema): User
    {
        $docente = User::factory()->create();
        $rol     = Role::firstOrCreate(['name' => 'docente']);

        DB::table('user_roles')->insert([
            'user_id'               => $docente->id,
            'role_id'               => $rol->id,
            'ecosistema_laboral_id' => $ecosistema->id,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        return $docente;
    }

    /**
     * Crea estudiante matriculado con perfil en el ecosistema.
     */
    private function crearEstudiante(EcosistemaLaboral $ecosistema): array
    {
        $estudiante = User::factory()->create();

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'modulo_id'     => $ecosistema->modulo_id,
        ]);

        $perfil = PerfilHabilitacion::create([
            'estudiante_id'         => $estudiante->id,
            'ecosistema_laboral_id' => $ecosistema->id,
            'calificacion_actual'   => 0.00,
        ]);

        return [$estudiante, $perfil];
    }

    // ─── Autenticación y autorización ────────────────────────────────────────

    public function test_calificacion_requires_authentication(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $estudiante = User::factory()->create();

        $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id))
             ->assertStatus(401);
    }

    public function test_calificacion_forbidden_for_user_without_docente_role(): void
    {
        $usuario    = User::factory()->create();
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $estudiante = User::factory()->create();

        Sanctum::actingAs($usuario);

        $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id))
             ->assertStatus(403);
    }

    public function test_calificacion_forbidden_for_docente_of_different_ecosistema(): void
    {
        $ecosistemaA = EcosistemaLaboral::factory()->create(['activo' => true]);
        $ecosistemaB = EcosistemaLaboral::factory()->create(['activo' => true]);

        // Docente solo tiene rol en ecosistema A
        $docente = $this->crearDocente($ecosistemaA);
        [$estudiante] = $this->crearEstudiante($ecosistemaB);

        Sanctum::actingAs($docente);

        // Intenta acceder al ecosistema B
        $this->getJson($this->urlCalificacion($ecosistemaB->id, $estudiante->id))
             ->assertStatus(403);
    }

    // ─── Respuesta 404 ───────────────────────────────────────────────────────

    public function test_calificacion_returns_404_when_student_has_no_profile(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $docente    = $this->crearDocente($ecosistema);
        $estudiante = User::factory()->create(); // sin perfil

        Sanctum::actingAs($docente);

        $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id))
             ->assertStatus(404);
    }

    // ─── Estructura de la respuesta ───────────────────────────────────────────

    public function test_calificacion_returns_correct_structure(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $docente    = $this->crearDocente($ecosistema);
        [$estudiante] = $this->crearEstudiante($ecosistema);

        Sanctum::actingAs($docente);

        $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id))
             ->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     'estudiante_id',
                     'ecosistema_id',
                     'calificacion_total',
                     'desglose_ra',
                 ],
                 'meta' => ['version', 'timestamp'],
             ]);
    }

    public function test_calificacion_total_is_zero_when_no_conquistas(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $docente    = $this->crearDocente($ecosistema);
        [$estudiante] = $this->crearEstudiante($ecosistema);

        Sanctum::actingAs($docente);

        $response = $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id));

        $response->assertStatus(200)
                 ->assertJson(fn (AssertableJson $json) =>
                     $json->where('data.calificacion_total', 0)
                          ->where('data.estudiante_id', $estudiante->id)
                          ->where('data.ecosistema_id', $ecosistema->id)
                          ->etc()
                 );
    }

    // ─── Lógica de calificación ───────────────────────────────────────────────

    public function test_calificacion_desglose_ra_contains_all_ra_of_modulo(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $docente    = $this->crearDocente($ecosistema);
        [$estudiante, $perfil] = $this->crearEstudiante($ecosistema);

        // Crear 2 RA en el módulo del ecosistema
        ResultadoAprendizaje::factory()->count(2)->create([
            'modulo_id' => $ecosistema->modulo_id,
        ]);

        Sanctum::actingAs($docente);

        $response = $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id));

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.desglose_ra'));
    }

    public function test_calificacion_desglose_ce_marked_as_not_covered_without_conquistas(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $docente    = $this->crearDocente($ecosistema);
        [$estudiante] = $this->crearEstudiante($ecosistema);

        $ra = ResultadoAprendizaje::factory()->create([
            'modulo_id'       => $ecosistema->modulo_id,
        ]);

        CriterioEvaluacion::factory()->count(2)->create([
            'resultado_aprendizaje_id' => $ra->id,
        ]);

        Sanctum::actingAs($docente);

        $response = $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id));

        $response->assertStatus(200);

        $criterios = $response->json('data.desglose_ra.0.criterios');
        $this->assertCount(2, $criterios);
        $this->assertFalse($criterios[0]['cubierto']);
        $this->assertFalse($criterios[1]['cubierto']);
        $this->assertEquals(0.0, $criterios[0]['puntuacion']);
    }

    public function test_calificacion_gradiente_autonomo_does_not_reduce_score(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $docente    = $this->crearDocente($ecosistema);
        [$estudiante, $perfil] = $this->crearEstudiante($ecosistema);

        $ra = ResultadoAprendizaje::factory()->create([
            'modulo_id'       => $ecosistema->modulo_id,
        ]);

        $ce = CriterioEvaluacion::factory()->create([
            'resultado_aprendizaje_id' => $ra->id,
        ]);

        $sc = SituacionCompetencia::factory()->create([
            'ecosistema_laboral_id' => $ecosistema->id,
            'umbral_maestria'       => 50.00,
        ]);

        $sc->criteriosEvaluacion()->attach($ce->id, ['peso_en_sc' => 100]);

        // Gradiente autónomo: factor 1.00 → puntuacion_efectiva = puntuacion_conquista
        $perfil->situacionesConquistadas()->attach($sc->id, [
            'gradiente_autonomia'  => 'autonomo',
            'puntuacion_conquista' => 80.0,
            'intentos'             => 1,
            'fecha_conquista'      => now(),
        ]);

        Sanctum::actingAs($docente);

        $response = $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id));

        $response->assertStatus(200);

        $ce0 = $response->json('data.desglose_ra.0.criterios.0');
        $this->assertTrue($ce0['cubierto']);
        $this->assertEquals(80.0, $ce0['puntuacion']); // sin reducción
    }

    public function test_calificacion_gradiente_supervisado_reduces_score(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $docente    = $this->crearDocente($ecosistema);
        [$estudiante, $perfil] = $this->crearEstudiante($ecosistema);

        $ra = ResultadoAprendizaje::factory()->create([
            'modulo_id'       => $ecosistema->modulo_id,
        ]);

        $ce = CriterioEvaluacion::factory()->create([
            'resultado_aprendizaje_id' => $ra->id,
        ]);

        $sc = SituacionCompetencia::factory()->create([
            'ecosistema_laboral_id' => $ecosistema->id,
            'umbral_maestria'       => 50.00,
        ]);

        $sc->criteriosEvaluacion()->attach($ce->id, ['peso_en_sc' => 100]);

        // Gradiente supervisado: factor 0.90 → puntuacion_efectiva = 100 × 0.90 = 90
        $perfil->situacionesConquistadas()->attach($sc->id, [
            'gradiente_autonomia'  => 'supervisado',
            'puntuacion_conquista' => 100.0,
            'intentos'             => 1,
            'fecha_conquista'      => now(),
        ]);

        Sanctum::actingAs($docente);

        $response = $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id));

        $response->assertStatus(200);

        $puntuacionCe = $response->json('data.desglose_ra.0.criterios.0.puntuacion');
        $this->assertEquals(90.0, $puntuacionCe); // 100 × 0.90
    }

    public function test_calificacion_ce_cubierto_by_multiple_scs_is_weighted(): void
    {
        $ecosistema = EcosistemaLaboral::factory()->create(['activo' => true]);
        $docente    = $this->crearDocente($ecosistema);
        [$estudiante, $perfil] = $this->crearEstudiante($ecosistema);

        $ra = ResultadoAprendizaje::factory()->create([
            'modulo_id'       => $ecosistema->modulo_id,
        ]);

        $ce = CriterioEvaluacion::factory()->create([
            'resultado_aprendizaje_id' => $ra->id,
        ]);

        // Dos SCs cubren el mismo CE con pesos distintos
        $sc1 = SituacionCompetencia::factory()->create([
            'ecosistema_laboral_id' => $ecosistema->id,
            'umbral_maestria'       => 50.00,
        ]);
        $sc2 = SituacionCompetencia::factory()->create([
            'ecosistema_laboral_id' => $ecosistema->id,
            'umbral_maestria'       => 50.00,
        ]);

        $sc1->criteriosEvaluacion()->attach($ce->id, ['peso_en_sc' => 40]);
        $sc2->criteriosEvaluacion()->attach($ce->id, ['peso_en_sc' => 60]);

        // SC1: autónomo, 60.0 → efectiva = 60.0
        // SC2: autónomo, 90.0 → efectiva = 90.0
        // CE ponderado = (60×40 + 90×60) / (40+60) = (2400+5400)/100 = 78.0
        $perfil->situacionesConquistadas()->attach($sc1->id, [
            'gradiente_autonomia'  => 'autonomo',
            'puntuacion_conquista' => 60.0,
            'intentos'             => 1,
            'fecha_conquista'      => now(),
        ]);
        $perfil->situacionesConquistadas()->attach($sc2->id, [
            'gradiente_autonomia'  => 'autonomo',
            'puntuacion_conquista' => 90.0,
            'intentos'             => 1,
            'fecha_conquista'      => now(),
        ]);

        Sanctum::actingAs($docente);

        $response = $this->getJson($this->urlCalificacion($ecosistema->id, $estudiante->id));

        $response->assertStatus(200);

        $puntuacionCe = $response->json('data.desglose_ra.0.criterios.0.puntuacion');
        $this->assertEquals(78.0, $puntuacionCe);
    }
}
