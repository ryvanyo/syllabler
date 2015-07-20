<?php

//Setting the library path
set_include_path(get_include_path() . PATH_SEPARATOR . '../library');


header("Content-Type: text/html;charset=UTF-8");

echo "<html>\n";
echo " <header>\n";
echo "  <title>Syllabler 0.1</title>\n";
echo "  <meta http-equiv=\"Content-type\" content=\"text/html; charset=UTF-8\" />\n";
echo " </header>\n\n";
echo " <body>\n";

require_once 'Fwok/Word/Syllabler/Spanish.php';

// Logger, only use it if you have a Zend Framework
/*
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Mock.php';
require_once 'Zend/Log/Formatter/Simple.php';
require_once 'Zend/Log/Filter/Priority.php';

$logger = new Zend_Log($writer = new Zend_Log_Writer_Mock());
$logger -> log('Logger created (' . time() . ')', 7);

$formatter = new Zend_Log_Formatter_Simple("%priorityName%: %message%\n");

$writer->setFormatter($formatter);
//*/

echo "<h1>Syllabler (beta)</h1>";
echo "\n\n";

/**
 * Ejemplo de uso
 */
 
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="get" enctype="multipart/form-data" accept-charset="UTF-8">
	<input type="text" name="word" value="' . (isset($_GET['word'])?$_GET['word']:'') .'" />
	<BUTTON name="submit" value="submit" type="submit">Silabizar</BUTTON>
</form>';

echo "<p>Errores Conocidos:</p>\n";
echo "<p>El soporte para palabras con guiones, que debe ser añadido";
echo " externamente a la clase no está añadido.</p>\n"
echo "<p>Los prefijos pueden dar lugar a errores por el momento.</p>\n";
echo "<p>Aunque el algoritmo si lo soporta no está implementado el";
echo " soporte para \"tl\" u otras configuraciones. Solo funciona en modalidad";
echo " Castellano (Español de España y con los errores mencionados en la parte";
echo " superior.</p>";

if($_REQUEST['word']) {
    $word = $_REQUEST['word'];
    
    //Example configuration
    Fwok_Word_Syllabler_Spanish::setSpain(true);
    Fwok_Word_Syllabler_Spanish::setTl(false);
    Fwok_Word_Syllabler_Spanish::setIgnorePrefix(true); //The all prefixes isn't supported by the moment
    //Fwok_Word_Syllabler_Spanish::setLogger($logger); //Logger
    $w = new Fwok_Word_Syllabler_Spanish($word);
    
    
    //Types of words
    $typeStressed = array('Aguda', 'Llana', 'Esdrújula', 'Sobre-Esdrújula', 'Ante-Esdrújula');
    //*
    echo "<pre>Sílabas: ";
    var_dump(($w->getSyllables()));
    echo "\n";
    echo "Sílaba tónica: ";
    var_dump($w->getStressedSyllable());
    echo "\n";
    echo "Tipo: " . $typeStressed[$w->getStressedType()] . "\n";
    echo "La letra tónica es: " . mb_substr($word, $w->getStressedLetter(), 1) . "\n";
    echo "Número de sílabas: " . $w->getNumberOfSyllables() . "\n";
    echo "Tiene \"tl\"? " . ($w->hasTl()? 'Si':'No') . "\n";
    echo "Tiene algún prefijo? " . ($w->hasPrefix()? 'Si':'No') . "\n\n";
    echo "</pre>";
    //*/
}

//Printing the logger
/*
echo "<pre>\n";
foreach($writer->events as $v) {
    if($v['priority'] > 6) {
        if(is_array($v['message'])) {
            print_r($v['message']);
            echo"\n";
        } else {
            echo $v['message'];
            echo "\n";
        }
    }
}
echo "\n</pre>";

//*/

echo "\n";
echo " </body>\n";
echo "</html>";
