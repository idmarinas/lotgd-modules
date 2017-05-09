var
	//-- Dependencies
	console = require('better-console'),
	minimist = require('minimist'),
	gutil = require('gulp-util'),

	//-- Options
	themeOptions =  { theme: gutil.env.theme || 'jade' },
	envOptions = { env: gutil.env.env || 'development' },
	options = Object.assign(gutil.env, envOptions, themeOptions)
;

module.exports = {

	log: {
		created: function(file) {
			return 'Created: ' + file;
		},
		modified: function(file) {
			return 'Modified: ' + file;
		},
		copied: function(file) {
			return 'Copied: ' + file;
		},
		deleted: function(file) {
			return 'Deleted: ' + file;
		}
	},
	//-- Determinate if is a enviroment of DEVELOPMENT or PRODUCTION
	//-- By default is development
	isProduction : function ()
	{
		if (options.env === 'production') return true;
		else if (options.env === 'prod') return true;
		else return false;
	},

	theme: function ()
	{
		return options.theme;
	},

	settings: {

		/* Remove Files in Clean */
		del: {
			silent : true
		},

		removeCode : {
			production: true
		}
	}
};
