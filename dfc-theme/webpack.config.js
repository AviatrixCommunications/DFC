const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const prefixwrap = require('postcss-prefixwrap');

module.exports = {
  mode: 'production', // or 'development'
  entry: {
    global: './js/global.js',
    style: './sass/style.scss',               // frontend CSS
    'editor-style': './sass/editor-style.scss', // Gutenberg editor CSS
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'js/[name].js',
    assetModuleFilename: 'assets/[name][ext][query]',
  },
  module: {
    rules: [
      // JavaScript
      {
        test: /\.m?js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            sourceType: 'unambiguous',
            presets: ['@babel/preset-env'],
          },
        },
        type: 'javascript/auto',
      },

      // SCSS / CSS
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: '../', 
            },
          },
          {
            loader: 'css-loader',
            options: {
              url: true,
              importLoaders: 2,
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: (loader) => {
                const plugins = [require('autoprefixer')];
                if (loader.resource.includes('editor-style.scss')) {
                  plugins.push(prefixwrap('.editor-styles-wrapper'));
                }
                return { plugins };
              },
            },
          },
          'sass-loader',
        ],
      },

      // Fonts
      {
        test: /\.(woff(2)?|ttf|otf|eot)$/i,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[name][ext][query]',
        },
      },

      // Images
      {
        test: /\.(png|jpe?g|gif|svg)$/i,
        type: 'asset/resource',
        generator: {
          filename: 'images/[name][ext][query]',
        },
      },
    ],
  },

  resolve: {
    extensions: ['.js'],
    alias: {
      '@': path.resolve(__dirname, 'src'),
      '@img': path.resolve(__dirname, 'img'), 
    },
  },

  plugins: [
    new MiniCssExtractPlugin({
      filename: 'css/[name].css', 
    }),
    new BrowserSyncPlugin(
      {
        proxy: 'https://dupage-flight-center.local',
        https: true,
        files: [
          '**/*.php',
          'dist/**/*.(css|js)',
          {
            match: ['blocks-acf/*/style.css'],
            fn: function (event, file) {
              if (event === 'change') {
                this.reload();
              }
            },
          },
        ],
        injectChanges: true,
        notify: false,
        open: false,
      },
      { reload: true }
    ),
  ],

  devtool: 'source-map',
};
