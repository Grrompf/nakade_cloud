var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    .copyFiles({
        from: './assets/images',
        // optional target path, relative to the output dir
        //to: 'images/[path][name].[ext]',

        // if versioning is enabled, add the file hash too
        to: 'images/[path][name].[hash:8].[ext]',

        // only copy files matching this pattern
        pattern: /\.(png|jpg|jpeg|gif|ico|svg|webp)$/
    })

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')
    .addEntry('clubs', './assets/js/clubs.js')
    .addEntry('common', './assets/js/common.js')
    .addEntry('contact', './assets/js/contact.js')
    .addEntry('home', './assets/js/home.js')
    .addEntry('login', './assets/js/login.js')
    .addEntry('main', './assets/js/main.js')
    .addEntry('news', './assets/js/news.js')
    .addEntry('profile', './assets/js/profile.js')
    .addEntry('register', './assets/js/register.js')
    .addEntry('reply', './assets/js/reply.js')
    .addEntry('feature', './assets/js/feature.js')
    .addEntry('bundesliga', './assets/js/bundesliga.js')
    .addEntry('autocomplete', './assets/js/nakade-autocomplete.js')
    .addEntry('dgob', './assets/js/dgob.js')
    .addEntry('player-stats', './assets/js/player-stats.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    // enables @babel/preset-env polyfills
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // enables PostCSSLOader for browser compatibility
    .enablePostCssLoader()
    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')

;

module.exports = Encore.getWebpackConfig();
