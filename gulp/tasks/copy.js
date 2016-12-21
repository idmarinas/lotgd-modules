var gulp = require('gulp');
var variables = require('../variables');

var filesCopy = [
	//-- Todos los archivos y subdirectorios
	'**/**',
	//-- Ingorar archivos .js .json
	'!*.js',
	'!*.json',
	//-- Ignorar archivos que solo se usan en el desarrollo
	'!gulp{,/**}',
	'!node_modules{,/**}',
	'!bower_components{,/**}',
	//-- Otros archivos
	'!.gitignore',
	'!.htaccess',
	'!.watchmanconfig',
	'!gulpfile.js'
];

//-- Copiar a la carpeta de desarrollo
gulp.task('dev-copy', function () {
	return gulp.src(filesCopy)
		.pipe(gulp.dest(variables.development_dir + '/modules'))
	;
});

//-- Copiar a la carpeta de producción
gulp.task('prod-copy', function () {
	return gulp.src(filesCopy)
		.pipe(gulp.dest(variables.production_dir + '/modules'))
	;
});