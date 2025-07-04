<?php

namespace App\Http\Controllers;

use App\Http\Requests\unor\UnorCreateRequest;
use App\Http\Requests\unor\UnorUpdateRequest;
use App\Http\Resources\UnorResource;
use App\Models\Jabatan;
use App\Models\Unor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UnorController extends Controller
{
    public function index() {
        $jabatan = Jabatan::all();
        return UnorResource::collection($jabatan);
    }

    public function store(UnorCreateRequest $request){
        $data = $request->validated();
        
        if($request->hasFile("cap")){
            $data["cap"] = $request->file("cap")->store("unor/cap" , "public");
        }

        if($request->hasFile("ttd")){
            $data["ttd"] = $request->file("ttd")->store("unor/ttd" , "public");
        }

        $unor = new Unor($data);
        $unor->save();

        return new UnorResource($data);
    }

    public function show(int $id) {
        $unor = Unor::where('id' , $id)->first();

        return new UnorResource($unor);
    }

    public function update(UnorUpdateRequest $request, int $id) {
        $data = $request->validated();

        $unor = Unor::where('id' , $id)->first();

        if(isset($data['nama'])){
            $unor->nama = $data['nama'];
        }
        
        if(isset($data['nip'])){
            $unor->nip = $data['nip'];
        }

        if(isset($data['nama_pimpinan'])){
            $unor->nama_pimpinan = $data['nama_pimpinan'];
        }

        if(isset($data['jabatan'])){
            $unor->jabatan = $data['jabatan'];
        }

        if($request->hasFile('cap')){
            if($unor->cap && Storage::disk("public")->exists($unor->cap)){
                Storage::disk("public")->delete($unor->cap);
            }

            $unor->cap = $request->file("cap")->store("unor/cap" , "public");
        }

        if($request->hasFile("ttd")){
            if($unor->ttd && Storage::disk("public")->exists($unor->ttd)){
                Storage::disk("public")->delete($unor->ttd);
            }
            $unor->ttd = $request->file("ttd")->store("unor/cap" , "public");
        }

        $unor->save();

        return new UnorResource($unor);

    }

    public function destroy(int $id){
        $unor = Unor::where("id" , $id)->first();

        if($unor->cap && Storage::disk("public")->exists($unor->cap)){
            Storage::disk("public")->delete($unor->cap);
        }

        if($unor->ttd && Storage::disk("ttd")->exists($unor->ttd)){
            Storage::disk("public")->delete($unor->ttd);
        }

        $unor->save();

        return response()->json([
            "success" => true
        ]);

    }
}
