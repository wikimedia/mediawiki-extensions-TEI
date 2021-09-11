/* eslint-env node, es6 */
module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			options: {
				cache: true
			},
			// Exclude mw_tei_json_definition due to bug in eslint - T273997
			all: [
				'**/*.{js,json}',
				'!data/mw_tei_json_definition.json',
				'!node_modules/**',
				'!modules/jquery/*.js',
				'!vendor/**'
			]
		},
		stylelint: {
			all: [
				'modules/**/*.css',
				'modules/**/*.less'
			]
		},
		banana: conf.MessagesDirs
	} );

	grunt.registerTask( 'test', [ 'eslint', 'stylelint', 'banana' ] );
	grunt.registerTask( 'default', 'test' );
};
