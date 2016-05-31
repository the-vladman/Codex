<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Requests\crearLibrorequest;
use App\Http\Requests\modificarLibroRequest;
use App\User;
use Auth;

use App\Libro;
use App\Extracto;
use App\Historia;


class AdminController extends Controller
{
    //
    //
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
    	$libros = Libro::all();
        $user = Auth::user();
        return view('Admin/home/index',compact('user','libros'));
    }

    public function agregar_libro(){
    	$user = Auth::user();
    	return view('Admin/libros/agregar',compact('user'));
    }

     public function guardar_libro(crearLibrorequest $request){
        $libro = new Libro();
        $extracto1 = new Extracto();
        $extracto2 = new Extracto();
        $extracto3 = new Extracto();

        $file = $request->file('imagen');
        

        $libro->nombre = $request->input('nombre');
        $libro->autor = $request->input('autor');
        $libro->url_amazon = $request->input('url_amazon');
        $libro->genero = $request->input('genero');
        $libro->subgenero = $request->input('subgenero');
        $libro->save();
        $extension = $file->getClientOriginalExtension();
        $nombre = 'libro'.$libro->id.'.'.$extension;
        $libro->url_img = $nombre;
        $libro->save();
        \Storage::disk('local')->put($nombre, \File::get($file)); 


        $extracto1->libro_id = $libro->id;
        $extracto1->extracto_texto = $request->input('extracto1');
        $extracto1->tipo = $request->input('tipo1');
        
        $extracto1->save();

        $extracto2->libro_id = $libro->id;
        $extracto2->extracto_texto = $request->input('extracto2');
        $extracto2->tipo = $request->input('tipo2');
       
        $extracto2->save();

        $extracto3->libro_id = $libro->id;
        $extracto3->extracto_texto = $request->input('extracto3');
        $extracto3->tipo = $request->input('tipo3');
        
        $extracto3->save();

        return redirect('xyz/admin/home');
    }

    public function editar_libro($id){
    	$user = Auth::user();
        $libro = Libro::find($id);
        return view('Admin/libros/editar',compact('user','libro'));
    }

    public function modificar_libro($id,ModificarLibroRequest $request){
       	$libro = Libro::find($id);
        $borrar = $libro->url_img;
        if($request->file('imagen')){
            //Borra la imagen
            \Storage::disk('local')->exists($borrar);
            //Agrega la nueva imagen
            $file = $request->file('imagen');
            $extension = $file->getClientOriginalExtension();
            $nombre = 'libro'.$libro->id.'.'.$extension;
            //$libro->url_img = $nombre;
            \Storage::disk('local')->put($nombre, \File::get($file));
            //modifica los campos
            $libro->url_img = $nombre; 
        }
        $libro->nombre = $request->input('nombre');
        $libro->autor = $request->input('autor');
        $libro->url_amazon = $request->input('url_amazon');
        $libro->genero = $request->input('genero');
        $libro->subgenero = $request->input('subgenero');
        $libro->save();

        return redirect('xyz/admin/home');
    }

    public function borrar_libro($id){
        $libro = Libro::find($id);
        foreach ($libro->extractos as $opcion) {
            $opcion->delete();
        }
        $libro->delete();
        return redirect('xyz/admin/home');
    }


    public function mostrar_libro($id){
    	$user = Auth::user();
        $libro = Libro::find($id);
        return view('Admin/extractos/index',compact('user','libro'));
    }  
}