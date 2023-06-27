<?php

namespace App\Http\Controllers;

use App\Models\servicio_tecnico;
use App\Models\producto;
use App\Models\servicio_tecnico_producto;

use Illuminate\Http\Request;

class ServicioTecnicoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Número de registros por página, predeterminado: 10
        $page = $request->input('page', 1); // Página actual, predeterminado: 1
        $search = $request->input('search'); // Término de búsqueda

        $valorFinalServicio = 0;
        


        $query = Servicio_Tecnico::query()
        ->join('usuario', 'servicio_tecnico.id_usuario', '=', 'usuario.id_usuario')
        ->leftJoin('cliente', 'servicio_tecnico.id_cliente', '=', 'cliente.id_cliente')
        ->leftJoin('transacciones', 'servicio_tecnico.id_transacciones', '=', 'transacciones.id_transacciones')
        ->select(
            'servicio_tecnico.id_servicio',
            'servicio_tecnico.fecha_ingreso_serv',
            'servicio_tecnico.fecha_salida_serv',
            'servicio_tecnico.descripcion_serv',
            'servicio_tecnico.estado_serv', 
            'servicio_tecnico.precio_serv',
            'servicio_tecnico.id_cliente', 
            'servicio_tecnico.id_usuario',
            'servicio_tecnico.id_transacciones',
            'transacciones.tipo_pago',
            \DB::raw("CONCAT(usuario.nombre_usu, ' ', usuario.apellido_usu) AS nombre_usuario"),
            \DB::raw("CONCAT(cliente.nombre_clie, ' ', cliente.apellido_clie) AS nombre_cliente")
        )
        ->orderBy('servicio_tecnico.id_servicio', 'asc');

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
        $registros = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        // Obtener los servicio_tecnico_producto agrupados por el ID del servicio_tecnico
        $servicio_tecnicoIds = $registros->pluck('id_servicio')->toArray();

        $servicio_tecnico_productos = Servicio_tecnico_producto::whereIn('id_servicio', $servicio_tecnicoIds)
        ->join('producto', 'servicio_tecnico_producto.id_producto', '=', 'producto.id_producto')
        ->select('servicio_tecnico_producto.*', 'producto.nombre_producto')
        ->get()
        ->groupBy('id_servicio');

        // Asignar los detalles a cada registro de movimiento
        $registros->each(function ($registro) use ($servicio_tecnico_productos) {
            $idServicio = $registro->id_servicio;
            $registro->servicio_tecnico_productos = $servicio_tecnico_productos->get($idServicio);
        });
        
        $registros->each(function ($registro) use ($servicio_tecnico_productos, &$valorFinalServicio) {
            $idServicio = $registro->id_servicio;
            $registro->servicio_tecnico_productos = $servicio_tecnico_productos->get($idServicio);
        
            $servicio_tecnico_productos = $servicio_tecnico_productos->get($idServicio);
            if ($servicio_tecnico_productos) {
                $valorTotalPServicio = 0;
                foreach ($servicio_tecnico_productos as $producto) {
                    $valorTotalPServicio += $producto->valor_unitario * $producto->cantidad;
                }
                $valorFinalServicio = $valorTotalPServicio + $registro->precio_serv;
                $registro->valorFinalServicio = $valorFinalServicio;
            }
            
        });

        
        
        
        
        $servicios = $query->get();

        return response()->json([
            'data' => $registros,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'valorFinalServicio' => $valorFinalServicio,
        ]);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $servicio_tecnico = new Servicio_Tecnico;
        $servicio_tecnico->fecha_ingreso_serv = $request->input('fecha_ingreso_serv');
        $servicio_tecnico->fecha_salida_serv = $request->input('fecha_salida_serv');
        $servicio_tecnico->descripcion_serv = $request->input('descripcion_serv');
        $servicio_tecnico->estado_serv = $request->input('estado_serv');
        $servicio_tecnico->precio_serv = $request->input('precio_serv');
        $servicio_tecnico->id_usuario = $request->input('id_usuario');
        $servicio_tecnico->id_cliente = $request->input('id_cliente');
        $servicio_tecnico->id_transacciones = $request->input('id_transacciones');

        return response()->json(['message' => 'Servicio tecnico creado con éxito', 'data' => $servicio_tecnico]);
    }

    /**
     * Display the specified resource.
     */
    public function show(servicio_tecnico $servicio_tecnico)
    {
        $servicioEncontrado = Servicio_Tecnico::find($servicio_tecnico->id_servicio);

        if ($servicioEncontrado) {
            // El movimiento se encontró en la base de datos
            return response()->json($servicioEncontrado);
        } else {
            // El movimiento no se encontró en la base de datos
            return response()->json(['message' => 'Servicio Tecnico no encontrado'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, servicio_tecnico $servicio_tecnico)
    {
        // Validar si el movimiento existe
        if (!$servicio_tecnico->exists()) {
            return response()->json(['error' => 'El servicio tecnico no existe'], 404);
        }

        // Actualizar los valores del modelo con los datos validados
        $servicio_tecnico->fecha_ingreso_serv = $request->input('fecha_ingreso_serv');
        $servicio_tecnico->fecha_salida_serv = $request->input('fecha_salida_serv');
        $servicio_tecnico->descripcion_serv = $request->input('descripcion_serv');
        $servicio_tecnico->estado_serv = $request->input('estado_serv');
        $servicio_tecnico->precio_serv = $request->input('precio_serv');

        // Guardar los cambios en la base de datos
        $servicio_tecnico->save();

        // Retornar o devolver una respuesta 
       
        return response()->json(['message' => 'servicio tecnico actualizado con éxito', 'data' => $servicio_tecnico]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(servicio_tecnico $servicio_tecnico)
    {
        //
    }
}
