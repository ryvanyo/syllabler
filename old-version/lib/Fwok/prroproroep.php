<?php

/**
 * Fwok Libraries
 *
 * LICENSE
 *
 * Check: http://code.fwok.org/license
 *
 * @category   Fwok
 * @package    Fwok
 * @copyright  You must see the url: http://code.fwok.org
 * @license    http://code.fwok.org/license
 * @version    You must see http://code.fwok.org/fwok_word_spanish_syllabler
 */

/**
 * @category   Fwok
 * @package    Fwok
 * @subpackage Fwok_Word
 * @copyright  Copyleft 2012 (http://code.fwok.org)
 * @license    http://code.fwok.org/license Copyleft
*/
class Fwok_Word_Spanish_Syllabler
{
    /**
	 * Private var for Spain exceptions
	 */
	private $_spain = true;


	/**
	 * Private var for tl exceptions
	 * $_spain and this are incompatible together
	 */
	private $_tl = false; //true joined, off separated.
	
	/**
	 * Private var for ignore (true) or not prefixes
	 */
	private $_ignorePrefix = true;


	/**
	 * Private var for know if the word has a tl group of consonants
	 */
	private $_has_tl = false;


	/**
	 * Private var with neccesary prefixes to advertise
	 * array of prefixes to advert that can produce a differents divisions
	 */
	private $_prefixes = array('sub');
	
	/**
	 * Advert over one prefix
	 */
	private $_prefixAdvert = '';


	/**
	 * Private var $this->_word
	 */
	private $_word = '';


	/**
	 * Private var with the information of the divisions in the word
	 */
	private $_divisions = array();


	/**
	 * Stressed Vowel
	 */
	private $_stressedLetter = 0;


	/**
	 * Stressed Syllable
	 */
	private $_stressedSyllable = 0;




	/**
	 * Public function to setup how to run this class
	 * @return $this
	 */
	public function setSpain($bool) {
		$this->_spain = $bool;
		return $this;
	}


	/**
	 * Public function to setup how to run this class with tl
	 * @return $this
	 */
	public function setTl($bool) {
		$this->_spain = $bool;
		return $this;
	}


	/**
	 * Public function to setup if you want to ignore the prefix of words
	 * @param bool $bool
	 * @return $this
	 */
	public function setIgnorePrefix($bool)
	{
		$this->_ignorePrefix = $bool;

		return $this;
	}


	/**
	 * Public function to setup one or an array of prefixes
	 * "sub" prefix is added by default
	 * @param string|array prefix or array of prefixes
	 * @return $this
	 */
	public function setPrefix($prefix)
	{
		if(is_array($prefix)) {
			foreach($prefix as $p)
				$this->_prefixes [] = $prefix;
		} else {
			$this->_prefixes [] = $prefix;
		}

		return $this;
	}


	/**
	 * Construct to Set a word
	 *
	 * @param string $this->_word
	 */
	public function __construct($word)
	{
	 	//Setting the word
	 	$this->_word = $this->_formatString($word);
	}


	/**
	 * Function to know if the word has a tl group of consonants
	 * @return bool
	 */
	public function hasTl()
	{
		if(empty($this->_divisions)) $this->_run();

		return $this->_has_tl;
	}


	/**
	 * Function to know if the word has a tl group of consonants
	 * @return empty or completed string with prefix
	 */
	public function hasPrefix()
	{
		if(empty($this->_divisions)) $this->_run();

		return $this->_prefixAdvert;
	}


	/**
	 * Function to know how many syllables has the word
	 *
	 * Función que devuelve el número de sílabas
	 *
	 * @return int Syllables
	 */
	public function getNumberOfSyllables()
	{
		if(empty($this->_divisions)) $this->_run();

		return (count($this->_divisions)-1);
	}


	/**
	 * Function to get the stressed letter
	 *
	 * Función para saber cual es la letra tónica
	 *
	 * @return int Stressed letter
	 */
	public function getStressedLetter()
	{
		if(empty($this->_divisions)) $this->_run();

		return $this->_stressedLetter;
	}


	/**
	 * Function to get the type of word (aguda, llana, esdrújula or sobre-esdrújula)
	 *
	 * Función para saber cual es la sílaba tónica (aguda, llana, esdrújula o sobre-esdrújula)
	 *
	 * @return int Stressed Syllable
	 */
	public function getStressedType()
	{
		if(empty($this->_divisions)) $this->_run();

		return $this->_stressedSyllable;
	}


