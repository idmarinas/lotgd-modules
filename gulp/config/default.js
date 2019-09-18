const merge = require('lodash.merge')

const config = {
    paths: {
        //-- Directory for construct game
        build: 'dist'
    },
    files: {
        //-- Files to copy
        main: [
            //-- All files including subdirectories
            'src/{,/**}'
        ]
    }
}

var custom = {}
try
{
    custom = require('../custom/config/default')
}
catch (error)
{
    console.log('Not find custom config default')
}

module.exports = merge(config, custom)
