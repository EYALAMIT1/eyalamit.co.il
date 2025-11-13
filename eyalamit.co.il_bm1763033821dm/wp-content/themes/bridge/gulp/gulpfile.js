// Include gulp
var gulp = require('gulp');
var browserSync = require('browser-sync').create();

// Include Plugins
var jshint = require('gulp-jshint');
var sass = require('gulp-sass');
var sassGlob = require('gulp-sass-glob');
var sourcemaps = require('gulp-sourcemaps');
var concat = require('gulp-concat');
var concatCss = require('gulp-concat-css');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var cssmin = require('gulp-cssmin');
var debug  = require('gulp-debug');

/****************** GENERAL THEME SETTINGS - START ******************/

// Concatenate theme js files
gulp.task('concat-js', function () {
	return gulp.src([
		'../js/default-temp.js',
        '../../../plugins/bridge-core/modules/shortcodes/shortcode-elements/**/assets/js/**.js',
		'../includes/shortcodes/shortcode-elements/**/assets/js/**.js'

	]).pipe(concat('../js/default.js'))
		.pipe(gulp.dest('../js'));
});

// Minify JS
gulp.task('minifyjs',  ['concat-js'],  function () {
    return gulp.src([
        '../js/default.js',
        '../js/ajax.js',
        '../framework/modules/woocommerce/assets/js/woocommerce.js'
    ]).pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../js'))
});

// Compile Theme Sass
gulp.task('sass', function () {
    return gulp.src('../css/scss/*.scss')
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sassGlob())
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(sourcemaps.write('../css'))
        .pipe(gulp.dest('../css'))
        .pipe(browserSync.stream({match: '**/*.css'}))
});

// Compile Woo Sass
gulp.task('woo-sass', function () {
    return gulp.src('../framework/modules/woocommerce/assets/css/scss/*.scss')
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sassGlob())
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(sourcemaps.write('../css'))
        .pipe(gulp.dest('../css'))
});

