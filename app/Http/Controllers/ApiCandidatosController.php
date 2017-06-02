<?php

namespace App\Http\Controllers;

use App\Candidato;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Mail;

class ApiCandidatosController extends Controller
{
	public function index()
	{

		$candidatos = Candidato::with('jobs')->get();
		return response()->json($candidatos);
	}

	public function show($id)
	{
		$candidato = Candidato::with('jobs')->find($id);

		if(!$candidato) {
			return response()->json([
				'message'   => 'Record not found',
				], 404);
		}

		return response()->json($candidato);
	}

	public function store(Request $request)
	{

		$confirmation_code = str_random(30);

		// $imageName = $request['nome'] . " | " . $request['email'] . '.' . 
		// $request->file('arquivo')->getClientOriginalExtension();

		// $request->file('arquivo')->move(base_path() . '/public/uploads/', $imageName);

		$nome = $request['nome'];
		$mail = $request['email'];

		$candidato = $request->all();
		$candidato['cod_confirmacao'] = $confirmation_code;
		// $candidato['arquivo'] = $imageName;
		Candidato::create($candidato);

		$data = array('confirmacao' => $confirmation_code);

		Mail::send('candidatos.verify', $data, function($message) use ($mail, $nome) {
			$message->to($mail, $nome)->subject('Verifique seu endereço de e-mail');
		});

		return response()->json($candidato, 201);
	}

	public function update(Request $request, $id)
	{
		try {
			$candidato = Candidato::findOrFail($id);

			$candidato->fill($request->all());
			$candidato->save();

			return response()->json($candidato);
		} catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			response()->json($e);
		}
	}

	public function destroy($id)
	{
		$candidato = Candidato::find($id);

		if(!$candidato) {
			return response()->json([
				'message'   => 'Record not found',
				], 404);
		}

		$candidato->delete();
	}
}