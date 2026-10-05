import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import {
  mkdirSync,
  readFileSync,
  readdirSync,
  rmSync,
  statSync,
  writeFileSync,
} from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

import { build as esbuildBuild } from 'esbuild';
import * as sass from 'sass';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const sourceRoot = path.join(root, 'admin-ui', 'src');
const assetRoot = path.join(root, 'assets', 'admin');

const entries = {
  main: path.join(sourceRoot, 'main.ts'),
  taxonomy: path.join(sourceRoot, 'taxonomy.ts'),
  fields: path.join(sourceRoot, 'fields.ts'),
  query: path.join(sourceRoot, 'query.ts'),
  'columns-runtime': path.join(sourceRoot, 'columns-runtime.ts'),
};

const styles = {
  main: path.join(sourceRoot, 'admin.scss'),
  taxonomy: path.join(sourceRoot, 'admin.scss'),
  fields: path.join(sourceRoot, 'fields.scss'),
  query: path.join(sourceRoot, 'query.scss'),
  'columns-runtime': path.join(sourceRoot, 'columns.scss'),
};

const approvedDevDependencies = {
  '@biomejs/biome': '2.5.15',
  esbuild: '0.28.2',
  sass: '1.105.1',
  typescript: 'npm:@typescript/typescript6@6.0.2',
};

const expectedScripts = {
  build: 'npm run build:admin',
  'build:admin': 'node tools/admin/admin-toolchain.mjs build',
  'check:engines': 'node tools/admin/admin-toolchain.mjs check-engines',
  'lint:css': 'node tools/admin/admin-toolchain.mjs lint-css',
  'lint:js': 'biome lint admin-ui/src',
  'lint:package': 'node tools/admin/admin-toolchain.mjs lint-package',
  typecheck: 'tsc6 --noEmit --project tsconfig.json',
};

function fail(message) {
  throw new Error(message);
}

function readPackage() {
  return JSON.parse(readFileSync(path.join(root, 'package.json'), 'utf8'));
}

function sameJson(left, right) {
  return JSON.stringify(left) === JSON.stringify(right);
}

function npmMajor() {
  const userAgent = process.env.npm_config_user_agent ?? '';
  const match = userAgent.match(/(?:^|\s)npm\/(\d+)(?:\.|\s|$)/);
  if (match) {
    return Number(match[1]);
  }

  const version = execFileSync('npm', ['--version'], {
    cwd: root,
    encoding: 'utf8',
  }).trim();
  const major = Number(version.split('.')[0]);
  if (!Number.isInteger(major)) {
    fail(`Unable to determine npm major from "${version}".`);
  }

  return major;
}

function checkEngines() {
  const nodeMajor = Number(process.versions.node.split('.')[0]);
  if (nodeMajor !== 24) {
    fail(`Node 24 is required; observed ${process.versions.node}.`);
  }

  const npm = npmMajor();
  if (npm < 10 || npm >= 12) {
    fail(`npm >=10 <12 is required; observed major ${npm}.`);
  }
}

function lintPackage() {
  const pkg = readPackage();

  if (pkg.private !== true) {
    fail('package.json must remain private.');
  }
  if (pkg.license !== 'GPL-3.0-or-later') {
    fail('package.json license must remain GPL-3.0-or-later.');
  }
  if (
    pkg.engines?.node !== '>=24 <25'
    || pkg.engines?.npm !== '>=10 <12'
  ) {
    fail('package.json engine contract drifted.');
  }
  if (!sameJson(pkg.devDependencies, approvedDevDependencies)) {
    fail('package.json devDependencies are outside the approved minimal toolchain.');
  }
  if (pkg.dependencies !== undefined && Object.keys(pkg.dependencies).length !== 0) {
    fail('Runtime npm dependencies are not allowed.');
  }
  if (pkg.overrides !== undefined) {
    fail('npm overrides are forbidden in the minimal toolchain.');
  }
  if (!sameJson(pkg.scripts, expectedScripts)) {
    fail('package.json scripts do not match the approved toolchain contract.');
  }
  if (pkg.repository?.url !== 'git+https://github.com/Vertex-Systems-Network/wpessential.git') {
    fail('package.json repository metadata drifted.');
  }
  if (pkg.homepage !== 'https://wpessential.org') {
    fail('package.json homepage metadata drifted.');
  }
}

function scssFiles() {
  return readdirSync(sourceRoot)
    .filter((file) => file.endsWith('.scss'))
    .map((file) => path.join(sourceRoot, file))
    .sort();
}

