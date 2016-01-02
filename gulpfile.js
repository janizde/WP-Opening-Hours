var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var jshint = require('gulp-jshint');
var less = require('gulp-less');
var cssmin = require('gulp-minify-css');
var watch = require('gulp-watch');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var gulpZip = require('gulp-zip');
var runSequence = require('run-sequence');

var paths = {
  src: {
    scripts: [
      './assets/scripts/**/*.js'
    ],
    styles: [
      './assets/styles/main.less'
    ]
  },
  dest: {
    scripts: './dist/scripts/',
    styles: './dist/styles/'
  }
};

gulp.task( 'scripts', [], function () {
  return gulp.src( paths.src.scripts )
    .pipe( jshint() )
    .pipe( sourcemaps.init() )
    .pipe( concat( 'main.js' ) )
    .pipe( uglify() )
    .pipe( sourcemaps.write('.') )
    .pipe( gulp.dest( paths.dest.scripts ) );
} );

gulp.task( 'styles', [], function () {
  return gulp.src( paths.src.styles )
    .pipe( sourcemaps.init() )
    .pipe( less() )
    .pipe( autoprefixer() )
    .pipe( cssmin() )
    .pipe( sourcemaps.write('.') )
    .pipe( gulp.dest( paths.dest.styles ) )
} );

gulp.task( 'watch', [], function () {
  gulp.start( 'scripts' );
  gulp.start( 'styles' );

  watch( paths.src.scripts, function () {
    gulp.start( 'scripts' );
  } );

  watch( './assets/styles/**/*.less', function () {
    gulp.start( 'styles' );
  } );
} );

gulp.task( 'build', [], function () {
  return runSequence( ['scripts', 'styles'] );
} );

gulp.task( 'export', ['build'], function () {
  var files = [
    './**/*',
    '!./node_modules/**/*',
    '!node_modules',
    '!./assets/**/*',
    '!assets',
    '!./vendor/**/*',
    '!vendor',
    '!**/.git/**/*',
    '!.gitignore',
    '!gulpfile.js',
    '!package.json',
    '!composer.lock'
  ];

  return gulp.src( files )
    .pipe( gulpZip( 'opening-hours.zip' ) )
    .pipe( gulp.dest( '.' ) );
} );