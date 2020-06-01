const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')
const StyleLintPlugin = require('stylelint-webpack-plugin')
const packageJson = require('./package.json')
const appName = packageJson.name

module.exports = {
	entry: {
		Questionaire: path.join(__dirname, 'src', 'Questionaire.js'),
		Settings: path.join(__dirname, 'src', 'Settings.js')
	},
	output: {
		path: path.resolve(__dirname, './js'),
		publicPath: '/js/',
		filename: `${appName}_[name].js`,
		chunkFilename: 'chunks/[name]-[hash].js'
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				exclude: [ /node_modules/ ],
				use: ['vue-style-loader', 'css-loader']
			},
			{
				test: /\.scss$/,
				exclude: [ /node_modules/ ],
				use: ['vue-style-loader', 'css-loader', 'sass-loader']
			},
			{
				test: /\.(js|vue)$/,
				use: 'eslint-loader',
				exclude: [ /node_modules/, /\.min\.js$/ ],
				enforce: 'pre'
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				exclude: /node_modules/
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/
			}
		]
	},
	plugins: [
		new VueLoaderPlugin(),
		new StyleLintPlugin({ files: "**/*.(sc|sa|c)ss" })
	],
	resolve: {
		extensions: ['*', '.js', '.vue']
	}
}
