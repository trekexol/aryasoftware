
  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 
<title></title>
<style>
  table, td, th {
    border: 1px solid black;
    font-size: x-small;
  }
  
  table {
    border-collapse: collapse;
    width: 100%;
  }
  
  th {
    
    text-align: left;
  }
  </style>
</head>

<body>
  <table>
    <tr>
      <th style="text-align: left; font-weight: normal; width: 10%; border-color: white; font-weight: bold;"> <img src="{{ asset(Auth::user()->company->foto_company ?? 'img/northdelivery.jpg') }}" width="90" height="30" class="d-inline-block align-top" alt="">
      </th>
      <th style="text-align: left; font-weight: normal; width: 90%; border-color: white; font-weight: bold;"><h4>{{Auth::user()->company->code_rif ?? ''}} </h4></th>
    </tr> 
  </table>
  <h4 style="color: black; text-align: center">Clientes</h4>
  <h5 style="color: black; text-align: center">Fecha de Emisión: {{ $datenow ?? '' }} / Fecha desde: {{ $date_begin ?? '' }} Fecha Hasta: {{ $date_end ?? '' }}</h5>
   
 
  <table class="table table-light2 table-bordered" id="dataTable" width="100%" cellspacing="0">
    <thead>
    <tr> 
       
       <th style="text-align: center; align: center;">N</th>  
       <th style="text-align: center; align: center;">Nombre</th>
        <th style="text-align: center; align: center;">Cedula o Rif</th>
        <th style="text-align: left; align: left;">Dirección</th>
        <th style="text-align: center; align: center;">Telefono</th>
        <th style="text-align: center; align: center;">D.Crédito</th>
        <th style="text-align: center; align: center;">Vendedor</th>
    </tr>
    </thead>
    
    <tbody>
        @if (empty($clients))
        @else  
        <?php $cont =1; ?>
            @foreach ($clients as $client)
                <tr>
                  <td style="text-align: center; align: center;">{{$cont}}</td>
                    <td style="text-align: center; align: center;">{{$client->name}}</td>
                    <td style="text-align: center; align: center;">{{$client->type_code}} {{$client->cedula_rif}}</td>
                    <td style="text-align: left; align: left;">{{$client->direction}}</td>
                    <td style="text-align: center; align: center;">{{$client->phone1}}</td>
                    <td style="text-align: center; align: center;">{{$client->days_credit}}</td>
                    @if (isset($client->vendors['name']))
                        <td style="text-align: center; align: center;">{{$client->vendors['name']}} {{$client->vendors['surname']}}</td>
                    @else
                        <td style="text-align: center; align: center;"></td>
                    @endif
                </tr>     
                <?php $cont ++;?> 
            @endforeach   
        @endif
    </tbody>
</table>

</body>
</html>
