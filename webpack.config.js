const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
  ...defaultConfig,
  entry: {
    'openverse-search': './blocks/openverse-search/src/index.js',
  },
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: '[name]/index.js',
  },
  plugins: [
    ...defaultConfig.plugins,
    new CopyWebpackPlugin({
      patterns: [
        {
          from: './blocks/openverse-search/block.json',
          to: './openverse-search/block.json',
        },
      ],
    }),
  ],
}; 