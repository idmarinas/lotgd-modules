//-- Dependencies
var env = require('minimist')(process.argv.slice(2))

//-- Options
var envOptions = { env: env.env || 'development', project: env.project || 'main' }
var options = Object.assign(env, envOptions)

module.exports = {
    log: {
        created: function (file)
        {
            return 'Created: ' + file
        },
        modified: function (file)
        {
            return 'Modified: ' + file
        },
        copied: function (file)
        {
            return 'Copied: ' + file
        },
        deleted: function (file)
        {
            return 'Deleted: ' + file
        }
    },
    //-- Determinate if is a enviroment of DEVELOPMENT or PRODUCTION
    //-- By default is development
    isProduction: function ()
    {
        if (options.env === 'production') return true
        else if (options.env === 'prod') return true
        else return false
    },

    /**
     * Get files to copy.
     *
     * @param {object} files Allowed config.files in config file
     */
    getProject: function (files)
    {
        if (files[options.project])
        {
            return files[options.project]
        }

        return files.main
    },

    theme: function ()
    {
        return options.theme
    },

    settings: {

        /* Remove Files in Clean */
        del: {
            silent: true
        },

        removeCode: {
            production: true
        }
    }
}