//Minify css files
gulp.task('minifycss', ['sass', 'woo-sass'], function () {
    return gulp.src([
        '../css/stylesheet.css',
        '../css/responsive.css',
        '../css/vertical_responsive.css',
        '../css/woocommerce.css',
        '../css/woocommerce_responsive.css',
        '../css/timetable-schedule.css',
        '../css/timetable-schedule-responsive.css'
    ])
        .pipe(cssmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../css'))
        .pipe(browserSync.stream())
});

/****************** GENERAL THEME SETTINGS - END ******************/

/****************** NEWS PLUGIN SETTINGS - START ******************/

// Concatenate news js files and minify them
gulp.task('news-js', function () {
    return gulp.src([
        '../../../plugins/qode-news/assets/js/modules/*.js',
        '../../../plugins/qode-news/modules/**/assets/js/*.js',
    ]).pipe(concat('news.js'))
        .pipe(gulp.dest('../../../plugins/qode-news/assets/js'))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../../../plugins/qode-news/assets/js'));
});

// Compile News Sass
gulp.task('news-sass', function () {
    return gulp.src('../../../plugins/qode-news/assets/css/scss/*.scss')
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sassGlob())
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(sourcemaps.write('../../../qode-news/assets/css'))
        .pipe(gulp.dest('../../../plugins/qode-news/assets/css'))
        .pipe(cssmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../../../plugins/qode-news/assets/css'))
        .pipe(browserSync.stream())
});

/****************** NEWS PLUGIN SETTINGS - END ******************/

/****************** MUSIC PLUGIN SETTINGS - START ******************/

// Concatenate news js files and minify them
gulp.task('music-js', function () {
	return gulp.src([
		'../../../plugins/qode-music/post-types/**/assets/js/*.js',
	]).pipe(concat('music.js'))
		.pipe(gulp.dest('../../../plugins/qode-music/assets/js'))
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-music/assets/js'));
});

// Compile News Sass
gulp.task('music-sass', function () {
	return gulp.src('../../../plugins/qode-music/assets/css/scss/*.scss')
		.pipe(sourcemaps.init({loadMaps: true}))
		.pipe(sassGlob())
		.pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
		.pipe(sourcemaps.write('../../../qode-music/assets/css'))
		.pipe(gulp.dest('../../../plugins/qode-music/assets/css'))
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-music/assets/css'))
		.pipe(browserSync.stream())
});

/****************** MUSIC PLUGIN SETTINGS - END ******************/

/****************** LISTING PLUGIN SETTINGS - START ******************/

// Concatenate listing js files and minify them
gulp.task('listing-js', function () {
	return gulp.src([
		'../../../plugins/qode-listing/assets/js/front/*.js',
		'../../../plugins/qode-listing/modules/**/assets/js/front/*.js',
	]).pipe(concat('listing.js'))
		.pipe(gulp.dest('../../../plugins/qode-listing/assets/js'))
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-listing/assets/js'));
});

// Compile Listing Sass
gulp.task('listing-sass', function () {
	return gulp.src('../../../plugins/qode-listing/assets/css/scss/*.scss')
		.pipe(sourcemaps.init({loadMaps: true}))
		.pipe(sassGlob())
		.pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
		.pipe(sourcemaps.write('../../../qode-listing/assets/css'))
		.pipe(gulp.dest('../../../plugins/qode-listing/assets/css'))
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-listing/assets/css'))
		.pipe(browserSync.stream())
});

/****************** LISTING PLUGIN SETTINGS - END ******************/

/****************** LMS PLUGIN SETTINGS - START ******************/

// Concatenate lms js files and minify them
gulp.task('lms-js', function () {
	return gulp.src([
		'../../../plugins/qode-lms/post-types/**/assets/js/*.js',
		'../../../plugins/qode-lms/reviews/assets/js/reviews.js'
	]).pipe(concat('lms.js'))
		.pipe(gulp.dest('../../../plugins/qode-lms/assets/js'))
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-lms/assets/js'));
});
// Compile lms Sass
gulp.task('lms-sass', function () {
	return gulp.src('../../../plugins/qode-lms/assets/css/scss/*.scss')
		.pipe(sourcemaps.init({loadMaps: true}))
		.pipe(sassGlob())
		.pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
		.pipe(sourcemaps.write('../../../../plugins/qode-lms/assets/css'))
		.pipe(gulp.dest('../../../plugins/qode-lms/assets/css'))
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-lms/assets/css'))
		.pipe(browserSync.stream())
});

/****************** LMS PLUGIN SETTINGS - END ******************/

/****************** TOURS PLUGIN SETTINGS - START ******************/

// Concatenate lms js files and minify them
gulp.task('tours-js', function () {
	return gulp.src([
		'../../../plugins/qode-tours/post-types/**/assets/js/modules/*.js',
		'../../../plugins/qode-tours/post-types/tours/reviews/assets/js/reviews.js'
	]).pipe(concat('tours.js'))
		.pipe(gulp.dest('../../../plugins/qode-tours/assets/js'))
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-tours/assets/js'));
});
// Compile lms Sass
gulp.task('tours-sass', function () {
	return gulp.src('../../../plugins/qode-tours/assets/css/scss/*.scss')
		.pipe(sourcemaps.init({loadMaps: true}))
		.pipe(sassGlob())
		.pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
		.pipe(sourcemaps.write('../../../../plugins/qode-tours/assets/css'))
		.pipe(gulp.dest('../../../plugins/qode-tours/assets/css'))
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-tours/assets/css'))
		.pipe(browserSync.stream())
});

/****************** TOURS PLUGIN SETTINGS - END ******************/

/****************** RESTAURANT PLUGIN SETTINGS - START ******************/

// Concatenate restaurant js files and minify them
gulp.task('restaurant-js', function () {
    return gulp.src([
        '../../../plugins/qode-restaurant/assets/js/modules/*.js',
        '../../../plugins/qode-restaurant/modules/**/assets/js/*.js',
    ]).pipe(concat('qode-restaurant.js'))
        .pipe(gulp.dest('../../../plugins/qode-restaurant/assets/js'))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../../../plugins/qode-restaurant/assets/js'));
});

// Compile Restaurant Sass
gulp.task('restaurant-sass', function () {
    return gulp.src('../../../plugins/qode-restaurant/assets/css/scss/*.scss')
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sassGlob())
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(sourcemaps.write('../../../qode-restaurant/assets/css'))
        .pipe(gulp.dest('../../../plugins/qode-restaurant/assets/css'))
        .pipe(cssmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../../../plugins/qode-restaurant/assets/css'))
        .pipe(browserSync.stream())
});

/****************** RESTAURANT PLUGIN SETTINGS - END ******************/

/****************** PHOTOGRAPHY PLUGIN SETTINGS - START ******************/

// Concatenate news js files and minify them
gulp.task('photography-js', function () {
	return gulp.src([
		'../../../plugins/qode-photography/post-types/**/assets/js/*.js',
	]).pipe(concat('photography.js'))
		.pipe(gulp.dest('../../../plugins/qode-photography/assets/js'))
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-photography/assets/js'));
});

// Compile News Sass
gulp.task('photography-sass', function () {
	return gulp.src('../../../plugins/qode-photography/assets/css/scss/*.scss')
		.pipe(sourcemaps.init({loadMaps: true}))
		.pipe(sassGlob())
		.pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
		.pipe(sourcemaps.write('../../../qode-photography/assets/css'))
		.pipe(gulp.dest('../../../plugins/qode-photography/assets/css'))
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-photography/assets/css'))
		.pipe(browserSync.stream())
});

/****************** PHOTOGRAPHY PLUGIN SETTINGS - END ******************/
/****************** DEMOS PLUGIN SETTINGS - START ******************/

// Concatenate news js files and minify them
gulp.task('demos-js', function () {
	return gulp.src([
		'../../../plugins/qode-demos/post-types/**/assets/js/*.js',
        '../../../plugins/qode-demos/assets/js/modules/*.js',
	]).pipe(concat('demos.js'))
		.pipe(gulp.dest('../../../plugins/qode-demos/assets/js'))
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-demos/assets/js'));
});

// Compile News Sass
gulp.task('demos-sass', function () {
	return gulp.src('../../../plugins/qode-demos/assets/css/scss/*.scss')
		.pipe(sourcemaps.init({loadMaps: true}))
		.pipe(sassGlob())
		.pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
		.pipe(sourcemaps.write('../../../qode-demos/assets/css'))
		.pipe(gulp.dest('../../../plugins/qode-demos/assets/css'))
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('../../../plugins/qode-demos/assets/css'))
		.pipe(browserSync.stream())
});

