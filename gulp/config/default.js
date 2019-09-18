//-- Modify with your data
module.exports = {
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
