const fs = require('fs');
const path = require('path');

const rootDir = process.cwd();
const defaultsPath = path.join(rootDir, 'init.defaults.json');
const envPath = path.join(rootDir, '.env');
const packageJsonPath = path.join(rootDir, 'package.json');
const packageLockPath = path.join(rootDir, 'package-lock.json');
const styleCssPath = path.join(rootDir, 'project-theme', 'style.css');
const enqueuePath = path.join(rootDir, 'project-theme', 'inc', 'enqueue.php');
const indexPath = path.join(rootDir, 'project-theme', 'index.php');

function readText(filePath) {
  return fs.readFileSync(filePath, 'utf8');
}

function writeText(filePath, content) {
  fs.writeFileSync(filePath, content, 'utf8');
}

function ensureFile(filePath) {
  if (!fs.existsSync(filePath)) {
    throw new Error(`File not found: ${filePath}`);
  }
}

function parseDefaults() {
  ensureFile(defaultsPath);
  return JSON.parse(readText(defaultsPath));
}

function replaceOrAppend(content, key, value) {
  const pattern = new RegExp(`^${key}=.*$`, 'm');
  if (pattern.test(content)) {
    return content.replace(pattern, `${key}=${value}`);
  }
  const normalized = content.replace(/\s+$/, '');
  return `${normalized}\n${key}=${value}\n`;
}

function updateEnv(defaults) {
  const existing = fs.existsSync(envPath) ? readText(envPath) : '';
  let next = existing || '';

  next = replaceOrAppend(next, 'COMPOSE_PROJECT_NAME', defaults.composeProjectName);
  next = replaceOrAppend(next, 'PROJECT_NAME', defaults.composeProjectName);
  next = replaceOrAppend(next, 'SITE_NAME', defaults.siteName);
  next = replaceOrAppend(next, 'THEME_NAME', defaults.themeName);
  next = replaceOrAppend(next, 'THEME_TEXT_DOMAIN', defaults.themeTextDomain);
  next = replaceOrAppend(next, 'WORDPRESS_PORT', defaults.wordpressPort);
  next = replaceOrAppend(next, 'ADMINER_PORT', defaults.adminerPort);
  next = replaceOrAppend(next, 'WP_AUTO_PLUGINS', defaults.autoPlugins);

  writeText(envPath, next.endsWith('\n') ? next : `${next}\n`);
}

function updatePackageJson(defaults) {
  const pkg = JSON.parse(readText(packageJsonPath));
  pkg.name = defaults.packageName;
  pkg.version = defaults.packageVersion;
  pkg.description = defaults.packageDescription;
  writeText(packageJsonPath, `${JSON.stringify(pkg, null, 2)}\n`);

  if (fs.existsSync(packageLockPath)) {
    const lock = JSON.parse(readText(packageLockPath));
    lock.name = defaults.packageName;
    lock.version = defaults.packageVersion;

    if (lock.packages && lock.packages['']) {
      lock.packages[''].name = defaults.packageName;
      lock.packages[''].version = defaults.packageVersion;
    }

    writeText(packageLockPath, `${JSON.stringify(lock, null, 2)}\n`);
  }
}

function updateStyleCss(defaults) {
  const styleCss = readText(styleCssPath);
  const header = [
    '@charset "UTF-8";',
    '/*',
    `    Theme Name: ${defaults.themeName}`,
    `    Theme URI: ${defaults.themeUri}`,
    `    Description: ${defaults.themeDescription}`,
    `    Author: ${defaults.themeAuthor}`,
    `    Author URI: ${defaults.themeAuthorUri}`,
    `    Version: ${defaults.themeVersion}`,
    `    Requires at least: ${defaults.themeRequiresAtLeast}`,
    '    Tested up to: 8.0',
    '    Requires PHP: 8.0',
    `    Text Domain: ${defaults.themeTextDomain}`,
    '*/'
  ].join('\n');

  const next = styleCss.replace(/@charset "UTF-8";[\s\S]*?\*\//, header);
  writeText(styleCssPath, next);
}

function updatePhpTextDomain(defaults) {
  const textDomain = defaults.themeTextDomain;
  const handleBase = `${textDomain}-main`;

  let enqueue = readText(enqueuePath);
  enqueue = enqueue.replace(/'[^']*-main'/g, `'${handleBase}'`);
  writeText(enqueuePath, enqueue);

  let index = readText(indexPath);
  index = index.replace(/'No content found\.',\s*'[^']+'/g, `'No content found.', '${textDomain}'`);
  writeText(indexPath, index);
}

function main() {
  const defaults = parseDefaults();

  updateEnv(defaults);
  updatePackageJson(defaults);
  updateStyleCss(defaults);
  updatePhpTextDomain(defaults);

  console.log('Init completed from init.defaults.json');
  console.log(`COMPOSE_PROJECT_NAME=${defaults.composeProjectName}`);
  console.log(`Theme Name=${defaults.themeName}`);
  console.log(`Text Domain=${defaults.themeTextDomain}`);
  console.log(`Package Name=${defaults.packageName}`);
}

main();
