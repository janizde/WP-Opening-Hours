const gulp = require("gulp");
const gulpZip = require("gulp-zip");

const exportTask = gulp.series(clean, build, function () {
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
    "!.travis.yml",
    "!yarn.lock",
    "!.prettierrc",
  ];

  return gulp.src(files).pipe(gulpZip("wp-opening-hours.zip")).pipe(gulp.dest("."));
});

exports.export = exportTask;
