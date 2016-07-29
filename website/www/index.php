<?php
error_reporting(0);

set_include_path(get_include_path() . PATH_SEPARATOR
	. '/library' . PATH_SEPARATOR
	. '../templates/'
);

require_once 'Fwok/Word/Syllabler/Spanish.php';

//Vars
$result = '';
$word = '';


//Getting word information if it was passed in the request
if(isset($_GET['word'])) {
	
	$word = utf8_decode($_GET['word']);
    
    //Example configuration
    Fwok_Word_Syllabler_Spanish::setSpain(true);
    Fwok_Word_Syllabler_Spanish::setTl(false);
    Fwok_Word_Syllabler_Spanish::setIgnorePrefix(true); //The all prefixes isn't supported by the moment
    //Fwok_Word_Syllabler_Spanish::setLogger($logger); //Logger
    $w = new Fwok_Word_Syllabler_Spanish($word);

    $result = array(
	    'word'            => utf8_encode($word),
	    'raeUrl'      => 'http://dle.rae.es/' . urlencode($word),
	    'syllables'       => $w->getSyllables(),
	    'stressed'        => $w->getStressedSyllable(),
	    'stressedType'    => $w->getStressedType(),
	    'stressedLetter'  => $w->getStressedLetter(),
	    'numSyllables'    => $w->getNumberOfSyllables(),
	    'hasTl'              => ((bool)$w->hasTl()?'true':'false'),
	    'hasPrefix'       => ((bool)$w->hasPrefix()?'true':'false')
    );
}

if(isset($_GET['json']) && ((bool) $_GET['json']) !== false) {
	header("Content-Type: application/json");
	//Types of words
    print(json_encode($result));
} else {
	//Result for html
	//Types of words
    $typeStressed = array('Aguda', 'Llana', 'Esdrújula', 'Sobre-Esdrújula', 'Ante-Esdrújula');
    
	
	
	header("Content-Type: text/html; charset=UTF-8");
	include("index.phtml");
}