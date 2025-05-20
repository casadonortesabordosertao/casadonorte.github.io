const path = require('path');

module.exports = {
  entry: {
    app: [
      './assets/js/bootstrap.min.js',
      './assets/js/glightbox.min.js',
      './assets/js/main.js',
      './assets/js/tiny-slider.js'
    ],
  },
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'dist'),
  },
  mode: 'production',
};
