<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $req) {
        // Validar os dados do formulário, se necessário
        $req->validate([
            'query' => 'required|string|max:255', // Adapte as regras de validação conforme necessário
        ]);

        // Obter a consulta digitada pelo usuário
        $query = $req->input('query');

        // Realizar a lógica de pesquisa no seu modelo ou na fonte de dados desejada
        // Suponhamos que você tenha um modelo chamado "Veiculo" para pesquisa
        $results = Cars::where('placa', 'LIKE', '%' . $query . '%')->get();

        // Você pode retornar os resultados para uma visualização ou em outro formato necessário
        return view('/', ['results' => $results]);
    }
}
