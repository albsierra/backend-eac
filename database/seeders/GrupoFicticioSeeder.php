<?php

namespace Database\Seeders;

use App\Models\EcosistemaLaboral;
use App\Models\Modulo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GrupoFicticioSeeder extends Seeder
{
    // ────────────────────────────────────────────────────────────────────────
    // Constantes del escenario
    // ────────────────────────────────────────────────────────────────────────

    private const MODULO_CODIGO      = 441;
    private const ECOSISTEMA_CODIGO  = 'GA-TC';
    private const ROLE_DOCENTE       = 1;
    private const ROLE_ESTUDIANTE    = 2;

    /**
     * Multiplicador de puntuación efectiva según el gradiente de autonomía.
     * Fórmula verificada con los datos reales:
     *   supervisado → 84,5 × 0,90 = 76,05 ✓
     *   autónomo    → 91  × 1,00 = 91,00 ✓
     */
    private const GRADIENTE_MULT = [
        'asistido'    => 0.70,
        'guiado'      => 0.80,
        'supervisado' => 0.90,
        'autonomo'    => 1.00,
    ];

    private $modulo,
        $ciclo_formativo,
        $familia_profesional,
        $ecosistema_laboral,
        $curriculo,
        $sc_ce_map;

    function initiate()
    {
        $this->modulo = Modulo::where('codigo', self::MODULO_CODIGO)->first();
        if (! $this->modulo) {
            $this->command->error("⛔ No se encontró el módulo con código " . self::MODULO_CODIGO . ". Asegúrate de que el módulo existe antes de ejecutar este seeder.");
            exit(1);
        }

        $this->ciclo_formativo = $this->modulo->cicloFormativo;
        $this->familia_profesional = $this->ciclo_formativo->familiaProfesional;
        $this->curriculo = $this->getCurriculoData($this->modulo->id);
        $this->sc_ce_map = $this->buildScCeMap($this->curriculo);
    }

    private function getCurriculoData($idModulo): array
    {
        $curriculo = [];

        $ras = $this->modulo->resultadosAprendizaje;

        foreach ($ras as $ra) {
            $criterios = [];

            foreach ($ra->criteriosEvaluacion()->orderBy('codigo')->get() as $ce) {
                $criterios[] = [
                    'id' => $ce->id,
                    'ce' => $ce->codigo,
                    'peso' => 1,
                    'descripcion' => $ce->descripcion,
                ];
            }

            $curriculo[$ra->codigo] = [
                'descripcion' => $ra->descripcion,
                'peso' => 1,
                'criterios' => $criterios,
            ];
        }

        return $curriculo;
    }

    private function buildScCeMap($curriculo): array
    {
        $sc_ce_map = [];

        $raKeys = array_keys($curriculo);
        $sc_ce_map[1] = [
            $curriculo[$raKeys[0]]['criterios'][0]['id'] => 30.00,
            $curriculo[$raKeys[0]]['criterios'][1]['id'] => 40.00,
            $curriculo[$raKeys[0]]['criterios'][2]['id'] => 30.00,
        ];
        $sc_ce_map[2] = [
            $curriculo[$raKeys[0]]['criterios'][2]['id'] => 30.00,
            $curriculo[$raKeys[1]]['criterios'][0]['id'] => 40.00,
        ];
        $sc_ce_map[3] = [
            $curriculo[$raKeys[0]]['criterios'][1]['id'] => 30.00,
            $curriculo[$raKeys[1]]['criterios'][0]['id'] => 40.00,
            $curriculo[$raKeys[1]]['criterios'][1]['id'] => 30.00,
        ];
        $sc_ce_map[4] = [
            $curriculo[$raKeys[1]]['criterios'][4]['id'] => 40.00,
            $curriculo[$raKeys[1]]['criterios'][5]['id'] => 30.00,
            $curriculo[$raKeys[1]]['criterios'][6]['id'] => 30.00,
        ];
        $sc_ce_map[5] = [
            $curriculo[$raKeys[2]]['criterios'][0]['id'] => 35.00,
            $curriculo[$raKeys[2]]['criterios'][1]['id'] => 35.00,
            $curriculo[$raKeys[2]]['criterios'][2]['id'] => 30.00,
        ];
        return $sc_ce_map;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Punto de entrada
    // ────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        if (! app()->isLocal()) {
            $this->command->error('⛔  Este seeder solo puede ejecutarse con APP_ENV=local.');
            return;
        }

        $this->command->info('🌱 Iniciando DatabaseSeeder…');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->initiate();

        $this->truncateTables();
        $this->seedRoles();
        $this->seedEcosistemaLaboral();

        [$teacher, $students] = $this->seedUsers();
        $this->seedUserRoles($teacher, $students);
        $this->seedMatriculas($students);

        $scs = $this->seedSituacionesCompetencia();
        $this->seedNodosRequisito();
        $this->seedScPrecedencia();
        $this->seedScCriteriosEvaluacion();

        $this->seedStudentProgress($students, $scs);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('✅ Seeder completado correctamente.');
    }

    // ────────────────────────────────────────────────────────────────────────
    // Truncado
    // ────────────────────────────────────────────────────────────────────────

    private function truncateTables(): void
    {
        /*
         * Orden: primero las tablas "hoja" para evitar conflictos incluso con
         * FK checks desactivados, aunque en este contexto no es estrictamente
         * necesario.
         * No se truncan: resultados_aprendizaje ni criterios_evaluacion, que
         * contienen los 460 y 3 685 registros reales del currículo oficial y
         * se asume que ya existen en la base de datos.
         */
        $tables = [
            'huellas_talento',
            'perfil_situacion',
            'perfiles_habilitacion',
            'sc_precedencia',
            'sc_criterios_evaluacion',
            'nodos_requisito',
            'situaciones_competencia',
            'user_roles',
            'matriculas',
            'users',
            'ecosistemas_laborales',
            'roles',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        $this->command->line('  Tablas truncadas.');
    }

    // ────────────────────────────────────────────────────────────────────────
    // Datos de referencia
    // ────────────────────────────────────────────────────────────────────────

    private function seedRoles(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'docente',    'description' => 'Docente del ecosistema', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'estudiante', 'description' => 'Estudiante matriculado', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedEcosistemaLaboral(): void
    {
        $this->ecosistema_laboral = EcosistemaLaboral::create([
            'modulo_id'   => $this->modulo->id,
            'nombre'      => $this->modulo->nombre,
            'codigo'      => self::ECOSISTEMA_CODIGO,
            'descripcion' => null,
            'activo'      => true,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Usuarios
    // ────────────────────────────────────────────────────────────────────────

    /** @return array{0: array, 1: array[]} */
    private function seedUsers(): array
    {
        $password = Hash::make('password');
        $now      = now();

        $teacher = [
            'id'                => 1,
            'name'              => 'Profesora Ejemplo',
            'email'             => 'docente@backend-eac.test',
            'email_verified_at' => $now,
            'password'          => $password,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        DB::table('users')->insert($teacher);

        $students = [];
        for ($i = 1; $i <= 20; $i++) {
            $pad        = str_pad($i, 2, '0', STR_PAD_LEFT);
            $students[] = [
                'id'                => $i + 1,   // IDs 2 – 21
                'name'              => "Estudiante {$pad}",
                'email'             => "estudiante{$pad}@backend-eac.test",
                'email_verified_at' => $now,
                'password'          => $password,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        DB::table('users')->insert($students);

        $this->command->line('  Usuarios: 1 docente + 20 estudiantes.');
        return [$teacher, $students];
    }

    private function seedUserRoles(array $teacher, array $students): void
    {
        $rows = [[
            'user_id'               => $teacher['id'],
            'role_id'               => self::ROLE_DOCENTE,
            'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]];

        foreach ($students as $s) {
            $rows[] = [
                'user_id'               => $s['id'],
                'role_id'               => self::ROLE_ESTUDIANTE,
                'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
                'created_at'            => now(),
                'updated_at'            => now(),
            ];
        }

        DB::table('user_roles')->insert($rows);
    }

    private function seedMatriculas(array $students): void
    {
        $rows = [];
        foreach ($students as $s) {
            $rows[] = [
                'estudiante_id' => $s['id'],
                'modulo_id'     => $this->modulo->id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        DB::table('matriculas')->insert($rows);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Situaciones de competencia
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Crea 5 SC que forman el siguiente DAG acíclico:
     *
     *   SC-01 ──► SC-02 ──► SC-05
     *     │                   ▲
     *     ├──► SC-03           │
     *     └──► SC-04 ──────────┘
     *
     * Dependencias:
     *   SC-02 requiere SC-01
     *   SC-03 requiere SC-01
     *   SC-04 requiere SC-01
     *   SC-05 requiere SC-02 y SC-04
     *
     * @return array[]
     */
    private function seedSituacionesCompetencia(): array
    {
        // Actualizar los campos `titulo` y `descripcion` a la temática del módulo para mayor realismo.
        // Los criterios de evaluación que se pueden tomar de referencia están pegados al final de este archivo.

        $scs = [
            [
                'id'                    => 1,
                'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
                'codigo'                => 'SC-01',
                'titulo'                => 'Clasificar los elementos patrimoniales de una empresa en masas patrimoniales',
                'descripcion'           => 'El estudiante analiza el conjunto de bienes, derechos y obligaciones de una empresa dada, los identifica como elementos patrimoniales y los agrupa correctamente en las masas patrimoniales del activo, el pasivo exigible y el patrimonio neto, relacionando cada masa con la fase del ciclo económico que le corresponde.',
                'umbral_maestria'       => 80.00,
                'nivel_complejidad'     => 2,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'id'                    => 2,
                'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
                'codigo'                => 'SC-02',
                'titulo'                => 'Aplicar la metodología contable por partida doble a un ciclo contable completo',
                'descripcion'           => 'El estudiante registra una secuencia de hechos económicos utilizando el método de partida doble, aplica correctamente los criterios de cargo y abono, elabora el balance de comprobación para detectar posibles errores y realiza los asientos de cierre y apertura del ejercicio.',
                'umbral_maestria'       => 80.00,
                'nivel_complejidad'     => 2,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'id'                    => 3,
                'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
                'codigo'                => 'SC-03',
                'titulo'                => 'Interpretar y aplicar la estructura del Plan General de Contabilidad PYME',
                'descripcion'           => 'El estudiante identifica las partes del PGC-PYME, distingue las secciones obligatorias de las voluntarias, describe los principios contables del marco conceptual y codifica un conjunto de elementos patrimoniales conforme al sistema de codificación del plan, justificando la cuenta asignada a cada elemento.',
                'umbral_maestria'       => 75.00,
                'nivel_complejidad'     => 3,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'id'                    => 4,
                'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
                'codigo'                => 'SC-04',
                'titulo'                => 'Contabilizar los hechos económicos básicos de un ejercicio económico',
                'descripcion'           => 'El estudiante identifica las cuentas patrimoniales y de gestión que intervienen en las operaciones habituales de una empresa, las codifica según el PGC-PYME, determina qué cuentas se cargan y cuáles se abonan en cada operación y realiza todos los asientos del ejercicio garantizando la seguridad y confidencialidad de la información.',
                'umbral_maestria'       => 80.00,
                'nivel_complejidad'     => 2,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'id'                    => 5,
                'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
                'codigo'                => 'SC-05',
                'titulo'                => 'Gestionar el plan de cuentas y los asientos en una aplicación de contabilidad',
                'descripcion'           => 'El estudiante da de alta y de baja cuentas y subcuentas en una aplicación informática contable, introduce asientos predefinidos y manuales respetando los procedimientos establecidos, resuelve incidencias recurriendo a los recursos de ayuda del programa y realiza la copia de seguridad de cuentas, saldos y movimientos siguiendo el plan de custodia establecido.',
                'umbral_maestria'       => 80.00,
                'nivel_complejidad'     => 3,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
        ];

        DB::table('situaciones_competencia')->insert($scs);

        $this->command->line('  5 situaciones de competencia creadas (DAG acíclico).');
        return $scs;
    }

    private function seedNodosRequisito(): void
    {
        DB::table('nodos_requisito')->insert([
            // SC-01 – nivel entrada, sin SC previas
            ['situacion_competencia_id' => 1, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer las fases del ciclo económico de la actividad empresarial y distinguir los conceptos de inversión, financiación, gasto, pago, ingreso y cobro.', 'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 1, 'tipo' => 'habilidad',    'descripcion' => 'Identificar y clasificar un conjunto de elementos patrimoniales agrupándolos en activo, pasivo exigible y patrimonio neto a partir de la información económica de una empresa.', 'orden' => 2, 'created_at' => null, 'updated_at' => null],
            // SC-02 – requiere SC-01
            ['situacion_competencia_id' => 2, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer el concepto de cuenta contable y las características del método de registro por partida doble, incluyendo los criterios de cargo y abono.', 'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 2, 'tipo' => 'habilidad',    'descripcion' => 'Elaborar un balance de comprobación a partir de un conjunto de asientos y detectar errores u omisiones en las anotaciones de las cuentas.', 'orden' => 2, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 2, 'tipo' => 'habilidad',    'descripcion' => 'Redactar los asientos de cierre y apertura de un ejercicio económico distinguiendo las cuentas de ingresos y gastos que intervienen en el cálculo del resultado contable.', 'orden' => 3, 'created_at' => null, 'updated_at' => null],
            // SC-03 – requiere SC-01
            ['situacion_competencia_id' => 3, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer las distintas partes del PGC-PYME, identificar cuáles son de aplicación obligatoria y describir los principios contables recogidos en su marco conceptual.', 'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 3, 'tipo' => 'conocimiento', 'descripcion' => 'Comprender el sistema de codificación del PGC-PYME y su función para asociar y desglosar la información contable por grupos, subgrupos y cuentas.', 'orden' => 2, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 3, 'tipo' => 'habilidad',    'descripcion' => 'Codificar un conjunto de elementos patrimoniales conforme al cuadro de cuentas del PGC-PYME e identificar las cuentas anuales que el plan establece.', 'orden' => 3, 'created_at' => null, 'updated_at' => null],
            // SC-04 – requiere SC-02 y SC-03
            ['situacion_competencia_id' => 4, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer las cuentas patrimoniales y de gestión que intervienen en las operaciones básicas de compraventa, cobros, pagos y periodificaciones según el PGC-PYME.', 'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 4, 'tipo' => 'habilidad',    'descripcion' => 'Determinar qué cuentas se cargan y cuáles se abonan en cada hecho contable y registrar los asientos correspondientes a todas las operaciones de un ejercicio económico básico.', 'orden' => 2, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 4, 'tipo' => 'habilidad',    'descripcion' => 'Aplicar los principios de responsabilidad, seguridad y confidencialidad en el tratamiento de la información contable durante el registro de operaciones.', 'orden' => 3, 'created_at' => null, 'updated_at' => null],
            // SC-05 – requiere SC-03 y SC-04
            ['situacion_competencia_id' => 5, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer el funcionamiento general de una aplicación informática contable: gestión del plan de cuentas, introducción de asientos predefinidos y recursos de ayuda disponibles.', 'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 5, 'tipo' => 'habilidad',    'descripcion' => 'Dar de alta y de baja cuentas, subcuentas y conceptos codificados en la aplicación siguiendo los procedimientos establecidos e introducir asientos manuales y predefinidos.', 'orden' => 2, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 5, 'tipo' => 'habilidad',    'descripcion' => 'Realizar y gestionar copias de seguridad de cuentas, saldos y movimientos conforme al plan de custodia establecido, eligiendo el soporte y el momento adecuados.', 'orden' => 3, 'created_at' => null, 'updated_at' => null],
        ]);
    }

    private function seedScPrecedencia(): void
    {
        // Grafo dirigido acíclico – ver diagrama en seedSituacionesCompetencia()
        DB::table('sc_precedencia')->insert([
            ['sc_id' => 2, 'sc_requisito_id' => 1],
            ['sc_id' => 3, 'sc_requisito_id' => 1],
            ['sc_id' => 4, 'sc_requisito_id' => 1],
            ['sc_id' => 5, 'sc_requisito_id' => 2],
            ['sc_id' => 5, 'sc_requisito_id' => 4],
        ]);
    }

    private function seedScCriteriosEvaluacion(): void
    {
        $rows = [];
        foreach ($this->sc_ce_map as $scId => $ceMap) {
            foreach ($ceMap as $ceId => $peso) {
                $rows[] = [
                    'situacion_competencia_id' => $scId,
                    'criterio_evaluacion_id'   => $ceId,
                    'peso_en_sc'               => $peso,
                ];
            }
        }
        DB::table('sc_criterios_evaluacion')->insert($rows);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Progreso de los 20 estudiantes
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Distribución realista del progreso del grupo-clase:
     *
     *  Grupo A (4 alumnos) – recién incorporados, sin SC superadas
     *  Grupo B (5 alumnos) – han superado SC-01
     *  Grupo C (4 alumnos) – han superado SC-01 y SC-02
     *  Grupo D (4 alumnos) – han superado SC-01, SC-02, SC-03 y SC-04
     *  Grupo E (3 alumnos) – han superado las 5 SC
     */
    private function seedStudentProgress(array $students, array $scs): void
    {
        $groups = [
            'A' => ['students' => array_slice($students,  0, 4), 'conquered' => []],
            'B' => ['students' => array_slice($students,  4, 5), 'conquered' => [1]],
            'C' => ['students' => array_slice($students,  9, 4), 'conquered' => [1, 2]],
            'D' => ['students' => array_slice($students, 13, 4), 'conquered' => [1, 2, 3, 4]],
            'E' => ['students' => array_slice($students, 17, 3), 'conquered' => [1, 2, 3, 4, 5]],
        ];

        foreach ($groups as $groupKey => $group) {
            foreach ($group['students'] as $student) {
                $perfilId = $this->insertPerfilHabilitacion($student, $group['conquered']);
                $this->insertPerfilSituacion($perfilId, $student, $group['conquered']);
                if (! empty($group['conquered'])) {
                    $this->insertHuellaTalento($student, $group['conquered'], $scs);
                }
            }
            $this->command->line(sprintf(
                '  Grupo %s: %d estudiantes – %d SC superadas.',
                $groupKey,
                count($group['students']),
                count($group['conquered'])
            ));
        }
    }

    private function insertPerfilHabilitacion(array $student, array $conqueredIds): int
    {
        $calificacion = 0.00;
        if (! empty($conqueredIds)) {
            $sum = 0.0;
            foreach ($conqueredIds as $scId) {
                $sum += $this->scoreForSc($student['id'], $scId);
            }
            $calificacion = round($sum / count($conqueredIds) / 10, 2);
        }

        return DB::table('perfiles_habilitacion')->insertGetId([
            'estudiante_id'         => $student['id'],
            'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
            'calificacion_actual'   => $calificacion,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    private function insertPerfilSituacion(int $perfilId, array $student, array $conqueredIds): void
    {
        if (empty($conqueredIds)) {
            return;
        }

        $rows = [];
        foreach ($conqueredIds as $scId) {
            $score    = $this->scoreForSc($student['id'], $scId);
            $intentos = $this->intentosForSc($student['id'], $scId);
            $rows[]   = [
                'perfil_habilitacion_id'   => $perfilId,
                'situacion_competencia_id' => $scId,
                'gradiente_autonomia'      => $this->gradienteFromScore($score),
                'puntuacion_conquista'     => $score,
                'intentos'                 => $intentos,
                'fecha_conquista'          => Carbon::now()->subDays(rand(1, 45))->subHours(rand(0, 23)),
            ];
        }

        DB::table('perfil_situacion')->insert($rows);
    }

    private function insertHuellaTalento(array $student, array $conqueredIds, array $scs): void
    {
        $scsByid     = array_column($scs, null, 'id');
        $now         = Carbon::now();
        $ngsiLdId    = sprintf(
            'urn:ngsi-ld:PerfilHabilitacion:estudiante-%d-ecosistema-%d',
            $student['id'],
            $this->ecosistema_laboral->id
        );

        // ── Situaciones conquistadas ──────────────────────────────────────
        $situacionesConquistadas = [];
        $sumScore = 0.0;
        foreach ($conqueredIds as $scId) {
            $sc       = $scsByid[$scId];
            $score    = $this->scoreForSc($student['id'], $scId);
            $grad     = $this->gradienteFromScore($score);
            $efectiva = round($score * self::GRADIENTE_MULT[$grad], 2);
            $sumScore += $score;
            $situacionesConquistadas[] = [
                'codigo'               => $sc['codigo'],
                'titulo'               => $sc['titulo'],
                'gradiente_autonomia'  => $grad,
                'puntuacion_conquista' => $score,
                'puntuacion_efectiva'  => $efectiva,
                'intentos'             => $this->intentosForSc($student['id'], $scId),
                'fecha_conquista'      => Carbon::now()->subDays(rand(1, 45))->format('Y-m-d H:i:s'),
            ];
        }
        $calificacion = round($sumScore / count($conqueredIds) / 10, 2);

        // ── Desglose curricular ───────────────────────────────────────────
        $desgloseEfectivas = $this->buildCeEffectiveScores($student['id'], $conqueredIds);
        $desgloseCurricular = [];

        foreach ($this->curriculo as $raCode => $ra) {
            $critRows    = [];
            $sumRa       = 0.0;
            $totalWeight = 0.0;

            foreach ($ra['criterios'] as $ce) {
                $efectiva  = $desgloseEfectivas[$ce['id']] ?? 0.0;
                $critRows[] = [
                    'ce'          => $ce['ce'],
                    'descripcion' => $ce['descripcion'],
                    'peso'        => $ce['peso'],
                    'puntuacion'  => $efectiva,
                    'cubierto'    => $efectiva > 0,
                ];
                $sumRa       += $efectiva * $ce['peso'];
                if ($efectiva > 0) {
                    $totalWeight += $ce['peso'];
                }
            }

            $desgloseCurricular[] = [
                'ra'          => $raCode,
                'descripcion' => $ra['descripcion'],
                'peso'        => $ra['peso'],
                'puntuacion'  => round($totalWeight > 0 ? $sumRa / $totalWeight : 0.0, 2),
                'criterios'   => $critRows,
            ];
        }

        // ── Payload NGSI-LD ───────────────────────────────────────────────
        $payload = [
            'ngsi_ld_id'               => $ngsiLdId,
            '@context'                 => 'https://vfds.example.org/ngsi-ld/eac-context.jsonld',
            'modulo'                   => [
                'codigo'              => $this->modulo->codigo,
                'nombre'              => $this->modulo->nombre,
                'ciclo'               => $this->modulo->ciclo,
                'familia_profesional' => $this->modulo->familia_profesional,
            ],
            'ecosistema'               => [
                'id'     => $this->ecosistema_laboral->id,
                'codigo' => $this->ecosistema_laboral->codigo,
                'nombre' => $this->ecosistema_laboral->nombre,
            ],
            'calificacion'             => $calificacion,
            'situaciones_conquistadas' => $situacionesConquistadas,
            'desglose_curricular'      => $desgloseCurricular,
            'generada_en'              => $now->toIso8601String(),
            'version'                  => '1.0',
        ];

        DB::table('huellas_talento')->insert([
            'estudiante_id'         => $student['id'],
            'ecosistema_laboral_id' => $this->ecosistema_laboral->id,
            'payload'               => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ngsi_ld_id'            => $ngsiLdId,
            'generada_en'           => $now,
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helpers de cálculo
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Genera una puntuación determinista y variada para el par (estudiante, SC).
     * Cada estudiante tiene un rendimiento estable y propio gracias a la semilla
     * basada en sus IDs, con variación ±9 puntos respecto a la media de la SC.
     */
    private function scoreForSc(int $studentId, int $scId): float
    {
        $bases  = [1 => 78.0, 2 => 85.0, 3 => 72.0, 4 => 81.0, 5 => 88.0];
        $base   = $bases[$scId] ?? 80.0;
        $offset = (($studentId * 7 + $scId * 13) % 19) - 9;  // rango −9…+9
        return round(max(65.0, min(98.0, $base + $offset)), 2);
    }

    /** Número de intentos: 1, 2 o 3 según los IDs del par. */
    private function intentosForSc(int $studentId, int $scId): int
    {
        return (($studentId + $scId * 3) % 3) + 1;
    }

    /** Gradiente de autonomía a partir de la puntuación efectiva final. */
    private function gradienteFromScore(float $score): string
    {
        return match (true) {
            $score >= 90 => 'autonomo',
            $score >= 80 => 'supervisado',
            $score >= 70 => 'guiado',
            default      => 'asistido',
        };
    }

    /**
     * Para cada CE cubierto por al menos una SC conquistada, calcula la
     * puntuación efectiva mediante media ponderada normalizada:
     *
     *   puntuacion_efectiva(CE) =
     *     Σ (score_efectiva(SC) × peso_en_sc) / Σ peso_en_sc
     *
     * donde score_efectiva(SC) = puntuacion_conquista × mult(gradiente).
     *
     * @param  int   $studentId
     * @param  int[] $conqueredScIds
     * @return array<int, float>  [ce_id => puntuacion_efectiva]
     */
    private function buildCeEffectiveScores(int $studentId, array $conqueredScIds): array
    {
        // Acumuladores: [ce_id => ['weighted_sum' => float, 'total_weight' => float]]
        $acc = [];

        foreach ($conqueredScIds as $scId) {
            if (! isset($this->sc_ce_map[$scId])) {
                continue;
            }
            $score    = $this->scoreForSc($studentId, $scId);
            $grad     = $this->gradienteFromScore($score);
            $efectiva = $score * self::GRADIENTE_MULT[$grad];

            foreach ($this->sc_ce_map[$scId] as $ceId => $peso) {
                if (! isset($acc[$ceId])) {
                    $acc[$ceId] = ['weighted_sum' => 0.0, 'total_weight' => 0.0];
                }
                $acc[$ceId]['weighted_sum']  += $efectiva * $peso;
                $acc[$ceId]['total_weight']  += $peso;
            }
        }

        $result = [];
        foreach ($acc as $ceId => $data) {
            $result[$ceId] = $data['total_weight'] > 0
                ? round($data['weighted_sum'] / $data['total_weight'], 2)
                : 0.0;
        }
        return $result;
    }
}
