<?php

iconv_set_encoding("internal_encoding", "ISO-8859-1");
iconv_set_encoding("input_encoding", "ISO-8859-1");
iconv_set_encoding("output_encoding", "ISO-8859-1");

header("Content-Type: text/html;charset=ISO-8859-1");

echo '<meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1" />';

//La idea de todo es hacerlo sin regexp por que son bastante lentas como para procesar un fichero
//con 88000 palabras...

//NOTA: Cambiar la última letra si es una "y" por "i", ya que cuando la
//      "i" griega va al final funciona como vocal
//      Tomar las posiciones de la tónica y de donde empieza y acaba cada
//		sílaba para luego hacer la división sobre la palabra original

//TENER EN CUENTA QUE LA FUNCIÓN STRTOLOWER DE PHP NO PONE EN MINÚSCULAS LAS PALABRAS ACENTUADAS

/**
 * Función para comprobar si una vocal es fuerte o no
 *
 * Function to check if one vowel is strong or not
 *
 * @param char $letter
 * @return bool
 */

function is_strong_vowel($letter) {
	$letter = strtolower($letter);
	
	return in_array($letter, array('a','e','o', 'á', 'é', 'ó', 'í', 'ú'), true);
}

/**
 * Función para comprobar si una letra es una vocal débil
 *
 * Function to check if a letter is a weak vowel or not
 *
 * @param char $letter
 * @return bool
 */
function is_weak_vowel($letter) {
	$letter = strtolower($letter);
	
	return in_array($letter, array('i', 'u', 'ü'), true);
}

/**
 * Función para comprobar si hay un hiato entre dos letras
 *
 * Function to check if it is an hiatus
 *
 * @param string $letters 2 letters
 * @return bool true if it is an hiatus, false if not
 */
function is_hiatus($letters) {
	$letters = strtolower($letters);
	
	return is_strong_vowel($letters[0]) and is_strong_vowel($letters[1]);
}

/**
 * Función para ver si una letra es una vocal o consonante
 *
 * Function to watch if a letter is a vowel or not
 *
 * @param char $letter
 * @return bool
 */
function is_vowel($letter) {
	$letter = strtolower($letter);
	
	if(is_strong_vowel($letter) or is_weak_vowel($letter))
		return true;
	else
		return false;
}

/**
 * Función para comprobar que una letra sea una consonante
 * el guión lo tratará como una consonante.
 *
 * Function to check if one letter is a consonant
 *
 * @param char $letter
 * @return bool
 */
function is_consonant($letter) {
	$letter = strtolower(trim($letter));
	
	$consonants = 'bcdfghjklmnñpqrstvwxyz-';
	
	if($letter !== '' and strpos($consonants, $letter) !== false)
		return true;
	else
		return false;
}
 

/**
 * Función para comprobar si hay un triptongo
 *
 * Function to check if it is an triphthong
 *
 * @param string 3 letters
 * @return bool true if it is a tripthonge, false if not, or -1 if the param isn't correct
 */
function is_triphthong($letters) {
	$letters = strtolower($letters);
	
	return is_weak_vowel($letters[0]) and 
				(is_strong_vowel($letters[1]) and $letters[1] !== 'í' and $letters[1] !== 'ú') and
				is_weak_vowel($letter[2]);
}

/**
 * Función para comprobar si es una doble consonante de las excepciones
 *
 * Function to check if it is a double consonant of exceptions
 *
 * @param string 2 letters
 * @return bool
 */
function is_a_double_consonant_exceptions($letters) {
	$letters = strtolower($letters);
	
	if(is_vowel($letters[0]) || is_vowel($letters[1]))
		return false;
	
	if(in_array($letters, array('bl', 'cl', 'fl', 'gl', 'kl', 'pl', 'll',
								'br', 'cr', 'dr', 'gr', 'kr', 'pr', 'tr', 'rr')))  //tl separated
																					//in Spain
		return true;
	
	return false;
}

