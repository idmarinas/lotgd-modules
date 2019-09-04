//-- Dependencies
var gulp = require('gulp')

//-- Tasks
require('./gulp/tasks/collections/build')(gulp)
require('./gulp/tasks/collections/delete')(gulp)
require('./gulp/tasks/collections/css')(gulp)

gulp.task('default', gulp.series('build'))
