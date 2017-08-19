<?php

namespace Mapos\Http\Controllers;

use Mapos\Models\Brand;
use Illuminate\Http\Request;
use Datatables;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('brands.index');
    }

    /**
     * Datatables list
     */
    public function get_list()
    {
        $brands = Brand::select(['id', 'brand', 'active','created_at', 'updated_at']);
        
        return Datatables::of($brands)
            ->addColumn('action', function ($brand) {
                return '<a href="#edit-'.$brand->id.'" class="btn btn-primary"><i class="glyphicon glyphicon-edit"></i></a>
                        <a href="'. route('brands.destroy',$brand->id).'" class="btn btn-danger"><i class="glyphicon glyphicon-remove"></i></a>';
            })
            ->editColumn('active', function($brand) {
                return $brand->active ? '<span class="label label-success">Ativo</span>' : '<span class="label label-danger">Inativo</span>';
            })
            ->make(true);
    }

    /**
     * Show the form for creating a new brand.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('brands.create');
    }

    /**
     * Store a newly created brand in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'brand' => 'min:2',
            'active' => 'required'
        ]);

        $brand = Brand::create([
            'brand' => $request->brand,
            'active' => true
        ]);

        return redirect('brands');

    }

    /**
     * Display the specified brand.
     *
     * @param  \Mapos\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Show the form for editing the specified brand.
     *
     * @param  \Mapos\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function edit(Brand $brand)
    {
        //
    }

    /**
     * Update the specified brand in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Mapos\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
    {
        //
    }

    /**
     * Remove the specified brand from storage.
     *
     * @param  \Mapos\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        Brand::destroy($brand);
        
        
    }
}
