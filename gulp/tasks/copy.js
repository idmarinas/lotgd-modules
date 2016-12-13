var gulp = require('gulp');
var variables = require('../variables');

var filesCopy = [
	//-- Todos los archivos y subdirectorios
	'**/**',
	//-- Ignorar archivos de ejecución de tareas
	'!gulp',
	'!gulp/**',
	//-- Ingorar archivos .js .json
	'!*.js',
	'!*.json',
	//-- Ignorar archivos que solo se usan en el desarrollo
	'!node_modules',
	'!node_modules/**',
	'!bower_components',
	'!bower_components/**',
	//-- Otros archivos
	'!.gitignore',
	'!.htaccess',
	'!.watchmanconfig'
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