<?php

namespace App\Http\Controllers;

use App\Models\servicio_tecnico;
use App\Models\producto;

use Illuminate\Http\Request;

class ServicioTecnicoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Número de registros por página, predeterminado: 10
        $page = $request->input('page', 1); // Página actual, predeterminado: 1
        $search = $request->input('search'); // Término de búsqueda

        $query = Servicio_Tecnico::query();

        // Aplicar los filtros de búsqueda si se proporcionan
        if ($search) {
            $query->where('cliente', 'LIKE', "%$search%")
                ->orWhere('fecha_ingreso_serv', 'LIKE', "%$search%")
                ->orWhere('fecha_salida_serv', 'LIKE', "%$search%")
                ->orWhere('estado_serv', 'LIKE', "%$search%")
                ->orWhere('id_usuario', 'LIKE', "%$search%");
        }

        $total = $query->count(); // Total de registros antes de paginación

        // Aplicar paginación
        $query->skip(($page - 1) * $perPage)
            ->take($perPage);

        $servicios = $query->get();

        return response()->json([
            'data' => $servicios,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(servicio_tecnico $servicio_tecnico)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, servicio_tecnico $servicio_tecnico)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(servicio_tecnico $servicio_tecnico)
    {
        //
    }
}
