//-- Modify with your data
module.exports = {
    paths: {
        /** ***********************************************
         * Directory for diferents test server
         * Only need if you want copy to your localserver
         ************************************************/
        development: {
            final: 'D:/xampp/htdocs/lotgd-dev', //-- Directory for final version
            beta: 'D:/xampp/htdocs/lotgd-beta', //-- Directory for beta version
            alpha: 'D:/xampp/htdocs/lotgd-alpha' //-- Directory for alpha version
        },
        //-- Directory for construct game
        build: 'dist'
    },
    files: {
        //-- Files to copy
        main: [
            //-- All files including subdirectories
            '**{,/**,/.htaccess}',
            //-- Ignore files of development
            '!gulp{,/**}',
            '!gulpfile.js',
            '!assets{,/**}',
            '!dist{,/**}',
            '!waiting{,/**}',
            '!node_modules{,/**}',
            '!bower_components{,/**}',
            '!**/*.{dist,md,lock,json}',
            '!semantic{,/**}',
            //-- Other files
            '!{CHANGELOG.txt,QUICKINSTALL.TXT,README_FIRST.txt,README.txt,INSTALL.TXT}'
        ]
    }
}
