module.exports = function (gulp)
{
    //-- Tasks
    var remove = require('../delete')

    gulp.task('delete', remove)
    gulp.task('delete').description = 'Delete dist folder'
}
