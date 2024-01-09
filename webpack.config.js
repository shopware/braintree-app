const path = require("path");
const Encore = require('@symfony/webpack-encore');
const Dotenv = require('dotenv-webpack');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('admin', './assets/admin.ts')

    .splitEntryChunks()

    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    .enableSassLoader()
    .enableTypeScriptLoader()
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

Encore.addPlugin(
    new Dotenv({
        path: ".env.local",
        defaults: ".env",
        systemvars: true,
        allowEmptyValues: true,
    })
)

Encore.configureDefinePlugin(options => {
    options.__VUE_OPTIONS_API__ = true;
    options.__VUE_PROD_DEVTOOLS__ = false;
});

module.exports = Encore.getWebpackConfig();
