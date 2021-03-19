const path = require("path");
const webpack = require("webpack");
const { merge } = require("webpack-merge");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const WebpackBuildNotifierPlugin = require('webpack-build-notifier');

const devMode = process.env.NODE_ENV !== "production";
const ROOT = __dirname;
const ASSETS = ROOT + "/assets";
const SRC = ASSETS + "/src";
const DIST = ASSETS + "/dist";

module.exports = {
	entry: {
		app: path.join(SRC, "/js/main.js")
	},
	mode: devMode ? "development" : "production",
	output: {
		filename: 'js/[name].bundle.js',
		path: DIST
	},
	resolve: {
		modules: ['node_modules']
	},
	plugins: [
		new webpack.ProvidePlugin({
			$: "jquery",
			jQuery: "jquery",
			"window.jQuery": "jquery",
			"window.$": "jquery"
		}),
		new MiniCssExtractPlugin({
			filename: "css/storage.css"
		}),
    new WebpackBuildNotifierPlugin({
      title: "My Webpack Project",
      // logo: path.resolve("./img/favicon.png"),
      // suppressSuccess: true, // don't spam success notifications
      sound: true
    })
	],
	module: {
		noParse: /^(vue|vue-router|vuex|vuex-router-sync)$/,
		rules: [
			{
				test: /\.(css|scss|sass)$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							sourceMap: false,
							importLoaders: 2,
							modules: false
						}
					},
					{
						loader: "postcss-loader",
						options: {
							postcssOptions: {
								ident: "postcss",
								plugins: [require("autoprefixer")]	
							}
						}
					},
					"sass-loader"
				],
			}
		]
	},
	watch: true,
	devtool: 'source-map'
};


if (process.env.NODE_ENV === "production") {
	const production = {
		devtool: "none",
		plugins: [
			new OptimizeCSSAssetsPlugin(),
		],
	};

	module.exports = merge(module.exports, production);
}