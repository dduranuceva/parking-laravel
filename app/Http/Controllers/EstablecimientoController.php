<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;                          
use Illuminate\Validation\ValidationException;

class EstablecimientoController extends Controller
{
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

    // Crear un nuevo establecimiento (se asigna estado 'A' por defecto)
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre'         => 'required|string',
                'nit'            => 'required|string|unique:establecimientos,nit',
                'direccion'      => 'required|string',
                'telefono'       => 'required|string',
                'logo'           => 'nullable|file|mimes:jpeg,png,jpg,gif,svg',
                'logo_formatos'  => 'nullable|file|mimes:jpeg,png,jpg,gif,svg'
            ]);

            // Asignar estado 'A' de forma predeterminada
            $validatedData['estado'] = 'A';

            $establecimiento = Establecimiento::create($validatedData);

            if ($request->hasFile('logo')) {
                $file     = $request->file('logo');
                $filename = $file->hashName();
                $file->move(public_path('logos'), $filename);
                $establecimiento->logo = $filename;
                $establecimiento->save();
            }

            if ($request->hasFile('logo_formatos')) {
                $file     = $request->file('logo_formatos');
                $filename = $file->hashName();
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

    // Actualizar un establecimiento (incluyendo actualización de archivos)
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nombre'         => 'sometimes|required|string',
                'nit'            => "sometimes|required|string|unique:establecimientos,nit,{$id}",
                'direccion'      => 'sometimes|required|string',
                'telefono'       => 'sometimes|required|string',
                'logo'           => 'nullable|file|mimes:jpeg,png,jpg,gif,svg',
               
            ]);

            $establecimiento = Establecimiento::findOrFail($id);
            $establecimiento->update($validatedData);

            if ($request->hasFile('logo')) {
                $file     = $request->file('logo');
                $filename = $file->hashName();
                $file->move(public_path('logos'), $filename);
                $establecimiento->logo = $filename;
                $establecimiento->save();
            }

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
