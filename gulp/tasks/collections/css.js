module.exports = function (gulp)
{
    //-- Tasks
    const css = require('../css')
    const less = require('../build/less')
    const prefixer = require('../build/prefixer')
    const min = require('../build/min')

    gulp.task('css', css)
    gulp.task('css').description = 'Process all .css and .less files of modules'

    gulp.task('less', less)
    gulp.task('less').description = 'Process all .less files of modules'

    gulp.task('prefixer', prefixer)
    gulp.task('prefixer').description = 'Prefixer all .css files of modules'

    gulp.task('min', min)
    gulp.task('min').description = 'Min all .css files of modules'
}
