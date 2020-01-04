const gulp = require("gulp");
const uglify = require("gulp-uglify");
const concat = require("gulp-concat");
const jshint = require("gulp-jshint");
const sass = require("gulp-sass");
const cleanCSS = require("gulp-clean-css");
const sourcemaps = require("gulp-sourcemaps");
const autoprefixer = require("gulp-autoprefixer");
const gulpZip = require("gulp-zip");
const gulpIf = require("gulp-if");
const del = require("del");

const paths = {
  src: {
    scripts: ["./includes/jquery-ui-timepicker/jquery.ui.timepicker.js", "./assets/scripts/**/*.js"],
    styles: ["./assets/styles/main.scss", "./includes/jquery-ui-timepicker/jquery.ui.timepicker.css"]
  },
  dest: {
    scripts: "./dist/scripts/",
    styles: "./dist/styles/"
  }
};

function scripts() {
  return gulp
    .src(paths.src.scripts)
    .pipe(jshint())
    .pipe(sourcemaps.init())
    .pipe(concat("main.js"))
    .pipe(uglify())
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest(paths.dest.scripts));
}

function styles() {
  return gulp
    .src(paths.src.styles)
    .pipe(sourcemaps.init())
    .pipe(gulpIf("*.scss", sass()))
    .pipe(concat("main.css"))
    .pipe(autoprefixer())
    .pipe(cleanCSS())
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest(paths.dest.styles));
}

const build = gulp.parallel(styles, scripts);

const watch = gulp.series(build, function() {
  gulp.watch(paths.src.scripts, scripts);
  gulp.watch(paths.src.styles, styles);
});

function clean() {
  return del("dist");
}

const exportTask = gulp.series(clean, build, function() {
  const files = [
    "./**/*",
    "!./node_modules/**/*",
    "!node_modules",
    "!./assets/**/*",
    "!assets",
    "!./vendor/**/*",
    "!vendor",
    "!**/.git/**/*",
    "!.gitignore",
    "!.gitmodules",
    "!gulpfile.js",
    "!package.json",
    "!composer.lock",
    "!phpunit.xml",
    "!./tests/**/*",
    "!tests",
    "!./doc/**/*",
    "!doc",
    "!.travis.yml"
  ];

  return gulp
    .src(files)
    .pipe(gulpZip("wp-opening-hours.zip"))
    .pipe(gulp.dest("."));
});

exports.scripts = scripts;
exports.styles = styles;
exports.export = exportTask;
exports.clean = clean;
exports.build = build;
exports.watch = watch;
