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
	
	$mustache->addHelper('num', [
		'format_int'      => function($value) { return helper_format_int($value);      },       
		'format_float'    => function($value) { return helper_format_float($value);    },      
		'format_pct'      => function($value) { return helper_format_pct($value);      },   
		'format_work_dur' => function($value) { return helper_format_work_dur($value); }
	]);
	
	
	$mustache->addHelper('date', [
		'format_long'  => function($value) { if(empty($value)) return "-"; $date=date_create_from_format("Y-m-d\TG:i:sT", $value);	return date_format($date, 'd/m/Y H:i:s'); },
		'format_short' => function($value) { if(empty($value)) return "-"; $date=date_create_from_format("Y-m-d\TG:i:sT", $value);	return date_format($date, 'd/m/Y'); },
	]);
	
	$mustache->addHelper('imagen', [
		'rating-small' => function($rating) { return helper_img_rating_small($rating); }, 
		'rating-big'   => function($rating) { return helper_img_rating_big($rating);   },
		'status-big'   => function($status) { return helper_img_status_big($status);   },
		'status-small' => function($status) { return helper_img_status_small($status); },
		'size-big'     => function($size)   { return helper_img_size_big($size);       }
    ]);
	
	

	// El helper "describe" contiene funciones para traducir métricas, comparadores...
	$mustache->addHelper('describe', [
		'metricKey' => function($value) { 
			global $keysReplacements;
			return isset($keysReplacements[$value])?$keysReplacements[$value]:$value; 
		},
		'rating' => function($value) { 
			global $ratingsReplacements;
			return isset($ratingsReplacements[$value])?$ratingsReplacements[$value]:$value; 
		},
		'comparator' => function($value) { 
			global $comparatorReplacements;
			return isset($comparatorReplacements[$value])?$comparatorReplacements[$value]:$value; 
		}, 
		'ratingComparator' => function($value) { 
			global $ratingComparatorReplacements;
			return isset($ratingComparatorReplacements[$value])?$ratingComparatorReplacements[$value]:$value; 
		}
	]);

	$mustache->addHelper('components', [
		'value'                   => function($measure) { return helper_render_measure($measure); },
		'link'                    => function($measure) { return helper_link_measure($measure, helper_render_measure($measure)); },
		'direction'               => function($measure) { return helper_measure_direction($measure); },
		'pct_non_leak'            => function($measure) { return helper_measure_pct_non_leak($measure); },
		'big_image_size_link'     => function($measure) { return sonar_url_link($measure["key"], helper_img_size_big(helper_size($measure["value"]))); }, 
		'small_image_size_link'   => function($measure) { return sonar_url_link($measure["key"], helper_img_size_small(helper_size($measure["value"]))); },
		'big_image_rating_link'   => function($measure) { return sonar_url_link($measure["key"], helper_img_rating_big($measure["value"])); }, 
		'small_image_rating_link' => function($measure) { return sonar_url_link($measure["key"], helper_img_rating_small($measure["value"])); }
	]);


	$mustache->addHelper('gates', [
	
		'actualValue'     => function($metricKey) { return helper_render_condition($metricKey, "actualValue"); }, 
		'warningThreshold'=> function($metricKey) { return helper_render_condition($metricKey, "warningThreshold"); }, 
		'errorThreshold'  => function($metricKey) { return helper_render_condition($metricKey, "errorThreshold"); },
		'status'          => function($metricKey) { return helper_render_condition($metricKey, "status"); }, 
		'link'            => function($condition) { return helper_link_condition($condition, helper_render_condition($condition->metric, "actualValue")); }
		
	]);
	
	$mustache->addHelper('link', [
		'measure' => function($metricKey) { return helper_link_measure($metricKey); }, 
		'image'  =>  function($metricKey) { return sonar_url_link($metricKey, helper_link_measure($metricKey)); }
		
	]);
	
	$mustache->addHelper('is', [
		'condition_OK'   => function($condition)     { return ($condition->status  !== null) && ($condition->status == "OK"); 		},
		'status_OK'      => function($text)          { return $text == "OK"; },
		'type_RATING'    => function($metricKey)     { return (helper_render_metric_type($metricKey) == "RATING") ? TRUE : FALSE ; },
		'critical_violations'    => function($metricKey)     { return $metricKey == "critical_violations"  ; }
		
		
	]);

	// se añade el nuevo helper
	$mustache->addHelper('checkLanguage', function($language)
	{ 
		return $language ." <- lenguaje programación.";
	});
		
		
	$mustache->addHelper('size', function($size)      { return helper_size($size); });
	$mustache->addHelper('umbral', function($value)   { return helper_umbral($value); });
	$mustache->addHelper('toString', function($value) { return json_encode($value); });
	
	return $mustache;


