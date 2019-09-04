//-- Dependencies
const { series } = require('gulp')

module.exports = function (cb)
{
    console.info('Building application modules')

    return series('delete', 'main', 'css')(cb)
}
