'use strict';

/*
 * NPM Packages
 */
 const gulp = require('gulp'),
 	notify = require('gulp-notify'),
 	cache = require('gulp-cache'),
 	concat = require('gulp-concat'),
    mqpacker = require('css-mqpacker'),
 	cssnano = require('cssnano'),
    imagemin = require('gulp-imagemin'),
    pngquant = require('imagemin-pngquant'),
    svgstore = require('gulp-svgstore'),
    svgmin = require('gulp-svgmin'),
 	uglify = require('gulp-uglify-es').default,
 	postcss = require('gulp-postcss'),
 	autoprefixer = require('autoprefixer'),
    htmlclean = require('gulp-htmlclean'),
    browserSync = require('browser-sync').create(),
    reload = browserSync.reload,
    removeLogging = require('gulp-remove-logging'),
 	sass = require('gulp-sass'),
 	rev = require('gulp-rev'),
    environments = require('gulp-environments'),
    order = require('gulp-order'),
    purify = require('gulp-purifycss'),
    pump = require('pump'),
 	del = require('del'),
    path = require('path');

 sass.compiler = require('node-sass');


/*
 * Variables
 */
// ENV vars
const development = environments.development,
    production = environments.production;

// Bring in env gulp file if development envoirnment
const config = development() ? require('./.env-gulp.json') : '';

const basePaths = {
    root: 'web/',
    frontend: 'frontend/',
    assets: 'web/assets/',
    revManifest: './'
};
const paths = {
    graphics: basePaths.assets + 'graphics/',
    scripts: basePaths.frontend + 'js/',
    scss: basePaths.frontend + 'scss/',
    dist: basePaths.assets + 'dist/',
    templates: './templates/'
};

const sassPrecision = 10;


/*** Functions ***/
function clean() {

    return del([paths.dist]).then(paths => {
	    console.log('Deleted files and folders:\n', paths.join('\n'));
	});

}// END clean

function css(cb) {

    var devPlugins = [
            autoprefixer(),
            mqpacker()
        ],
        prodPlugins = [
            autoprefixer(),
            mqpacker(),
            cssnano()
        ];

    pump(
        [
            gulp.src(paths.scss + '*.scss'),
	        sass({
    	        precision: sassPrecision,
    	        sourceComments: production() ? false : true,
    	        outputStyle: 'nested'
	        }).on('error', sass.logError),
        	postcss(development() ? devPlugins : prodPlugins),
    		gulp.dest(paths.dist),
    		development(browserSync.stream()),
            development(notify({ message: 'Scss task complete' }))
        ],
        cb
    );

}// END css

function jsBundle(cb) {

    pump(
        [
            gulp.src(paths.scripts + 'src/**/*.js'),
			order([
				'vendor/**/*.js',
				'app/**/*.js'
			]),
            concat('main.min.js'),
            gulp.dest(paths.dist),
            development(notify({ message: 'App scripts concat task complete' }))
        ]
    );

    pump(
        [
            gulp.src(paths.scripts + 'write/**/*.js'),
			order([
                'vendor/**/*.js',
				'vendor-dev/**/*.js',
				'app/**/*.js'
			]),
            concat('write.min.js'),
            gulp.dest(paths.dist),
            development(notify({ message: 'Write scripts concat task complete' }))
        ],
        cb
    );

}// END jsBundle

function jsMinify(cb) {

    return gulp
        .src(paths.dist + '*.js')
        .pipe(
            production(
                uglify({
                    mangle: false
                })
            )
        )
        .pipe(gulp.dest(paths.dist))
        .pipe(development(reload({stream: true})))
        .pipe(development(notify({ message: 'JS minify task complete' })))

}// END jsMinify

function images() {

    return gulp
        .src(paths.graphics + '**/*.{jpg,jpeg,png,svg}')
        .pipe(
            imagemin([
                imagemin.gifsicle({ interlaced: true }),
                imagemin.jpegtran({ progressive: true }),
                imagemin.optipng({ optimizationLevel: 5 }),
                imagemin.svgo({
                    plugins: [
                        {
                            removeViewBox: false,
                            collapseGroups: true
                        }
                    ]
                })
            ])
        )
        .pipe(gulp.dest(paths.graphics))
        .pipe(development(notify({ message: 'Image min task complete' })));

}// END imagemin

function templates() {

    return gulp.src(paths.templates + '**/*.{html,twig}')
        .pipe(htmlclean())
        .pipe(gulp.dest(paths.templates))
        .pipe(development(notify({ message: 'Templates task complete' })));

}// END templates

function revFiles(){

    return gulp.src([paths.dist + '*.{css,js}'])
        .pipe(rev())
        .pipe(gulp.dest(paths.dist))  // write rev'd assets to build dir
        .pipe(rev.manifest())
        .pipe(gulp.dest(basePaths.revManifest)) // write manifest to build dir
        .pipe(development(notify({ message: 'Rev task complete' })));

}// END rev

function watchFiles() {

    // Watch Sass
	gulp.watch(paths.scss + '**/*.scss', css);

    // Watch JS
	gulp.watch(paths.scripts + '**/*.js', js);

    // Watch templates
    gulp.watch(paths.templates + '**/*.{html,twig}').on('change', reload);

    // Watch images
	gulp.watch(paths.graphics + '**/*').on('change', reload);

}// END watchFiles

function browserSyncReload() {

    const https = config.browserSyncHttps ? 'https://' : 'http://';

    browserSync.init({
        proxy: {
            target: https + config.browserSyncProxy
        },
        host: config.browserSyncProxy,
        open: 'external'
    });

}// END browserSyncReload


/*** Tasks ***/
// Define complex tasks
const js = gulp.series(jsBundle, jsMinify);
const watch = gulp.parallel(watchFiles, browserSyncReload);
const project = gulp.series(
    clean,
    gulp.parallel(
        css,
        jsBundle
    )
);
const build = gulp.series(
    clean,
    gulp.series(
        gulp.parallel(
            css,
            js,
            images
        ),
        revFiles
    )
);
const projectDefault = gulp.parallel(project, watch);


// Export tasks
exports.project = project;
exports.default = projectDefault;
exports.watch = watch;
exports.css = css;
exports.js = js;

exports.default = projectDefault;
exports.dev = projectDefault;
exports.build = build;
exports.buildProd = gulp.parallel(build, templates);
