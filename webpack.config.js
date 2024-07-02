const path = require("path");
const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('admin', './assets/admin.ts')

    // we currently only have one entry point
    // .splitEntryChunks()

    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.unshift(['@babel/plugin-transform-typescript', {}])
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.37';
    })

    .enableEslintPlugin((options) => {
        options.extensions.push('.ts', '.vue');
    })
    .enableSassLoader()
    .enableBabelTypeScriptPreset()
    .enableVueLoader(() => {}, { runtimeCompilerBuild: true })
    .enableIntegrityHashes(Encore.isProduction())

    .copyFiles({
        from: './assets/img',
        to: 'img/[path][name].[ext]',
        pattern: /\.(png|jpg|jpeg|svg|webp)$/,
    })

    .addAliases({
        '@': path.resolve(__dirname, 'assets/src'),
    })
;

Encore.configureDefinePlugin(options => {
    options.__VUE_OPTIONS_API__ = true;
    options.__VUE_PROD_DEVTOOLS__ = false;
});

module.exports = Encore.getWebpackConfig();
