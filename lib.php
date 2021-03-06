<?php
namespace HW2_Group\Hw2_composer;

use seekquarry\yioop\configs as SYC;
use seekquarry\yioop\library as SYL;
use seekquarry\yioop\library\PhraseParser;

require_once 'vendor/autoload.php';
/*
This data structure stores the magnitude of vector for each 
*/
$docMagnitude = array();

/*
This function takes the list of files and an empty word map. It creates an 
inverse index and maps list of files 
*/
function createIndex(&$files, &$word_map, $tokenizationMethod){
    /*TODO: How to free up memory of doc_vector object is the question!
    http://stackoverflow.com/questions/584960/whats-better-at-freeing-memory-
    with-php-unset-or-var-null
    */
    $doc_vector = null;
    for ($fil = 0;$fil<count($files);$fil++) {
        $content = file_get_contents($files[$fil]);
        $words = explode(" ", $content);
        $count = count($words);
        for ($j=0;$j<$count;$j++) {
			
			$word = filter($words[$j]);
			$processedWord = tokenize(array($word), $tokenizationMethod);	
			$offset = $j + 1;
			$f = $fil+1;
			for ($index=0;$index<count($processedWord);$index++) {	
				mapWord($word_map, $processedWord[$index], $offset, $f);
			}
        }
    }
}

/*
This method applies tokenization on words.
*/
function tokenize($words, $tokenizationMethod){
    $processedWord = array();
            
    for($j = 0;$j<count($words);$j++){
        $word = filter($words[$j]);
        if ($tokenizationMethod == 'chargram') {
		    $processedWord = PhraseParser::getNGramsTerm(array($word),5);
	    }
	    else if ($tokenizationMethod == 'stem') {
		    $processedWord = PhraseParser::stemTerms(array($word),"en-US");
	    }
	    else {
	      // no tokenization
		    array_push($processedWord,$word);
	    }
	}
	return $processedWord;
}

/*
This function filters the special characters from the word. It only allows 
alphanumeric characters in the word.
*/
function filter($word)
{
    $word = trim($word);
    $word = preg_replace('/[^A-Za-z0-9\-]/','', $word);
    return strtolower($word);
}

/*
This function maps the file number and offset against the word. If the word is
not in the map then the function will add it. After adding the offset, doc_count
(if different) and term_count are increamented.

word -> ["term_count"-> 1,
         "doc_count"-> 1,
         [0->1,2,3],
         [1->1,2,3]
        ]
*/
function mapWord(&$word_map, $word, $offset, $f)
{
    if (isset($word_map[$word])) {
        if (isset($word_map[$word][$f])){
            $term_count = $word_map[$word]['term_count']+1;
            $word_map[$word]['term_count'] = $term_count;
            array_push($word_map[$word][$f], $offset);
        } else {
            $doc_count = $word_map[$word]['doc_count']+1;
            $word_map[$word]['doc_count'] = $doc_count;
            $word_map[$word][$f] = array();
            $term_count = $word_map[$word]['term_count']+1;
            $word_map[$word]['term_count'] = $term_count;
            array_push($word_map[$word][$f],$offset);
        }
    }
    else {
        $word_map[$word] = array();
        $word_map[$word]['doc_count'] = 1;
        $word_map[$word]['term_count'] = 1;
        $word_map[$word][$f] = array();
        array_push($word_map[$word][$f], $offset);
    }
}

/*
This method calculates the vector for the current document. The dimention of the
vector is specified by the word. The vector looks like,
docid = [word1=>count1, word2=>count2 ...]
*/
function createVector(&$docVector, &$word){
    if(isset($docVector[$word]) == false){
        $docVector[$word] = 1;
    }
    $docVector[$word] = $docVector[$word]+1;
}

/*
This function normalizes the vector. First it finds the magnitude of the vector
    sum(value1^2+value2^2+...)
and then every value is divided by magnitude giving a unit or normalized vector.    
*/
function normalizeVector(&$docVector, $fil){
    $sum = 0;
    foreach($docVector as $key=>$value){
        $sum += pow($docvector[$key], 2);
    }
    $docMagnitude[$fil] = sqrt($sum);
    
}

/*
This function prints the map.
*/
function printMap(&$word_map)
{
    ksort($word_map);
    if ($word_map != null) {
        foreach ($word_map as $word => $value) {
            $line = $word . ":" ;
            $data = "";
            foreach ($word_map[$word] as $page => $occurance) {
                if (is_array($occurance)) {
                    $data = $data."(".$page;
                    foreach ($occurance as $occ) {
                        $data = $data.",".$occ;
                    }
                    $data = $data."),";
                }
            }
            $line = $line.$word_map[$word]['doc_count'].":"
                    .$word_map[$word]['term_count'].":"
                    .rtrim($data, ",")."\n";
            print $line;
        }
    }
}

/*
This function returns the common documents where all the query words are present

*/
function findCommonDocuments(&$word_map, &$keywords)
{
    $count = count($keywords);
    //Use document list from 0th word as a reference.
    $docidList = array_keys($word_map[$keywords[0]]);
    $words = array();
    $commonDocId = array();    
    
    $totalDocs = count($docidList);
    
    //TODO: Galloping search TBD !!!
    //Starting for index 2 as 0,1 points to doc_count and term_count
    for($i=2;$i<$totalDocs;$i++) {
        $all = true;// Assuming that all words are present in document.
        for($j=1;$j<$count;$j++) {
            if(isset($word_map[$keywords[$j]][$docidList[$i]]) == false) {
                $all = false;//Assumption failed :(
                break;
            }
        }
        if($all == true) {
            array_push($commonDocId, $docidList[$i]);
        }
    } 
    
    return $commonDocId;  
}

function findDocumentsWithAtleastOneWord(&$word_map, &$keywords)
{
    $docList = array();
    $len = count($keywords);
    for($i=0;$i<$len;$i++){
        foreach($word_map[$keywords[$i]] as $docid => $pos){
            
        }
    }
}
