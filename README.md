# Syllabler
A PHP library to split Spanish words in syllables through the algorithm.

# Pending Works
In the TODO

# Demo

> http://syllabler.fwok.org/

> http://syllabler.fwok.org/camión

# Deploy

To deploy you need to keep almost the same structure or change the set_include_path on `website/www/index.php`.

Anyway it should run normally if you deploy the app from the [github](https://github.com/gtrabanco/PHP-Syllabler) with the consideration that you should change `apache.htaccess` or `nginx.htaccess` to `.htaccess`.


# Known Issues
The support for hypen words must be added externally and process separately, it is not added.

The prefixes can make issues by the moment.

In spite of the algorithm supports it, it is not implemented the support for "tl" or other configurations that are not from Spain. It is only works with "Castillian" (spanish from Spain with the known mistakes).

You must use utf8_decode before pass the word to the object, this is because the library convert the word to utf8 and recodification to utf8 must give problems.


----
## More relative information (in Spanish):
https://es.wikipedia.org/wiki/S%C3%ADlaba
https://www.xn--slabas-3va.com/regalas/

* For planned future:
https://es.wikipedia.org/wiki/Fonolog%C3%ADa_del_espa%C3%B1ol

## Similar projects
http://www.aucel.com/pln/transbase.html - (See the source code in perl)
