module.exports = function (gulp)
{
    //-- Tasks
    const main = require('../build/main')
    const build = require('../build')

    gulp.task('build', build)
    gulp.task('build').description = 'Build files of application'

    gulp.task('main', main)
    gulp.task('main').description = 'Copy main files of application'
}
