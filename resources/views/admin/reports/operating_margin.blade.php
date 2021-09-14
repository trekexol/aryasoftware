
  
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
  <h4 style="color: black; text-align: center">Margen Operativo</h4>
  <h5 style="color: black; text-align: center">Fecha de Emisión: {{ $datenow ?? '' }} / Fecha desde: {{ $date_begin ?? '' }} Fecha Hasta: {{ $date_end ?? '' }} / Tasa: {{ number_format(($rate ?? 0), 2, ',', '.') }}</h5>
   
 
<table style="width: 100%;">
  <tr>
    <th style="text-align: center; ">CENTRO DE COSTOS</th>
    <th style="text-align: center; ">Matriz</th>
    <th style="text-align: center; ">TOTAL</th>
  </tr> 
  
  <tr>
    <td style="text-align: center; ">Ingresos</td>
    <td style="text-align: center; ">{{ number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
    <td style="text-align: center; font-weight: normal;">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
  </tr> 
  <tr>
    <td style="text-align: center; ">Costos</td>
    <td style="text-align: center; ">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
    <td style="text-align: center; font-weight: normal;">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
  </tr> 
  <tr>
    <td style="text-align: center; ">Gastos</td>
    <td style="text-align: center; ">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
    <td style="text-align: center; font-weight: normal;">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
  </tr> 
  <tr>
    <td style="text-align: center; ">UTILIDAD</td>
    <td style="text-align: center; ">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
    <td style="text-align: center; font-weight: normal;">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
  </tr> 
  <tr>
    <td style="text-align: center; ">% M.O.</td>
    <td style="text-align: center; ">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
    <td style="text-align: center; font-weight: normal;">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
  </tr> 
  <tr>
    <td style="text-align: center; ">% RENTABILIDAD</td>
    <td style="text-align: center; ">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
    <td style="text-align: center; font-weight: normal;">{{number_format(($product->price ?? 0) / ($rate ?? 1), 2, ',', '.') }}</td>
  </tr> 

</table>

</body>
</html>
