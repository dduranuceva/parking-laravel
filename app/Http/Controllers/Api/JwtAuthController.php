<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Autenticación",
 *     description="Endpoints para autenticación con JWT"
 * )
 */
class JwtAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Autenticación"},
     *     summary="Iniciar sesión",
     *     description="Inicia sesión y retorna el token JWT junto con los datos del usuario.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="admin@alpha.com"),
     *             @OA\Property(property="password", type="string", example="12345678")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login exitoso con token JWT"),
     *     @OA\Response(response=401, description="Credenciales inválidas"),
     *     @OA\Response(response=500, description="Error interno")
     * )
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno al intentar iniciar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/perfil",
     *     tags={"Autenticación"},
     *     summary="Perfil del usuario autenticado",
     *     description="Retorna la información del usuario autenticado mediante JWT.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Datos del usuario autenticado"),
     *     @OA\Response(response=500, description="Error interno")
     * )
     */
    public function perfil()
    {
        try {
            return response()->json([
                'success' => true,
                'user' => auth('api')->user()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Autenticación"},
     *     summary="Cerrar sesión",
     *     description="Cierra la sesión eliminando el token JWT actual.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Sesión cerrada correctamente"),
     *     @OA\Response(response=500, description="Error interno")
     * )
     */
    public function logout()
    {
        try {
            auth('api')->logout();
            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
}