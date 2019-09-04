/**
 * Process .less files
 */

//-- Dependencias
const gulp = require('gulp')
const less = require('gulp-less')
const vinylPaths = require('vinyl-paths')
const del = require('del')
const revertPath = require('gulp-revert-path')

//-- Configuration
var config = require('../../config/default')

module.exports = function (callback)
{
    return gulp.src(config.paths.build + '/public/**/*.less')
        .pipe(less())
        .pipe(gulp.dest(config.paths.build + '/public'))
        .pipe(revertPath())
        .pipe(vinylPaths(del))
}
