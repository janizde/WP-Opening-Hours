var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var jshint = require('gulp-jshint');
var sass = require('gulp-sass');
var cssmin = require('gulp-minify-css');
var watch = require('gulp-watch');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var gulpZip = require('gulp-zip');
var gulpIf = require('gulp-if');
var merge = require('merge-stream');
var runSequence = require('run-sequence');

var paths = {
  src: {
    scripts: {
      main: [
        './includes/jquery-ui-timepicker/jquery.ui.timepicker.js',
        './assets/scripts/**/_*.js'
      ],
      others: [
        './assets/scripts/tinyMCE.js',
        './assets/scripts/noneditable.js'
      ]
    },
    styles: [
      './assets/styles/main.scss',
      './assets/styles/tiny-mce.scss'
    ]
  },
  dest: {
    scripts: './dist/scripts/',
    styles: './dist/styles/'
  }
};

gulp.task( 'scripts', [], function () {
  var main = gulp.src( paths.src.scripts.main )
    .pipe( jshint() )
    .pipe( sourcemaps.init() )
    .pipe( concat( 'main.js' ) )
    .pipe( uglify() )
    .pipe( sourcemaps.write('.') )
    .pipe( gulp.dest( paths.dest.scripts ) );

  var others = gulp.src(paths.src.scripts.others)
    .pipe(jshint())
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.dest.scripts));

  return merge(main, others);
} );

gulp.task( 'styles', [], function () {
  return gulp.src( paths.src.styles )
    .pipe( sourcemaps.init() )
    .pipe( gulpIf('*.scss', sass()) )
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

  watch( './assets/styles/**/*.scss', function () {
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
    '!.gitmodules',
    '!gulpfile.js',
    '!package.json',
    '!composer.lock',
    '!phpunit.xml',
    '!./tests/**/*',
    '!tests',
    '!./doc/**/*',
    '!doc',
    '!.travis.yml',
    '!./dist/**/*.map'
  ];

  return gulp.src( files )
    .pipe( gulpZip( 'wp-opening-hours.zip' ) )
    .pipe( gulp.dest( '.' ) );
} );