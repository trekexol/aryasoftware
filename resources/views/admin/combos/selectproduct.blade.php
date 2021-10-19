@extends('admin.layouts.dashboard')

@section('content')

<!-- container-fluid -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="row py-lg-2">
      <div class="col-md-8">
          <h2>Seleccione los Productos para el Combo</h2>
      </div>
      <div class="col-md-4">
        <form method="POST" action="{{ route('combos.store_assign') }}" enctype="multipart/form-data">
            @csrf
            <input id="id_products" type="hidden" class="form-control @error('id_products') is-invalid @enderror" name="id_products"  readonly required autocomplete="id_products">
            <input id="id_combo" type="hidden" class="form-control @error('id_combo') is-invalid @enderror" name="id_combo" value="{{ $id_combo }}" readonly required autocomplete="id_combo">
                       
            <button type="submit" class="btn btn-primary float-md-right" role="button" aria-pressed="true">Asignar Productos</button>
        </form>
    </div>
    </div>
  </div>
  <!-- /.container-fluid -->
  {{-- VALIDACIONES-RESPUESTA--}}
  @include('admin.layouts.success')   {{-- SAVE --}}
  @include('admin.layouts.danger')    {{-- EDITAR --}}
  @include('admin.layouts.delete')    {{-- DELELTE --}}
  {{-- VALIDACIONES-RESPUESTA --}}
<!-- DataTales Example -->
<div class="card shadow mb-4">
    
    <div class="card-body">
        <div class="container">
            @if (session('flash'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{session('flash')}}
                <button type="button" class="close" data-dismiss="alert" aria-label="close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>   
        @endif
        </div>
        <div class="table-responsive">
        <table class="table table-light2 table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
            <tr> 
                <th></th>
                <th>Código Comercial</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Moneda</th>
                <th>Segmento</th>
                <th>Sub Segmento</th>
                
                
            </tr>
            </thead>
            
            <tbody>
                @if (empty($products))
                @else  
                    @foreach ($products as $product)
                        <tr>
                            <td>
                                <input onclick="addProduct({{ $product->id }});" type="checkbox" id="flexCheckChecked{{$product->id}}">                        
                            </td>
                            <td>{{$product->code_comercial ?? ''}}</td>
                            <td>{{$product->description ?? ''}}</td>
                            <td>{{$product->price ?? ''}}</td>
                            @if($product->money == "D")
                                <td>Dolar</td>
                            @else
                                <td>Bolívar</td>
                            @endif
                            <td>{{$product->segments['description'] ?? ''}}</td>
                            <td>{{$product->subsegments['description'] ?? ''}}</td> 
                           
                            
                           
                         
                        </tr>     
                    @endforeach   
                @endif
            </tbody>
        </table>
        </div>
    </div>
</div>
  
@endsection
@section('javascript')
    <script>
        $('#dataTable').DataTable({
            "ordering": false,
            "order": [],
            'aLengthMenu': [[50, 100, 150, -1], [50, 100, 150, "All"]]
        });

        let products = [];
        function addProduct(id_product){

            products.push(id_product);
            document.getElementById("id_products").value = products;
        }
    </script> 
@endsection
