var gulp = require('gulp');

var config = require('../../gulp-config.json');

// Dependencies
var beep        = require('beepbeep');
var browserSync = require('browser-sync');
var concat      = require('gulp-concat');
var del         = require('del');
var composer    = require('gulp-composer');
var gutil       = require('gulp-util');
var path        = require('path');
var plumber     = require('gulp-plumber');
var rename      = require('gulp-rename');
var uglify      = require('gulp-uglify');

var libraryName = 'latch';

var baseTask        = 'libraries.' + libraryName;
var extPath         = '../extensions/libraries/' + libraryName;
var manifestFile    = libraryName + '.xml';
var mediaPath       = extPath + '/media/' + libraryName;
var assetsPath      = './media/libraries/' + libraryName;
var nodeModulesPath = './node_modules';

var wwwManifestsFolder = config.wwwDir + '/administrator/manifests/libraries';
var wwwPath            = config.wwwDir + '/libraries/' + libraryName;
var wwwMediaPath       = config.wwwDir + '/media/' + libraryName;

var onError = function (err) {
    beep([0, 0, 0]);
    gutil.log(gutil.colors.green(err));
};

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':library',
		'clean:' + baseTask + ':manifest',
		'clean:' + baseTask + ':media'
	],
	function() {
});

// Clean: library
gulp.task('clean:' + baseTask + ':library', function(cb) {
	return del(wwwPath, {force : true});
});

// Clean: manifest
gulp.task('clean:' + baseTask + ':manifest', function(cb) {
	return del(wwwManifestsFolder + '/' + manifestFile, {force : true});
});

// Clean: Media
gulp.task('clean:' + baseTask + ':media', function() {
	return del(wwwMediaPath, {force: true});
});

// Composer
gulp.task('composer:' + baseTask, function(cb) {
	composer({ cwd: extPath}).on('end', cb);
});

// Copy
gulp.task('copy:' + baseTask,
	[
		'copy:' + baseTask + ':library',
		'copy:' + baseTask + ':manifest'
	],
	function() {
});

// Copy: library
gulp.task('copy:' + baseTask + ':library',
	['clean:' + baseTask + ':library'], function() {
	return gulp.src([
		extPath + '/**',
		'!' + extPath + '/vendor/**/composer.*'
	],{ base: extPath })
	.pipe(gulp.dest(wwwPath));
});

// Copy: manifest
gulp.task('copy:' + baseTask + ':manifest', ['clean:' + baseTask + ':manifest'], function() {
	return gulp.src(extPath + '/' + manifestFile)
		.pipe(gulp.dest(config.wwwDir + '/administrator/manifests/libraries'));
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':library',
		'watch:' + baseTask + ':manifest'
	],
	function() {
});

// Watch: library
gulp.task('watch:' +  baseTask + ':library', function() {
	gulp.watch([
			extPath + '/**/*',
			'!' + extPath + '/' + manifestFile,
			'!' + extPath + '/media',
			'!' + extPath + '/media/**/*'
		], ['copy:' + baseTask + ':library', browserSync.reload]);
});

// Watch: manifest
gulp.task('watch:' +  baseTask + ':manifest', function() {
	gulp.watch(extPath + '/' + manifestFile, ['copy:' + baseTask + ':manifest', browserSync.reload]);
});
