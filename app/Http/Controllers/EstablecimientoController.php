<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EstablecimientoController extends Controller
{
    // Mostrar todos los establecimientos
    public function index()
    {
        try {
            $establecimientos = Establecimiento::all();
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

    // Crear un nuevo establecimiento
    public function store(Request $request)
    {
        try {
            // Validar la información.
            // Se actualiza la validación para que los campos de imagen sean del tipo file y con formatos permitidos.
            $validatedData = $request->validate([
                'nombre'         => 'required|string',
                'nit'            => 'required|string|unique:establecimientos,nit',
                'direccion'      => 'required|string',
                'telefono'       => 'required|string',
                'logo'           => 'nullable|file|mimes:jpeg,png,jpg,gif,svg',
                'logo_formatos'  => 'nullable|file|mimes:jpeg,png,jpg,gif,svg'
            ]);

            // Crear el establecimiento sin archivos
            $establecimiento = Establecimiento::create($validatedData);

            // Manejar el archivo "logo"
            if ($request->hasFile('logo')) {
                $file     = $request->file('logo');
                $filename = $file->hashName();
                // Mover el archivo al directorio public/logos
                $file->move(public_path('logos'), $filename);
                $establecimiento->logo = $filename;
                $establecimiento->save();
            }

            // Manejar el archivo "logo_formatos"
            if ($request->hasFile('logo_formatos')) {
                $file     = $request->file('logo_formatos');
                $filename = $file->hashName();
                // Mover el archivo al directorio public/logo_formatos
                $file->move(public_path('logo_formatos'), $filename);
                $establecimiento->logo_formatos = $filename;
                $establecimiento->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Establecimiento creado correctamente',
                'data'    => $establecimiento
            ], 201);
        } catch (ValidationException $ve) {
            // Manejo de errores de validación
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            // Manejo de cualquier otra excepción
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el establecimiento',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

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

    // Actualizar un establecimiento
    public function update(Request $request, $id)
    {
        try {
            // Validar la información.
            $validatedData = $request->validate([
                'nombre'         => 'sometimes|required|string',
                'nit'            => "sometimes|required|string|unique:establecimientos,nit,{$id}",
                'direccion'      => 'sometimes|required|string',
                'telefono'       => 'sometimes|required|string',
                'logo'           => 'nullable|file|mimes:jpeg,png,jpg,gif,svg',
                'logo_formatos'  => 'nullable|file|mimes:jpeg,png,jpg,gif,svg'
            ]);

            $establecimiento = Establecimiento::findOrFail($id);
            // Actualizar los campos que no sean archivos
            $establecimiento->update($validatedData);

            // Manejar el archivo "logo" en caso de que se envíe
            if ($request->hasFile('logo')) {
                $file     = $request->file('logo');
                $filename = $file->hashName();
                $file->move(public_path('logos'), $filename);
                $establecimiento->logo = $filename;
                $establecimiento->save();
            }

            // Manejar el archivo "logo_formatos" en caso de que se envíe
            if ($request->hasFile('logo_formatos')) {
                $file     = $request->file('logo_formatos');
                $filename = $file->hashName();
                $file->move(public_path('logo_formatos'), $filename);
                $establecimiento->logo_formatos = $filename;
                $establecimiento->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Establecimiento actualizado correctamente',
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

    // Eliminar un establecimiento
    public function destroy($id)
    {
        try {
            $establecimiento = Establecimiento::findOrFail($id);
            $establecimiento->delete();
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
