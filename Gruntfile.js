module.exports = function (grunt) {

    grunt.initConfig({
        pkg:        grunt.file.readJSON( 'package.json' ),

        jshint:     {
            files:      [ 'Gruntfile.js', 'javascript/_*.js' ],
            options:    {
                globals:    {
                    jQuery:     true
                }
            }
        },

        concat:     {
            dist:       {
                src:        ['javascript/_*.js', 'includes/jquery-ui-timepicker/jquery.ui.timepicker.js'],
                dest:       'javascript/opening-hours.js'
            }
        },

        uglify:     {
            options:    {
                banner:     '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist:       {
                files:      {
                    'javascript/opening-hours.min.js':  ['javascript/opening-hours.js']
                }
            }
        },

        less:       {
            dev:        {
                files:      {
                    'css/opening-hours.css' :   'less/main.less'
                },
                options:    {
                    strictUnit:     true
                }
            },

            build:      {
                files:      {
                    'css/opening-hours.min.css':    'less/main.less'
                },

                options:    {
                    compress:       true
                }
            }
        },

        watch:      {
            files:      ['less/main.less', 'less/_*.less', 'javascript/_*.js'],
            tasks:      ['dev']
        }
    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask(
        'dev',
        ['jshint', 'concat', 'less:dev', 'watch']
    );

    grunt.registerTask(
        'build',
        ['jshint', 'concat', 'uglify', 'less:build']
    );

    grunt.registerTask(
        'default',
        'dev'
    );

};