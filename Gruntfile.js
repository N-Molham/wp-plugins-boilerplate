// requires
var filesystem = require( 'fs' );

/**
 * Plugin base arguments
 *
 * @type {Object}
 */
var plugin_args = {
	path         : './', // plugin directory path
	domain_path  : '/languages', // language files location ( relative to "path" )
	pot_file_name: 'template.pot', // generated pot file name
	exclude      : [ // excluded files and directory from parsing
		'vendor/' // composer libs vendor dir
	],
	main_file    : 'init.php', // plugin main file ( with plugin description comment doc )
	watch_files  : {
		assets : [
			'assets/src/css/**/*.css',
			'assets/src/css/**/*.scss',
			'assets/src/js/**/*.js'
		],
		potfile: [ './**/*.php' ]
	},
	author       : 'Nabeel Molham <n.molham@gmail.com>'
};

/**
 * Grunt tasks
 */
module.exports = function ( grunt ) {
	// Project configuration.
	grunt.initConfig( {
		pkg    : grunt.file.readJSON( 'package.json' ),
		uglify : {
			my_target: {
				options: {
					preserveComments: 'some'
				},
				files  : [ {
					expand: true,
					cwd   : 'assets/src/js',
					src   : '**/*.js',
					dest  : 'assets/dist/js'
				} ]
			}
		},
		cssmin : {
			minify: {
				expand: true,
				cwd   : 'assets/src/css',
				src   : '**/*.css',
				dest  : 'assets/dist/css'
			}
		},
		sass   : {
			dist: {
				options: {
					style: 'compressed'
				},
				files  : [ {
					expand: true,
					cwd   : 'assets/src/css',
					src   : [ '**/*.scss' ],
					dest  : 'assets/dist/css',
					ext   : '.css'
				} ]
			}
		},
		makepot: {
			target: {
				options: {
					cwd            : plugin_args.path,
					domainPath     : plugin_args.domain_path,
					exclude        : plugin_args.exclude,
					main_file      : plugin_args.main_file,
					pot_file_name  : plugin_args.pot_file_name,
					potHeaders     : {
						poedit                 : true,
						'x-poedit-keywordslist': true,
						'Last-Translator'      : '',
						'Language-Team'        : plugin_args.author
					},
					type           : 'wp-plugin',
					updateTimestamp: true,
					updatePoFiles  : true
				}
			}
		},
		watch  : {
			// for localization .pot file
			potfile: {
				files: plugin_args.watch_files.potfile,
				tasks: [ 'makepot' ]
			},
			// for JS & CSS assets
			assets : {
				files: plugin_args.watch_files.assets,
				tasks: [ 'uglify', 'sass', 'cssmin' ]
			},
			// for JS & CSS assets
			all    : {
				files: plugin_args.watch_files.potfile.concat( plugin_args.watch_files.assets ),
				tasks: [ 'makepot', 'uglify', 'sass', 'cssmin' ]
			}
		}
	} );

	// Load plugins
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	// when the watch task triggers
	grunt.event.on( 'watch', function () {
		// save assets compile date & time
		filesystem.writeFile( 'assets/last_update', (new Date()).toISOString().replace( /[^0-9]/g, '' ), function ( err ) {
			if ( err ) {
				return console.log( err );
			}

			console.log( "The file was saved!" );
		} );
	} );

	// Default task(s).
	grunt.registerTask( 'default', [ 'watch:all' ] );
};