var gulp        = require( 'gulp' );
var sass        = require( 'gulp-sass' );
var sourcemaps  = require( 'gulp-sourcemaps' );
var cleanCSS    = require( 'gulp-clean-css' );
var browserSync = require( 'browser-sync' ).create();
var file_system = require( 'fs' );
var uglify      = require( 'gulp-uglify' );
var pump        = require( 'pump' );
var wp_pot      = require( 'gulp-wp-pot' );

var update_assets_version = function () {

	file_system.writeFile( './assets/last_update', (new Date()).toISOString().replace( /[^0-9]/g, '' ), function ( error ) {

		if ( error ) {

			return console.log( error );

		}

		console.log( "The file was saved!" );

	} );

};

gulp.task( 'make_pot', function ( done ) {

	gulp.src( './**/*.php' )
	.pipe( wp_pot( {
		domain        : 'wp-plugin-domain',
		package       : 'WP Plugins Boilerplate',
		headers       : {
			poedit                 : true,
			'x-poedit-keywordslist': true
		},
		lastTranslator: 'Nabeel Molham <n.molham@gmail.com>'
	} ) )
	.pipe( gulp.dest( './languages/template.pot' ) );

	done();

} );

gulp.task( 'styles', function ( done ) {

	gulp.src( ['./assets/src/css/**/*.scss', './assets/src/css/**/*.css'] )
	.pipe( sourcemaps.init() )
	.pipe( sass().on( 'error', sass.logError ) )
	.pipe( cleanCSS( { compatibility: 'ie8' } ) )
	.pipe( sourcemaps.write( './maps' ) )
	.pipe( gulp.dest( './assets/dist/css/' ) )
	.pipe( browserSync.stream() );

	update_assets_version();

	done();

} );

gulp.task( 'compress_js', function ( done ) {

	pump( [
		gulp.src( './assets/src/js/**/*.js' ),
		uglify( {
			mangle: {
				eval: true
			}
		} ),
		gulp.dest( './assets/dist/js/' )
	], done );

	update_assets_version();

} );

gulp.task( 'serve', gulp.series( 'styles', 'compress_js', 'make_pot', function ( done ) {

	browserSync.init( {
		proxy: "localhost"
	} );

	gulp.watch( [
		'assets/src/css/**/*.scss',
		'assets/src/css/**/*.css',
		'style.css'
	], gulp.series( 'styles' ) );

	gulp.watch( [
		'assets/src/js/**/*.js'
	], gulp.series( 'compress_js' ) );

	gulp.watch( [
		'**/*.php'
	], gulp.series( 'make_pot' ) );

	done();

} ) );