var gulp = require('gulp');
var runSequence = require('run-sequence');

//-- Copia los archivos a la carpeta de producción para luego subirlos al servidor
gulp.task('production', function (callback) {
	runSequence(
		'prod-copy',

		//-- El último siempre. Para eliminar lineas que no se quieren en producción
		function (error) {
			if (error) {
				console.log(error.message);
			} else {
				console.log('SE HAN TERMINADO LAS TAREAS DE PRODUCCIÓN CON ÉXITO');
			}
			callback(error);
		}
	);
});