/****************** DEMOS PLUGIN SETTINGS - END ******************/
/****************** MEMBERSHIP PLUGIN SETTINGS - START ******************/

// Concatenate membership js files and minify them
gulp.task('membership-js', function () {
    return gulp.src([
        '../../../plugins/qode-membership/assets/js/modules/*.js',
    ]).pipe(concat('qode-membership.js'))
        .pipe(gulp.dest('../../../plugins/qode-membership/assets/js'))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../../../plugins/qode-membership/assets/js'));
});

// Compile membership Sass
gulp.task('membership-sass', function () {
    return gulp.src('../../../plugins/qode-membership/assets/css/scss/*.scss')
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sassGlob())
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(sourcemaps.write('../../../qode-membership/assets/css'))
        .pipe(gulp.dest('../../../plugins/qode-membership/assets/css'))
        .pipe(cssmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../../../plugins/qode-membership/assets/css'))
        .pipe(browserSync.stream())
});

/****************** MEMBERSHIP PLUGIN SETTINGS - END ******************/

/****************** HOTEL PLUGIN SETTINGS - START ******************/

// Concatenate hotel js files and minify them
gulp.task('hotel-js', function () {
    return gulp.src([
        '../../../plugins/qode-hotel/post-types/**/assets/js/*.js',
        '../../../plugins/qode-hotel/post-types/**/shortcodes/**/assets/js/*.js',
    ]).pipe(concat('hotel.js'))
        .pipe(gulp.dest('../../../plugins/qode-hotel/assets/js'))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../../../plugins/qode-hotel/assets/js'));
});

