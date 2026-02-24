<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TurnoController extends Controller
{
    private function prefijos(): array
    {
        return [
            'Caja'     => 'CA',
            'Servicio' => 'SC',
            'Creditos' => 'CR',
        ];
    }

    /**
     * Verifica si la sesión pertenece al día actual; si no, la reinicia.
     */
    private function verificarFecha(): void
    {
        $fechaSession = session('turnos_fecha');
        $hoy = now()->toDateString();

        if ($fechaSession !== $hoy) {
            session()->forget(['turnos', 'turnos_fecha', 'contador', 'turno_actual']);
            session(['turnos_fecha' => $hoy]);
        }
    }

    /**
     * Obtiene la cola de turnos de sesión o array vacío.
     */
    private function getCola(): array
    {
        return session('turnos', []);
    }

    public function index()
    {
        $this->verificarFecha();
        $cola = $this->getCola();
        $turnoActual = session('turno_actual');

        return view('turnos.index', [
            'tipos'        => array_keys($this->prefijos()),
            'cola'         => $cola,
            'turnoActual'  => $turnoActual,
            'turnoNuevo'   => session('turno_nuevo'),
        ]);
    }

    public function store(Request $request)
    {
        $this->verificarFecha();

        $request->validate([
            'nombre' => 'required|string|max:80',
            'tipo'   => 'required|in:Caja,Servicio,Creditos',
        ]);

        $prefijos  = $this->prefijos();
        $prefijo   = $prefijos[$request->tipo];
        $contador  = session('contador', []);
        $num       = ($contador[$prefijo] ?? 0) + 1;
        $contador[$prefijo] = $num;
        session(['contador' => $contador]);

        $codigo = $prefijo . str_pad($num, 3, '0', STR_PAD_LEFT);

        $turno = [
            'id'        => (string) Str::uuid(),
            'codigo'    => $codigo,
            'nombre'    => $request->nombre,
            'tipo'      => $request->tipo,
            'prefijo'   => $prefijo,
            'hora'      => now()->format('H:i:s'),
            'estado'    => 'espera', // espera | atendiendo
        ];

        $cola   = $this->getCola();
        $cola[] = $turno;
        session(['turnos' => $cola]);
        session(['turno_nuevo' => $turno]);

        return redirect()->route('turnos.index')->with('turno_nuevo', $turno);
    }

    public function cancel(string $id)
    {
        $this->verificarFecha();
        $cola = $this->getCola();
        $cola = array_values(array_filter($cola, fn($t) => $t['id'] !== $id));
        session(['turnos' => $cola]);

        return redirect()->route('turnos.admin')->with('success', 'Turno cancelado.');
    }

    public function siguiente()
    {
        $this->verificarFecha();
        $cola = $this->getCola();

        if (empty($cola)) {
            return redirect()->route('turnos.admin')->with('info', 'No hay turnos en cola.');
        }

        // Eliminar el primero (ya atendido)
        $atendido = array_shift($cola);
        session(['turnos' => array_values($cola)]);
        session(['turno_actual' => $cola[0] ?? null]); // El siguiente pasa a "actual"

        return redirect()->route('turnos.admin')->with('success', "Turno {$atendido['codigo']} atendido.");
    }

    public function reordenar(Request $request)
    {
        $this->verificarFecha();
        $request->validate(['orden' => 'required|array']);

        $ids  = $request->orden; // array de UUIDs en nuevo orden
        $cola = $this->getCola();

        $mapa   = collect($cola)->keyBy('id');
        $nueva  = array_values(array_filter(
            array_map(fn($id) => $mapa->get($id), $ids)
        ));

        session(['turnos' => $nueva]);

        return response()->json(['ok' => true]);
    }

    public function pantalla()
    {
        $this->verificarFecha();
        return view('turnos.pantalla', [
            'cola'        => $this->getCola(),
            'turnoActual' => session('turno_actual'),
        ]);
    }

    public function admin()
    {
        $this->verificarFecha();
        return view('turnos.admin', [
            'cola'        => $this->getCola(),
            'turnoActual' => session('turno_actual'),
        ]);
    }

    /**
     * Endpoint AJAX para actualizar la cola en pantalla pública.
     */
    public function cola()
    {
        $this->verificarFecha();
        return response()->json([
            'cola'        => $this->getCola(),
            'turnoActual' => session('turno_actual'),
            'hora'        => now()->format('H:i:s'),
        ]);
    }
}