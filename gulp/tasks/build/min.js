/**
 * Process .less files
 */

//-- Dependencias
var gulp = require('gulp')
var gulpif = require('gulp-if')
var minifyCSS = require('gulp-clean-css')

//-- Configuration
var config = require('../../config/default')
var configTasks = require('../../config/tasks')
// var log = configTasks.log
var isProduction = configTasks.isProduction()

module.exports = function (callback)
{
    return gulp.src(config.paths.build + '/public/**/*.css')
        .pipe(gulpif(isProduction, minifyCSS()))
        .pipe(gulp.dest(config.paths.build + '/public'))
}
