<?php

namespace Database\Seeders;

use App\Models\CicloFormativo;
use App\Models\Modulo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModulosFormativosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deshabilitar temporalmente la verificación de claves foráneas para evitar errores al insertar módulos sin ciclos formativos existentes
        Schema::disableForeignKeyConstraints();

        $path = database_path('seeders/csv/modulos.csv');

        if (!file_exists($path)) {
            $this->command->error("CSV no encontrado: $path");
            return;
        }

        // Leer todas las líneas y parsear con str_getcsv
        $rows = array_map('str_getcsv', file($path));

        // El primer registro es la cabecera
        $header = array_map('trim', array_shift($rows));

        $data = [];
        foreach ($rows as $row) {
            // Ignorar filas vacías o mal formadas
            if (count($row) < count($header)) {
                continue;
            }

            $rec = array_combine($header, $row);

            $data[] = [
                'codigo' => trim($rec['cod_modulo'] ?? ''),
                'nombre' => trim($rec['nombre_modulo'] ?? ''),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar/actualizar usando upsert para evitar duplicados por 'codigo'
        DB::transaction(function () use ($data) {
            foreach (array_chunk($data, 200) as $chunk) {
                DB::table('modulos')->upsert(
                    $chunk,
                    ['codigo'], // llave única para evitar duplicados
                    ['nombre', 'descripcion', 'updated_at']
                );
            }
        });

        $path = database_path('seeders/csv/ciclo_modulo_relaciones.csv');

        if (!file_exists($path)) {
            $this->command->error("CSV no encontrado: $path");
            return;
        }

        // Leer todas las líneas y parsear con str_getcsv
        $rows = array_map('str_getcsv', file($path));

        // El primer registro es la cabecera
        $header = array_map('trim', array_shift($rows));

        $data = [];
        foreach ($rows as $row) {
            // Ignorar filas vacías o mal formadas
            if (count($row) < count($header)) {
                continue;
            }

            $rec = array_combine($header, $row);

            $ciclo = CicloFormativo::where('codigo', trim($rec['cod_ciclo'] ?? ''))->first();
            if (!$ciclo) {
                $this->command->error("Ciclo formativo no encontrado para código: " . trim($rec['cod_ciclo'] ?? ''));
                continue;
            }

            Modulo::where('codigo', trim($rec['cod_modulo'] ?? ''))->update([
                'ciclo_formativo_id' => $ciclo->id,
            ]);
        }

        Schema::enableForeignKeyConstraints();
    }
}
