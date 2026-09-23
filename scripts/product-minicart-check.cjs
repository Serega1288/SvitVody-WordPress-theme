const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  try {
    const out = path.resolve(__dirname, '../audit-screenshots/constructor-products');
    fs.mkdirSync(out, { recursive: true });
    const errors = [];
    for (const width of [1440, 390]) {
      for (const language of ['uk', 'ru']) {
        const page = await browser.newPage({ viewport: { width, height: 950 } });
        page.on('pageerror', error => errors.push(error.message));
        await page.goto('http://localhost:8080' + (language === 'uk' ? '/' : '/ru/'), { waitUntil: 'networkidle' });
        await page.locator('[data-additional-products] [data-cart-item="pump"]').click();
        assert.equal(await page.locator('[data-cart-count]').innerText(), '1');
        await page.locator('[data-cart-open]').click();
        await page.locator('[data-cart-related-add="bottle-deposit"]').click();
        assert.equal(await page.locator('[data-cart-count]').innerText(), '2');
        assert.ok((await page.locator('[data-cart-total]').innerText()).includes('480'));
        assert.ok((await page.locator('[data-cart-items]').innerText()).includes(language === 'uk' ? 'Повертається після здачі тари' : 'Возвращается после сдачи тары'));
        await page.locator('[data-cart-increase="pump"]').click();
        assert.equal(await page.locator('[data-cart-quantity="pump"]').inputValue(), '2');
        assert.ok((await page.locator('[data-cart-total]').innerText()).includes('680'));
        await page.locator('[data-cart-decrease="pump"]').click();
        assert.ok((await page.locator('[data-cart-total]').innerText()).includes('480'));
        await page.goto('http://localhost:8080' + (language === 'uk' ? '/about-water/' : '/ru/about-water-ru/'), { waitUntil: 'networkidle' });
        assert.equal(await page.locator('[data-cart-count]').innerText(), '2');
        await page.locator('[data-cart-open]').click();
        assert.equal(await page.locator('[data-cart-items] article').count(), 2);
        await page.locator('[data-cart-related-add="pump"]').click();
        assert.ok((await page.locator('[data-cart-total]').innerText()).includes('680'));
        await page.screenshot({ path: path.join(out, `minicart-${language}-${width}.png`) });
        await page.locator('[data-cart-remove="pump"]').click();
        assert.ok((await page.locator('[data-cart-total]').innerText()).includes('280'));
        await page.locator('[data-cart-remove="bottle-deposit"]').click();
        assert.equal(await page.locator('[data-cart-items] article').count(), 0);
        await page.close();
      }
    }
    if (process.env.WP_USER && process.env.WP_PASSWORD) {
      const page = await browser.newPage();
      await page.goto('http://localhost:8080/wp-login.php');
      await page.locator('#user_login').fill(process.env.WP_USER);
      await page.locator('#user_pass').fill(process.env.WP_PASSWORD);
      await Promise.all([page.waitForURL('**/wp-admin/**'), page.locator('#wp-submit').click()]);
      await page.goto('http://localhost:8080/wp-admin/post.php?post=735&action=edit', { waitUntil: 'networkidle' });
      for (const name of ['product_label', 'product_button_text', 'product_price', 'product_currency', 'product_image', 'product_image_alt', 'product_cart_description']) {
        assert.equal(await page.locator(`[data-name="${name}"]`).count(), 1, name);
      }
      assert.equal(await page.locator('[data-name="product_cart_description"] textarea').inputValue(), 'Повертається після здачі тари');
      await page.close();
    }
    assert.deepEqual(errors, []);
    console.log('Mini-cart: add from cards and related list, quantity, totals, navigation persistence, removal passed in uk/ru at 1440/390; product fields verified.');
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
