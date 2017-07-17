/**
 * @author Gabriel Trabanco Llano
 * @desc JS Logic for example website of Syllabler
 */

"use strict";

//URL to get the json data of the word
var url = 'http://syllabler.fwok.org/?json=1&word=';

//Get the ajax Object
var ajax = function() {
    var xmlhttp = false;

    //First IE
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch(e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            xmlhttp = false;
        }
    }

    if (xmlhttp === false && typeof XMLHttpRequest !== "undefined") {
        xmlhttp = new XMLHttpRequest();
    }

    return xmlhttp;
}();


//Function to show the result after ask for a word
function showResult(response) {
    var dv, pre, wordSyllabled = "";
    var i = 0, stressed = 0;
    var w = JSON.parse(response);

    var stressedTypes = ["aguda", "llana", "esdrújula", "sobreesdrújula"];

    //We will calculate the reverse stressed to bold it
    stressed = w.syllables.length - (w.stressedType + 1);

    //The word in syllables
    for(i=0; i<w.syllables.length; i++) {
        if (i === stressed) {
            wordSyllabled += " - <span class=\"stressed\">" + w.syllables[i] + "</span>";
        } else {
            wordSyllabled += " - " + w.syllables[i];
        }
    }
    wordSyllabled = wordSyllabled.substr(2, wordSyllabled.length);

    //Creating the elements
    var wsyllables = document.createElement("p");
    //wsyllables.appendChild(document.createTextNode(wordSyllabled))
    wsyllables.innerHTML = wordSyllabled + " ( " + w.numSyllables + " )";

    //The link for the RAE dictionary
    var aword = document.createElement("a");
    aword.href = w.raeUrl;
    aword.innerHTML = w.word;
    //Or
    //var linkText = document.createTextNode(w.word);
    //aword.appendChild(linkText);


    //Showing the div
    dv = document.getElementById("divresult");
    dv.appendChild(aword);
    dv.appendChild(wsyllables);

    //Showing the rest of information as json
    pre = document.getElementById("preresult");
    pre.innerHTML = response;
}




//Unobtrusive events logic
window.addEventListener("load", function() {
    if (ajax && JSON.parse !== "undefinied") {
        var sylForm = document.getElementById("sylForm");
        var btn = document.getElementById("sylButton");

        //First of all cancelling the submission of the form
        sylForm.addEventListener("submit", function (f) {
            f.preventDefault();
            return false;
        }, false);

        //Ajax
        ajax.onreadystatechange = function () {
            if (ajax.readyState == 4 && ajax.status == 200) {
                seeResult(ajax.responseText);
            }
        }

        //Onclick
        btn.addEventListener("click", function () {
            var word = document.getElementById("word").value;

            if (word.length > 2) {
                ajax.open("GET", url + word, true);
                ajax.send();
            }

            return false;
        });
    } /* else {
        alert("This app would NOT run in your navigator.\n\
					It is needed a XMLHttpRequest Javascript Object (Chrome, Firefox, \
					Safari...).");
    }
    //*/
    // I disabled the advert because if the user does not have a browser that supports XMLHttp
    // the webpage should run without it
});