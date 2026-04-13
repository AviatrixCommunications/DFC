const fs = require('fs');
const path = require('path');
const sass = require('sass');
const postcss = require('postcss');
const autoprefixer = require('autoprefixer');
const { globSync } = require('glob');

const isWatch = process.argv.includes('--watch');

function buildOne(srcPath) {
  const outPath = srcPath.replace(/\.scss$/, '.css');
  try {
    const result = sass.compile(srcPath, { style: 'compressed' });
    const prefixed = postcss([autoprefixer]).process(result.css, {
      from: srcPath,
      to: outPath,
    });
    fs.writeFileSync(outPath, prefixed.css);
    console.log(`Block CSS: compiled ${srcPath}`);
  } catch (err) {
    console.error(`Error compiling ${srcPath}:`, err.message);
  }
}

function buildAll() {
  const files = globSync('blocks-acf/*/style.scss');
  if (!files.length) {
    console.log('No block SCSS files found.');
    return;
  }
  files.forEach(buildOne);
}

// Initial full build
buildAll();

if (isWatch) {
  const chokidar = require('chokidar');

  console.log('Watching blocks-acf/*/style.scss for changes...');

  chokidar
    .watch('blocks-acf/*/style.scss', {
      ignoreInitial: true,
      awaitWriteFinish: { stabilityThreshold: 300 },
    })
    .on('change', (filePath) => {
      const srcPath = filePath.replace(/\\/g, '/');
      console.log(`Change detected: ${srcPath}`);
      buildOne(srcPath);
    });
}
