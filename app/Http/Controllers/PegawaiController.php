<?php

namespace App\Http\Controllers;

use App\Http\Requests\pegawai\PegawaiCreateRequest;
use App\Http\Requests\pegawai\PegawaiUpdateRequest;
use App\Http\Resources\PegawaiResource;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function index(){
        $data = Pegawai::all();
        return PegawaiResource::collection($data);
    }

    public function store(PegawaiCreateRequest $request){
        $data = $request->validated();

        if($request->hasFile("foto")){
            $data["foto"] = $request->file("foto")->store("pegawai" , "public");
        }

        $pegawai = new Pegawai($data);

        $pegawai->status()->attach($data["status_id"]);
        $pegawai->jabatan()->attach($data["jabatan_id"]);
        $pegawai->unor()->attach($data["unor_id"]);

        $pegawai->save();

        return new PegawaiResource($pegawai);
    }

    public function show(int $id){
        $pegawai = Pegawai::where('id' , $id)->first();
        return new PegawaiResource($pegawai);
    }

    public function update(PegawaiUpdateRequest $request , int $id){
        $data = $request->validated();
        $pegawai = Pegawai::where('id', $id)->first();

        if(isset($data['nama'])){
            $pegawai->nama = $data['nama'];
        }

        if(isset($data['jenis_kelamin'])){
            $pegawai->jenis_kelamin = $data['jenis_kelamin'];
        }

        if(isset($data['nomor_telepon'])){
            $pegawai->nomor_telepon = $data["nomor_telepon"];
        }

        if($request->hasFile('foto')){
            if($pegawai->foto && Storage::disk("public")->exists($pegawai->foto)){
                Storage::disk("public")->delete($pegawai->foto);
            }

            $pegawai->foto = $request->file('foto')->store("pegawai" , "public");
        }

        $pegawai->save();

        if(isset($data["status_id"])){
            $pegawai->status()->sync([$data['status_id']]);
        }

        if(isset($data['jabatan_id'])){
            $pegawai->jabatan()->sync([$data["jabatan_id"]]);
        }

        if(isset($data["unor_id"])){
            $pegawai->unor()->sync([$data["unor_id"]]);
        }

        return new PegawaiResource($pegawai);

    }

    public function destroy(int $id){
        $pegawai = Pegawai::where('id' , $id)->first();
        $pegawai->delete();

        return response()->json([
            "success" => true
        ]); 
    }
}