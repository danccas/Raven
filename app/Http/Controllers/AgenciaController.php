<?php

namespace App\Http\Controllers;

use App\Agencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Redirect;

class AgenciaController extends Controller
{
    /**
     * The instance of Tablefy
     * @var Tablefy
     */
    protected $table;
    protected $form;
    /**
     * Display a listing of the resource.
     *
     * @param  \App\lain  $lain
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->table = new Tablefy;
        $this->table->resource('agencia');
        $this->table->setOption('asdasd', 'AgenciaController@asdads');

        $this->form = new Formity;
        $this->form->resource('agencia');
    }
    public function index(Request $request)
    {
        return render($this->table);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\lain  $lain
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view($this->form);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\lain  $lain
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $agencia = Agencia::create($this->form->data());
        return redirect('agencias.edit', $agencia->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\lain  $lain
     * @param  \DummyFullModelClass  $DummyModelVariable
     * @return \Illuminate\Http\Response
     */
    public function edit(Agencia $agencia)
    {
        $this->form->fill($agencia);
        return view($this->form);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\lain  $lain
     * @param  \DummyFullModelClass  $DummyModelVariable
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Agencia $agencia)
    {
        $agencia->update($this->form->data());
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\lain  $lain
     * @param  \DummyFullModelClass  $DummyModelVariable
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Agencia $agencia)
    {
        /* Proceso para eliminar */
        return Redirect::to('/agencias');
    }
}
