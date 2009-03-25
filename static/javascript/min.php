<? require('../jsmin-1.1.1.php');
echo JSMin::minify(file_get_contents('all.js'));
