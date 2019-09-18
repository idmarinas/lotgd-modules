/** *****************************
                Copy main files
*******************************/
//-- Dependencias
var gulp = require('gulp')
var flatten = require('gulp-flatten')

//-- Configuration
var config = require('../../config/default')
var configTasks = require('../../config/tasks')
var copyFiles = configTasks.getProject(config.files)

module.exports = function (callback)
{
    return gulp.src(copyFiles)
        .pipe(flatten({ subPath: 1 })) //-- Avoid folder name module
        .pipe(gulp.dest(config.paths.build))
}
