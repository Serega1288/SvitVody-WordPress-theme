const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

// One-time content extraction from the pre-constructor theme, using the HTML parser.
(async () => {
  const root = path.resolve(__dirname, '..');
  const out = path.join(root, 'project-theme/inc/migrations/constructor');
  fs.mkdirSync(out, { recursive: true });
  const source = fs.readFileSync(path.join(root, 'project-theme/old/04-page-constructor/index.php'), 'utf8')
    .replace(/<\?php[\s\S]*?\?>/g, '');
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const page = await browser.newPage();
  await page.setContent(source);
  const data = await page.evaluate(() => {
    const q = (el, s) => { const result = el.querySelector(s); if (!result) throw new Error(s); return result; };
    const all = (el, s) => [...el.querySelectorAll(s)];
    const text = (el) => el.textContent.replace(/\s+/g, ' ').trim();
    const html = (el) => el.innerHTML.trim().replace(/\s*<br\s*\/?>(\s*)/gi, '\n');
    const rich = (el) => `<p>${el.innerHTML.trim()}</p>`;
    const img = (el) => ({ file: el.getAttribute('src').replace(/^\/assets\//, ''), alt: el.getAttribute('alt') || '' });
    const row = (name, fields) => ({ acf_fc_layout: `template-${name}`, disable_block: 0, ...fields });
    const home = q(document, '#home');
    const heroPrice = q(home, '.rotate-12');
    const desktop = q(home, '.desktop-hero-title').cloneNode(true);
    const accent = text(q(desktop, 'span')); q(desktop, 'span').remove();
    const mobile = q(home, '.mobile-hero-title').cloneNode(true);
    const mobileAccent = text(q(mobile, 'span')); q(mobile, 'span').remove();
    const seeds = {};
    seeds.home = row('banner-main', {
      eyebrow: text(q(home, '.tracking-wider')), title: html(desktop).trim(), title_mobile: html(mobile).trim(), title_accent: accent, title_accent_mobile: mobileAccent,
      description: rich(q(home, 'p')), button_text: text(q(home, 'button')), image: img(q(home, 'img')),
      price_label: text(q(heroPrice, ':scope > span')), price: 160, currency: '₴', price_note: html(q(heroPrice, ':scope > div:last-child')),
    });
    const promo = q(document, '#promo');
    seeds.promo = row('promotions', {
      title: text(q(promo, 'h2')), description: rich(q(promo, '.desktop-promo-copy')), description_mobile: rich(q(promo, '.mobile-promo-copy')),
      promotions: all(promo, '.promo-grid-actions > div').map(el => ({
        label: text(q(el, '.inline-block')), title: text(q(el, 'h3')), title_mobile: q(el, 'h3').dataset.mobileTitle,
        price: Number(text(q(el, '.text-5xl'))), currency: '₴', unit: text(q(el, '.ml-2')), note: rich(q(el, 'p')), image: img(q(el, 'img')),
      })),
      offers_heading: text(all(promo, 'h2')[1]), offers_intro: rich(q(promo, ':scope > div > .mb-10 > p')),
      offers: all(promo, '.promo-grid-offers > div').map((el, i) => ({
        label: text(q(el, '.inline-block')), title: text(q(el, 'h3')), description: rich(q(el, 'p')),
        page: { page_slug: i === 0 ? 'water-for-office' : 'water-for-home' }, button_text: text(q(el, 'a')), image: img(q(el, 'img')),
      })),
    });
    for (const id of ['about-water', 'water-for-home', 'water-for-office']) {
      const el = q(document, `#${id}`);
      seeds[id] = row('banner-inner', {
        eyebrow: text(q(el, '.tracking-wider')), title: html(q(el, 'h1')), description: rich(q(el, 'p')),
        button_text: text(q(el, 'button')), background: img(q(el, '.absolute > img')), image: img(q(el, '.banner-bottle-hover')),
        stats: all(el, '.about-water-stats > div').map(stat => ({ value: text(q(stat, 'span')), label: text(q(stat, 'strong')) })),
      });
    }
    for (const [selector, name] of [['.water-quality','water-quality'], ['.feature-slider-section','home-features'], ['.office-usecases','office-usecases'], ['#how-it-works','work-steps']]) {
      const el = q(document, selector);
      seeds[name] = row(name, {
        title: text(q(el, 'h2')),
        items: all(el, '.grid > div').map(item => ({
          icon: [...q(item, 'i').classList].find(c => c.startsWith('fa-') && c !== 'fa-solid').slice(3),
          title: text(q(item, 'h3')), description: rich(q(item, 'p')),
        })),
      });
      if (name === 'office-usecases') seeds[name].background = { file: 'img/water-texture-local.svg', alt: '' };
    }
    const comp = q(document, '.water-composition');
    seeds.composition = row('water-composition', {
      title: text(q(comp, 'h2')),
      primary: all(comp, '.w-48.composition-circle').map(el => ({ label: text(q(el, 'span')), value: text(q(el, ':scope > div')), unit: text(q(el, ':scope > div:last-child')) })),
      minerals: all(comp, '.w-36.composition-circle').map(el => ({ label: text(q(el, 'span')), value: text(q(el, ':scope > div')), unit: text(q(el, ':scope > div:last-child')) })),
      description: rich(q(comp, 'p')), closing: text(q(comp, '.max-w-4xl > div')), background: { file: 'img/water-texture-local.svg', alt: '' },
    });
    const pricing = q(document, '#pricing');
    seeds.pricing = row('water-pricing', {
      title: text(q(pricing, 'h2')),
      prices: all(pricing, ':scope > div > .grid > div').map((el, i) => ({
        label: el.querySelector(':scope > .absolute') ? text(q(el, ':scope > .absolute')) : '',
        title: text(q(el, 'h3')), price: [180, 170, 160][i], min_bottles: [2, 6, 12][i], currency: '₴', unit: rich(q(el, 'p')), button_text: text(q(el, 'button')),
      })),
      products_title: text(q(pricing, 'h3[data-mobile-title]')), products_title_mobile: q(pricing, 'h3[data-mobile-title]').dataset.mobileTitle,
    });
    seeds.products = all(pricing, '.max-w-5xl > .grid > div').map((el, i) => ({
      slug: i === 0 ? 'bottle-deposit' : 'pump', title: text(q(el, 'h4')), label: text(q(el, '.inline-block')),
      price: Number(text(q(el, '.text-5xl'))), currency: '₴', button_text: text(q(el, 'button')), image: img(q(el, 'img')),
    }));
    return seeds;
  });
  fs.writeFileSync(path.join(out, 'content-uk.json'), JSON.stringify(data, null, 2) + '\n');
  const strings = new Set();
  const collect = obj => { for (const [key, value] of Object.entries(obj)) {
    if (typeof value === 'string' && /[А-Яа-яІіЇїЄєҐґ]/.test(value) && key !== 'acf_fc_layout') strings.add(value);
    else if (value && typeof value === 'object') collect(value);
  }};
  collect(data);
  if (!fs.existsSync(path.join(out, 'translations-ru.json'))) {
    fs.writeFileSync(path.join(out, 'translations-ru.json'), JSON.stringify(Object.fromEntries([...strings].map(s => [s, ''])), null, 2) + '\n');
  }
  console.log('Extracted layouts:', Object.keys(data), 'Translations:', strings.size);
  await browser.close();
})();
