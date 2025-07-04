<?php

namespace App\Http\Controllers;

use App\Http\Requests\jabatan\JabatanCreateRequest;
use App\Http\Requests\jabatan\JabatanUpdateRequest;
use App\Http\Resources\JabatanResource;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(){
        $jabatan = Jabatan::all();
        return JabatanResource::collection($jabatan);
    }

    public function store(JabatanCreateRequest $request){
        $data = $request->validated();

        $jabatan = new Jabatan($data);

        $jabatan->save();

        return new JabatanResource($jabatan);
    }

    public function show(int $id){
        $jabatan = Jabatan::where("id", $id)->first(); 
        
        return new JabatanResource($jabatan);
    }

    public function update(JabatanUpdateRequest $request, int $id){
        $data = $request->validated();

        $jabatan = Jabatan::where("id", $id)->first();

        if(isset($data['nama'])){
            $jabatan->nama = $jabatan['nama'];
        }

        return new JabatanResource($jabatan);
    }

    public function destroy(int $id){
        $jabatan = Jabatan::where('id', $id)->first();

        $jabatan->delete();

        return response()->json([
            "success" => true
        ])->setStatusCode(200);
    }
    
}
