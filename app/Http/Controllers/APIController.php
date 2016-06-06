<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Requests\CrearLector;

use App\User;
use App\Extracto;
use App\Lector;
use App\Libro;

//
use App\Gusto;
use App\leidoExtracto;
use App\leidoLibro;
//

use Crypt;
use Storage;
use File;
use Carbon\Carbon;
use Validator;


class APIController extends Controller
{
    
    function lista_libros(){
        $libros = Libro::with('extractos')->get();
        return $libros;
    }

    function lista_lectores(){
        $lectores = Lector::with('gustos')->get();
        return $lectores;
    }

    //LECTORES
    function crear_lector(Request $request){
        $v = Validator::make($request->all(),[
            'email' => 'unique:lectores'
            ]);

        if($v->fails()){
            $errors = $v->errors();
            return $errors->toJson();
        }
        else
        {
            $lector = new Lector();
            $lector->email = $request->input('email');
            $lector->nombre = $request->input('nombre') ;
            $lector->apellido = $request->input('apellido');
            $lector->genero = $request->input('genero');
            $lector->nacimiento = $request->input('nacimiento');
            if ($request->input('password')) {
                $lector->password = Crypt::encrypt($request->input('password'));
            }
            $lector->save();

            $file = $request->file('imagen');
            $extension = $file->getClientOriginalExtension();
            $nombre = 'lector'.$lector->id.'.'.$extension;
            Storage::disk('s3')->put('lectores/' . $nombre, File::get($file));
            $url = Storage::cloud()->url('lectores/' . $nombre);
            $lector->url_img = $url;
            $lector->save();
 
            $gustos = new Gusto();
            $gustos->lector_id = $lector->id;
            $gustos->save();

            return $lector->id;
        }
        
    }

    function modificar_lector(Request $request){
        $lector = Lector::find($request->input('id'));
        //$lector->email = $request->input('email');
        if ($lector) {
            if ($request->input('nombre')) {
                $lector->nombre = $request->input('nombre');
            }
            if ($request->input('apellido')) {
                $lector->apellido = $request->input('apellido');
            }
            if ($request->input('email')) {
                $lector->email = $request->input('email');
            }
            if ($request->input('genero')) {
                $lector->genero = $request->input('genero');
            }
            if ($request->input('nacimiento')) {
                $lector->nacimiento = $request->input('nacimiento');
            }
            if ($request->file('imagen')) {

                $file = $request->file('imagen');
                $extension = $file->getClientOriginalExtension();
                $nombre = 'lector'.$lector->id.'.'.$extension;
                Storage::disk('s3')->put('lectores/' . $nombre, File::get($file));
                $url = Storage::cloud()->url('lectores/' . $nombre);
                $lector->url_img = $url;

            }

            $lector->save();
            return 'Reader Changed!';
        }
        else{
            return 'NotFound';
        }
    }

    function change_password(Request $request){
        $lector = Lector::find($request->input('id'));
        if ($lector) {
            if($lector->password){
                $lector->password = Crypt::encrypt($request->input('password'));
                $lector->save();
                return 'Password Changed!';   
            }
            else{
                return 'FB login';
            }
        }
        else{
            return 'NotFound';
        }
    }

    function login_lector(Request $request){
        $lector = Lector::where('email',$request->input('email'))->first();
        if ($lector) {
            $p = Crypt::decrypt($lector->password);
            $r = $request->input('password');
            if($p == $r){
                return $lector;   
            }
            else{
                return 'Incorrect Password';
            }
        }
        else{
            return 'NotFound';
        }
    }



    public function obtener_gustos(Request $request){
        $id = $request->input('id');        
        $lector = Lector::find($id);
        $libros = $request->input('libros'); 
        $t = count($libros);
        $a = gettype($libros);
        /*
        
        if ($lector) {
            return 'encontrado'.$t;
        }
        else{
            return gettype($json).$t;
        } */
        return $lector . $t . $a;
    }
}
