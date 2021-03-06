// http://eslint.org/docs/user-guide/configuring

module.exports = {
    root: true,
    parser: 'babel-eslint',
    parserOptions: {
        sourceType: 'module'
    },
    env: {
        browser: true
    },
    // https://github.com/feross/standard/blob/master/RULES.md#javascript-standard-style
    extends: 'standard',
    plugins: [
        'html'
    ],
    globals: {
        define: true
    },
    // add your custom rules here
    rules: {
        // allow paren-less arrow functions
        'arrow-parens': ['error', 'as-needed'],
        // allow async-await
        'generator-star-spacing': 0,
        // allow debugger during development
        'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0,

        indent: ['error', 4, { SwitchCase: 1 }],
        'brace-style': ['error', 'allman', { allowSingleLine: true }],
        'no-tabs': 0,
        'spaced-comment': ['error', 'always', { markers: ['--', '!'], exceptions: ['-'] }]
    }
}