// Compile hotel Sass
gulp.task('hotel-sass', function () {
    return gulp.src('../../../plugins/qode-hotel/assets/css/scss/*.scss')
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sassGlob())
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(sourcemaps.write('../../../qode-hotel/assets/css'))
        .pipe(gulp.dest('../../../plugins/qode-hotel/assets/css'))
        .pipe(cssmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('../../../plugins/qode-hotel/assets/css'))
        .pipe(browserSync.stream())
});

/****************** HOTEL PLUGIN SETTINGS - END ******************/

// Default Task
gulp.task('default', ['sass','woo-sass', 'news-sass', 'music-sass', 'listing-sass', 'lms-sass', 'tours-sass', 'restaurant-sass', 'photography-sass', 'demos-sass', 'membership-sass', 'hotel-sass']);

// Minify Files
gulp.task('minify', ['minifyjs', 'news-js', 'music-js', 'listing-js', 'lms-js', 'restaurant-js', 'photography-js', 'demos-js', 'membership-js', 'hotel-js', 'minifycss', 'news-sass', 'music-sass', 'listing-sass', 'lms-sass', 'tours-sass', 'restaurant-sass', 'photography-sass', 'demos-sass', 'membership-sass', 'hotel-sass']);

// Watch Files For Changes
gulp.task('watch', function () {
    gulp.watch([
        '../../../plugins/qode-*/**/assets/css/scss/**/*.scss',
        '../css/scss/**/*.scss',
        '../framework/modules/woocommerce/assets/css/scss/**/*.scss',
        '../framework/modules/woocommerce/shortcodes/**/assets/css/scss/**/*.scss',
        '../framework/modules/woocommerce/plugins/**/assets/css/scss/**/*.scss',
    ], ['minifycss', 'news-sass', 'music-sass', 'listing-sass', 'lms-sass', 'tours-sass', 'restaurant-sass', 'photography-sass', 'demos-sass', 'membership-sass', 'hotel-sass']);
    gulp.watch([
        '../../../plugins/qode-*/assets/js/modules/*.js',
        '../../../plugins/qode-*/modules/**/assets/js/*.js',
        '../../../plugins/qode-listing/modules/**/assets/js/front/*.js',
        '../js/default-temp.js',
        '../js/ajax.js',
        '../framework/modules/woocommerce/assets/js/woocommerce.js'
    ], ['minifyjs', 'news-js', 'music-js', 'listing-js', 'lms-js', 'restaurant-js', 'photography-js', 'demos-js','membership-js', 'hotel-js']);
});

gulp.task('watch-theme', function () {
    gulp.watch([
        '../css/scss/**/*.scss',
        '../framework/modules/woocommerce/assets/css/scss/**/*.scss',
        '../framework/modules/woocommerce/shortcodes/**/assets/css/scss/**/*.scss',
        '../framework/modules/woocommerce/plugins/**/assets/css/scss/**/*.scss',
    ], ['minifycss']);
    gulp.watch([
        '../js/default-temp.js',
        '../js/ajax.js',
        '../framework/modules/woocommerce/assets/js/woocommerce.js'
    ], ['minifyjs']);
});

