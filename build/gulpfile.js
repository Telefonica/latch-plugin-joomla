var gulp = require('gulp');

var extension = require('./package.json');
var config    = require('./gulp-config.json');

var requireDir = require('require-dir');
var zip        = require('gulp-zip');
var fs         = require('fs');
var xml2js     = require('xml2js');
var parser     = new xml2js.Parser();

var jgulp = requireDir('./node_modules/joomla-gulp', {recurse: true});
var dir = requireDir('./joomla-gulp-extensions', {recurse: true});

var rootPath = '../extensions';
var packageName = 'latch';
var manifestFile = 'pkg_' + packageName + '.xml';
var manifestPath = rootPath + '/' + manifestFile;

// Override of the release script
gulp.task('release', ['composer:libraries.' + packageName], function (cb) {
	fs.readFile(manifestPath, function(err, data) {
		parser.parseString(data, function (err, result) {
			var version = result.extension.version[0];

			var fileName = extension.name + '-v' + version + '.zip';

			return gulp.src([
					rootPath + '/**/*',
					'!' + rootPath + '/libraries/' + packageName + '/vendor/**/test',
					'!' + rootPath + '/libraries/' + packageName + '/**/composer.*'
				],{ base: rootPath })
				.pipe(zip(fileName))
				.pipe(gulp.dest('releases'))
				.on('end', cb);
		});
	});
});