/**
 * Función para comprobar si una palabra tiene acento
 *
 * Function to check if one word have an accent
 *
 * @param char $letter
 * @return bool
 */
function has_accent($letter) {
	$letter = strtolower($letter);
	
	if(in_array($letter, array('á', 'é', 'í', 'ó', 'ú'), true))
		return true;
	
	return false;
}

/**
 * Función para la división en sílabas
 *
 * Function to de division
 *
 * @param string $word
 * @return Array Array( 'vowels'       => int number of vowels
 *						'consonants'   => int number of consonats
 *						'syllables'    => int number of syllables
 *						'stressed'     => int number of letter which is stressed
 *						'stressed_syl' => int number of syllable which is stressed from left to rigth
 *						'division'     => Array(int number of letter start first syllable,
 *												int number of letter start the second syllable,
 *												...
 *												this array is ready to use with substr
 *										       )
 *					  )
 */
function word2divisions($word) {
	$syllables                 = array();
	$total_syllables           = 0;
	$divisions                 = array(0);
	$stressed                  = 0; //0 "aguda", 1 "llana", 2 "esdrújula", 3 "sobresdrújula" 
	$stressed_syl              = 0;
	
	$vowels                    = 0;
	$consonants                = 0;
	
	$vowelsPositions           = array();
	$consonantsPositions       = array();
	
	$c1                        = '';
	$c2                        = '';
	$c3                        = '';
	$c4                        = '';
	$c5                        = '';
	
	$word                      = strtolower(trim($word));
	$original_word             = $word;
	$len                       = strlen($word);
	
	$i                         =  0;
	
	$qu_pos = 0;
	$gu_pos = false;
	
	//Next var is for exceptions of prefix sub followed by "l" or "r"
	$has_posible_divisible_exception = false;
	
	//Firs exceptions "truhán" and "guión"
	if($word == 'truhán') {
		return array( 	'syllables'    => 2,
						'stressed'     => 5,
						'stressed_syl' => 2,
						'divisions'     => array(0,3,6)
					);
	} else if ($word == 'guión') {
		return array(	'syllables'	   => 2,
						'stressed'	   => 4,
						'stressed_syl' => 2,
						'divisions'	   => array(0,3,5)
					);
	}
	
	//if the last word is an "y" we change it to "i" because it run like one
	if($word[$len-1] == 'y')
		$word[$len-1] = 'i';
	
	
	while($i < $len) {
		$c1 = $c2 = $c3 = $c4 = '';
		$c1  = $word[$i];
		$c2  = $word[$i+1];
		$c3  = $word[$i+2];
		$c4  = $word[$i+3];
		
		echo "+----------------------------------------------------+\n";
		echo "Hasta el ciclo $i, los valores de las divisiones son:\n";
		echo "Las letras para este ciclo son: $c1 $c2 $c3 $c4\n\n\n";
		var_dump($divisions);
		
		//If the word begin with consonant we check from 2nd letter
		/*
		if($i === 0 and is_consonant($c1)) {
			++$i;
			continue;
		}
		//*/
		
		//Now we do all checks here without levels of if's
		//Consonant + Consonant + Consonant + Consonant
		if(is_consonant($c1) and is_consonant($c2) and is_consonant($c3) and is_consonant($c4)) {
			echo "Ciclo $i: Cuatro consonantes seguidas\n";
			echo "Valores:\n\t$c1 $c2 $c3 $c4\n";
			echo "I: $i\n";
			echo "Palabra: $word\n\n";
			array_push($divisions, ($i+=2));
			continue;
		
		//Consonant + Consonant + Consonant + Vowel
		} else if(is_consonant($c1) and is_consonant($c2) and is_consonant($c3) and is_vowel($c4)) {
			echo "Ciclo $i: Se cumple consonante + consonante + consonante\nValores:\n\t$c1 $c2 $c3\nI: $i\nPalabra: $word\n\n";
			if($c3 === 'l' or $c3 === 'r') {
				echo "Ciclo $i: Tercera consonante r o l\nValores:\n\t$c1 $c2 $c3\nI: $i\nPalabra: $word\n\n";
				array_push($divisions, ($i+1));
				$i += 3;
				continue;
			} else {
				$divisions [] = ($i+=2);
				continue;
			}
			
		//Consonant + Consonant + Vowel
		} else if(is_consonant($c1) and is_consonant($c2) and is_vowel($c3)) {
			echo "Ciclo $i: Se cumple consonante + consonante\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  	if(is_a_double_consonant_exceptions($c1.$c2)) {
		  		echo "Ciclo $i: Se cumple doble consonante del grupo de excepciones\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  		array_push($divisions, $i);
		  		$i += 2;
		  		continue;
		  	} else {
		  		array_push($divisions, ($i+1));
		  		$i += 2; //If we don't do this we will have a problems with
		  				 //the coincidence of consonant + vowel
		  		continue;
		  	}
		  	
		//Consonant + Vowel
		} else if(is_consonant($c1) and is_vowel($c2)) {
			echo "Ciclo $i: Se cumple consonante + vocal\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  	//First check if it is qu cause it's indivisible
		  	//Secondly check if it is gue or gui
		  	if(($c1 === 'q' or $c1 === 'g') and $c2 === 'u') {
		  		echo "Ciclo $i: 'qu' o 'gu'\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  		//Then continue without 2 first letters of this syllable
		  		$i += 2;
		  		continue;
		  	} else if(count($divisions) > 1 or $i > 2) { //Consonant + Vowel
		  			//We must to count syllables because if the first letter it is an consonant
		  			//we continue with the loop then only divide this if it isn't the first
		  		array_push($divisions, $i);
		  		++$i;
		  		continue;
		  	}//*/
		//Vowel + Vowel
		} else if(is_vowel($c1) and is_vowel($c2)) {
			//Before continue we going to check if vowels in $c1, $c2 or $c3 have accent
			if(has_accent($c1))
				$stressed = $i;
			else if(has_accent($c2))
				$stressed = $i+1;
			else if(has_accent($c3))
				$stressed = $i+2;
			
			echo "Ciclo $i: Se cumple vocal + vocal\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  	if(is_hiatus($c1.$c2)) {
		  		echo "Ciclo $i: Hiato entre la 1 y 2 \nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  		array_push($divisions, (++$i));
		  		continue;
		  	} else if(is_vowel($c3) and is_hiatus($c2.$c3)) {
		  		echo "Ciclo $i: Hiato entre la 2 y 3\nValores:\n\t$c1 $c2 $c3\nI: $i\nPalabra: $word\n\n";
		  	  	array_push($divisions, ($i+=2));
		  		continue;
		  	} else if(is_vowel($c3)) { //Then it is a triphthong
		  		//Triphthong has always accent in the 2nd syllable
		  		$stressed = $i+2;
		  		
		  		echo "Ciclo $i: Se cumple triple vocal (triptongo).\nValores:\n\t$c1 $c2 $c3\nI: $i\nPalabra: $word\n\n";
		  		$i += 3;
		  		continue;
		  	} else { //Then it is a diphthong with 1 consonant after so we must check the consonants
		  		echo "Ciclo $i: Se cumple diptongo + consonante\nValores:\n\t$c1 $c2 $c3\nI: $i\nPalabra: $word\n\n";
		  		$i += 2;
		  		continue;
		  	}

		//Vowel + Consonant + Vowel
		} else if(is_vowel($c1) and is_consonant($c2) and is_vowel($c3)) {
			//Before continue we going to check if vowel in $c1 has accent
			if(has_accent($c1))
				$stressed = $i;
			echo "Ciclo $i: Se cumple vocal + consonante + vocal\nValores:\n\t$c1 $c2 $c3 $c4 $c5\nI: $i\nPalabra: $word\n\n";
			echo "EOOOOOOOOOOOO  AQUI\n\n\n\n\n";
			array_push($divisions, ($i+1));
			$i +=2;
			continue;
		
		//Vowel + Consonant + Consonant will check in next cycle
		} else {
			//Before continue we going to check if vowel in $c1 has accent
			if(has_accent($c1))
				$stressed = $i;
			
			echo "Ciclo $i: No se cumple ninguna condición\nValores:\n\t$c1 $c2 $c3 $c4\nI: $i\nPalabra: $word\n\n";
		}
		
		++$i;
	} //End Loop
	
	//The last división is the number of the last letter so
	array_push($divisions, strlen($word));
	
	//Total of syllables
	$total_syllables = count($divisions)-1; //We have to discount 0 and the last letter
	
	//Now we get the syllables from original word
	for($i=0; $i <= $total_syllables; $i++)
		$syllables [] = substr($word, $divisions[$i], $divisions[$i+1]-$divisions[$i]);
	
	//Now we check the stressed syllable
	if($stressed > 0) {
	    echo "\nLA PALABRA TIENE UN ACENTO EN LA LETRA $stressed las sílabas son $total_syllables"
	    	 . "($stressed_syl)\n\n";
	    
	    for($i=0; $i<=$total_syllables; $i++) {
	    	if($stressed >= $divisions[$i] and $stressed < $divisions[$i+1]) {
	    		$stressed_syl = $total_syllables - $i;
	    		break;
	    	}
	    }
	    
	    echo "Último i para el acento $i entre " . $divisions[$i] . " y " . $divisions[$i+1] . "\n\n";
	} else {
		$lastLetter = $word[$len-1];
		
	    echo "Última letra: ($lastLetter)\n\n";
		
		if(	$total_syllables === 1 or in_array($lastLetter, array('n', 's'), true)
			or is_vowel($lastLetter)) $stressed_syl = 2; //Llana por que no lleva acento
		else $stressed_syl = 1; //Agudo por que no lleva acento
        
        //Now we going to get the stressed letter
        //First we get the syllable
        $startDivision  = $divisions[$total_syllables - $stressed_syl];
        $finishDivision = $divisions[$total_syllables - $stressed_syl + 1];
        $syl            = substr($word, $startDivision, $finishDivision);
        $syl_len        = strlen($syl);
        
        echo "Sílaba a analizar en busca de la tónica: $syl [$startDivision - $finishDivision]\n\n";
        
        //Now we going to check the syllable, We must know that there isn't any syllable with more
        //than 4 vowels
        for($i=0; $i<$syl_len; $i++) {
        	$c1 = $c2 = $c3 = $c4 = '';
        	
        	$c1 = $syl[$i];
        	$c2 = $syl[$i+1];
        	$c3 = $syl[$i+2];
        	$c4 = $syl[$i+3];
        	
        	if(is_consonant($c1)) continue;
        	
        	echo "¿Será \"$c1\" la letra tónica ($c2 $c3 $c4)?\n";
        	
        	if( ($c1 === 'q' or $c1 === 'g') and $c2 === 'u' and ($c3 === 'e' or $c3 === 'i') ) {
        		++$i; //Because automatic increment in each cycle and we only want
        			  //to increment in 2 the counter
        		continue;
        		
        	} else if(is_triphthong($c1.$c2.$c3)) {
        		echo "\n\n---TRIPTONGO ($c1$c2$c3)---\n\n";
        		$stressed = $startDivision + $i + 1;
        		break;
        	
        	} else if(!is_hiatus($c1.$c2)) {
        		echo "\n\n---DIPTONGO ($c1$c2)---\n\n";
        		if(is_strong_vowel($c1)) $stressed = $startDivision + $i + 1;
        		else $stressed = $startDivision + $i + 2;
        		
        		break;
        	
        	} else if(is_vowel($c1)) {
        		echo "\n\n---VOCAL ($c1)---\n\n";
        		$stressed = $startDivision + $i + 1;
        		break;
        	}
        }
    }


	//There we must check the prefix subr and subl but we do it later
	$prefix = substr($word, 0, 4);
	
	echo "Prefijo: $prefix\n";
	
	
	if($prefix === 'subr' or $prefix === 'subl') {
		//Now we going to check if it is "llana" or have more than 3 syllables
		//Really I don't know if this is correct to check if sub is joined or not
		if($stressed_syl === 1 or $total_syllables > 3) {
			$divisions[1] +=1;
		}
	}
	
	echo PHP_EOL . PHP_EOL . "Letra con el acento prosódico $stressed" . PHP_EOL . PHP_EOL;
	echo PHP_EOL . PHP_EOL . "Sílaba tónica $stressed_syl" . PHP_EOL . PHP_EOL;
	
	//Wich one is the stressed syllable
					
	
	return array(	'syllables'	   => $total_syllables,
					'stressed'	   => $stressed,
					'stressed_syl' => $stressed_syl,
					'divisions'	   => $divisions
				);
} //End Function

