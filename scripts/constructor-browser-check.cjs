const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

(async () => {
  const root = path.resolve(__dirname, '..');
  const dir = path.join(root, 'audit-screenshots/constructor');
  fs.mkdirSync(dir, { recursive: true });
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const paths = ['/', '/ru/', '/about-water/', '/ru/about-water-ru/', '/water-for-home/', '/ru/water-for-home-ru/', '/water-for-office/', '/ru/water-for-office-ru/'];
  const results = [];
  for (const width of [1440, 390]) {
    const page = await browser.newPage({ viewport: { width, height: 900 } });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    for (const pathname of paths) {
      const start = errors.length;
      const response = await page.goto('http://localhost:8080' + pathname, { waitUntil: 'networkidle' });
      await page.evaluate(async () => {
        await document.fonts.ready;
        for (let top = 0; top < document.body.scrollHeight; top += 700) {
          window.scrollTo(0, top);
          await new Promise(resolve => setTimeout(resolve, 35));
        }
        window.scrollTo(0, 0);
      });
      await page.waitForTimeout(350);
      const metrics = await page.evaluate(() => {
        const ids = [...document.querySelectorAll('[id]')].map(el => el.id);
        return {
          title: document.title, body: document.body.className,
          sections: [...document.querySelectorAll('.constructor > section')].map(el => ({ id: el.id, title: el.querySelector('h1,h2')?.innerText, height: el.getBoundingClientRect().height })),
          duplicates: [...new Set(ids.filter((id, i) => ids.indexOf(id) !== i))],
          brokenImages: [...document.querySelectorAll('main img')].filter(el => !el.complete || el.naturalWidth === 0).map(el => el.src),
          overflow: document.documentElement.scrollWidth > innerWidth + 1,
          sliders: document.querySelectorAll('.mobile-content-swiper').length,
        };
      });
      const label = pathname.replaceAll('/', '_') || 'home';
      await page.screenshot({ path: path.join(dir, `${width}${label}.png`), fullPage: true });
      results.push({ pathname, width, status: response.status(), errors: errors.slice(start), ...metrics });
    }
    await page.close();
  }
  fs.writeFileSync(path.join(dir, 'browser-results.json'), JSON.stringify(results, null, 2));
  console.log(JSON.stringify(results, null, 2));
  await browser.close();
})();
