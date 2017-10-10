/** *****************************
                Define Sub-Tasks
*******************************/

module.exports = function (gulp)
{
    //-- Tasks
    var main = require('../build/main')

    gulp.task('main', 'Copy main files of application', main)
}