	/**
	 * Function to get the stressed Syllable
	 *
	 * Función para saber cual es la sílaba tónica
	 *
	 * @return int Stressed Syllable
	 */
	public function getStressedSyllable()
	{
		if(empty($this->_divisions)) $this->_run();
		
		//Start key of array
		$startKey = $this->getNumberOfSyllables() - $this->_stressedSyllable ;

		//Start and end of the syllable
		$start = $this->_divisions[$startKey];
		$len = $this->_divisions[$startKey + 1] - $start; //How many letters?

		return substr($this->_word, $start, $len);
	}


	/**
	 * Function to get the divisions of the word
	 *
	 * Función que devuelve las divisiones de palabra
	 *
	 * @return arrray divisions
	 */
	public function getDivisions()
	{
		if(empty($this->_divisions)) $this->_run();

		return $this->_divisions;
	}


	/**
	 * Function to get an array of Syllables. You can set one argument with the word
	 * if you don't want watched the word in lower characters for example. Because the class
	 * set the word in lower character and utf8.
	 * The unique requirement is that the argument must be the same word, if not the function will
	 * return an array of saved word.
	 *
	 * Función que devuelve un array con clave numerica y como valores las sílabas. Puedes poner un
	 * argumento que podría ser la palabra original si quieres, sino lo hará soble la palabra
	 * almacenada. Se permite esto por que se almacenan las palabras en minúsculas y podrías querer
	 * tener la palabra en mayúsculas o con la primera letra capital o algún formato diferente.
	 * El único requisito es que coincida la palabra con la original, si no es así devuelve
	 * las sílabas de la palabra guardada
	 *
	 * @param string $this->_word (optional)
	 * @return array
	 */
	public function getSyllables($word = null)
	{
		if(empty($this->_divisions)) $this->_run();

		$i = 0; //Counter
		$syllables = array();


		$total_syllables = $this->getNumberOfSyllables();


		if($word !== null and $this->_word !== $this->_formatString($word))
			$word = $this->_word;
		

		//Now we get the syllables from original word
		for($i=0; $i < $total_syllables; $i++)
			$syllables [] = substr(	$this->_word,
									$this->_divisions[$i],
									$this->_divisions[$i+1]-$this->_divisions[$i]);


		return $syllables;
	}


	/**
	 * Function to format a string (because of the accents we need this)
	 *
	 * Función para formatear un string (la necesitamos por los acentos)
	 *
	 * @param string $string
	 * @return string
	 */
	private function _formatString($string)
	{
		static $accentsUpper = array('Á', 'É', 'Í', 'Ó', 'Ú', 'Ü');
		static $accentsLower = array('á', 'é', 'í', 'ó', 'ú', 'ü');
				
		return trim(strtolower(str_replace($accentsUpper, $accentsLower, $string)));
	}


	/**
	 * Function to check if one letter has an accent
	 *
	 * Función para comprobar si una letra tiene o no acento
	 *
	 * @param chat $letter
	 * @return bool
	 */
	private function _has_accent($letter)
	{
		static $accents = array('á', 'é', 'í', 'ó', 'ú');
		
		if(strlen($letter) !== 1)
			return false;
		
		return in_array($letter, $accents, true);
	}


	/**
	 * Function to check if one letter is a strong vowel
	 *
	 * Función para comprobar si una letra es una vocal fuerte o media
	 *
	 * @param char $letter
	 * @return bool
	 */
	private function _is_strong_vowel($char)
	{
		static $strongVowels = array('a', 'e', 'o');
	 	
	 	
	 	if(strlen($char) !== 1)
	 		return false;
	 	
	 	
	 	return in_array($char, $strongVowels, true) or $this->_has_accent($char);
	}
	
	
	/**
	 * Function to check if one letter is a weak vowel
	 *
	 * Función para comprobar si una letra es una vocal débil
	 *
	 * @param char $letter
	 * @return bool
	 */
	private function _is_weak_vowel($char)
	{
		static $weakVowels = array('i', 'u', 'ü');
	 	
	 	
	 	if(strlen($char) !== 1)
	 		return false;
	 	
	 	
	 	return in_array($char, $weakVowels, true);
	}
	
	
	/**
	 * Function to check if one letter is vowel
	 *
	 * Función para comprobar si una letra es una vocal
	 *
	 * @param char $letter
	 * @return bool
	 */
	private function _is_vowel($letter)
	{
		return $this->_is_strong_vowel($letter) or $this->_is_weak_vowel($letter);
	}
	
	
	/**
	 * Function to check if one letter is a consonant
	 *
	 * Función para comprobar si una letra es consonante
	 *
	 * @param char $letter
	 * @return bool
	 */
	private function _is_consonant($letter)
	{
		static $consonants = array( 'b', 'c', 'd', 'f', 'g', 'h', 'j',
									'k', 'l', 'm', 'n', 'ñ', 'p', 'q',
									'r', 's', 't', 'v', 'w', 'x', 'y');
		
		//I could do this checking that it isn't a vowel but I want to return false if
		//the user want to check a number or something different from a letter
		return in_array($letter, $consonants, true);
	}
	
	
	/**
	 * Function to check if it is a consonant of group exceptions
	 *
	 * Función para comprobar si es un grupo de consonantes de las excepciones
	 *
	 * @param string $letters 2 letters
	 * @return bool
	 */
	private function _is_double_consonant_exception($letters, $tl = false)
	{
		static $exceptions = array(	'bl', 'cl', 'fl', 'gl', 'kl', 'pl', 'll',
									'br', 'cr', 'dr', 'gr', 'kr', 'pr', 'tr', 'rr',
									'ch');

		//First the exception of tl. This only for advise the user.
		if($letters === 'tl') {
			echo "Tiene tl pero: $tl " . $letters. "\n";
			var_export($tl);
			$this->_has_tl = true;
		}
		
		return in_array($letters, $exceptions, true) or ($tl and $letters === 'tl');
	}
	
