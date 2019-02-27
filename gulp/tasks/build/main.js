/** *****************************
                Copy main files
*******************************/
//-- Dependencias
var gulp = require('gulp')
var flatten = require('gulp-flatten')

//-- Configuration
var config = require('../../config/default')

module.exports = function (callback)
{
    return gulp.src(config.files.main)
        .pipe(flatten({ subPath: 1 })) //-- Avoid folder name module
        .pipe(gulp.dest(config.paths.build))
}
