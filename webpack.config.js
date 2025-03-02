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
        // Copy CSS files if they exist
        {
          from: './blocks/openverse-search/style.css',
          to: './openverse-search/style.css',
          noErrorOnMissing: true,
        },
        {
          from: './blocks/openverse-search/index.css',
          to: './openverse-search/index.css',
          noErrorOnMissing: true,
        },
      ],
    }),
  ],
}; 