/* -----------------------------------------------------------------------------
FIN DE FUNCIONES
----------------------------------------------------------------------------- */
/* -----------------------------------------------------------------------------
TRADUCCIONES
----------------------------------------------------------------------------- */

$comparatorReplacements["GT"]="Es mayor que";
$comparatorReplacements["LT"]="Es menor que";


$ratingComparatorReplacements["GT"]="Es peor que";
$ratingcomparatorReplacements["lt"]="Es mejor que";
/* Metricas sobre todo el código */
$keysReplacements["ncloc"]                         ="Líneas de código";
$keysReplacements["blocker_violations"]            ="Evidencias bloqueantes";
$keysReplacements["bugs"]                          ="Bugs";
$keysReplacements["code_smells"]                   ="Code Smells";
$keysReplacements["coverage"]                      ="Cobertura";
$keysReplacements["critical_violations"]           ="Evidencias críticas";
$keysReplacements["duplicated_lines_density"]      ="Líneas Duplicadas (%)";
$keysReplacements["reliability_rating"]            ="Rating SQALE de BUGS (Calificación Fiabilidad)";
$keysReplacements["skipped_tests"]                 ="Tests unitarios omitidos";
$keysReplacements["sqale_debt_ratio"]              ="Ratio deuda técnica";
$keysReplacements["sqale_index"]                   ="Deuda técnica";
$keysReplacements["sqale_rating"]                  ="Calificación Mantenibilidad";
$keysReplacements["security_rating"]               ="Calificación Seguridad";
$keysReplacements["tests"]                         ="Tests unitarios";
$keysReplacements["test_failures"]                 ="Fallos en los tests unitarios";
$keysReplacements["test_errors"]                   ="Errores en los tests unitarios";
$keysReplacements["vulnerabilities"]               ="Vulnerabilidades";
$keysReplacements["rules_compliance"]			   ="Índice de cumplimiento de reglas";

/* Metricas sobre código nuevo */
$keysReplacements["new_lines"]                     ="Nuevas líneas de código";
$keysReplacements["new_blocker_violations"]        ="Evidencias bloqueantes en código nuevo";
$keysReplacements["new_bugs"]                      ="Bugs en código nuevo";
$keysReplacements["new_code_smells"]               ="Code Smells en código nuevo";
$keysReplacements["new_coverage"]                  ="Cobertura en código nuevo";
$keysReplacements["new_critical_violations"]       ="Evidencias críticas en código nuevo";
$keysReplacements["new_duplicated_lines_density"]  ="Líneas Duplicadas en código nuevo (%)";
$keysReplacements["new_reliability_rating"]        ="Calificación Fiabilidad en código nuevo";
$keysReplacements["new_sqale_debt_ratio"]          ="Ratio Deuda técnica en código nuevo";
$keysReplacements["new_maintainability_rating"]    ="Calificación Mantenibilidad en código nuevo";
$keysReplacements["new_security_rating"]           ="Calificación Seguridad en código nuevo";
$keysReplacements["new_technical_debt"]            ="Deuda técnica en código nuevo";
$keysReplacements["new_vulnerabilities"]           ="Vulnerabilidades en código nuevo";	

// lenguaje
$keysReplacements["language"]					   ="Lenguaje";

$ratingsReplacements["1"] = "A";
$ratingsReplacements["2"] = "B";
$ratingsReplacements["3"] = "C";
$ratingsReplacements["4"] = "D";
$ratingsReplacements["5"] = "E";

