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
		chunkFilename: 'chunks/[name].js'
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				exclude: [ /node_modules/, /wpcs/ ],
				use: ['vue-style-loader', 'css-loader']
			},
			{
				test: /\.scss$/,
				exclude: [ /node_modules/, /wpcs/ ],
				use: ['vue-style-loader', 'css-loader', 'sass-loader']
			},
			{
				test: /\.(js|vue)$/,
				use: 'eslint-loader',
				exclude: [ /node_modules/, /wpcs/, /\.min\.js$/ ],
				enforce: 'pre'
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				exclude: [ /node_modules/, /wpcs/ ],
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: [ /node_modules/, /wpcs/ ],
			}
		]
	},
	plugins: [
		new VueLoaderPlugin(),
		new StyleLintPlugin({ files: "css/*.(sc|sa|c)ss" })
	],
	resolve: {
		extensions: ['*', '.js', '.vue']
	}
}
