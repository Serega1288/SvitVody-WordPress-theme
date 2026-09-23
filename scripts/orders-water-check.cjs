const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { randomUUID } = require('node:crypto');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const out = path.resolve(__dirname, '../audit-screenshots/orders-water');
  fs.mkdirSync(out, { recursive: true });
  const errors = [];
  const orders = [];
  let lastPayload;
  let lastConfig;
  try {
    for (const width of process.env.SMOKE ? [390] : [1440, 390]) {
      for (const language of process.env.SMOKE ? ['ru'] : ['uk', 'ru']) {
        const page = await browser.newPage({ viewport: { width, height: 1000 } });
        page.on('pageerror', error => errors.push(error.message));
        await page.goto('http://localhost:8080' + (language === 'uk' ? '/' : '/ru/'), { waitUntil: 'networkidle' });
        const water = await page.evaluate(() => window.svitvodyWaterProducts[0]);
        assert.ok(water?.id);
        const pricing = page.locator('.constructor-pricing');
        assert.equal(await pricing.locator('[data-constructor-quantity]').count(), 3);
        assert.equal(await pricing.locator('[data-additional-products] [data-product-id]').count(), 2);
        await pricing.scrollIntoViewIfNeeded();
        await pricing.screenshot({ path: path.join(out, `pricing-${language}-${width}.png`) });
        await pricing.locator('[data-constructor-quantity="6"]').click();
        const quantity = page.locator(`[data-cart-quantity="${water.id}"]`);
        assert.equal(await quantity.inputValue(), '6');
        for (const [amount, total] of [[2, 360], [5, 900], [6, 1020], [11, 1870], [12, 1920]]) {
          await quantity.fill(String(amount));
          await quantity.dispatchEvent('change');
          const value = await page.locator('[data-cart-total]').innerText();
          assert.equal(Number(value.replace(/[^\d,]/g, '').replace(',', '.')), total);
          const active = await pricing.locator('[data-constructor-quantity].cart-added').getAttribute('data-constructor-quantity');
          assert.equal(Number(active), amount >= 12 ? 12 : amount >= 6 ? 6 : 2);
        }
        await page.locator('[data-cart-related-add="pump"]').click();
        // Catalogue and tier pricing must work even on pages without the price block.
        await page.goto('http://localhost:8080' + (language === 'uk' ? '/about-water/' : '/ru/about-water-ru/'), { waitUntil: 'networkidle' });
        await page.locator('[data-cart-open]').click();
        assert.equal(await page.locator('[data-cart-count]').innerText(), '13');
        const form = page.locator('.order-modal-form');
        await form.locator('[name="name"]').fill('Тест заявки');
        await form.locator('[name="phone"]').fill('+380671234567');
        await form.locator('[name="address"]').fill('Тестова адреса 1');
        const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);
        await form.locator('[name="date"]').fill(tomorrow);
        assert.equal(await form.locator('[name="modal-time"]').count(), 0);
        await form.locator('[name="message"]').fill(`[AUTOTEST-ORDERS] ${language} ${width}`);
        if (language === 'uk' && width === 1440) {
          await page.route('**/admin-ajax.php', route => route.fulfill({ status: 503, contentType: 'application/json', body: JSON.stringify({ success: false, data: { message: 'Тестова помилка сервера' } }) }));
          await form.locator('[type="submit"]').click();
          await page.waitForFunction(() => document.querySelector('[data-order-feedback]').textContent.includes('Тестова помилка'));
          assert.equal(await page.locator('[data-cart-count]').innerText(), '13');
          assert.equal(await form.locator('[name="name"]').inputValue(), 'Тест заявки');
          await page.unroute('**/admin-ajax.php');
        }
        const pendingResponse = page.waitForResponse(response => response.url().endsWith('/admin-ajax.php') && response.request().method() === 'POST');
        await form.locator('[type="submit"]').click();
        const response = await pendingResponse;
        const result = await response.json();
        assert.equal(result.success, true, JSON.stringify(result));
        orders.push(result.data.order_id);
        lastPayload = JSON.parse(new URLSearchParams(response.request().postData()).get('payload'));
        lastConfig = await page.evaluate(() => window.svitvodyCheckout);
        await page.waitForFunction(() => document.querySelector('[data-cart-count]').textContent === '0');
        assert.ok((await form.locator('[data-order-feedback]').innerText()).includes(String(result.data.order_id)));
        await form.locator('[data-order-feedback]').scrollIntoViewIfNeeded();
        await page.screenshot({ path: path.join(out, `submitted-${language}-${width}.png`) });
        const repeated = await page.request.post(lastConfig.url, { form: { action: 'sv_submit_order', nonce: lastConfig.nonce, payload: JSON.stringify(lastPayload) } });
        assert.equal((await repeated.json()).data.order_id, result.data.order_id);
        const privateResponse = await page.request.get(`http://localhost:8080/?post_type=sv_order&p=${result.data.order_id}`);
        assert.equal(privateResponse.status(), 404);
        await page.close();
      }
    }
    const context = await browser.newContext();
    const send = async (payload, nonce = lastConfig.nonce) => {
      const response = await context.request.post(lastConfig.url, { form: { action: 'sv_submit_order', nonce, payload: JSON.stringify(payload) } });
      return { status: response.status(), body: await response.json() };
    };
    for (const changes of process.env.SMOKE ? [] : [
      { items: [] }, { phone: '123' }, { date: '2020-01-01' }, { date: '2026-02-31' },
      { language: 'xx' }, { expected_total_cents: 1 }, { items: [{ id: 'product-1', amount: 2 }] },
      { items: [{ id: lastPayload.items[0].id, amount: 1 }] },
      { items: [{ id: lastPayload.items[0].id, amount: 2.5 }] },
      { items: [{ id: lastPayload.items[0].id, amount: 100 }] },
      { items: [lastPayload.items[0], lastPayload.items[0]] }, { phone: ['123'] }
    ]) {
      const rejected = await send({ ...lastPayload, ...changes, request_id: randomUUID() });
      assert.equal(rejected.body.success, false, JSON.stringify(changes));
      assert.ok([409, 422].includes(rejected.status), JSON.stringify(rejected));
    }
    assert.equal((await send(lastPayload, 'invalid')).status, 403);
    const concurrentPayload = { ...lastPayload, request_id: randomUUID(), items: lastPayload.items.map(item => ({ ...item, unitPrice: 1, title: 'Forged title' })) };
    const concurrent = await Promise.all([send(concurrentPayload), send(concurrentPayload)]);
    assert.equal(concurrent[0].body.success, true, JSON.stringify(concurrent));
    assert.equal(concurrent[0].body.data.order_id, concurrent[1].body.data.order_id);
    orders.push(concurrent[0].body.data.order_id);
    await context.close();
    if (process.env.WP_USER && process.env.WP_PASSWORD) {
      const page = await browser.newPage({ viewport: { width: 1440, height: 1100 } });
      await page.goto('http://localhost:8080/wp-login.php');
      await page.locator('#user_login').fill(process.env.WP_USER);
      await page.locator('#user_pass').fill(process.env.WP_PASSWORD);
      await Promise.all([page.waitForURL('**/wp-admin/**'), page.locator('#wp-submit').click()]);
      await page.goto(`http://localhost:8080/wp-admin/post.php?post=${orders.at(-1)}&action=edit`, { waitUntil: 'networkidle' });
      const details = page.locator('#sv_order_details');
      assert.equal(await page.locator('#postdivrich').count(), 0);
      const text = await details.innerText();
      assert.ok(text.includes('Тест заявки'));
      assert.ok(text.includes('Вода питьевая 18.9 л'));
      assert.ok(!text.includes('Forged title'));
      assert.ok(text.includes('160,00'));
      assert.ok(text.replace(/\s/g, '').includes('2120,00'));
      await page.locator('#sv_order_status').selectOption('processing');
      await Promise.all([page.waitForNavigation(), page.locator('#publish').click()]);
      assert.equal(await page.locator('#sv_order_status').inputValue(), 'processing');
      await page.screenshot({ path: path.join(out, 'admin-order.png') });
      await page.goto('http://localhost:8080/wp-admin/edit.php?post_type=sv_order', { waitUntil: 'networkidle' });
      assert.ok((await page.locator('#the-list').innerText()).includes('В обробці'));
      await page.screenshot({ path: path.join(out, 'admin-orders.png') });
      await page.goto('http://localhost:8080/wp-admin/post.php?post=753&action=edit', { waitUntil: 'networkidle' });
      assert.equal(await page.locator('[data-name="product_kind"] select').inputValue(), 'water');
      assert.equal(await page.locator('[data-name="water_tiers"] > .acf-input > .acf-repeater > .acf-table > tbody > .acf-row:not(.acf-clone)').count(), 3);
      await page.locator('[data-name="water_tiers"]').screenshot({ path: path.join(out, 'admin-water-tiers.png') });
      await page.close();
    }
    assert.deepEqual(errors, []);
    fs.writeFileSync(path.join(out, process.env.SMOKE ? 'smoke-results.json' : 'results.json'), JSON.stringify({ orders, errors }, null, 2));
    console.log('Water tiers, checkout, failure retention, private orders, admin status, tampering checks and concurrent retry deduplication passed. Orders: ' + orders.join(', '));
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
