#!/usr/bin/php
<?php

//Setting the library path
set_include_path(get_include_path() . PATH_SEPARATOR . '../library');

//Library
require_once 'Fwok/Word/Syllabler/Spanish.php';

if(!empty($argv[1])) {
    $word = $argv[1];
    
    //Example configuration
    Fwok_Word_Syllabler_Spanish::setSpain(true);
    Fwok_Word_Syllabler_Spanish::setTl(false);
    Fwok_Word_Syllabler_Spanish::setIgnorePrefix(true); //The all prefixes isn't supported by the moment
    //Fwok_Word_Syllabler_Spanish::setLogger($logger); //Logger
    $w = new Fwok_Word_Syllabler_Spanish($word);
    
    
    //Types of words
    $typeStressed = array('Aguda', 'Llana', 'Esdrújula', 'Sobre-Esdrújula', 'Ante-Esdrújula');
    
    //Result of all
    //*
    echo "Sílabas: ";
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
    //*/
} else {
    echo "\nHELP\n\tHow to use:\n\t\t/usr/bin/php console.php spanish_word\n\n";
}
