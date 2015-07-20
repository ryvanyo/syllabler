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


/**
 * Función para comprobar si una vocal es fuerte o no
 *
 * Function to check if one vowel is strong or not
 *
 * @param char $letter
 * @return bool
 */

function is_strong_vowel($letter) {
	$letter = htmlentities(strtolower($letter));
	
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
	$letter = htmlentities(strtolower($letter));
	
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
		return array( 	'vowels'       => 2,
						'consonants'   => 4,
						'syllables'    => 2,
						'stressed'     => 5,
						'stressed_syl' => 2,
						'division'     => array(2)
					);
	} else if ($word == 'guión') {
		return array(	'vowels'       => 3,
						'consonants'   => 2,
						'syllables'	   => 2,
						'stressed'	   => 4,
						'stressed_syl' => 2,
						'division'	   => array(2)
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
		
		echo "Hasta el ciclo $i, los valores de las divisiones son:\n";
		var_dump($divisions);
		echo PHP_EOL;
		echo "Las letras para este ciclo son: $c1 $c2 $c3 $c4\n\n\n";
		
		//Now we do all checks here without levels of if's
		//Consonant + Consonant + Consonant + Consonant
		if(is_consonant($c1) and is_consonant($c2) and is_consonant($c3) and is_consonant($c4)) {
			echo "Ciclo $i: Cuatro consonantes seguidas\nValores:\n\t$c1 $c2 $c3 $c4\nI: $i\nPalabra: $word\n\n";
			array_push($divisions, ($i+=2));
			continue;
		
		//Consonant + Consonant + Consonant + Vowel
		} else if(is_consonant($c1) and is_consonant($c2) and is_consonant($c3)) {
			echo "Ciclo $i: Se cumple consonante + consonante + consonante\nValores:\n\t$c1 $c2 $c3\nI: $i\nPalabra: $word\n\n";
			if($c3 === 'l' or $c3 === 'r') {
				echo "Ciclo $i: Tercera consonante r o l\nValores:\n\t$c1 $c2 $c3\nI: $i\nPalabra: $word\n\n";
				array_push($divisions, (++$i));
				continue;
			} else {
				$divisions [] = ($i+=2);
				continue;
			}
			
		//Consonant + Consonant + Vowel
		} else if(is_consonant($c1) and is_consonant($c2)) {
			echo "Ciclo $i: Se cumple consonante + consonante\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  	if(is_a_double_consonant_exceptions($c1.$c2)) {
		  		echo "Ciclo $i: Se cumple doble consonante del grupo de excepciones\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  		array_push($divisions, $i);
		  		$i += 2;
		  		continue;
		  	} else {
		  		array_push($divisions, (++$i));
		  		continue;
		  	}
		  	
		//Consonant + Vowel
		} else if(is_consonant($c1)) {
			echo "Ciclo $i: Se cumple consonante + vocal\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  	//First check if it is qu cause it's indivisible
		  	//Secondly check if it is gue or gui
		  	if(($c1 === 'q' or $c1 === 'g') and $c2 === 'u') {
		  		echo "Ciclo $i: 'qu' o 'gu'\nValores:\n\t$c1 $c2\nI: $i\nPalabra: $word\n\n";
		  		//Then continue without 2 first letters of this syllable
		  		$i += 2;
		  		continue;
		  	} //else continue; really isn't necessay but I put it as coment for do it more understable
		
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
			array_push($divisions, (++$i));
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
	$total_syllables = count($divisions)-2; //We have to discount 0 and the last letter
	
	//Now we get the syllables from original word
	for($i=0; $i <= $total_syllables; $i++)
		$syllables [] = substr($word, $divisions[$i], $divisions[$i+1]-$divisions[$i]);
	
	//Now we check the stressed syllable
	if($stressed > 0) {
	   echo "\nLA PALABRA TIENE UN ACENTO EN LA LETRA $stressed\n\n";
	   
		//We check the number of division to know the syllable
		for($i=$total_syllables+1; $i >= 1; $i--) {
			if($divisions[$i] <= $stressed) { //If the division its less than stressed break
				$stressed_syl = $total_syllables - $i+1;
				break;
			}
		}
	} else {
		$lastLetter = $word[$len-1];
		
		if($total_syllables === 1) {
			$stressed_syl = 1;
		} else {
            if(in_array($lastLetter, array('n', 's')) or is_vowel($lastLetter))
                $stressed_syl = 2;
            else
                $stressed_syl = 1;
        }
    }
			 
			 

	
	//There we must check the prefix subr and subl but we do it later
	$prefix = substr($word, 0, 4);
	if($prefix === 'subr' or $prefix === 'subl') {
		//Now we going to check if it is "llana"
		if($stressed_syl === 2) {
			$divisions[1] +=1;
			$stressed_syl = 2;
		}
	}
	
	echo PHP_EOL . PHP_EOL . "Letra con el acento prosódico $stressed" . PHP_EOL . PHP_EOL;
	echo PHP_EOL . PHP_EOL . "Sílaba tónica $stressed_syl" . PHP_EOL . PHP_EOL;
	
	//Wich one is the stressed syllable
					
	
	return $divisions;
} //End Function

/**
 * Función para devolver la palabra dividida correctamente usando el separador que se indique como
 * tercer parámetro.
 *
 * Function to return the divided word with the separator 3rd in param
 *
 * @param array divisions result of word2divisions
 * @param string separator
 * @return string
 */
function divisions2syllabledword($word, Array $divisions, $separator = '-') {
	$syllabledWord = ''; //Return var
	$i             = 0;  //Counter
	
	for($i=0; $i < count($divisions)-1; $i++) {
		$syllabledWord .= substr($word, $divisions[$i], $divisions[$i+1]-$divisions[$i]);
	
		if($i < count($divisions)-2)
			$syllabledWord .= '-';
	}
	
	return $syllabledWord;
}


echo <<<EOF
<form action="Syllable3rdtry.php" method="post">
	<input type="text" name="word" />
	<BUTTON name="submit" value="submit" type="submit">Silabizar</BUTTON>
</form>
EOF;

echo "<pre>\n";
var_export(iconv_get_encoding('all'));
echo "</pre>\n";

$word = trim(strtolower($_POST['word']));

if(!empty($word)) {
    echo "<pre>";
	$divisions = word2divisions($word);
	$syllableword = divisions2syllabledword($word, $divisions);
	echo "</pre>";
	
	//*
	echo "<pre>". PHP_EOL . PHP_EOL;
	printf('La palabra es: %s', $word);
	echo PHP_EOL . PHP_EOL;
	echo 'La función devuelve: ';
	echo var_export($divisions);
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