/* FUNCIONES PARA HELPERS ----------------------------------------------------*/



function helper_size($size) {
	
	// console_log($size, "tamaño:");
	
	// if     ($size <   1000 ) { $result = "XS"; } 
	// elseif ($size > 500000 ) { $result = "XL"; } 
	// elseif ($size > 100000 ) { $result = "L" ; } 
	// elseif ($size >  10000 ) { $result = "M" ; } 
	// else                     { $result = "S" ; }
	
	
	if ($size > 500000 )     { $result = "XL"; } 
	elseif ($size > 100000 ) { $result = "L" ; } 
	elseif ($size >  10000 ) { $result = "M" ; } 
	elseif ($size >=  1000 ) { $result = "S" ; }
	else                     { $result = "XS"; } 
	
	// console_log($result, "result:");

	return $result;
};

function helper_umbral($value) {
	switch ($value) {
		case "OK":      $result = "SUPERADO";      break;
		case "WARN":    $result = "ADVERTENCIA";   break;
		case "ERROR":   $result = "NO SUPERADO";   break;
		default:        $result = $value;
	}
	return $result;
};

function helper_format_int($value)   { 
	//console_log(is_numeric($value) && (int)$value >= 0, "is_numeric($value) && (int)$value >= 0"); 
	// Los enteros negativos son ausencia de valor.
	return !(is_numeric($value) && (int)$value >= 0)?'-':number_format($value, 0, ',', '.'); 
}; 
	
function helper_format_float($value) { return !is_numeric($value)?'-':number_format($value, 1, ',', '.'); };
function helper_format_pct($value)   { return !is_numeric($value)?'-':number_format($value, 1, ',', '.').'%'; };
function helper_format_work_dur($value) { 

	if (is_numeric($value)) {
		// Technical Debt (sqale_index) The measure is stored in minutes in the database. An 8-hour day is assumed when values are shown in days.
		$result = ($value > 8) ? round((float) $value/60/8, 0).'d' : $value.'m';
	} else {
		$result = '-';
	}

	return $result;
}    



function helper_img_rating_small($rating) { 
	// return !isset($rating)?'':'<img src="images/circulo_rating_'.$rating.'_100px.png" width="20" height="20" border="0" />'; 
	// return !isset($rating)?'':'<img src="images/ppt_rating_'.$rating.'_300px.png" width="20" height="20" border="0" />'; 
	
	// debug("helper_img_rating_small - $rating - ".empty($rating));
	
	return empty($rating)?'-':'<img src="images/ppt_rating_'.$rating.'_300px.jpg" width="20" height="20" border="0" />'; 
};
function helper_img_rating_big($rating)   { 
	// return !isset($rating)?'':'<img src="images/circulo_rating_'.$rating.'_100px.png" width="100" height="100" border="0" />'; 
	// return !isset($rating)?'':'<img src="images/ppt_rating_'.$rating.'_300px.png" width="100" height="100" border="0" />'; 
	return empty($rating)?'-':'<img src="images/ppt_rating_'.$rating.'_300px.jpg" width="80" height="80" border="0" />'; 
};
function helper_img_status_big($status)   { 
	// return !isset($status)?'':'<img src="images/elipse_'.$status.'_100px.png" width="120" height="30" border="0" />'; 
	// return !isset($status)?'':'<img src="images/ppt_condition_'.$status.'_300px.png" height="30" border="0" />'; 
	return !isset($status)?'-':'<img src="images/ppt_condition_'.$status.'_300px.jpg" height="30" border="0" />'; 
};
function helper_img_status_small($status) { 
	// return !isset($status)?'':'<img src="images/elipse_'.$status.'_100px.png" width="80" height="20" border="0" />'; 
	// return !isset($status)?'':'<img src="images/ppt_condition_'.$status.'_300px.png" height="20" border="0" />'; 
	return !isset($status)?'-':'<img src="images/ppt_condition_'.$status.'_300px.jpg" height="20" border="0" />'; 
};
function helper_img_size_big($size)       { 
	 // console_log($size, "helper_img_size_big:");
	// return !isset($size)?  '':'<img src="images/circulo_size_'.$size.'_100px.png" width="100" height="100" border="0" />'; 
	// return !isset($size)?  '':'<img src="images/ppt_size_'.$size.'_300px.png" width="100" height="100" border="0" />'; 
	return !isset($size)?  '-':'<img src="images/ppt_size_'.$size.'_300px.jpg" width="80" height="80" border="0" />'; 
};


