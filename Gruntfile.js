/**
 * Plugin base arguments
 *
 * @type {Object}
 */
var plugin_args = {
	path: './', // plugin directory path
	domainPath: '/languages', // language files location ( relative to "path" )
	potFilename: 'template.pot', // generated pot file name
	exclude: [ // excluded files and directory from parsing
		'vendor/' // composer libs vendor dir
	],
	mainFile: 'init.php', // plugin main file ( with plugin description comment doc )
	watchFiles: {
		assets: [ 'assets/src/css/**/*.css', 'assets/src/js/**/*.js' ],
		potfile: [ './**/*.php' ]
	}
};

/**
 * Grunt tasks
 */
module.exports = function ( grunt ) {
	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		uglify: {
			my_target: {
				options: {
					preserveComments: 'some'
				},
				files: [ {
					expand: true,
					cwd: 'assets/src/js',
					src: '**/*.js',
					dest: 'assets/dist/js'
				} ]
			}
		},
		cssmin: {
			minify: {
				expand: true,
				cwd: 'assets/src/css',
				src: '**/*.css',
				dest: 'assets/dist/css'
			}
		},
		makepot: {
			target: {
				options: {
					cwd: plugin_args.path,
					domainPath: plugin_args.domainPath,
					exclude: plugin_args.exclude,
					mainFile: plugin_args.mainFile,
					potFilename: plugin_args.potFilename,
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true,
						'Last-Translator': '',
						'Language-Team': 'Nabeel Molham <n.molham@gmail.com>'
					},
					type: 'wp-plugin',
					updateTimestamp: true,
					updatePoFiles: true
				}
			}
		},
		watch: {
			// for localization .pot file
			potfile: {
				files: plugin_args.watchFiles.potfile,
				tasks: [ 'makepot' ]
			},
			// for JS & CSS assets
			assets: {
				files: plugin_args.watchFiles.assets,
				tasks: [ 'uglify', 'cssmin' ]
			},
			// for JS & CSS assets
			all: {
				files: plugin_args.watchFiles.potfile.concat( plugin_args.watchFiles.assets ),
				tasks: [ 'makepot', 'uglify', 'cssmin' ]
			}
		}
	} );

	// Load plugins
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	// Default task(s).
	grunt.registerTask( 'default', [ 'watch:all' ] );
};