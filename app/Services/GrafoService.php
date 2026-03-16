<?php
namespace App\Services;

use App\Models\EcosistemaLaboral;
use App\Models\SituacionCompetencia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GrafoService
{
    /**
     * Calcula la Zona de Despliegue Proximal del estudiante.
     *
     * @param  EcosistemaLaboral  $ecosistema
     * @param  array<string>      $codigosConquistados  Códigos SC ya conquistados
     * @return Collection<SituacionCompetencia>
     */
    public function calcularZdp(
        EcosistemaLaboral $ecosistema,
        array $codigosConquistados
    ): Collection {
        $todas = $ecosistema->situacionesCompetencia()
            ->where('activa', true)
            ->with('prerequisitos:id,codigo')
            ->get();

        return $todas->filter(function (SituacionCompetencia $sc) use ($codigosConquistados) {
            // Condición 1: no conquistada
            if (in_array($sc->codigo, $codigosConquistados)) {
                return false;
            }

            // Condición 2: todos sus prerequisitos están conquistados
            $codigosRequisito = $sc->prerequisitos->pluck('codigo')->toArray();

            return empty($codigosRequisito)
                || count(array_diff($codigosRequisito, $codigosConquistados)) === 0;
        })->values();
    }

    /**
     * Devuelve las SCs bloqueadas: no conquistadas y con al menos un prerequisito
     * sin conquistar.
     *
     * @param  EcosistemaLaboral  $ecosistema
     * @param  array<string>      $codigosConquistados
     * @return Collection<SituacionCompetencia>
     */
    public function calcularBloqueadas(
        EcosistemaLaboral $ecosistema,
        array $codigosConquistados
    ): Collection {
        $todas = $ecosistema->situacionesCompetencia()
            ->where('activa', true)
            ->with('prerequisitos:id,codigo')
            ->get();

        return $todas->filter(function (SituacionCompetencia $sc) use ($codigosConquistados) {
            if (in_array($sc->codigo, $codigosConquistados)) {
                return false;
            }

            $codigosRequisito = $sc->prerequisitos->pluck('codigo')->toArray();

            return !empty($codigosRequisito)
                && count(array_diff($codigosRequisito, $codigosConquistados)) > 0;
        })->values();
    }

    /**
     * Clasifica todas las SCs del ecosistema en tres grupos:
     * 'conquistada', 'disponible' (ZDP) y 'bloqueada'.
     *
     * @param  EcosistemaLaboral  $ecosistema
     * @param  array<string>      $codigosConquistados
     * @return array{conquistadas: Collection, zdp: Collection, bloqueadas: Collection}
     */
    public function clasificar(
        EcosistemaLaboral $ecosistema,
        array $codigosConquistados
    ): array {
        $todas = $ecosistema->situacionesCompetencia()
            ->where('activa', true)
            ->with('prerequisitos:id,codigo', 'nodosRequisito')
            ->get();

        $conquistadas = $todas->filter(
            fn($sc) => in_array($sc->codigo, $codigosConquistados)
        )->values();

        $restantes = $todas->filter(
            fn($sc) => !in_array($sc->codigo, $codigosConquistados)
        );

        $zdp = $restantes->filter(function ($sc) use ($codigosConquistados) {
            $reqs = $sc->prerequisitos->pluck('codigo')->toArray();
            return empty($reqs)
                || count(array_diff($reqs, $codigosConquistados)) === 0;
        })->values();

        $bloqueadas = $restantes->filter(function ($sc) use ($codigosConquistados) {
            $reqs = $sc->prerequisitos->pluck('codigo')->toArray();
            return !empty($reqs)
                && count(array_diff($reqs, $codigosConquistados)) > 0;
        })->values();

        return compact('conquistadas', 'zdp', 'bloqueadas');
    }

    /**
     * Valida que añadir la arista (sc_id → sc_requisito_id) no introduce un ciclo
     * en el grafo. Usa DFS desde sc_requisito_id buscando sc_id.
     *
     * @throws \RuntimeException si la arista crea un ciclo
     */
    public function validarAristaAciclica(
        int $scId,
        int $scRequisitoId,
        EcosistemaLaboral $ecosistema
    ): void {
        if ($scId === $scRequisitoId) {
            throw new \RuntimeException(
                'Una SC no puede ser prerequisito de sí misma.'
            );
        }

        // Lista de adyacencia: sc_id → [sc_requisito_id, ...]
        // groupBy preserva todas las filas aunque sc_id se repita
        $adyacencia = DB::table('sc_precedencia')
            ->join('situaciones_competencia as sc', 'sc.id', '=', 'sc_precedencia.sc_id')
            ->where('sc.ecosistema_laboral_id', $ecosistema->id)
            ->get(['sc_precedencia.sc_id', 'sc_precedencia.sc_requisito_id'])
            ->groupBy('sc_id')
            ->map(fn($filas) => $filas->pluck('sc_requisito_id')->toArray())
            ->toArray();

        // DFS desde $scRequisitoId: ¿podemos llegar a $scId?
        // Si llegamos, la nueva arista $scId → $scRequisitoId crearía un ciclo.
        $visitados = [];
        $pila      = [$scRequisitoId];

        while (!empty($pila)) {
            $actual = array_pop($pila);

            if ($actual === $scId) {
                throw new \RuntimeException(
                    "La arista crea un ciclo: la SC #{$scId} ya es alcanzable "
                    . "desde la SC #{$scRequisitoId}."
                );
            }

            if (isset($visitados[$actual])) {
                continue;
            }

            $visitados[$actual] = true;

            foreach ($adyacencia[$actual] ?? [] as $destino) {
                if (!isset($visitados[$destino])) {
                    $pila[] = $destino;
                }
            }
        }
    }

    /**
     * Ordenación topológica del grafo (algoritmo de Kahn).
     * Devuelve las SCs en un orden de estudio válido (prerequisitos antes que dependientes).
     *
     * @param  EcosistemaLaboral  $ecosistema
     * @return Collection<SituacionCompetencia>   SCs ordenadas
     * @throws \RuntimeException si el grafo tiene ciclos
     */
    public function ordenTopologico(EcosistemaLaboral $ecosistema): Collection
    {
        $scs = $ecosistema->situacionesCompetencia()
            ->with('prerequisitos:id,codigo')
            ->get()
            ->keyBy('id');

        // Calcular grado de entrada de cada nodo
        $gradoEntrada = $scs->mapWithKeys(fn($sc) => [$sc->id => 0]);

        foreach ($scs as $sc) {
            foreach ($sc->prerequisitos as $pre) {
                $gradoEntrada[$sc->id] = $gradoEntrada[$sc->id] + 1;
            }
        }

        // Cola inicial: SCs sin prerequisitos
        $cola      = $gradoEntrada->filter(fn($grado) => $grado === 0)->keys()->toArray();
        $resultado = collect();

        while (!empty($cola)) {
            $id = array_shift($cola);
            $resultado->push($scs[$id]);

            // Reducir grado de entrada de los dependientes
            foreach ($scs[$id]->dependientes ?? [] as $dep) {
                $gradoEntrada[$dep->id] = $gradoEntrada[$dep->id] - 1;
                if ($gradoEntrada[$dep->id] === 0) {
                    $cola[] = $dep->id;
                }
            }
        }

        if ($resultado->count() !== $scs->count()) {
            throw new \RuntimeException(
                'El grafo de precedencia contiene ciclos. '
                . 'Revisa la tabla sc_precedencia del ecosistema #' . $ecosistema->id
            );
        }

        return $resultado;
    }
}