/**
 * Función para devolver la palabra dividida correctamente usando el separador que se indique como
 * tercer parámetro.
 *
 * Function to return the divided word with the separator 3rd in param
 *
 * @param string $word
 * @param string separator
 * @param string $stressedFilter stressed function to filter (see function boldStressed to watch the arguments)
 * @return string
 */
function syllabled_word($word, $separator = ' - ', $stressedFilter = '') {
	$currentSyllable = ''; //For loop
	$syllabledWord = ''; //Return var
	$i             = 0;  //Counter
	
	//It it's a word with one "-"
	if(strpos('-', $word)) {
	    $word = str_replace('-', ' ', $word);
		list($word1, $word) = explode(' ', $word);
		$syllabledWord = divisions2syllableword($word1, $separator, $stressedFilter) . $separator;
	
	} else extract(word2divisions($word));

	
	for($i=0; $i < $syllables; $i++) {
		$currentSyllable = substr($word, $divisions[$i], $divisions[$i+1]-$divisions[$i]);
		
		if(($syllables - $stressed_syl) === $i)
			$currentSyllable = $stressedFilter($word, $currentSyllable, $stressed);
		
		$syllabledWord .= $currentSyllable;
	
		if($i < count($divisions)-2)
			$syllabledWord .= $separator;
	}
	
	return $syllabledWord;
}

