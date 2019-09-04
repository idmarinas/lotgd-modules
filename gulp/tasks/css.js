//-- Dependencies
const { series } = require('gulp')

module.exports = function (cb)
{
    return series('less', 'prefixer', 'min')(cb)
}
