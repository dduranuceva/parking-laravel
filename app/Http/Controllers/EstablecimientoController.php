<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;


/**
 * @OA\Info(
 *     title="API de Parqueadero",
 *     version="1.0.0",
 *     description="Documentación de la API de Parqueadero"
 * )
 */

class EstablecimientoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/establecimientos",
     *    tags={"Establecimientos"},
     *     summary="Mostrar establecimientos",
     *     description="Retorna todos los establecimientos activos",
     *     @OA\Response(
     *         response=200,
     *         description="Listado de establecimientos."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="Ha ocurrido un error."
     *     )
     * )
     */
    // Mostrar todos los establecimientos activos (estado = 'A')
    public function index()
    {
        try {
            $establecimientos = Establecimiento::where('estado', 'A')->get();
            return response()->json([
                'success' => true,
                'data'    => $establecimientos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los establecimientos',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/establecimientos",
     *    tags={"Establecimientos"},
     *     summary="Crear un establecimiento",
     *     description="Crea un nuevo establecimiento y asigna estado = 'A' por defecto.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nombre", "nit", "direccion", "telefono"},
     *                 @OA\Property(
     *                     property="nombre",
     *                     type="string",
     *                     description="Establecimiento Uno"
     *                 ),
     *                 @OA\Property(
     *                     property="nit",
     *                     type="string",
     *                     description="123456789"
     *                 ),
     *                 @OA\Property(
     *                     property="direccion",
     *                     type="string",
     *                     description="Calle Falsa 123"
     *                 ),
     *                 @OA\Property(
     *                     property="telefono",
     *                     type="string",
     *                     description="555-1234"
     *                 ),
     *                 @OA\Property(
     *                     property="logo",
     *                     type="Base64",
     *                     format="binary",
     *                     description="Archivo del logo"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Establecimiento creado correctamente."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="Ha ocurrido un error."
     *     )
     * )
     */

    // Crear un nuevo establecimiento
     // Se asigna estado 'A' por defecto
     
     public function store(Request $request)
     {
         try {
             $validatedData = $request->validate([
                 'nombre'    => 'required|string',
                 'nit'       => 'required|string|unique:establecimientos,nit',
                 'direccion' => 'required|string',
                 'telefono'  => 'required|string',
                 // 'logo'    => 'nullable|file|mimes:jpeg,png,jpg,gif,svg'
             ]);
     
             // Asignar estado 'A' por defecto
             $validatedData['estado'] = 'A';
     
             $establecimiento = Establecimiento::create($validatedData);
     
             // Determinar ruta correcta del directorio 'logos'
             $destinationPath = app()->environment('production')
                 ? base_path('../parking.visiontic.com.co/logos') // Ruta real del subdominio en producción
                 : public_path('logos'); // Ruta local en desarrollo
     
             // Asegurar que la carpeta exista
             if (!file_exists($destinationPath)) {
                 mkdir($destinationPath, 0775, true);
             }
     
             if ($request->hasFile('logo')) {
                 // Si viene como archivo
                 $file = $request->file('logo');
                 $filename = $file->hashName();
                 $file->move($destinationPath, $filename);
     
                 $establecimiento->logo = $filename;
                 $establecimiento->save();
             } elseif ($request->filled('logo') && Str::startsWith($request->logo, ['data:image', '/9j', 'iVBOR'])) {
                 // Si viene como base64
                 try {
                     $base64Image = $request->logo;
                     $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                     $extension = 'png'; 
     
                     if (Str::startsWith($base64Image, 'data:image/jpeg')) $extension = 'jpg';
                     if (Str::startsWith($base64Image, 'data:image/png'))  $extension = 'png';
                     if (Str::startsWith($base64Image, 'data:image/gif'))  $extension = 'gif';
     
                     $filename = uniqid() . '.' . $extension;
                     file_put_contents("$destinationPath/$filename", $imageData);
     
                     $establecimiento->logo = $filename;
                     $establecimiento->save();
                 } catch (\Exception $e) {
                     return response()->json([
                         'success' => false,
                         'message' => 'Error al procesar la imagen en base64',
                         'error'   => $e->getMessage()
                     ], 422);
                 }
             }
     
             return response()->json([
                 'success' => true,
                 'message' => 'Establecimiento creado correctamente',
                 'data'    => $establecimiento
             ], 201);
         } catch (ValidationException $ve) {
             return response()->json([
                 'success' => false,
                 'message' => 'Error de validación',
                 'errors'  => $ve->errors()
             ], 422);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Error al crear el establecimiento',
                 'error'   => $e->getMessage()
             ], 500);
         }
     }
     
    /**
     * @OA\Get(
     *     path="/api/establecimientos/{id}",
     *    tags={"Establecimientos"},
     *     summary="Mostrar un establecimiento",
     *     description="Retorna un establecimiento específico por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del establecimiento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalle del establecimiento."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="Ha ocurrido un error."
     *     )
     * )
     */
    // Mostrar un establecimiento por ID
    public function show($id)
    {
        try {
            $establecimiento = Establecimiento::findOrFail($id);
            return response()->json([
                'success' => true,
                'data'    => $establecimiento
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Establecimiento no encontrado',
                'error'   => $e->getMessage()
            ], 404);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/establecimientos/{id}",
     *    tags={"Establecimientos"},
     *     summary="Actualizar un establecimiento",
     *     description="Actualiza un establecimiento existente. Para enviar archivos, se recomienda usar override con _method=PUT.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del establecimiento a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="_method",
     *                     type="string",
     *                     description="Override del método HTTP - PUT",
     *                     description="PUT"
     *                 ),
     *                 @OA\Property(
     *                     property="nombre",
     *                     type="string",
     *                     description="Establecimiento Actualizado"
     *                 ),
     *                 @OA\Property(
     *                     property="nit",
     *                     type="string",
     *                     description="987654321"
     *                 ),
     *                 @OA\Property(
     *                     property="direccion",
     *                     type="string",
     *                     description="Nueva dirección"
     *                 ),
     *                 @OA\Property(
     *                     property="telefono",
     *                     type="string",
     *                     description="555-5678"
     *                 ),
     *                 @OA\Property(
     *                     property="logo",
     *                     type="Base64",
     *                     format="binary",
     *                     description="Archivo para actualizar el logo"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Establecimiento actualizado correctamente."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="Ha ocurrido un error."
     *     )
     * )
     */


    // Actualizar un establecimiento (incluyendo actualización de archivos)
  
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nombre'    => 'sometimes|required|string',
                'nit'       => "sometimes|required|string|unique:establecimientos,nit,{$id}",
                'direccion' => 'sometimes|required|string',
                'telefono'  => 'sometimes|required|string',
                // 'logo'   => 'nullable',
            ]);
    
            $establecimiento = Establecimiento::findOrFail($id);
            $establecimiento->update($validatedData);
    
            // Determinar ruta correcta del directorio 'logos'
            $destinationPath = app()->environment('production')
                ? base_path('../parking.visiontic.com.co/logos') // Producción
                : public_path('logos'); // Local
    
            // Crear la carpeta si no existe
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0775, true);
            }
    
            if ($request->hasFile('logo')) {
                // Si viene como archivo
                $file = $request->file('logo');
                $filename = $file->hashName();
                $file->move($destinationPath, $filename);
    
                $establecimiento->logo = $filename;
                $establecimiento->save();
            } elseif ($request->filled('logo') && Str::startsWith($request->logo, ['data:image', '/9j', 'iVBOR'])) {
                // Si viene como base64
                try {
                    $base64Image = $request->logo;
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                    $extension = 'png'; // Por defecto
    
                    if (Str::startsWith($base64Image, 'data:image/jpeg')) $extension = 'jpg';
                    if (Str::startsWith($base64Image, 'data:image/png'))  $extension = 'png';
                    if (Str::startsWith($base64Image, 'data:image/gif'))  $extension = 'gif';
    
                    $filename = uniqid() . '.' . $extension;
                    file_put_contents("$destinationPath/$filename", $imageData);
    
                    $establecimiento->logo = $filename;
                    $establecimiento->save();
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al procesar la imagen en base64',
                        'error'   => $e->getMessage()
                    ], 422);
                }
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Establecimiento actualizado correctamente.',
                'data'    => $establecimiento
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el establecimiento',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * @OA\Delete(
     *     path="/api/establecimientos/{id}",
     *   tags={"Establecimientos"},
     *     summary="Eliminar un establecimiento (borrado lógico)",
     *     description="Cambia el estado del establecimiento a 'I' (inactivo)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del establecimiento a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Establecimiento eliminado correctamente."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="Ha ocurrido un error."
     *     )
     * )
     */
    // Borrado lógico: cambiar el estado de activo ('A') a inactivo ('I')
    public function destroy($id)
    {
        try {
            $establecimiento = Establecimiento::findOrFail($id);
            $establecimiento->estado = 'I';
            $establecimiento->save();
            return response()->json([
                'success' => true,
                'message' => 'Establecimiento eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el establecimiento',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