// Watch Files For Listing Changes
gulp.task('watch-listing', function () {
	gulp.watch([
		'../../../plugins/qode-listing/**/assets/css/scss/**/*.scss'
	], ['listing-sass']);
	gulp.watch([
		'../../../plugins/qode-listing/assets/js/modules/*.js',
		'../../../plugins/qode-listing/modules/**/assets/js/*.js',
		'../../../plugins/qode-listing/modules/**/assets/js/front/*.js'
	], ['listing-js']);
});
// Watch Files For LMS Changes
gulp.task('watch-lms', function () {
	gulp.watch([
		'../../../plugins/qode-lms/post-types/**/assets/css/scss/**/*.scss',
		'../../../plugins/qode-lms/reviews/assets/css/scss/**/*.scss'
	], ['lms-sass']);
	gulp.watch([
		'../../../plugins/qode-lms/post-types/**/assets/js/*.js',
		'../../../plugins/qode-lms/reviews/assets/js/*.js'
	], ['lms-js']);
});
// Watch Files For TOURS Changes
gulp.task('watch-tours', function () {
	gulp.watch([
		'../../../plugins/qode-tours/post-types/**/assets/css/scss/**/*.scss',
		'../../../plugins/qode-tours/reviews/assets/css/scss/**/*.scss'
	], ['tours-sass']);
	gulp.watch([
		'../../../plugins/qode-tours/post-types/**/assets/js/*.js',
		'../../../plugins/qode-tours/reviews/assets/js/*.js'
	], ['tours-js']);
});
// Watch Membership Files For Changes
gulp.task('watch-membership', function () {
	gulp.watch([
		'../../../plugins/qode-membership/assets/css/scss/**/*.scss'
	], ['membership-sass']);
	gulp.watch([
		'../../../plugins/qode-membership/assets/js/modules/*.js'
	], ['membership-js']);
});
// Watch News Files For Changes
gulp.task('watch-news', function () {
    gulp.watch([
        '../../../plugins/qode-news/**/assets/css/scss/**/*.scss',
    ], ['news-sass']);
    gulp.watch([
        '../../../plugins/qode-news/assets/js/modules/*.js',
        '../../../plugins/qode-news/modules/**/assets/js/*.js',
    ], ['news-js']);
});
// Watch Files For Demos Changes
gulp.task('watch-demos', function () {
	gulp.watch([
		'../../../plugins/qode-demos/assets/css/scss/**/*.scss',
		'../../../plugins/qode-demos/post-types/**/assets/css/scss/**/*.scss'
	], ['demos-sass']);
	gulp.watch([
        '../../../plugins/qode-demos/assets/js/modules/*.js',
		'../../../plugins/qode-demos/post-types/**/assets/js/*.js'
	], ['demos-js']);
});
// Watch Restaurant Files For Changes
gulp.task('watch-restaurant', function () {
    gulp.watch([
        '../../../plugins/qode-restaurant/**/assets/css/scss/**/*.scss',
    ], ['restaurant-sass']);
    gulp.watch([
        '../../../plugins/qode-restaurant/assets/js/modules/*.js',
        '../../../plugins/qode-restaurant/modules/**/assets/js/*.js',
    ], ['restaurant-js']);
});

// Watch Files For Hotel Changes
gulp.task('watch-hotel', function () {
    gulp.watch([
        '../../../plugins/qode-hotel/assets/css/scss/**/*.scss',
        '../../../plugins/qode-hotel/post-types/**/assets/css/scss/**/*.scss'
    ], ['hotel-sass']);
    gulp.watch([
        '../../../plugins/qode-hotel/assets/js/modules/*.js',
        '../../../plugins/qode-hotel/post-types/**/assets/js/*.js'
    ], ['hotel-js']);
});

// Watch with browser sync
gulp.task('dev', function () {
    browserSync.init({
        proxy: 'bridge.dev'
    });

    gulp.watch([
        '../../../plugins/qode-*/**/assets/css/scss/**/*.scss',
        '../css/scss/**/*.scss',
        '../framework/modules/woocommerce/assets/css/scss/**/*.scss',
        '../framework/modules/woocommerce/shortcodes/**/assets/css/scss/**/*.scss',
        '../framework/modules/woocommerce/plugins/**/assets/css/scss/**/*.scss',
    ], ['minifycss', 'news-sass', 'music-sass', 'listing-sass', 'lms-sass', 'tours-sass', 'restaurant-sass', 'photography-sass', 'demos-sass', 'membership-sass']);
    gulp.watch([
        '../../../plugins/qode-*/assets/js/modules/*.js',
        '../../../plugins/qode-*/modules/**/assets/js/*.js',
        '../js/default-temp.js',
        '../js/ajax.js',
        '../framework/modules/woocommerce/assets/js/woocommerce.js'
    ], ['minifyjs', 'news-js', 'music-js', 'listing-js', 'lms-js', 'restaurant-js', 'photography-js', 'demos-js', 'membership-js']);
});

// Compile Admin Sass
gulp.task('sass-admin', function () {
    return gulp.src('../framework/admin/assets/css/scss/*.scss')
        .pipe(sassGlob())
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(gulp.dest('../framework/admin/assets/css'));
});