/**
 * Función para marcar en negrita la sílaba tónica
 *
 * Function to sign the stressed syllable
 *
 * @param string $word
 * @param string $syllable
 * @param int $letter who have stressed
 * @return string bold stressed syllable
 */
function boldStressedSyllable($word, $syllable, $letter) {
	return "<strong>$syllable</strong>";
}

/**
 * Función para hacer la transcripción fonética de una palabra
 *
 * Function to do the fonetic transcription of one word
 * @param string $word
 * @param array returned value of word2division
 * @param int $option Number of type of transcription
 *               0: default with " ' " after stressed word
 *				 1: with double vowel in stressed vowel
 *				 2: with spanish acccent
 * @param string $separator between phonemes
 * @return string without slashes
 */
function word2phonemes($word, Array $word2division, $option = 0, $separator = ' ') {
	//Las comentadas son reglas especiales que hay que tratarlas a parte bien por que sean
	//una excepción o bien por que hay que añadir la separación y ponerlo chulo xD
	
	/*
	Tener en cuenta:
		* Varios alfabetos de transcripción: AFI, RFE, DEFSFE, SAMPA, SALA, WORLDBET, VIA
		* Seseo - Andalucía e iberoamérica
		* Ceceo - Andalucía
		* Yeísmo
		* La équis en palabras como mexico se pronuncia "j"
		* Alófonos vocales nasales
		* Consonantismo:	Yeísmo
		* Realización de archifonemas /B/ /D/ /G/:EnfáticaCuidadaFamiliarVulgar
		* tl peninsular t-l, guatemala tl
		* Convertir números a letra?
		* Números Romanos?
		* Contexto para medidas?
		* Hay mil excepciones en la transcripción sobre todo con alófonos
		
		Lo mejor que encontré fue: http://www.respublicae.net/lengua/silabas/index.php
	*/
	$phonemes = array(
						'a' => 'a',
						'b' => 'b',
						'ca' => 'k' . $separator . 'a',
						'co' => 'k' . $separator . 'o',
						'cu' => 'k' . $separator . 'u',
						'c' => 'th', //Puede sonar como "s"
						'd' => 'd',
						'e' => 'e',
						'f' => 'f',
						//'gi'=> 'xi',
						//'ge' => 'xe',
						'g' => 'g',
						'h' => '',
						'i' => 'i',
						'j' => 'j',
						'k' => 'k',
						//'ll' => 'll'
						'l' => 'l',
						'm' => 'm',
						'n' => 'n',
						'ñ' => 'ny',
						'o' => 'o',
						'p' => 'p',
						'q' => 'k',
						'rr' => 'rr',
						//'r' => 'r', //Requiere una regla especial ya que al principio
									  //de palabra sería rr y en medio r
						's' => 's', //Puede sonar como "z"
						't' => 't',
						'u' => 'u',
						'ü' => 'u',
						'v' => 'b',
						'w' => 'u', //realmente sería "b" pero nadie dice /biski/ (whisky)
						'x' => 'ks',
						//'y' => 'll' o 'i' una excepción muy clara
						'z' => 'th' //Puede sonar como "s"
						);
		
		$vowels_with_accent = array('á','é','í','ó','ú');
		
		$option1 = array(
						 'á' => 'aa',
						 'é' => 'ee',
						 'í' => 'ii',
						 'ó' => 'oo',
						 'ú' => 'uu'
						);
	
	extract($word2division);
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
	<input type="text" name="word" />
	<BUTTON name="submit" value="submit" type="submit">Silabizar</BUTTON>
</form>';

echo "<pre>\nCodificación (Importante en la detección de acentos):\n";
var_export(iconv_get_encoding('all'));
echo "</pre>\n";

$word = trim(strtolower($_POST['word']));

if(!empty($word)) {
    echo "<pre>";
    echo "\n\nNo cuenta aún con soporte para los adverbios acabados en mente en el sentido de";
    echo " que su sílaba tónica se mantiene. Así que pueden darse errores en este aspecto.\n\n";
    echo "\n\nDescribiendo el análisis (con fines de debug):\n\n";
	$syllableword = syllabled_word($word, '-', 'boldStressedSyllable');
	echo "</pre>";
	
	//*
	echo "<pre>". PHP_EOL . PHP_EOL;
	printf('La palabra es: %s', $word);
	echo PHP_EOL . PHP_EOL;
	echo 'La función devuelve: ';
	echo var_export($syllableword);
	echo PHP_EOL . PHP_EOL;
	echo $syllableword;
	echo PHP_EOL . '</pre>' . PHP_EOL;
	//*/
}
	



//$word = iconv(iconv_get_encoding('input_encoding'), 'utf-8', $argv[1]);

/*
$word = 'uno';

echo PHP_EOL . PHP_EOL;
echo PHP_EOL . PHP_EOL;
echo "Empezamos con la palabra: $word";
echo PHP_EOL;
echo '+++++++++++++++++++++++++++++++' . PHP_EOL;


//*
//$word = 'ciguena';

$divisions = word2syllables($word);

var_dump($divisions);

for($i=0; $i < count($divisions)-1; $i++) {
	echo substr($word, $divisions[$i], $divisions[$i+1]-$divisions[$i]);
	
	if($i < count($divisions)-2)
		echo '-';
}


echo PHP_EOL . PHP_EOL;
//*/