	/**
	 * Function to check if one group of two syllables is an hiatus
	 *
	 * Función para comprobar si un grupo de dos sílabas es un hiato
	 *
	 * @param string $letter 2 letters
	 * @return bool
	 */
	private function _is_hiatus($letters)
	{
		if($this->_is_strong_vowel($letters[0]) and $this->_is_strong_vowel($letters[1]))
			return true;


		return false;
	}
	
	/**
	 * Function to check if one group of two syllables is a diphthong
	 *
	 * Función para comprobar si un grupo de dos sílabas es un diptongo
	 *
	 * @param string $letters 2 letters
	 * @return bool
	 */
	private function _is_diphthong($letters)
	{
		if(!$this->_is_hiatus($letters))
			return true;
		
		return false;
	}


	/**
	 * Function to check if one group of three vowels is a triphthong
	 *
	 * Función para comprobar si un grupo silábico de 3 sílabas es un triptongo
	 *
	 * @param string $lettes
	 * @return bool
	 */
	private function _is_triphthong($letters)
	{
		$bool = false; //Return value at the end of function


		//For better understable code
		$c1 = $letter[0];
		$c2 = $letter[1];
		$c3 = $letter[2];


		if( $this->_is_strong_vowel($c1) and
		    $this->_is_weak_vowel($c2) and
		    $this->_is_strong_vowel($c3) )
			$bool = true;
		
		return $bool;
	}


	/**
	 * Function to add a value into $array if not exists
	 *
	 * Función para añadir un valor a un array si no existe
	 *
	 * @param mixed $value
	 * @param array &$array its a reference to the array
	 * @param bool $strict use or not strict comparation
	 */
	private function _addToArray($pos, array &$array, $strict = true)
	{
		if(!in_array($pos, $array, $strict))
			$array [] = $pos;
	}


