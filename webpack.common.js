const path = require( 'path' );
const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );
const combineLoaders = require( 'webpack-combine-loaders' );
const autoprefixer = require( 'autoprefixer' );
const externals = {
	jquery: 'jQuery',
	'@eventespresso/eejs': 'eejs',
	'@eventespresso/i18n': 'eejs.i18n',
};
/** see below for multiple configurations.
 /** https://webpack.js.org/configuration/configuration-types/#exporting-multiple-configurations */
const config = [
	{
		configName: 'base',
		entry: {
			//@todo this is where bundles will go for builds
		},
		externals,
		output: {
			filename: 'ee-[name].[chunkhash].dist.js',
			path: path.resolve( __dirname, 'assets/dist' ),
		},
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: 'babel-loader',
				},
				{
					test: /\.css$/,
					loader: ExtractTextPlugin.extract(
						combineLoaders( [
							{
								loader: 'css-loader',
								query: {
									modules: true,
									localIdentName: '[local]',
								},
								//can't use minimize because cssnano (the
								// dependency) doesn't parser the browserlist
								// extension in package.json correctly, there's
								// a pending update for it but css-loader
								// doesn't have the latest yet.
								// options: {
								//     minimize: true
								// }
							},
							{
								loader: 'postcss-loader',
								options: {
									plugins: function() {
										return [ autoprefixer ];
									},
									sourceMap: true,
								},
							},
						] ),
					),
				},
			],
		},
		watchOptions: {
			poll: 1000,
		},
	},
];
module.exports = config;
