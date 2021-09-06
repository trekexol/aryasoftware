@extends('admin.layouts.dashboard')

@section('content')



    {{-- VALIDACIONES-RESPUESTA--}}
    @include('admin.layouts.success')   {{-- SAVE --}}
    @include('admin.layouts.danger')    {{-- EDITAR --}}
    @include('admin.layouts.delete')    {{-- DELELTE --}}
    {{-- VALIDACIONES-RESPUESTA --}}
    
@if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center font-weight-bold h3">Registro de Productos</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <input id="id_user" type="hidden" class="form-control @error('id_user') is-invalid @enderror" name="id_user" value="{{ Auth::user()->id }}" readonly required autocomplete="id_user">
                       
                        <div class="form-group row">
                            <label for="type" class="col-md-2 col-form-label text-md-right">Tipo</label>
                            <div class="col-md-4">
                            <select class="form-control" name="type" id="type">
                                <option value="MERCANCIA">Mercancía</option>
                                <option value="SERVICIO">Servicio</option>
                            </select>
                            </div>
                            <label for="description" class="col-md-2 col-form-label text-md-right">Descripción</label>

                            <div class="col-md-4">
                                <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description') }}" required autocomplete="description">

                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                       
                        <div class="form-group row">
                                    
                            <label for="segment" class="col-md-2 col-form-label text-md-right">Segmento</label>
                        
                            <div class="col-md-4">
                            <select id="segment"  name="segment" class="form-control" required>
                                <option value="">Seleccione un Segmento</option>
                                @foreach($segments as $index => $value)
                                    <option value="{{ $index }}" {{ old('Segment') == $index ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                                </select>

                                @if ($errors->has('segment_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('segment_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                       
                            <label for="subsegment" class="col-md-2 col-form-label text-md-right">Sub Segmento</label>
                        
                            <div class="col-md-4">
                                <select  id="subsegment"  name="Subsegment" class="form-control" required>
                                    <option value="">Selecciona un Sub Segmento</option>
                                </select>

                                @if ($errors->has('subsegment_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('subsegment_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>  

                        <div class="form-group row">
                                    
                            <label for="segment" class="col-md-2 col-form-label text-md-right">Sub Segmento 2 (Opcional)</label>
                        
                            <div class="col-md-4">
                                <select  id="twosubsegment"  name="twoSubsegment" class="form-control" >
                                    <option value=""></option>
                                </select>

                                @if ($errors->has('subsegment_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('subsegment_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <label for="subsegment" class="col-md-2 col-form-label text-md-right">Sub Segmento 3 (Opcional)</label>
                        
                            <div class="col-md-4">
                                <select  id="threesubsegment"  name="threeSubsegment" class="form-control" >
                                    <option value=""></option>
                                </select>

                                @if ($errors->has('subsegment_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('subsegment_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>  

                       
                        <div class="form-group row">
                            <label for="unitofmeasure" class="col-md-2 col-form-label text-md-right">Unidad de Medida</label>

                            <div class="col-md-4">
                            <select class="form-control" id="unit_of_measure_id" name="unit_of_measure_id">
                                @foreach($unitofmeasures as $var)
                                    <option value="{{ $var->id }}">{{ $var->description }}</option>
                                @endforeach
                              
                            </select>
                            </div>
                            <label for="code_comercial" class="col-md-2 col-form-label text-md-right">Código Comercial</label>

                            <div class="col-md-4">
                                <input id="code_comercial" type="text" class="form-control @error('code_comercial') is-invalid @enderror" name="code_comercial" value="{{ old('code_comercial') }}" required autocomplete="code_comercial">

                                @error('code_comercial')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                       
                       
                        <div class="form-group row">
                            <label for="price" class="col-md-2 col-form-label text-md-right">Precio</label>

                            <div class="col-md-4">
                                <input id="price" type="text" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price') }}" required autocomplete="price">

                                @error('price')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <label for="price_buy" class="col-md-2 col-form-label text-md-right">Precio Compra</label>

                            <div class="col-md-4">
                                <input id="price_buy" type="text" class="form-control @error('price_buy') is-invalid @enderror" name="price_buy" value="{{ old('price_buy') }}" required autocomplete="price_buy">

                                @error('price_buy')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        
                        <div class="form-group row">
                            <label for="cost_average" class="col-md-2 col-form-label text-md-right">Costo Promedio</label>

                            <div class="col-md-4">
                                <input id="cost_average" type="text" class="form-control @error('cost_average') is-invalid @enderror" name="cost_average" value="{{ old('cost_average') }}" required autocomplete="cost_average">

                                @error('cost_average')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <label for="photo_product" class="col-md-2 col-form-label text-md-right">Foto del Producto</label>

                            <div class="col-md-4">
                                <input type="image" src="" alt="" width="48" height="48">
                                @error('photo_product')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                       
                        <div class="form-group row">
                            <label for="money" class="col-md-2 col-form-label text-md-right">Moneda</label>

                            <div class="col-md-4">
                                <select class="form-control" name="money" id="money">
                                    <option value="D">Dolar</option>
                                    <option value="Bs">Bolívares</option>
                                </select>
                            </div>
                            <label for="exento" class="col-md-2 col-form-label text-md-right">exento</label>
                            
                            <div class="form-check">
                                <input class="form-check-input position-static" type="checkbox" id="exento" name="exento" value="1" aria-label="...">
                            </div>
                            <label for="islr" class="col-md-1 col-form-label text-md-right">Islr</label>
                            
                            <div class="form-check">
                                <input class="form-check-input position-static" type="checkbox" id="islr" name="islr" value="1" aria-label="...">
                            </div>
                        </div>
                       
                        <div class="form-group row">
                            <label for="special_impuesto" class="col-md-2 col-form-label text-md-right">Impuesto Especial</label>

                            <div class="col-md-4">
                                <input id="special_impuesto" type="text" class="form-control @error('special_impuesto') is-invalid @enderror" name="special_impuesto" value="{{ old('special_impuesto') }}" required autocomplete="special_impuesto">

                                @error('special_impuesto')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            
                        </div>
                        <p id="valueInput"></p> 
                        <br>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                   Registrar Producto
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('products') }}" id="btnfacturar" name="btnfacturar" class="btn btn-danger" title="facturar">Volver</a>  
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('validacion')
    <script>    
        $(document).ready(function () {
            $("#price").mask('000.000.000.000.000,00', { reverse: true });
            
        });
        $(document).ready(function () {
            $("#price_buy").mask('000.000.000.000.000,00', { reverse: true });
            
        });
        $(document).ready(function () {
            $("#cost_average").mask('000.000.000.000.000,00', { reverse: true });
            
        });
        $(document).ready(function () {
            $("#special_impuesto").mask('000.000.000.000.000,00', { reverse: true });
            
        });

	$(function(){
        soloAlfaNumerico('code_comercial');
        soloAlfaNumerico('description');
    });
    </script>
@endsection

@section('javascript')
    <script>
            
            $("#segment").on('change',function(){
                var segment_id = $(this).val();
                $("#subsegment").val("");
               
                // alert(segment_id);
                getSubsegment(segment_id);
            });

        function getSubsegment(segment_id){
            // alert(`../subsegment/list/${segment_id}`);
            $.ajax({
                url:`../subsegment/list/${segment_id}`,
                beforSend:()=>{
                    alert('consultando datos');
                },
                success:(response)=>{
                    let subsegment = $("#subsegment");
                    let htmlOptions = `<option value='' >Seleccione..</option>`;
                    // console.clear();
                    if(response.length > 0){
                        response.forEach((item, index, object)=>{
                            let {id,description} = item;
                            htmlOptions += `<option value='${id}' {{ old('Subsegment') == '${id}' ? 'selected' : '' }}>${description}</option>`

                        });
                    }
                    //console.clear();
                    // console.log(htmlOptions);
                    subsegment.html('');
                    subsegment.html(htmlOptions);
                
                    
                
                },
                error:(xhr)=>{
                    
                }
            })
        }

        $("#subsegment").on('change',function(){
                var subsegment_id = $(this).val();
                var segment_id    = document.getElementById("segment").value;

                get2Subsegment(subsegment_id);
            });


        function get2Subsegment(subsegment_id){
           
            $.ajax({
                url:"{{ route('twosubsegments.list','') }}" + '/' + subsegment_id,
                beforSend:()=>{
                    alert('consultando datos');
                },
                success:(response)=>{
                    let subsegment = $("#twosubsegment");
                    let htmlOptions = `<option value='' >Seleccione..</option>`;
                    // console.clear();
                    if(response.length > 0){
                        response.forEach((item, index, object)=>{
                            let {id,description} = item;
                            htmlOptions += `<option value='${id}' {{ old('Subsegment') == '${id}' ? 'selected' : '' }}>${description}</option>`

                        });
                    }
                    //console.clear();
                    // console.log(htmlOptions);
                    subsegment.html('');
                    subsegment.html(htmlOptions);
                
                    
                
                },
                error:(xhr)=>{
                    
                }
            })
        }



        $("#twosubsegment").on('change',function(){
                var subsegment_id = $(this).val();
                var segment_id    = document.getElementById("segment").value;

                get3Subsegment(subsegment_id);
            });


        function get3Subsegment(subsegment_id){
           
            $.ajax({
                url:"{{ route('threesubsegments.list','') }}" + '/' + subsegment_id,
                beforSend:()=>{
                    alert('consultando datos');
                },
                success:(response)=>{
                    let subsegment = $("#threesubsegment");
                    let htmlOptions = `<option value='' >Seleccione..</option>`;
                    // console.clear();
                    if(response.length > 0){
                        response.forEach((item, index, object)=>{
                            let {id,description} = item;
                            htmlOptions += `<option value='${id}' {{ old('Subsegment') == '${id}' ? 'selected' : '' }}>${description}</option>`

                        });
                    }
                    //console.clear();
                    // console.log(htmlOptions);
                    subsegment.html('');
                    subsegment.html(htmlOptions);
                
                    
                
                },
                error:(xhr)=>{
                    
                }
            })
        }
    </script>
@endsection