	/**
	 * Function to process a word
	 * @param string $word
	 * @param bool $spain exceptions
	 * @param bool $tl if you want "tl" always join on syllables
	 * @return array divisions
	 */
	private function _runDivision($word, $spain = true, $tl = false)
	{
		$len  = strlen($word);

		$i    = 0; //Counter

		$switch = false; //if it is a word of exception we don't need begin the loop
		
		$divisions = array(0); //Return value


		//First the exceptions words for Spain
		if($spain and $word === 'guión') {
			//Guión
			$switch = true; //For don't begin the loop
			$divisions = array(0, 3, 5);
		}


		if($spain and  $word === 'truhán') {
			//Truhán
			$switch = true; //For don't begin the loop
			$divisions = array(0, 3, 6);
		}


		//Check if the last letter is an y, because if it is, it will run as a vowel
		//If it is we replace it because it don't take effect over the syllabler counter
		if($word[$len-1] === 'y')
			$word[$len-1] = 'i';


		//Begining the division of a letter
		while($i < $len and $switch === false) {
			$c1 = $c2 = $c3 = $c4 = '';
			$c1  = $word[$i];
			$c2  = $word[$i+1];
			$c3  = $word[$i+2];
			$c4  = $word[$i+3];
			
			//we going to advance to first vowel to begin
			//because I would like to escape first consonants of the words like "gnomo"
			if($this->_is_consonant($word[0]) and count($divisions) < 2 and $i < 2 and $this->_is_consonant($c1)) {
				++$i;
				continue;
			}

			//Now we do all checks here without levels of if's
			//Consonant + Consonant + Consonant + Consonant
			if($this->_is_consonant($c1) and $this->_is_consonant($c2) and $this->_is_consonant($c3) and $this->_is_consonant($c4)) {
				$this->_addToArray(($i+=2), $divisions);
				continue;

			//Consonant + Consonant + Consonant + Vowel
			} else if($this->_is_consonant($c1) and $this->_is_consonant($c2) and $this->_is_consonant($c3) and $this->_is_vowel($c4)) {
				if($c3 === 'l' or $c3 === 'r') {
					$this->_addToArray(($i+1), $divisions);
					$i += 3;
					continue;
				} else {
					$this->_addToArray(($i+=2), $divisions);
					continue;
				}

			//Consonant + Consonant + Vowel
			} else if($this->_is_consonant($c1) and $this->_is_consonant($c2) and $this->_is_vowel($c3)) {
				if($this->_is_double_consonant_exception($c1.$c2, $spain, $tl)) {
					var_export($c1.$c2);
		  			$this->_addToArray($i, $divisions);
		  			$i += 2;
			  		continue;
			  	} else {
			  		$this->_addToArray(($i+1), $divisions);
		  			$i += 2; //If we don't do this we will have a problems with
		  					 //the coincidence of consonant + vowel
			  		continue;
			  	}
	
			//Consonant + Vowel
			} else if($this->_is_consonant($c1) and $this->_is_vowel($c2)) {
				//First check if it is qu cause it's indivisible
		  		//Secondly check if it is gue or gui
		  		if(($c1 === 'q' or $c1 === 'g') and $c2 === 'u') {
		  			//Then continue without 2 first letters of this syllable
		  			$i += 2;
		  			continue;
		  		} else { //Consonant + Vowel
		  				//We must to count syllables because if the first letter it is an consonant
		  				//we continue with the loop then only divide this if it isn't the first
		  			$this->_addToArray($i, $divisions);
			  		++$i;
			  		continue;
			  	}//*/
			//Vowel + Vowel
			} else if($this->_is_vowel($c1) and $this->_is_vowel($c2)) {
				if($this->_is_hiatus($c1.$c2)) {
		  			$this->_addToArray((++$i), $divisions);
		  			continue;
			  	} else if($this->_is_vowel($c3) and $this->_is_hiatus($c2.$c3)) {
			  		$this->_addToArray(($i+=2), $divisions);
			  		continue;
		  		} else if($this->_is_vowel($c3) and $this->_is_triphthong($c1.$c2.$c3)) { //Then it is a triphthong
		  			//Continue
			  		$i += 3;
		  			continue;
			  	} else { //Then it is a diphthong with 1 consonant after so we must check the consonants
			  		$i += 2;
			  		continue;
		  		}

			//Vowel + Consonant + Vowel
			} else if( $this->_is_vowel($c1) and
					   $this->_is_consonant($c2) and $this->_is_vowel($c3)) {
				$this->_addToArray(($i+1), $divisions);
				++$i;
				continue;
			}

			//Vowel + Consonant + Consonant will be checked in next cycle
			++$i;
		} //End Loop

		//Now we add the end division for help us doing it later with substr function
		$this->_addToArray($len, $divisions);

		return $divisions;
	}


