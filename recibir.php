<?php
	//die('aca');
	$dbname="camara";
    $dbuser="root";
    $dbpass="root";
    $dbhost="localhost";
	$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
    function consultar($consulta)
    {
        global $con;
        $datos=mysqli_query($con,$consulta);
        if (!$datos) return array();
        $salida=array();
        for ($i=0;$i<@mysqli_num_rows($datos);$i++)
            $salida[]=@mysqli_fetch_assoc($datos);
        return $salida;
    }
	function base64_to_jpeg($base64_string, $output_file) {
		
		$ifp = fopen($output_file, 'x'); 
		$data = explode(',', $base64_string);
		
		$resultado = fwrite($ifp, base64_decode($data[1])); 
		fclose($ifp); 
		return $output_file; 
	}
	
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		$temporal = ((float)$usec + (float)$sec);
		$temporal = explode('.', $temporal);
		$temporal = $temporal[1];
		return $temporal;
	}
	date_default_timezone_set ('America/Argentina/Buenos_Aires');
	
	$hora = (int)date("H");
	if(strlen($hora)==1)
	{
		$hora = '0'.$hora;
	}
	$minuto = date('i');
	if(strlen($minuto)==1)
	{
		$minuto = '0'.$minuto;
	}
	$segundos = date('s');
	if(strlen($segundos)==1)
	{
		$segundos = '0'.$segundos;
	}
	$micro = microtime_float();
	$carpeta = date('Ymd_');
	$carpeta.= $hora;
	$archivo = date('Ymd_').$hora.$minuto.$segundos.'_'.$micro;
	$directorio = 'imagenes/'.$carpeta.'/'.(string)$archivo.'.jpg';
	$imagen = $_POST['imagen'];
	if (!file_exists('imagenes/'.$carpeta)) {
		mkdir('imagenes/'.$carpeta, 0777, true);
	}
	$archivoUno = base64_to_jpeg($imagen, $directorio);
	$query = "INSERT INTO `foto`(`path`, `hora`) VALUES ('$archivoUno','$archivo')";
	consultar($query);
	//echo ("insert into fotos values('$archivoUno', '$archivo')");
	$archivoDos = consultar("select max(ID) as id from foto");
	$archivoDos = (int)$archivoDos[0]['id'];
	
	$archivoDos--;
	$archivoDos = consultar("select * from foto where ID=$archivoDos");
	$archivoDos = $archivoDos[0]['path'];
	require('imagecompare.php');
	$comparador = new compareImages();
	$diferencia = $comparador->compare($archivoUno, $archivoDos);
	if((int)$diferencia>8)
	{
		if (!file_exists('alertas/'.$carpeta)) {
			mkdir('alertas/'.$carpeta, 0777, true);
		}
		$directorio = 'alertas/'.$carpeta.'/'.(string)$archivo.'.jpg';
		base64_to_jpeg($imagen, $directorio);
		$dirArchivoDos = str_replace('imagenes/', 'alertas/', $archivoDos);
		copy ($archivoDos, $dirArchivoDos);
		//print_r($archivoDos);
		//echo ("\n");
		//print_r($dirArchivoDos);
		consultar("insert into alerta values('$archivoUno')");
	}
?>
