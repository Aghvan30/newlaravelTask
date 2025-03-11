<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**

 * @OA\Tag(
 *     name="Users",
 *
 * )
 * @OA\PathItem(
 *     path="/api/"
 * )

 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get a list of all users",
     *     description="Fetch a list of all users (only for admin)",
     *     operationId="getUsers",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="A list of users",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (not allowed to view users)"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     * @OA\Schema(
     * schema="User",
     * type="object",
     * required={"name", "email"},
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", example="user@example.com"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     */
    public function index()
    {
        $this->authorize('admin');
        return UserResource::collection(User::all());
    }
    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get the authenticated user's information",
     *     description="Fetch the current authenticated user's details",
     *     operationId="getCurrentUser",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's information",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }
    /**
     * @OA\Put(
     *     path="/api/user",
     *     summary="Update the authenticated user's profile",
     *     description="Update the authenticated user's name and email",
     *     operationId="updateUserProfile",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="New Name"),
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile updated",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);
        $request->user()->update($request->only(['name', 'email']));
        return new UserResource($request->user());
    }
    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     description="Delete a user by ID (only for admin)",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь удалён")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (not allowed to delete this user)"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->authorize('admin');
        try{
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'Пользователь удалён']);
        }catch (ModelNotFoundException $e){
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

    }
}
