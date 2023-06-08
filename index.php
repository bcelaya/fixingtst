<?php 
  
  require "vendor/autoload.php"; 
  require "prepareMustacheEngine.php";
  $loader = new Mustache_Loader_FilesystemLoader(__DIR__.'/templates/'); 
  $mustache = prepareMustacheEngine();
  $mustache = new Mustache_Engine( [ 'loader' => $loader]);

// Ver mustache filters
	// https://github.com/bobthecow/mustache.php/wiki/FILTERS-pragma
	$mustache->addHelper('case', [
		'lower' => function($value) { return strtolower((string) $value); }, // pasa a minusculas
		'upper' => function($value) { return strtoupper((string) $value); }, // pasa a mayusculas
	]);

  // sudamos de la prueba de la clase persona y vamos a abrir el json a ver que pasa
    $metricas = fopen('data.json','r');
    $data = fread($metricas, filesize('data.json'));
    fclose($metricas);
    $data = json_decode($data, true);
    //var_dump($data);

    echo $mustache->render('plantilla',$data);
?>
