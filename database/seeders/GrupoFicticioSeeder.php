<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GrupoFicticioSeeder extends Seeder
{
    // ────────────────────────────────────────────────────────────────────────
    // Constantes del escenario
    // ────────────────────────────────────────────────────────────────────────

    private const MODULO_ID          = 70;
    private const MODULO_CODIGO      = '3069';
    private const MODULO_NOMBRE      = 'Técnicas básicas de merchandising';
    private const CICLO_NOMBRE       = 'Servicios Comerciales';
    private const FAMILIA_NOMBRE     = 'COMERCIO Y MARKETING';
    private const ECOSISTEMA_ID      = 1;
    private const ECOSISTEMA_CODIGO  = 'AC-TBM';
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

    /**
     * Mapa SC → [criterio_evaluacion_id => peso_en_sc]
     * Basado en el rango de IDs real de los CEs del módulo 70:
     *   RA1 CEs a–h : 3601–3608
     *   RA2 CEs a–k : 3609–3619
     *   RA3 CEs a–i : 3620–3628
     *   RA4 CEs a–i : 3629–3637
     */
    private const SC_CE_MAP = [
        1 => [3601 => 30.00, 3602 => 40.00, 3603 => 30.00],
        2 => [3603 => 60.00, 3609 => 40.00],
        3 => [3602 => 30.00, 3609 => 40.00, 3610 => 30.00],
        4 => [3614 => 40.00, 3615 => 30.00, 3616 => 30.00],
        5 => [3620 => 35.00, 3621 => 35.00, 3622 => 30.00],
    ];

    /**
     * Estructura curricular del módulo 70 (4 RA × todos sus CE).
     * Los 'id' coinciden con los IDs reales de criterios_evaluacion.
     */
    private const CURRICULUM = [
        'RA1' => [
            'descripcion' => 'Monta elementos de animación del punto de venta y expositores de productos describiendo los criterios comerciales que es preciso utilizar.',
            'peso'        => 1,
            'criterios'   => [
                ['id' => 3601, 'ce' => 'a', 'peso' => 1, 'descripcion' => 'Se ha identificado la ubicación física de los distintos sectores del punto de venta.'],
                ['id' => 3602, 'ce' => 'b', 'peso' => 1, 'descripcion' => 'Se han identificado las zonas frías y calientes del punto de venta.'],
                ['id' => 3603, 'ce' => 'c', 'peso' => 1, 'descripcion' => 'Se han descrito los criterios comerciales de distribución de los productos y mobiliario en el punto de venta.'],
                ['id' => 3604, 'ce' => 'd', 'peso' => 1, 'descripcion' => 'Se han diferenciado los distintos tipos de mobiliario utilizados en el punto de venta y los elementos promocionales utilizados habitualmente.'],
                ['id' => 3605, 'ce' => 'e', 'peso' => 1, 'descripcion' => 'Se han descrito los pasos y procesos de elaboración y montaje.'],
                ['id' => 3606, 'ce' => 'f', 'peso' => 1, 'descripcion' => 'Se han montado expositores de productos y góndolas con fines comerciales.'],
                ['id' => 3607, 'ce' => 'g', 'peso' => 1, 'descripcion' => 'Se ha colocado cartelería y otros elementos de animación, siguiendo criterios de «merchandising» y de imagen.'],
                ['id' => 3608, 'ce' => 'h', 'peso' => 1, 'descripcion' => 'Se han seguido las instrucciones de montaje y uso del fabricante y las normas de seguridad y prevención de riesgos laborales.'],
            ],
        ],
        'RA2' => [
            'descripcion' => 'Dispone productos en lineales y expositores seleccionando la técnica básica de «merchandising» apropiada a las características del producto.',
            'peso'        => 1,
            'criterios'   => [
                ['id' => 3609, 'ce' => 'a', 'peso' => 1, 'descripcion' => 'Se han identificado los parámetros físicos y comerciales que determinan la colocación de productos en los distintos niveles, zonas del lineal y posición.'],
                ['id' => 3610, 'ce' => 'b', 'peso' => 1, 'descripcion' => 'Se ha descrito el proceso de traslado de los productos conduciendo transpalés o carretillas de mano, siguiendo las normas de seguridad.'],
                ['id' => 3611, 'ce' => 'c', 'peso' => 1, 'descripcion' => 'Se ha descrito la clasificación del surtido por grupos, secciones, categorías, familias y referencias.'],
                ['id' => 3612, 'ce' => 'd', 'peso' => 1, 'descripcion' => 'Se han descrito los efectos que producen en el consumidor los distintos modos de ubicación de los productos en el lineal.'],
                ['id' => 3613, 'ce' => 'e', 'peso' => 1, 'descripcion' => 'Se ha identificado el lugar y disposición de los productos a partir de un planograma, foto o gráfico del lineal y la etiqueta del producto.'],
                ['id' => 3614, 'ce' => 'f', 'peso' => 1, 'descripcion' => 'Se ha realizado inventario de las unidades del punto de venta, detectando huecos o roturas de «stocks».'],
                ['id' => 3615, 'ce' => 'g', 'peso' => 1, 'descripcion' => 'Se han utilizado equipos de lectura de códigos de barras (lectores ópticos) para la identificación y control de los productos.'],
                ['id' => 3616, 'ce' => 'h', 'peso' => 1, 'descripcion' => 'Se ha elaborado la información relativa al punto de venta utilizando aplicaciones informáticas a nivel usuario, procesador de texto y hoja de cálculo.'],
                ['id' => 3617, 'ce' => 'i', 'peso' => 1, 'descripcion' => 'Se han colocado productos en diferentes tipos de lineales y expositores siguiendo criterios de «merchandising».'],
                ['id' => 3618, 'ce' => 'j', 'peso' => 1, 'descripcion' => 'Se han limpiado y acondicionado lineales y estanterías para la correcta colocación de los productos.'],
                ['id' => 3619, 'ce' => 'k', 'peso' => 1, 'descripcion' => 'Se han aplicado las medidas específicas de manipulación e higiene de los distintos productos.'],
            ],
        ],
        'RA3' => [
            'descripcion' => 'Coloca etiquetas y dispositivos de seguridad valorando la relevancia del sistema de codificación «European Article Numbering Association» (EAN) en el control del punto de venta.',
            'peso'        => 1,
            'criterios'   => [
                ['id' => 3620, 'ce' => 'a', 'peso' => 1, 'descripcion' => 'Se han identificado distintos tipos de dispositivos de seguridad que se utilizan en el punto de venta.'],
                ['id' => 3621, 'ce' => 'b', 'peso' => 1, 'descripcion' => 'Se ha descrito el funcionamiento de dispositivos de seguridad en el punto de venta.'],
                ['id' => 3622, 'ce' => 'c', 'peso' => 1, 'descripcion' => 'Se han descrito los procesos de asignación de códigos a los distintos productos.'],
                ['id' => 3623, 'ce' => 'd', 'peso' => 1, 'descripcion' => 'Se han interpretado etiquetas normalizadas y códigos EAN 13.'],
                ['id' => 3624, 'ce' => 'e', 'peso' => 1, 'descripcion' => 'Se ha verificado la codificación de productos, identificando sus características, propiedades y localización.'],
                ['id' => 3625, 'ce' => 'f', 'peso' => 1, 'descripcion' => 'Se han utilizado aplicaciones informáticas en la elaboración de documentación para transmitir los errores detectados entre la etiqueta y el producto.'],
                ['id' => 3626, 'ce' => 'g', 'peso' => 1, 'descripcion' => 'Se han etiquetado productos manualmente y utilizando herramientas específicas de etiquetado y siguiendo criterios de «merchandising».'],
                ['id' => 3627, 'ce' => 'h', 'peso' => 1, 'descripcion' => 'Se han colocado dispositivos de seguridad utilizando los sistemas de protección pertinentes.'],
                ['id' => 3628, 'ce' => 'i', 'peso' => 1, 'descripcion' => 'Se ha valorado la relevancia de la codificación de los productos en el control del punto de venta.'],
            ],
        ],
        'RA4' => [
            'descripcion' => 'Empaqueta productos relacionando la técnica seleccionada con los criterios comerciales y de imagen perseguidos.',
            'peso'        => 1,
            'criterios'   => [
                ['id' => 3629, 'ce' => 'a', 'peso' => 1, 'descripcion' => 'Se han identificado diferentes técnicas de empaquetado de productos.'],
                ['id' => 3630, 'ce' => 'b', 'peso' => 1, 'descripcion' => 'Se ha analizado la simbología de formas, colores y texturas en la transmisión de la imagen de la empresa.'],
                ['id' => 3631, 'ce' => 'c', 'peso' => 1, 'descripcion' => 'Se han identificado elementos y materiales que se utilizan en el empaquetado y presentación comercial de productos.'],
                ['id' => 3632, 'ce' => 'd', 'peso' => 1, 'descripcion' => 'Se han seleccionado los materiales necesarios para el empaquetado en función de la técnica establecida y de la imagen de la empresa.'],
                ['id' => 3633, 'ce' => 'e', 'peso' => 1, 'descripcion' => 'Se ha acondicionado el producto para su empaquetado, colocando elementos protectores y retirando el precio y los dispositivos de seguridad.'],
                ['id' => 3634, 'ce' => 'f', 'peso' => 1, 'descripcion' => 'Se han empaquetado productos asegurando su consistencia y su presentación conforme a criterios comerciales.'],
                ['id' => 3635, 'ce' => 'g', 'peso' => 1, 'descripcion' => 'Se han aplicado las medidas de prevención de riesgos laborales relacionadas.'],
                ['id' => 3636, 'ce' => 'h', 'peso' => 1, 'descripcion' => 'Se han colocado motivos ornamentales de forma atractiva.'],
                ['id' => 3637, 'ce' => 'i', 'peso' => 1, 'descripcion' => 'Se han retirado los restos del material utilizado para asegurar el orden y limpieza del lugar de trabajo.'],
            ],
        ],
    ];

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

        $this->truncateTables();
        $this->seedRoles();
        $this->seedFamiliasProfesionales();
        $this->seedCiclosFormativos();
        $this->seedModulos();
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
            'modulos',
            'ciclos_formativos',
            'familias_profesionales',
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

    private function seedFamiliasProfesionales(): void
    {
        DB::table('familias_profesionales')->insert([
            ['id' => 1, 'nombre' => 'ADMINISTRACIÓN Y GESTIÓN',     'codigo' => 'ADM', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'COMERCIO Y MARKETING',         'codigo' => 'COM', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'INFORMÁTICA Y COMUNICACIONES', 'codigo' => 'IFC', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedCiclosFormativos(): void
    {
        DB::table('ciclos_formativos')->insert([
            // ── Informática y Comunicaciones (familia 3) ──────────────
            ['id' =>  1, 'familia_profesional_id' => 3, 'nombre' => 'Desarrollo de Aplicaciones Multiplataforma',     'codigo' => '12242002', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  2, 'familia_profesional_id' => 3, 'nombre' => 'Desarrollo de Aplicaciones Web',                 'codigo' => '12242003', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  3, 'familia_profesional_id' => 3, 'nombre' => 'Informática de oficina',                         'codigo' => '12342002', 'grado' => 'GB', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  4, 'familia_profesional_id' => 3, 'nombre' => 'Administración de Sistemas Informáticos en Red', 'codigo' => '12242001', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  5, 'familia_profesional_id' => 3, 'nombre' => 'Sistemas Microinformáticos y Redes',             'codigo' => '12142001', 'grado' => 'GM', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  6, 'familia_profesional_id' => 3, 'nombre' => 'Informática y Comunicaciones',                   'codigo' => '12342001', 'grado' => 'GB', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Administración y Gestión (familia 1) ──────────────────
            ['id' =>  7, 'familia_profesional_id' => 1, 'nombre' => 'Servicios Administrativos',                      'codigo' => '12342101', 'grado' => 'GB', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  8, 'familia_profesional_id' => 1, 'nombre' => 'Gestión Administrativa',                         'codigo' => '12142101', 'grado' => 'GM', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  9, 'familia_profesional_id' => 1, 'nombre' => 'Administración y Finanzas',                      'codigo' => '12242102', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'familia_profesional_id' => 1, 'nombre' => 'Asistencia a la dirección',                      'codigo' => '12242101', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Comercio y Marketing (familia 2) ──────────────────────
            ['id' => 11, 'familia_profesional_id' => 2, 'nombre' => 'Actividades comerciales',                        'codigo' => '12142201', 'grado' => 'GM', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'familia_profesional_id' => 2, 'nombre' => 'Servicios Comerciales',                          'codigo' => '12342201', 'grado' => 'GB', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'familia_profesional_id' => 2, 'nombre' => 'Comercialización de Productos Alimentarios',     'codigo' => '12142202', 'grado' => 'GM', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'familia_profesional_id' => 2, 'nombre' => 'Comercio Internacional',                         'codigo' => '12242201', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'familia_profesional_id' => 2, 'nombre' => 'Gestión de ventas y espacios comerciales',       'codigo' => '12242202', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'familia_profesional_id' => 2, 'nombre' => 'Marketing y publicidad',                         'codigo' => '12242203', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'familia_profesional_id' => 2, 'nombre' => 'Transporte y Logística',                         'codigo' => '12242204', 'grado' => 'GS', 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedModulos(): void
    {
        DB::table('modulos')->insert([
            // ── DAM – ciclo 1 ──────────────────────────────────────────────
            ['id' =>  1, 'ciclo_formativo_id' =>  1, 'nombre' => 'Sistemas informáticos',                                            'codigo' => '483',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  2, 'ciclo_formativo_id' =>  1, 'nombre' => 'Bases de Datos',                                                   'codigo' => '484',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  3, 'ciclo_formativo_id' =>  1, 'nombre' => 'Programación',                                                     'codigo' => '485',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  4, 'ciclo_formativo_id' =>  1, 'nombre' => 'Lenguajes de Marcas y Sistemas de Gestión de Información.',        'codigo' => '373',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  5, 'ciclo_formativo_id' =>  1, 'nombre' => 'Entornos de desarrollo',                                          'codigo' => '487',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 23, 'ciclo_formativo_id' =>  1, 'nombre' => 'Acceso a datos',                                                   'codigo' => '486',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 24, 'ciclo_formativo_id' =>  1, 'nombre' => 'Desarrollo de interfaces',                                        'codigo' => '488',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 25, 'ciclo_formativo_id' =>  1, 'nombre' => 'Programación multimedia y dispositivos móviles',                  'codigo' => '489',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 26, 'ciclo_formativo_id' =>  1, 'nombre' => 'Programación de servicios y procesos.',                           'codigo' => '490',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 27, 'ciclo_formativo_id' =>  1, 'nombre' => 'Sistemas de gestión empresarial',                                 'codigo' => '491',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── DAW – ciclo 2 ──────────────────────────────────────────────
            ['id' =>  6, 'ciclo_formativo_id' =>  2, 'nombre' => 'Desarrollo Web en entorno cliente.',                              'codigo' => '612',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  7, 'ciclo_formativo_id' =>  2, 'nombre' => 'Desarrollo Web en entorno servidor.',                             'codigo' => '613',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  8, 'ciclo_formativo_id' =>  2, 'nombre' => 'Despliegue de aplicaciones Web.',                                 'codigo' => '614',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' =>  9, 'ciclo_formativo_id' =>  2, 'nombre' => 'Diseño de interfaces web',                                        'codigo' => '615',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Informática de oficina GB – ciclo 3 ───────────────────────
            ['id' => 10, 'ciclo_formativo_id' =>  3, 'nombre' => 'Montaje y mantenimiento de sistemas y componentes informáticos',  'codigo' => '3029', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'ciclo_formativo_id' =>  3, 'nombre' => 'Operaciones auxiliares para la configuración y la explotación',  'codigo' => '3030', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'ciclo_formativo_id' =>  3, 'nombre' => 'Ofimática y archivo de documentos',                              'codigo' => '3031', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'ciclo_formativo_id' =>  3, 'nombre' => 'Instalación y mantenimiento de redes para transmisión de datos', 'codigo' => '3016', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── ASIR GS – ciclo 4 ─────────────────────────────────────────
            ['id' => 14, 'ciclo_formativo_id' =>  4, 'nombre' => 'Implantación de Sistemas Operativos',                             'codigo' => '369',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'ciclo_formativo_id' =>  4, 'nombre' => 'Planificación y Administración de Redes.',                        'codigo' => '370',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'ciclo_formativo_id' =>  4, 'nombre' => 'Fundamentos de Hardware.',                                        'codigo' => '371',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'ciclo_formativo_id' =>  4, 'nombre' => 'Gestión de Base de Datos',                                        'codigo' => '372',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'ciclo_formativo_id' =>  4, 'nombre' => 'Administración de Sistemas Operativos',                           'codigo' => '374',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 19, 'ciclo_formativo_id' =>  4, 'nombre' => 'Servicios de Red e Internet.',                                    'codigo' => '375',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 20, 'ciclo_formativo_id' =>  4, 'nombre' => 'Implantación de Aplicaciones Web.',                               'codigo' => '376',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 21, 'ciclo_formativo_id' =>  4, 'nombre' => 'Administración de Sistemas Gestores de Bases de Datos.',          'codigo' => '377',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 22, 'ciclo_formativo_id' =>  4, 'nombre' => 'Seguridad y Alta Disponibilidad.',                                'codigo' => '378',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── SMR GM – ciclo 5 ──────────────────────────────────────────
            ['id' => 28, 'ciclo_formativo_id' =>  5, 'nombre' => 'Montaje y mantenimiento de equipos',                              'codigo' => '221',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 29, 'ciclo_formativo_id' =>  5, 'nombre' => 'Sistemas operativos monopuesto',                                  'codigo' => '222',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 30, 'ciclo_formativo_id' =>  5, 'nombre' => 'Aplicaciones ofimáticas',                                        'codigo' => '223',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 31, 'ciclo_formativo_id' =>  5, 'nombre' => 'Redes locales',                                                   'codigo' => '225',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 32, 'ciclo_formativo_id' =>  5, 'nombre' => 'Sistemas operativos en red',                                      'codigo' => '224',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 33, 'ciclo_formativo_id' =>  5, 'nombre' => 'Seguridad informática',                                           'codigo' => '226',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 34, 'ciclo_formativo_id' =>  5, 'nombre' => 'Servicios en red',                                                'codigo' => '227',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 35, 'ciclo_formativo_id' =>  5, 'nombre' => 'Aplicaciones web',                                                'codigo' => '228',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Informática y Comunicaciones GB – ciclo 6 ─────────────────
            ['id' => 36, 'ciclo_formativo_id' =>  6, 'nombre' => 'Equipos eléctricos y electrónicos.',                              'codigo' => '3015', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Servicios Administrativos GB – ciclo 7 ────────────────────
            ['id' => 39, 'ciclo_formativo_id' =>  7, 'nombre' => 'Técnicas administrativas básicas',                                'codigo' => '3003', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 40, 'ciclo_formativo_id' =>  7, 'nombre' => 'Archivo y comunicación',                                          'codigo' => '3004', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Gestión Administrativa GM – ciclo 8 ───────────────────────
            ['id' => 43, 'ciclo_formativo_id' =>  8, 'nombre' => 'Comunicación y atención al cliente',                              'codigo' => '437',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 44, 'ciclo_formativo_id' =>  8, 'nombre' => 'Operaciones adminstrativas de la compraventa',                    'codigo' => '438',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 45, 'ciclo_formativo_id' =>  8, 'nombre' => 'Empresa y administración',                                        'codigo' => '439',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 46, 'ciclo_formativo_id' =>  8, 'nombre' => 'Tratamiento informático de la información',                      'codigo' => '440',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 47, 'ciclo_formativo_id' =>  8, 'nombre' => 'Técnica Contable',                                                'codigo' => '441',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 48, 'ciclo_formativo_id' =>  8, 'nombre' => 'Operaciones adminstrativas de recursos humanos',                  'codigo' => '442',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 49, 'ciclo_formativo_id' =>  8, 'nombre' => 'Tratamiento de la documentación contable',                        'codigo' => '443',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 50, 'ciclo_formativo_id' =>  8, 'nombre' => 'Empresa en el Aula',                                              'codigo' => '446',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Administración y Finanzas GS – ciclo 9 ───────────────────
            ['id' => 51, 'ciclo_formativo_id' =>  9, 'nombre' => 'Gestión de la documentación jurídica y empresarial',              'codigo' => '647',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 52, 'ciclo_formativo_id' =>  9, 'nombre' => 'Recursos Humanos y responsabilidad social corporativa.',         'codigo' => '648',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 53, 'ciclo_formativo_id' =>  9, 'nombre' => 'Ofimática y proceso de la información',                          'codigo' => '649',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 54, 'ciclo_formativo_id' =>  9, 'nombre' => 'Proceso integral de la actividad comercial',                     'codigo' => '650',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 55, 'ciclo_formativo_id' =>  9, 'nombre' => 'Comunicación y atención al cliente',                              'codigo' => '651',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 56, 'ciclo_formativo_id' =>  9, 'nombre' => 'Gestión de recursos humanos',                                     'codigo' => '652',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 57, 'ciclo_formativo_id' =>  9, 'nombre' => 'Gestión Financiera',                                              'codigo' => '653',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 58, 'ciclo_formativo_id' =>  9, 'nombre' => 'Contabilidad y Fiscalidad',                                       'codigo' => '654',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 59, 'ciclo_formativo_id' =>  9, 'nombre' => 'Gestión Logística y Comercial',                                   'codigo' => '645',  'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Actividades comerciales GM – ciclo 11 ────────────────────
            ['id' => 60, 'ciclo_formativo_id' => 11, 'nombre' => 'Marketing en la actividad comercial',                             'codigo' => '1226', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 61, 'ciclo_formativo_id' => 11, 'nombre' => 'Gestión de compras',                                              'codigo' => '1229', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 62, 'ciclo_formativo_id' => 11, 'nombre' => 'Dinamización del punto de venta',                                'codigo' => '1231', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 63, 'ciclo_formativo_id' => 11, 'nombre' => 'Procesos de Venta',                                               'codigo' => '1232', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 64, 'ciclo_formativo_id' => 11, 'nombre' => 'Aplicaciones informáticas para el comercio',                     'codigo' => '1233', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 65, 'ciclo_formativo_id' => 11, 'nombre' => 'Servicios de atención comercial',                                 'codigo' => '1234', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 66, 'ciclo_formativo_id' => 11, 'nombre' => 'Comercio electrónico',                                            'codigo' => '1235', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 67, 'ciclo_formativo_id' => 11, 'nombre' => 'Gestión de un pequeño comercio',                                  'codigo' => '1227', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 68, 'ciclo_formativo_id' => 11, 'nombre' => 'Técnicas de almacén.',                                            'codigo' => '1228', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 69, 'ciclo_formativo_id' => 11, 'nombre' => 'Venta técnica',                                                   'codigo' => '1230', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            // ── Servicios Comerciales GB – ciclo 12  (módulo objetivo) ────
            ['id' => 37, 'ciclo_formativo_id' => 12, 'nombre' => 'Tratamiento informático de datos',                                'codigo' => '3001', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 38, 'ciclo_formativo_id' => 12, 'nombre' => 'Aplicaciones básicas de ofimática',                               'codigo' => '3002', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 41, 'ciclo_formativo_id' => 12, 'nombre' => 'Atención al cliente',                                             'codigo' => '3005', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 42, 'ciclo_formativo_id' => 12, 'nombre' => 'Preparación de pedidos y venta de productos',                     'codigo' => '3006', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 70, 'ciclo_formativo_id' => 12, 'nombre' => self::MODULO_NOMBRE,                                               'codigo' => self::MODULO_CODIGO, 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 71, 'ciclo_formativo_id' => 12, 'nombre' => 'Operaciones auxiliares de almacenaje.',                           'codigo' => '3070', 'horas_totales' => 0, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedEcosistemaLaboral(): void
    {
        DB::table('ecosistemas_laborales')->insert([
            'id'          => self::ECOSISTEMA_ID,
            'modulo_id'   => self::MODULO_ID,
            'nombre'      => self::MODULO_NOMBRE,
            'codigo'      => self::ECOSISTEMA_CODIGO,
            'descripcion' => null,
            'activo'      => true,
            'created_at'  => now(),
            'updated_at'  => now(),
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
            'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]];

        foreach ($students as $s) {
            $rows[] = [
                'user_id'               => $s['id'],
                'role_id'               => self::ROLE_ESTUDIANTE,
                'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
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
                'modulo_id'     => self::MODULO_ID,
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
        $scs = [
            [
                'id'                    => 1,
                'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
                'codigo'                => 'SC-01',
                'titulo'                => 'Diseñar la disposición de productos en un lineal',
                'descripcion'           => 'El estudiante diseña y argumenta la disposición óptima de una categoría de productos en un lineal de 2 m, aplicando los principios del visual merchandising y elaborando el planograma correspondiente.',
                'umbral_maestria'       => 80.00,
                'nivel_complejidad'     => 2,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'id'                    => 2,
                'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
                'codigo'                => 'SC-02',
                'titulo'                => 'Elaborar un planograma básico para un punto de venta',
                'descripcion'           => 'El estudiante elabora el planograma completo de una sección de un establecimiento, justificando la ubicación de cada producto en función de su rotación y margen.',
                'umbral_maestria'       => 80.00,
                'nivel_complejidad'     => 2,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'id'                    => 3,
                'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
                'codigo'                => 'SC-03',
                'titulo'                => 'Analizar el rendimiento de una zona caliente/fría',
                'descripcion'           => 'El estudiante analiza el rendimiento de un punto de venta real o simulado mediante indicadores de ventas e identifica propuestas de mejora para las zonas de bajo rendimiento.',
                'umbral_maestria'       => 75.00,
                'nivel_complejidad'     => 3,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'id'                    => 4,
                'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
                'codigo'                => 'SC-04',
                'titulo'                => 'Gestionar el reaprovisionamiento de un lineal',
                'descripcion'           => 'El estudiante planifica y ejecuta el reaprovisionamiento de un lineal detectando roturas de stock, calculando el índice de rotación y asegurando la disponibilidad óptima del surtido.',
                'umbral_maestria'       => 80.00,
                'nivel_complejidad'     => 2,
                'activa'                => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'id'                    => 5,
                'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
                'codigo'                => 'SC-05',
                'titulo'                => 'Elaborar un informe de rendimiento de categoría',
                'descripcion'           => 'El estudiante elabora un informe completo de rendimiento de una categoría de productos integrando indicadores de ventas, rotación de stock y eficacia del lineal, y propone acciones de mejora argumentadas.',
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
            ['situacion_competencia_id' => 1, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer los principios del color, la luz y la composición aplicados al escaparatismo y el visual merchandising.',     'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 1, 'tipo' => 'habilidad',    'descripcion' => 'Manejar herramientas básicas de diseño gráfico (Canva, PowerPoint o similar) para crear bocetos de lineales.',         'orden' => 2, 'created_at' => null, 'updated_at' => null],
            // SC-02 – requiere SC-01
            ['situacion_competencia_id' => 2, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer la estructura, simbología y convenciones de un planograma estándar (DotActiv, Excel).',                        'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 2, 'tipo' => 'habilidad',    'descripcion' => 'Calcular el facing y el lineal desarrollado óptimos para una referencia a partir de su rotación y margen.',             'orden' => 2, 'created_at' => null, 'updated_at' => null],
            // SC-03 – requiere SC-01
            ['situacion_competencia_id' => 3, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer los KPIs de rendimiento comercial: índice de rotación, ventas por metro lineal, tasa de conversión.',          'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 3, 'tipo' => 'habilidad',    'descripcion' => 'Calcular e interpretar el índice de rotación de stock y la ratio de ventas por zona en una hoja de cálculo.',           'orden' => 2, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 3, 'tipo' => 'habilidad',    'descripcion' => 'Identificar visualmente zonas frías y calientes mediante mapas de calor o informes de venta.',                          'orden' => 3, 'created_at' => null, 'updated_at' => null],
            // SC-04 – requiere SC-01
            ['situacion_competencia_id' => 4, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer los sistemas de gestión de inventario y los criterios de rotación FIFO/FEFO aplicados al punto de venta.',     'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 4, 'tipo' => 'habilidad',    'descripcion' => 'Utilizar lectores ópticos de códigos de barras y hojas de recuento para la detección de roturas de stock.',            'orden' => 2, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 4, 'tipo' => 'habilidad',    'descripcion' => 'Calcular el punto de pedido y el stock de seguridad para una referencia de alta rotación.',                             'orden' => 3, 'created_at' => null, 'updated_at' => null],
            // SC-05 – requiere SC-02 y SC-04
            ['situacion_competencia_id' => 5, 'tipo' => 'conocimiento', 'descripcion' => 'Conocer la estructura y los apartados clave de un informe de gestión de categorías (category management).',            'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 5, 'tipo' => 'habilidad',    'descripcion' => 'Elaborar gráficas e indicadores de rendimiento consolidados a partir de datos de ventas en hoja de cálculo.',          'orden' => 2, 'created_at' => null, 'updated_at' => null],
            ['situacion_competencia_id' => 5, 'tipo' => 'habilidad',    'descripcion' => 'Redactar conclusiones y propuestas de mejora con argumentación comercial clara y orientada a resultados.',              'orden' => 3, 'created_at' => null, 'updated_at' => null],
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
        foreach (self::SC_CE_MAP as $scId => $ceMap) {
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
            'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
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
            self::ECOSISTEMA_ID
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

        foreach (self::CURRICULUM as $raCode => $ra) {
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
                'codigo'              => self::MODULO_CODIGO,
                'nombre'              => self::MODULO_NOMBRE,
                'ciclo'               => self::CICLO_NOMBRE,
                'familia_profesional' => self::FAMILIA_NOMBRE,
            ],
            'ecosistema'               => [
                'id'     => self::ECOSISTEMA_ID,
                'codigo' => self::ECOSISTEMA_CODIGO,
                'nombre' => self::MODULO_NOMBRE,
            ],
            'calificacion'             => $calificacion,
            'situaciones_conquistadas' => $situacionesConquistadas,
            'desglose_curricular'      => $desgloseCurricular,
            'generada_en'              => $now->toIso8601String(),
            'version'                  => '1.0',
        ];

        DB::table('huellas_talento')->insert([
            'estudiante_id'         => $student['id'],
            'ecosistema_laboral_id' => self::ECOSISTEMA_ID,
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
            if (! isset(self::SC_CE_MAP[$scId])) {
                continue;
            }
            $score    = $this->scoreForSc($studentId, $scId);
            $grad     = $this->gradienteFromScore($score);
            $efectiva = $score * self::GRADIENTE_MULT[$grad];

            foreach (self::SC_CE_MAP[$scId] as $ceId => $peso) {
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