	/**
	 * Function to process the Stressed Syllable and Vowel
	 *
	 * Función para procesar la sílaba y letra tónica de una palabra
	 *
	 * @param string $word
	 * @param array $divisions
	 * @return array array(int|array Stressed Syllable, int|array stressed Letter)
	 */
	private function _runStressed($word, array $divisions)
	{
		$stressedSyllable = 0; //Return value
		$stressedLetter   = -1; //Return Value; I use -1 because it could be the letter in pos = 0

		$i = 0; //Counter

		$total_syllables = count($divisions)-1;

		$len = strlen($word);
		
		$original_word = $word;


		//First we going to check if it has a vowel with accent
		for($i = 0; $i < $len; $i++) {
			if($this->_has_accent($word[$i])) {
				$stressedLetter = $i;
			}
		}


		//Now we check the stressed syllable
		//First words with accent
		if($stressedLetter !== -1) {
		    for($i=0; $i<=$total_syllables; $i++) {
	    		if( $stressedLetter >= $divisions[$i] and
	    			$stressedLetter < $divisions[$i+1] ) {
	    			$stressedSyllable = $total_syllables - $i;  //Counting wich one is the stressed
	    														//syllable
	    			break;
	    		}
	    	}

		//Secondly words without accent
	    } else {
			//Last letter of the word. I do this over the original word because if the last letter
			//is an "y" it doesn't has accent in spite of it works as a vowel for diphthongs
			//and that stuff
			$lastLetter = $word[$len-1];
			
			//Check if the last letter is an y, because if it is, it will run as a vowel
			//If it is we replace it because it don't take effect over this function
			if($word[$len-1] === 'y')
				$word[$len-1] = 'i'; //Because of diphthongs and triphthongs we need to do this

			if(	$total_syllables > 1 and (in_array($lastLetter, array('n', 's'), true)
				or $this->_is_vowel($lastLetter))) $stressedSyllable = 2; //"Llana" because it hasn't
																		 //accent and end in vowel
																		 //"n" or "s"
			else $stressedSyllable = 1; //"Aguda" because it hasn't accent and don't end in "n",
										//"s" or vowel

        	//Now we going to get the stressed letter
	        //First we get the syllable
    	    $startDivision  = $divisions[$total_syllables - $stressedSyllable];
        	$finishDivision = $divisions[$total_syllables - $stressedSyllable + 1];
        	$syl            = substr($word, $startDivision, $finishDivision);


        	//Now we going to check the syllable, We must know that there isn't any syllable with
        	//more than 4 vowels
        	for($i=0; $i<$syl_len; $i++) {
        		$c1 = $c2 = $c3 = $c4 = '';

    	    	$c1 = $syl[$i];
        		$c2 = $syl[$i+1];
        		$c3 = $syl[$i+2];
        		$c4 = $syl[$i+3];
        		//Maximum syllables is 6 but if it has 6 syllables I will find a consonant before
        		//the vowels and the loop will continue, so 4 it's enough

        		//Only vowels have accent
        		if($this->_is_consonant($c1) and ($c1 !== 'g' or $c1 !== 'q')) continue;


        		//The exception of 'gu' and 'qu'
        		if( ($c1 === 'q' or $c1 === 'g') and $c2 === 'u' and ($c3 === 'e' or $c3 === 'i') ) {
        			++$i; //Because automatic increment in each cycle and we only want
        				  //to increment in 2 the counter
	        		continue;
        		
    	    	} else if($this->_is_triphthong($c1.$c2.$c3)) {
        			$stressedLetter = $startDivision + $i + 1;
        			break;
        	
        		} else if(!$this->_is_hiatus($c1.$c2)) {
        			if($this->_is_strong_vowel($c1)) $stressedLetter = $startDivision + $i + 1;
        			else $stressedLetter = $startDivision + $i + 2;
        		
        			break;
        	
        		} else if($this->_is_vowel($c1)) {
        			$stressedLetter = $startDivision + $i + 1;
        			break;
        		}
        	}
		}
		
		return array($stressedSyllable, $stressedLetter);
	}
	
	
	/**
	 * Function to check if we have something to advertise about prefixes
	 */
	private function _runPrefixes()
	{
		$number_prefixes = count($this->_prefixes);
		
		$i = 0;
		
		for($i = 0; $i < $number_prefixes; $i++) {
			$len = strlen($this->_prefixes[$i]);
			$prefix = substr($this->_word, 0, $len);
			if($prefix === $this->_prefixes[$i]) {
				$this->_prefixAdvert = $prefix;
				
				//Now if we haven't to ignore prefix we fix it
				if(!$this->_ignorePrefix) $this->_divisions[1] = $len;
			}
		}
	}
	
	
	/**
	 * Function to run all
	 */
	private function _run()
	{
		$this->_divisions = $this->_runDivision($this->_word, $this->_spain, $this->_tl);
		$stressedArray = $this->_runStressed( $this->_word, $this->_divisions);
		list($this->_stressedSyllable, $this->_stressedLetter) = $stressedArray;
		$this->_runPrefixes();
	}
}

/**
 * Ejemplo de uso
 */
 
$word = "subrayar";

$w = new Fwok_Word_Spanish_Syllabler($word);

$w->setSpain(false)->setTl(true)->setIgnorePrefix(false);

echo "Sílabas: ";
var_dump(($w->getSyllables()));
echo "\n";
echo "Sílaba tónica: ";
var_dump($w->getStressedSyllable());
echo "\n";
echo "Tipo (1 aguda, 2 llana, 3 esdrújula, 4 sobre-esdrújula): " . $w->getStressedType();
echo "\n";
echo "Número de sílabas: " . $w->getNumberOfSyllables();
echo "\n";
echo "Tiene \"tl\"? " . ($w->hasTl()? 'Si':'No') . "\n";
echo "Tiene algún prefijo? " . ($w->hasPrefix()? 'Si':'No') . "\n";
echo "\n";
echo "\n";