<?php
	
iconv_set_encoding("internal_encoding", "utf8");
iconv_set_encoding("input_encoding", "utf8");
iconv_set_encoding("output_encoding", "utf8");

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
								'br', 'cr', 'dr', 'gr', 'kr', 'pr', 'tr', 'rr',
								'ch')))  //tl separated
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
		
		//Now we do all checks here without levels of if's
		//Consonant + Consonant + Consonant + Consonant
		if(is_consonant($c1) and is_consonant($c2) and is_consonant($c3) and is_consonant($c4)) {
			if(!in_array(($i+2),$divisions))
			    array_push($divisions, ($i+=2));
			continue;
		
		//Consonant + Consonant + Consonant + Vowel
		} else if(is_consonant($c1) and is_consonant($c2) and is_consonant($c3) and is_vowel($c4)) {
			if($c3 === 'l' or $c3 === 'r') {
				if(!in_array(($i+1),$divisions))
			        array_push($divisions, ($i+1));
				$i += 3;
				continue;
			} else {
				if(!in_array(($i+2),$divisions))
			        $divisions [] = ($i+=2);
				continue;
			}
			
		//Consonant + Consonant + Vowel
		} else if(is_consonant($c1) and is_consonant($c2) and is_vowel($c3)) {
		  	if(is_a_double_consonant_exceptions($c1.$c2)) {
		  		if(!in_array($i,$divisions))
			        array_push($divisions, $i);
		  		$i += 2;
		  		continue;
		  	} else {
		  		if(!in_array(($i+1),$divisions))
			        array_push($divisions, ($i+1));
		  		$i += 2; //If we don't do this we will have a problems with
		  				 //the coincidence of consonant + vowel
		  		continue;
		  	}
		  	
		//Consonant + Vowel
		} else if(is_consonant($c1) and is_vowel($c2)) {
		  	//First check if it is qu cause it's indivisible
		  	//Secondly check if it is gue or gui
		  	if(($c1 === 'q' or $c1 === 'g') and $c2 === 'u') {
		  		//Then continue without 2 first letters of this syllable
		  		$i += 2;
		  		continue;
		  	} else if(!in_array($i, $divisions)) { //Consonant + Vowel
		  			//We must to count syllables because if the first letter it is an consonant
		  			//we continue with the loop then only divide this if it isn't the first
		  		if(!in_array($i,$divisions))
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
			
		  	if(is_hiatus($c1.$c2)) {
		  		if(!in_array(($i+1),$divisions))
			        array_push($divisions, (++$i));
		  		continue;
		  	} else if(is_vowel($c3) and is_hiatus($c2.$c3)) {
		  	  	if(!in_array(($i+2),$divisions))
			        array_push($divisions, ($i+=2));
		  		continue;
		  	} else if(is_vowel($c3)) { //Then it is a triphthong
		  		//Triphthong has always accent in the 2nd syllable
		  		$stressed = $i+2;
		  		$i += 3;
		  		continue;
		  	} else { //Then it is a diphthong with 1 consonant after so we must check the consonants
		  		$i += 2;
		  		continue;
		  	}

		//Vowel + Consonant + Vowel
		} else if(is_vowel($c1) and is_consonant($c2) and is_vowel($c3)) {
			//Before continue we going to check if vowel in $c1 has accent
			if(has_accent($c1))
				$stressed = $i;
			if(!in_array(($i+1),$divisions))
			    array_push($divisions, ($i+1));
			$i +=2;
			continue;
		
		//Vowel + Consonant + Consonant will check in next cycle
		} else {
			//Before continue we going to check if vowel in $c1 has accent
			if(has_accent($c1))
				$stressed = $i;
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
	    for($i=0; $i<=$total_syllables; $i++) {
	    	if($stressed >= $divisions[$i] and $stressed < $divisions[$i+1]) {
	    		$stressed_syl = $total_syllables - $i;
	    		break;
	    	}
	    }
	} else {
		$lastLetter = $word[$len-1];
		
	    if(	$total_syllables === 1 or in_array($lastLetter, array('n', 's'), true)
			or is_vowel($lastLetter)) $stressed_syl = 2; //Llana por que no lleva acento
		else $stressed_syl = 1; //Agudo por que no lleva acento
        
        //Now we going to get the stressed letter
        //First we get the syllable
        $startDivision  = $divisions[$total_syllables - $stressed_syl];
        $finishDivision = $divisions[$total_syllables - $stressed_syl + 1];
        $syl            = substr($word, $startDivision, $finishDivision);
        $syl_len        = strlen($syl);
        
        //Now we going to check the syllable, We must know that there isn't any syllable with more
        //than 4 vowels
        for($i=0; $i<$syl_len; $i++) {
        	$c1 = $c2 = $c3 = $c4 = '';
        	
        	$c1 = $syl[$i];
        	$c2 = $syl[$i+1];
        	$c3 = $syl[$i+2];
        	$c4 = $syl[$i+3];
        	
        	if(is_consonant($c1)) continue;
        	
        	if( ($c1 === 'q' or $c1 === 'g') and $c2 === 'u' and ($c3 === 'e' or $c3 === 'i') ) {
        		++$i; //Because automatic increment in each cycle and we only want
        			  //to increment in 2 the counter
        		continue;
        		
        	} else if(is_triphthong($c1.$c2.$c3)) {
        		$stressed = $startDivision + $i + 1;
        		break;
        	
        	} else if(!is_hiatus($c1.$c2)) {
        		if(is_strong_vowel($c1)) $stressed = $startDivision + $i + 1;
        		else $stressed = $startDivision + $i + 2;
        		
        		break;
        	
        	} else if(is_vowel($c1)) {
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

$word = trim(strtolower(strip_tags($_GET['word'])));

if(!empty($word)) {
    echo json_encode($syllableword);
}
