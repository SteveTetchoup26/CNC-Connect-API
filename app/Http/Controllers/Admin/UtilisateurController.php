<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UtilisateurController extends Controller
{
    /**
     * Lister tous les utilisateurs
     */
    public function index()
    {
        $utilisateurs = Utilisateur::all();
        return response()->json($utilisateurs);
    }

    /**
     * Afficher un utilisateur spécifique
     */
    public function show(int $id)
    {
        $utilisateur = Utilisateur::find($id);

        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json($utilisateur);
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:utilisateurs,email',
            'password' => 'required|string|min:6',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Création de l'utilisateur
        $utilisateur = Utilisateur::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'telephone' => $request->telephone,
        ]);

        // Attacher le rôle à l'utilisateur
        $utilisateur->roles()->attach($request->role_id);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'utilisateur' => $utilisateur->load('roles')
        ], 201);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, int $id)
    {
        $utilisateur = Utilisateur::find($id);

        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:utilisateurs,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $utilisateur->update($request->all());

        return response()->json($utilisateur);
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(int $id)
    {
        $utilisateur = Utilisateur::find($id);

        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $utilisateur->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }
}
