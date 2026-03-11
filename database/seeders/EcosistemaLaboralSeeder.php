<?php

namespace Database\Seeders;

use App\Models\CicloFormativo;
use App\Models\CriterioEvaluacion;
use App\Models\EcosistemaLaboral;
use App\Models\FamiliaProfesional;
use App\Models\Matricula;
use App\Models\Modulo;
use App\Models\NodoRequisito;
use App\Models\PerfilHabilitacion;
use App\Models\ResultadoAprendizaje;
use App\Models\Role;
use App\Models\SituacionCompetencia;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EcosistemaLaboralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints(); // Deshabilitar temporalmente las restricciones de clave foránea
        // 1. Jerarquía curricular
        $familia = FamiliaProfesional::where([
            'nombre'  => 'Comercio y Marketing',
            'codigo'  => 'COM',
        ])->first();

        $ciclo = CicloFormativo::where([
            'familia_profesional_id' => $familia->id,
            'nombre'  => 'Servicios Comerciales',
        ])->first();

        $modulo = Modulo::where([
            'ciclo_formativo_id' => $ciclo->id,
            'nombre'             => 'Técnicas básicas de merchandising',
        ])->first();


        DB::table('huellas_talento')->truncate(); // Limpiar huellas anteriores para evitar duplicados
        EcosistemaLaboral::truncate(); // Limpiar ecosistemas anteriores para evitar duplicados
        $ecosistema = EcosistemaLaboral::create([
            'modulo_id' => $modulo->id,
            'nombre'    => $modulo->nombre,
            'codigo'    => 'AC-TBM',
            'activo'    => true,
        ]);

        // 2. Resultados de Aprendizaje y Criterios de Evaluación
        $ra1 = ResultadoAprendizaje::where([
            'modulo_id' => $modulo->id,
            'codigo'      => 'RA1',
        ])->first();
            $ce1a = CriterioEvaluacion::where([
                'resultado_aprendizaje_id' => $ra1->id,
                'codigo'      => 'a',
            ])->first();
            $ce1b = CriterioEvaluacion::where([
                'resultado_aprendizaje_id' => $ra1->id,
                'codigo'      => 'b',
            ])->first();
            $ce1c = CriterioEvaluacion::where([
                'resultado_aprendizaje_id' => $ra1->id,
                'codigo'      => 'c',
            ])->first();

        $ra2 = ResultadoAprendizaje::where([
            'modulo_id' => $modulo->id,
            'codigo'      => 'RA2',
        ])->first();
            $ce2a = CriterioEvaluacion::where([
                'resultado_aprendizaje_id' => $ra2->id,
                'codigo'      => 'a',
            ])->first();
            $ce2b = CriterioEvaluacion::where([
                'resultado_aprendizaje_id' => $ra2->id,
                'codigo'      => 'b',
            ])->first();
            $ce2c = CriterioEvaluacion::where([
                'resultado_aprendizaje_id' => $ra2->id,
                'codigo'      => 'c',
            ])->first();

        // 3. Situaciones de Competencia
        SituacionCompetencia::truncate(); // Limpiar SC anteriores para evitar duplicados
        $sc01 = SituacionCompetencia::create([
            'ecosistema_laboral_id' => $ecosistema->id,
            'codigo'      => 'SC-01',
            'titulo'      => 'Diseñar la disposición de productos en un lineal',
            'descripcion' => 'El estudiante diseña y argumenta la disposición óptima de una categoría de productos en un lineal de 2m, aplicando los principios del visual merchandising y elaborando el planograma correspondiente.',
            'umbral_maestria'     => 80.00,
            'nivel_complejidad'   => 2,
        ]);

        $sc02 = SituacionCompetencia::create([
            'ecosistema_laboral_id' => $ecosistema->id,
            'codigo'      => 'SC-02',
            'titulo'      => 'Elaborar un planograma básico para un punto de venta',
            'descripcion' => 'El estudiante elabora el planograma completo de una sección de un establecimiento, justificando la ubicación de cada producto en función de su rotación y margen.',
            'umbral_maestria'     => 80.00,
            'nivel_complejidad'   => 2,
        ]);

        $sc03 = SituacionCompetencia::create([
            'ecosistema_laboral_id' => $ecosistema->id,
            'codigo'      => 'SC-03',
            'titulo'      => 'Analizar el rendimiento de una zona caliente/fría',
            'descripcion' => 'El estudiante analiza el rendimiento de un punto de venta real o simulado mediante indicadores de ventas e identifica propuestas de mejora para las zonas de bajo rendimiento.',
            'umbral_maestria'     => 75.00,
            'nivel_complejidad'   => 3,
        ]);

        // 4. Trazabilidad curricular: qué CE cubre cada SC
        DB::table('sc_criterios_evaluacion')->truncate(); // Limpiar trazabilidades anteriores para evitar duplicados
        $sc01->criteriosEvaluacion()->attach([
            $ce1a->id => ['peso_en_sc' => 30],
            $ce1b->id => ['peso_en_sc' => 40],
            $ce1c->id => ['peso_en_sc' => 30],
        ]);
        $sc02->criteriosEvaluacion()->attach([
            $ce1c->id => ['peso_en_sc' => 60],
            $ce2a->id => ['peso_en_sc' => 40],
        ]);
        $sc03->criteriosEvaluacion()->attach([
            $ce1b->id => ['peso_en_sc' => 30],
            $ce2a->id => ['peso_en_sc' => 40],
            $ce2b->id => ['peso_en_sc' => 30],
        ]);

        // 5. Nodos de Requisito
        NodoRequisito::truncate(); // Limpiar nodos anteriores para evitar duplicados
        NodoRequisito::insert([
            ['situacion_competencia_id' => $sc01->id, 'tipo' => 'conocimiento',
            'descripcion' => 'Conocer los principios del color en escaparatismo', 'orden' => 1],
            ['situacion_competencia_id' => $sc01->id, 'tipo' => 'habilidad',
            'descripcion' => 'Manejar software básico de diseño (Canva o similar)', 'orden' => 2],
            ['situacion_competencia_id' => $sc02->id, 'tipo' => 'conocimiento',
            'descripcion' => 'Conocer la estructura de un planograma', 'orden' => 1],
            ['situacion_competencia_id' => $sc02->id, 'tipo' => 'habilidad',
            'descripcion' => 'Manejar software de planogramas', 'orden' => 2],
            ['situacion_competencia_id' => $sc03->id, 'tipo' => 'conocimiento',
            'descripcion' => 'Conocer los indicadores de rendimiento comercial (KPIs)', 'orden' => 1],
            ['situacion_competencia_id' => $sc03->id, 'tipo' => 'habilidad',
            'descripcion' => 'Calcular el índice de rotación de stock', 'orden' => 2],
        ]);

        // 6. Grafo de Precedencia
        // SC-03 requiere haber conquistado SC-01 y SC-02
        DB::table('sc_precedencia')->truncate(); // Limpiar precedencias anteriores para evitar duplicados
        DB::table('sc_precedencia')->insert([
            ['sc_id' => $sc03->id, 'sc_requisito_id' => $sc01->id],
            ['sc_id' => $sc03->id, 'sc_requisito_id' => $sc02->id],
        ]);

        // 7. Usuarios de prueba
        User::truncate(); // Limpiar usuarios anteriores para evitar duplicados
        $docente = User::factory()->create([
            'name'  => 'Profesora Ejemplo',
            'email' => 'docente@backend-eac.test',
        ]);

        $estudiante = User::factory()->create([
            'name'  => 'Estudiante Ejemplo',
            'email' => 'estudiante@backend-eac.test',
        ]);

        Role::truncate(); // Limpiar roles anteriores para evitar duplicados
        $rolDocente    = Role::create(['name' => 'docente',    'description' => 'Docente del ecosistema']);
        $rolEstudiante = Role::create(['name' => 'estudiante', 'description' => 'Estudiante matriculado']);

        // Asignación de roles con contexto
        DB::table('user_roles')->truncate(); // Limpiar asignaciones anteriores para evitar duplicados
        DB::table('user_roles')->insert([
            ['user_id' => $docente->id,    'role_id' => $rolDocente->id,    'ecosistema_laboral_id' => $ecosistema->id],
            ['user_id' => $estudiante->id, 'role_id' => $rolEstudiante->id, 'ecosistema_laboral_id' => $ecosistema->id],
        ]);

        // Matrícula
        Matricula::truncate(); // Limpiar matrículas anteriores para evitar duplicados
        Matricula::create([
            'estudiante_id'        => $estudiante->id,
            'modulo_id' => $modulo->id,
        ]);

        // Perfil de Habilitación inicial (vacío)
        PerfilHabilitacion::truncate(); // Limpiar perfiles anteriores para evitar duplicados
        PerfilHabilitacion::create([
            'estudiante_id'        => $estudiante->id,
            'ecosistema_laboral_id' => $ecosistema->id,
            'calificacion_actual'  => 0.00,
        ]);

        Schema::enableForeignKeyConstraints(); // Habilitar nuevamente las restricciones de clave foránea
    }
}
