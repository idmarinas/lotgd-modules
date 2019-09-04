/**
 * Process .less files
 */

//-- Dependencias
var gulp = require('gulp')
var autoprefixer = require('gulp-autoprefixer')

//-- Configuration
var config = require('../../config/default')

module.exports = function (callback)
{
    return gulp.src(config.paths.build + '/public/**/*.css')
        .pipe(autoprefixer())
        .pipe(gulp.dest(config.paths.build + '/public'))
}
