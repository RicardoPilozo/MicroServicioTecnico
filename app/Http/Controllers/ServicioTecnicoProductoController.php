<?php

namespace App\Http\Controllers;

use App\Models\servicio_tecnico;
use App\Models\producto;
use App\Models\servicio_tecnico_producto;

use Illuminate\Http\Request;

class ServicioTecnicoProductoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = intval($request->input('per_page', 10)); // Número de elementos por página, valor por defecto: 10
        $page = intval($request->input('page', 1)); // Página actual, valor por defecto: 1
        $search = $request->input('search'); // Término de búsqueda, opcional


        // Consulta base
        $query = Servicio_Tecnico_Producto::query()
        ->orderBy('servicio_tecnico_producto.id_servicio', 'asc');

        // Aplicar búsqueda especializada si se proporciona
        if ($search) {
            $query->where(function ($query) use ($search)  {
                $innerQuery->where('id_servicio_tecnico_producto ', 'LIKE', "%$search%")
                    ->orWhere('cantidad', 'LIKE', "%$search%")
                    ->orWhere('valor_unitario', 'LIKE', "%$search%")
                    ->orWhere('id_inventario', 'LIKE', "%$search%")
                    ->orWhere('id_servicio ', 'LIKE', "%$search%")
                    ->orWhere('id_producto', 'LIKE', "%$search%");
            });
        }
        $total = $query->count();
        $registros = $query->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

        return response()->json([
            'data' => $registros,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }
/*************************************************************************** */
    public function store(Request $request)
    {
        $servicioTecnicoProducto = new Servicio_Tecnico_Producto;
        $servicioTecnicoProducto->cantidad = $request->input('cantidad');
        $servicioTecnicoProducto->valor_unitario = $request->input('valor_unitario');
        $servicioTecnicoProducto->id_inventario = $request->input('id_inventario');
        $servicioTecnicoProducto->id_servicio = $request->input('id_servicio');
        $servicioTecnicoProducto->id_producto = $request->input('id_producto');
        $servicioTecnicoProducto->ganancia_serv_prod= $request->input('ganancia_serv_prod');
        $servicioTecnicoProducto->save();

        return response()->json(['message' => 'Servicio Tecnico Producto agregado exitosamente', 'data' => $servicioTecnicoProducto]);

    }
/*************************************************************************** */



}