// enlaces para las measures
function helper_link_measure($measure, $value){
	global $sonar_host;
	global $sonarKey;
	
	if(empty($measure["key"])) {
		return "-";
	} else {
		$metricKey = $measure["key"];
		return sonar_url_link($metricKey, $value);
	}
}

// enlaces para las condiciones del umbral
function helper_link_condition($condition, $value){
	if(empty($condition->metric)) {
		return "";
	} else {
		$metricKey = $condition->metric;
		$sinceLeakPeriod = isset ($condition->period);
		
		// if($condition->metric == "critical_violations") {
			// console_log($condition, "$metricKey condition");
			// console_log($value, "$metricKey value");
		// }
		
		return sonar_url_link($metricKey, $value, $sinceLeakPeriod);
	}
}

function sonar_url_link($metricKey, $value, $sinceLeakPeriod="false"){
	global $sonar_host;
	global $sonarKey;
	
	// switch ($metricKey) {
		// case 'vulnerabilities':      $url = "${sonar_host}/project/issues?id=${sonarKey}&resolved=false&sinceLeakPeriod=${sinceLeakPeriod}&types=VULNERABILITY"; break;			
		// case 'bugs':                 $url = "${sonar_host}/project/issues?id=${sonarKey}&resolved=false&sinceLeakPeriod=${sinceLeakPeriod}&types=BUG"; break;
		// case 'code_smells':          $url = "${sonar_host}/project/issues?id=${sonarKey}&resolved=false&sinceLeakPeriod=${sinceLeakPeriod}&types=CODE_SMELL"; break;
		
		// case 'new_vulnerabilities':  $url = "${sonar_host}/project/issues?id=${sonarKey}&resolved=false&sinceLeakPeriod=true&types=VULNERABILITY"; 	break;
		// case 'new_bugs':             $url = "${sonar_host}/project/issues?id=${sonarKey}&resolved=false&sinceLeakPeriod=true&types=BUG"; break;
		// case 'new_code_smells':      $url = "${sonar_host}/project/issues?id=${sonarKey}&resolved=false&sinceLeakPeriod=true&types=CODE_SMELL"; break;
		
		// default:                     $url = "$sonar_host/component_measures/metric/$metricKey/list?id=$sonarKey";
	// }
	
	// METHOD
	switch ($metricKey) {
		case 'vulnerabilities':
		case 'new_vulnerabilities':
		case 'bugs':
		case 'new_bugs':
		case 'code_smells':
		case 'new_code_smells':
		
		case 'violations':
		case 'new_violations':
		case 'blocker_violations':
		case 'new_blocker_violations':
		case 'critical_violations':
		case 'new_critical_violations':
		case 'major_violations':
		case 'new_major_violations':
		case 'minor_violations':
		case 'new_minor_violations':
		case 'info_violations':
		case 'new_info_violations':
			$method = "project/issues";                                   break;
		default:                     
			$method = "component_measures/metric/$metricKey/list";
	}
	
	
	// STANDARD QUERY
	$query = "id=${sonarKey}";
	
	// NOT RESOLVED
	switch ($metricKey) {
		case 'vulnerabilities':
		case 'new_vulnerabilities':
		case 'bugs':
		case 'new_bugs':             
		case 'code_smells':
		case 'new_code_smells':
		
		case 'violations':
		case 'new_violations':
		case 'blocker_violations':
		case 'new_blocker_violations':
		case 'critical_violations':
		case 'new_critical_violations':
		case 'major_violations':
		case 'new_major_violations':
		case 'minor_violations':
		case 'new_minor_violations':
		case 'info_violations':
		case 'new_info_violations':
			$query .= "&resolved=false"; break;
	}
	
	// TYPES
	switch ($metricKey) {
		case 'vulnerabilities':
		case 'new_vulnerabilities':
			$query .= "&types=VULNERABILITY"; break;
		
		case 'bugs':
		case 'new_bugs':             
			$query .= "&types=BUG";           break;
		
		case 'code_smells':
		case 'new_code_smells':      
			$query .= "&types=CODE_SMELL";    break;
	}
	
	
	// SEVERITY
	switch ($metricKey) {
		case 'blocker_violations':
		case 'new_blocker_violations':
			$query .= "&severities=BLOCKER";                              break;
		case 'critical_violations':
		case 'new_critical_violations':
			$query .= "&severities=CRITICAL";                             break;
		case 'major_violations':
		case 'new_major_violations':
			$query .= "&severities=MAJOR";                                break;
		case 'minor_violations':
		case 'new_minor_violations':
			$query .= "&severities=MINOR";                                break;
		case 'info_violations':
		case 'new_info_violations':
			$query .= "&severities=INFO";                                 break;
	}
	
	// LEAK_PERIOD
	switch ($metricKey) {
		case 'vulnerabilities':      
		case 'bugs':                 
		case 'code_smells':
		
		case 'violations':
		case 'blocker_violations':
		case 'critical_violations':
		case 'major_violations':
		case 'minor_violations':
		case 'info_violations':
		
		
		
			$query .= "&sinceLeakPeriod=${sinceLeakPeriod}";              break;
		
		// las new son siempre desde el periodo de fuga
		case 'new_vulnerabilities':
		case 'new_bugs':  
		case 'new_code_smells':
		
		case 'new_violations':
		case 'new_blocker_violations':
		case 'new_critical_violations':
		case 'new_major_violations':
		case 'new_minor_violations':
		case 'new_info_violations':
		
			$query .= "&sinceLeakPeriod=true";                            break;
	}
	
	$url = "${sonar_host}/${method}?${query}";
	
	
	return html_link($url, $value);	
}


