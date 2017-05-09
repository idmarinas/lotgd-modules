/*******************************
				Define Sub-Tasks
*******************************/

module.exports = function(gulp)
{
	var
		//-- Tasks
		main = require('../build/main')
	;

	gulp.task('main', 'Copy main files of application', main);
};