function validateScssSource(file) {
  const source = readFileSync(file, 'utf8');
  if (/\r/.test(source)) {
    fail(`${path.relative(root, file)} must use LF line endings.`);
  }
  if (/[ \t]+$/m.test(source)) {
    fail(`${path.relative(root, file)} contains trailing whitespace.`);
  }
  if (/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/.test(source)) {
    fail(`${path.relative(root, file)} contains unsafe control characters.`);
  }

  const warnings = [];
  sass.compile(file, {
    style: 'expanded',
    sourceMap: false,
    logger: {
      warn(message) {
        warnings.push(message);
      },
      debug() {},
    },
  });

  if (warnings.length > 0) {
    fail(
      `${path.relative(root, file)} emitted Sass warnings:\n${warnings.join('\n')}`,
    );
  }
}

function lintCss() {
  const files = scssFiles();
  if (files.length === 0) {
    fail('No SCSS sources were discovered.');
  }
  for (const file of files) {
    validateScssSource(file);
  }
}

const ignoreScssPlugin = {
  name: 'wpessential-ignore-scss-side-effects',
  setup(build) {
    build.onResolve({ filter: /\.scss$/ }, (args) => ({
      path: path.resolve(args.resolveDir, args.path),
      namespace: 'wpessential-scss-stub',
    }));
    build.onLoad(
      { filter: /.*/, namespace: 'wpessential-scss-stub' },
      () => ({
        contents: '',
        loader: 'js',
      }),
    );
  },
};

function compileStyles() {
  for (const [entry, source] of Object.entries(styles)) {
    const warnings = [];
    const result = sass.compile(source, {
      style: 'compressed',
      sourceMap: false,
      logger: {
        warn(message) {
          warnings.push(message);
        },
        debug() {},
      },
    });
    if (warnings.length > 0) {
      fail(`${path.relative(root, source)} emitted Sass warnings:\n${warnings.join('\n')}`);
    }
    writeFileSync(path.join(assetRoot, `${entry}.css`), result.css, 'utf8');
  }
}

function assetVersion(entry) {
  const hash = createHash('sha256');
  hash.update(readFileSync(path.join(assetRoot, `${entry}.js`)));
  hash.update('\0');
  hash.update(readFileSync(path.join(assetRoot, `${entry}.css`)));
  return hash.digest('hex').slice(0, 20);
}

function writeAssetMetadata() {
  for (const entry of Object.keys(entries)) {
    const version = assetVersion(entry);
    const php = `<?php return ['dependencies' => [], 'version' => '${version}'];\n`;
    writeFileSync(path.join(assetRoot, `${entry}.asset.php`), php, 'utf8');
  }
}

function verifyAssets() {
  for (const entry of Object.keys(entries)) {
    for (const suffix of ['js', 'css', 'asset.php']) {
      const file = path.join(assetRoot, `${entry}.${suffix}`);
      if (statSync(file).size <= 0) {
        fail(`Generated asset is empty: ${path.relative(root, file)}`);
      }
    }

    const metadata = readFileSync(
      path.join(assetRoot, `${entry}.asset.php`),
      'utf8',
    );
    if (
      !/^<\?php return \['dependencies' => \[\], 'version' => '[0-9a-f]{20}'\];\n$/.test(
        metadata,
      )
    ) {
      fail(`Generated metadata is invalid for ${entry}.`);
    }
  }
}

async function buildAdmin() {
  lintCss();

  rmSync(assetRoot, { recursive: true, force: true });
  mkdirSync(assetRoot, { recursive: true });

  await esbuildBuild({
    entryPoints: entries,
    outdir: assetRoot,
    entryNames: '[name]',
    bundle: true,
    platform: 'browser',
    format: 'iife',
    target: ['es2022'],
    minify: true,
    sourcemap: false,
    legalComments: 'none',
    charset: 'utf8',
    plugins: [ignoreScssPlugin],
    logLevel: 'info',
  });

  compileStyles();
  writeAssetMetadata();
  verifyAssets();
}

const command = process.argv[2];
switch (command) {
  case 'check-engines':
    checkEngines();
    break;
  case 'lint-package':
    lintPackage();
    break;
  case 'lint-css':
    lintCss();
    break;
  case 'build':
    await buildAdmin();
    break;
  default:
    fail(
      'Usage: node tools/admin/admin-toolchain.mjs <check-engines|lint-package|lint-css|build>',
    );
}
