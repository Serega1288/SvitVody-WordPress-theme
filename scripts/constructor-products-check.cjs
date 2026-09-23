const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const out = path.resolve(__dirname, '../audit-screenshots/constructor-products');
  fs.mkdirSync(out, { recursive: true });
  const errors = [];
  const results = [];
  const count = Number(process.env.EXPECT_PRODUCTS || 2);
  for (const width of [1440, 390]) {
    const page = await browser.newPage({ viewport: { width, height: 950 } });
    page.on('pageerror', error => errors.push(error.message));
    for (const url of ['/', '/water-for-home/', '/water-for-office/', '/ru/', '/ru/water-for-home-ru/', '/ru/water-for-office-ru/']) {
      await page.goto('http://localhost:8080' + url, { waitUntil: 'networkidle' });
      const block = page.locator('[data-additional-products]');
      await block.scrollIntoViewIfNeeded();
      await page.waitForTimeout(400);
      const cards = await block.locator('[data-product-id]').evaluateAll(els => els.map(el => ({
        id: el.dataset.productId, title: el.querySelector('h4').textContent,
        price: el.querySelector('.text-5xl')?.textContent,
        button: el.querySelector('button')?.textContent,
        image: el.querySelector('img')?.naturalWidth > 0,
        links: el.querySelectorAll('a').length,
      })));
      assert.equal(cards.length, count, `${url}: product count`);
      assert.ok(cards.every(card => card.image && card.links === 0));
      assert.equal(cards[0].title, url.startsWith('/ru/') ? 'Залог за бутыль' : 'Застава за бутель');
      const hasOverflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1);
      assert.equal(hasOverflow, false, `${url}: viewport overflow`);
      await block.screenshot({ path: path.join(out, `${width}-${url.replaceAll('/', '_')}-${count}.png`) });
      await block.locator('[data-cart-item="pump"]').click();
      assert.equal(await page.locator('#orderModal').isVisible(), false);
      await page.locator('[data-cart-open]').click();
      assert.equal(await page.locator('#orderModal').isVisible(), true);
      assert.ok((await page.locator('[data-cart-items]').innerText()).includes('Помпа ECONOM PLUS'));
      assert.equal(await page.locator('[data-cart-related-add]').count(), count);
      assert.ok((await page.locator('[data-cart-total]').innerText()).includes('200'));
      if (count > 2) {
        const newProduct = page.locator('[data-cart-related-add^="product-"]').first();
        await newProduct.click();
        assert.ok((await page.locator('[data-cart-items]').innerText()).includes(url.startsWith('/ru/') ? 'Проверка нового товара' : 'Перевірка нового товару'));
        assert.ok((await page.locator('[data-cart-total]').innerText()).includes('323,45'));
      }
      results.push({ width, url, cards });
      await page.evaluate(() => localStorage.clear());
    }
    await page.close();
  }
  if (process.env.WP_USER && process.env.WP_PASSWORD) {
    const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('http://localhost:8080/wp-login.php');
    await page.locator('#user_login').fill(process.env.WP_USER);
    await page.locator('#user_pass').fill(process.env.WP_PASSWORD);
    await Promise.all([page.waitForURL('**/wp-admin/**'), page.locator('#wp-submit').click()]);
    await page.goto('http://localhost:8080/wp-admin/post.php?post=738&action=edit', { waitUntil: 'networkidle' });
    assert.equal(await page.locator('[data-name="product_price"] input[type="number"]').inputValue(), '200');
    assert.equal(await page.locator('[data-name="product_image"] .acf-image-uploader.has-value').count(), 1);
    assert.equal(await page.locator('#sv_product_categorydiv').count(), 1);
    assert.equal(await page.locator('#ml_box').count(), 1);
    await page.screenshot({ path: path.join(out, 'admin-product.png') });
    await page.goto('http://localhost:8080/wp-admin/edit.php?post_type=acf-field-group', { waitUntil: 'networkidle' });
    assert.ok((await page.locator('#the-list .row-title').allTextContents()).includes('Дані товару'));
    await page.close();
  }
  assert.deepEqual(errors, []);
  fs.writeFileSync(path.join(out, `results-${count}.json`), JSON.stringify({ results, errors }, null, 2));
  console.log(`${results.length} desktop/mobile page checks passed; ${count} products per block; no JS errors.`);
  await browser.close();
})().catch(error => { console.error(error); process.exit(1); });
