<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Usuarios"},
     *     summary="Listar usuarios",
     *     description="Retorna todos los usuarios del sistema.",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios."
     *     )
     * )
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => User::all()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Usuarios"},
     *     summary="Crear usuario",
     *     description="Crea un nuevo usuario en el sistema.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado correctamente."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación."
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:191',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => $user
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
                'message' => 'Error al crear el usuario',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Mostrar usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Usuario encontrado."),
     *     @OA\Response(response=404, description="Usuario no encontrado.")
     * )
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Actualizar usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuario actualizado."),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, User $user)
    {
        $user->update($request->only('name', 'email'));
        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'data' => $user
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Eliminar usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Usuario eliminado correctamente."),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}
