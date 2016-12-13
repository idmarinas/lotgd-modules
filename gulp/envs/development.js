var gulp = require('gulp');
var runSequence = require('run-sequence');

//-- Copia los archivos a la carpeta del servidor local (módulos finalizados y partes del juego)
gulp.task('development', function (callback) {
	runSequence(
		'dev-copy',

		function (error) {
			if (error) {
				console.log(error.message);
			} else {
				console.log('SE HAN TERMINADO LAS TAREAS DE DESARROLLO CON ÉXITO');
			}
			callback(error);
		}
	);
});