function html_link($url, $value){
	return "<a href='$url'>$value</a>";
}


function helper_render_metric_type($metricKey){
	global $metrics_data;
	return $metrics_data["metrics"][$metricKey]->type;
}

function helper_render_measure($measure){
	global $metrics_data;
	
	
	//debug ($measure, 'helper_render_measure');
	
	if(isset($measure["key"])) {
	
		$metricKey=$measure["key"];
		
		if(exist_component_measure($metricKey, "value", $value)) {
			
			$metric = $metrics_data["metrics"][$metricKey];
			$type   = $metric->type;
			$result = renderValue($type, $value);
			
			return $result;

		} 
	}
	
	return "-";
}


function helper_measure_direction($measure){
	// devuelve la direccion de una métrica. Debe ser una métrica qualitativa. 1 significa ascendente, -1 significa descendente.
	global $metrics_data;
	
	$result = "";
	
	if(isset($measure["key"])) {
	
		$metricKey=$measure["key"];
		
		if(exist_component_measure($metricKey, "value", $value)) {
			
			$metric      = $metrics_data["metrics"][$metricKey];
			$qualitative = $metric->qualitative;
			$direction   = $metric->direction;
			
			if ($qualitative) {
				$result = $direction;
			} 
		} 
	}
	
	return $result;
}

function helper_measure_pct_non_leak($measure){
	// devuelve el porcentaje de esa metrica respecto al total, no asociado al periodo de fuga
	global $metrics_data;
	$result = "";
	
	if(isset($measure["key"])) {
		$metricKey=$measure["key"];
		
		if(! preg_match('/^new_/', $metricKey)){
			// debug($metricKey, "helper_measure_pct_non_leak. no empieza por new_ - devuelvo 0");	
			return "0";
		}
		
		
		$nonLeakMetricKey=ltrim($metricKey, 'new_');
		
		// debug($nonLeakMetricKey, "helper_measure_pct_non_leak");	
		
		if(exist_component_measure($metricKey, "value", $leakValue)) {
			
			if(exist_component_measure($nonLeakMetricKey, "value", $nonLeakValue)) {
				
				// debug($leakValue, "helper_measure_pct_non_leak. value SI leak");	
				// debug($nonLeakValue, "helper_measure_pct_non_leak. value NO leak");	
				
				if ($nonLeakValue == 0 ) {
					$result = 0;
				} else {
					$result = 100 * ($nonLeakValue - $leakValue) / $nonLeakValue;
				}
			}
		} 
	}
	
	return $result;
}








