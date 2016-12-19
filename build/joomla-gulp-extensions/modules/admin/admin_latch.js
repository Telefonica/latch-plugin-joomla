var gulp      = require('gulp');
var config    = require('../../../gulp-config.json');
var extension = require('../../../package.json');

// Dependencies
var beep        = require('beepbeep');
var browserSync = require('browser-sync');
var concat      = require('gulp-concat');
var del         = require('del');
var gutil       = require('gulp-util');
var path        = require('path');
var plumber     = require('gulp-plumber');
var rename      = require('gulp-rename');
var uglify      = require('gulp-uglify');

var modName   = "admin_latch";
var modFolder = "mod_" + modName;
var modBase   = "admin";

var baseTask  = 'modules.backend.' + modName;
var extPath   = '../extensions/modules/' + modBase + '/' + modFolder;
var mediaPath = extPath + '/media/' + modFolder;
var assetsPath = './media/modules/' + modBase + '/' + modFolder;
var nodeModulesPath = './node_modules';

var wwwPath = config.wwwDir + '/administrator/modules/' + modFolder
var wwwMediaPath = config.wwwDir + '/media/' + modFolder;

var onError = function (err) {
    beep([0, 0, 0]);
    gutil.log(gutil.colors.green(err));
};

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':module',
		'clean:' + baseTask + ':media'
	],
	function() {
	});

// Clean: Module
gulp.task('clean:' + baseTask + ':module', function() {
	return del(wwwPath, {force: true});
});

// Clean: Media
gulp.task('clean:' + baseTask + ':media', function() {
	return del(wwwMediaPath, {force: true});
});

// Copy: Module
gulp.task('copy:' + baseTask,
	[
		'clean:' + baseTask,
		'copy:' + baseTask + ':module',
		'copy:' + baseTask + ':media'
	],
	function() {
	});

// Copy: Module
gulp.task('copy:' + baseTask + ':module', ['clean:' + baseTask + ':module'], function() {
	return gulp.src([
			extPath + '/**',
			'!' + extPath + '/media',
			'!' + extPath + '/media/**'
		])
		.pipe(gulp.dest(wwwPath));
});

// Copy: Media
gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
	return gulp.src([
			mediaPath + '/**'
		])
		.pipe(gulp.dest(wwwMediaPath));
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':module',
		'watch:' + baseTask + ':media'
	],
	function() {
	});

// Watch: Module
gulp.task('watch:' + baseTask + ':module', function() {
	gulp.watch([
			extPath + '/**/*',
			'!' + extPath + '/media',
			'!' + extPath + '/media/**/*'
		],
		['copy:' + baseTask + ':module', browserSync.reload]);
});

// Watch: media
gulp.task('watch:' + baseTask + ':media', function() {
	gulp.watch([
			extPath + '/media/**/*'
		],
		['copy:' + baseTask + ':media']);
});