function helper_render_condition($metricKey, $property){
	global $metrics_data;
	
		
	
	
	
	if($property == "type"){
		return helper_render_metric_type($metricKey);
	} elseif(exist_project_condition($metricKey, $property, $value) || $property == "warningThreshold" || $property == "errorThreshold") {
	
		// if($metricKey == "new_maintainability_rating"){
			// debug("helper_render_condition $metricKey - $value");
			// debug(renderValue($metrics_data["metrics"][$metricKey]->type, $value), "$metricKey $property ".$metrics_data['metrics'][$metricKey]->type);
		// }
		
		// if($metricKey == "critical_violations") {
			// console_log($metricKey, "helper_render_condition - metric");
			// console_log($value, "helper_render_condition - value");
		// }
		
		
		
		$metric = $metrics_data["metrics"][$metricKey];
		$type   = $metric->type;
		
		switch ($property) {
			case 'actualValue':      
			case 'errorThreshold': 
			case 'warningThreshold':
			
			
				// if($metricKey == "new_coverage") {
					// debug($metricKey, "helper_render_condition - metric");
					// debug($property, "helper_render_condition - property");
					// debug($value, "helper_render_condition - value");
				// }
			
			
			// console_log($metricKey, "helper_render_condition - metric");
			// console_log($value, "helper_render_condition - value");
		
			
			
				$result = renderValue($type, $value);
				break;
			case "status":
				$result = helper_img_status_small($value);
				break;
			default:        
				$result = $value;
		}
		
		return $result;
	
	} else {
		return "-";
	}
}

// visualiza el valor de la metrica dependiendo de su tipo
function renderValue($type, $value){
	switch ($type) {
			case "INT":
				$result = helper_format_int($value);
				break;
			case "RATING":
				$result = helper_img_rating_small($value);
				break;
			case "PERCENT":
				$result = helper_format_pct($value);
				break;
			case "WORK_DUR":
				$result = helper_format_work_dur($value);
				break;
				
				
			default:        
				$result = "($type) $value";
		}
	return $result;	
}


function exist_project_condition($metricKey, $property, &$value){
	global $data;
	if (isset($data["projectStatus"])){
		if (isset($data["projectStatus"]["conditions"])){
			if (isset($data["projectStatus"]["conditions"][$metricKey])){
				if (isset($data["projectStatus"]["conditions"][$metricKey][$property])){
					
					$value = $data["projectStatus"]["conditions"][$metricKey][$property];

					// if($metricKey == "critical_violations") {
						// console_log($metricKey, "exist_project_condition - metric");
						// console_log($value, "exist_project_condition - value");
					// }
					
					return true;
				}
			}
		}
	}
	return false;
}

function exist_component_measure($metricKey, $property, &$value){
	global $data;
	if (isset($data["component"])){
		if (isset($data["component"]["measures"])){
			
			// debug($metricKey, "metricKey = $metricKey");
			if (isset($data["component"]["measures"][$metricKey])){
				if (isset($data["component"]["measures"][$metricKey][$property])){
					$value = $data["component"]["measures"][$metricKey][$property];
					return true;
				}
			}
		}
	}
	return false;
}

// helper para chequear si idioma es Java, si no lo es muestra no se aplica 

function checkLanguage($value) {
	  return $value ." esto es una prueba de filtro";
  }


/* FIN FUNCIONES PARA HELPERS ------------------------------------------------*/


  


  
 
  // sudamos de la prueba de la clase persona y vamos a abrir el json a ver que pasa
    $metricas = fopen('data.json','r');
    $data = fread($metricas, filesize('data.json'));
    fclose($metricas);
    $data = json_decode($data, true);
    //var_dump($data);

    echo $mustache->render('plantilla',$data);
?>
