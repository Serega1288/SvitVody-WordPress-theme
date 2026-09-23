(() => {
  const getModal = (id) => document.getElementById(id);
  const assetUrl = (path) => `${window.svitvodyThemeUri || ''}/assets/${path}`;
  const translations = window.svitvodyI18n || {};
  const t = (key, fallback) => translations[key] || fallback;
  const formatTranslated = (value, replacement) => String(value).replace('%s', replacement);
  const cartStorageKey = 'svitvody-cart-v1';
  const cartCatalog = {};

  const extraProducts = (Array.isArray(window.svitvodyExtraProducts) ? window.svitvodyExtraProducts : [])
    .filter((product) => /^(product-\d+|pump|bottle-deposit)$/.test(product.id) && Number.isFinite(product.unitPrice) && product.unitPrice >= 0);
  extraProducts.forEach((product) => {
    cartCatalog[product.id] = { ...product, type: 'extra', quantity: 1 };
  });

  const waterProducts = (Array.isArray(window.svitvodyWaterProducts) ? window.svitvodyWaterProducts : [])
    .filter((product) => /^product-\d+$/.test(product.id) && product.tiers?.length);
  waterProducts.forEach((product) => {
    cartCatalog[product.id] = { ...product, type: 'water', tiers: [...product.tiers].sort((a, b) => b.quantity - a.quantity) };
  });
  const defaultWaterId = waterProducts[0]?.id || '';

  let cart = {};
  let orderSubmitting = false;
  let orderSuccessTimer = 0;

  const getTemplateHtml = (id, fallback = '') => {
    const template = document.getElementById(id);
    const html = template?.innerHTML?.trim();
    return html || fallback;
  };

  const getHeaderOptions = () => window.svitvodyHeaderOptions || {};
  const getOrderPopupOptions = () => window.svitvodyOrderPopupOptions || {};

  const escapeHtml = (value) => String(value || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const escapeAttr = escapeHtml;

  const getMessengerIconClass = (icon) => {
    const icons = {
      telegram: 'fa-brands fa-telegram',
      viber: 'fa-brands fa-viber'
    };

    return icons[String(icon || '').toLowerCase()] || 'fa-solid fa-link';
  };

  const getHeaderPhones = () => {
    const options = getHeaderOptions();

    return Array.isArray(options.phones) && options.phones.length ? options.phones : [
      { phone_label: '+380 (67) 123 45 67', phone_url: 'tel:+380671234567' }
    ];
  };

  const getOrderPopupPhones = () => {
    const options = getOrderPopupOptions();

    return Array.isArray(options.phones) && options.phones.length ? options.phones : getHeaderPhones();
  };

  const getOrderPopupMessengers = () => {
    const options = getOrderPopupOptions();

    return Array.isArray(options.messengers) && options.messengers.length ? options.messengers : [
      { messenger_icon: 'telegram', messenger_name: 'Telegram', messenger_url: '#' },
      { messenger_icon: 'viber', messenger_name: 'Viber', messenger_url: '#' }
    ];
  };

  const pageLinks = [
    [t('home', 'Головна'), window.svitvodyHomeUrl || '/', 'page-home'],
    [t('aboutWater', 'Про воду'), '#about-water', 'page-about-water'],
    [t('forHome', 'Для дому'), '#water-for-home', 'page-water-home'],
    [t('forOffice', 'Для офісу'), '#water-for-office', 'page-water-office'],
    [t('pricing', 'Вартість'), document.getElementById('pricing') ? '#pricing' : `${window.svitvodyHomeUrl || '/'}#pricing`, '']
  ];

  const buildMobileNavigation = () => {
    const desktopHeader = document.getElementById('main-header');
    if (!desktopHeader || document.querySelector('.mobile-site-header')) return;

    const options = getHeaderOptions();
    const activePage = [...document.body.classList].find((className) => className.startsWith('page-')) || '';
    const fallbackNavLinks = pageLinks.map(([label, href, pageClass]) => {
      const activeClass = pageClass === activePage ? ' is-active' : '';
      return `<a href="${href}" class="mobile-menu-link${activeClass}">${label}</a>`;
    }).join('');
    const navLinks = getTemplateHtml('mobile-menu-links-template', fallbackNavLinks);
    const languageSwitcher = getTemplateHtml('mobile-language-switcher-template', `
      <div class="mobile-language-switcher" aria-label="${escapeAttr(t('siteLanguage', 'Мова сайту'))}">
        <button type="button" class="is-active">UA</button>
        <button type="button">RU</button>
      </div>
    `);
    const phones = getHeaderPhones();
    const scheduleRows = Array.isArray(options.scheduleRows) && options.scheduleRows.length ? options.scheduleRows : [
      { schedule_text: 'Пн-Пт: 08:00 - 20:00' },
      { schedule_text: 'Сб-Нд: 09:00 - 18:00' }
    ];
    const phoneLinks = phones.map((phone, index) => (
      `<a href="${phone.phone_url || '#'}" class="mobile-contact-phone${index ? ' mobile-contact-phone-extra' : ''}">${phone.phone_label || ''}</a>`
    )).join('');
    const scheduleHtml = scheduleRows.map((row) => `<span>${row.schedule_text || ''}</span>`).join('');

    desktopHeader.insertAdjacentHTML('beforebegin', `
      <header class="mobile-site-header">
        <div class="mobile-site-header-inner">
          <a href="${window.svitvodyHomeUrl || '/'}" class="mobile-brand" aria-label="${escapeAttr(t('brandHome', 'SvitVody, головна'))}">
            <img class="site-logo site-logo-mobile" src="${window.svitvodyHeaderLogo || assetUrl('img/svit-vody-logo-blue.svg')}" alt="${escapeAttr(options.logoAlt || '')}">
          </a>
          <div class="mobile-header-actions">
            <button type="button" class="mobile-header-order" data-modal-open="${options.orderButtonModal || 'orderModal'}">
              <span class="mobile-order-main">${options.mobileOrderMain || 'Замовити'}</span><span class="mobile-order-extra">${options.mobileOrderExtra || 'воду'}</span>
            </button>
            <button type="button" class="mobile-tap mobile-phone" data-mobile-contacts-toggle aria-label="${escapeAttr(t('openContacts', 'Відкрити контакти'))}" aria-expanded="false">
              <i class="fa-solid fa-phone"></i>
            </button>
            <button type="button" class="mobile-tap mobile-menu-toggle" aria-label="${escapeAttr(t('openMenu', 'Відкрити меню'))}" aria-expanded="false">
              <i class="fa-solid fa-bars"></i>
            </button>
          </div>
        </div>
      </header>
      <div class="mobile-contact-layer" data-mobile-contact-backdrop aria-hidden="true">
        <section class="mobile-contact-panel" role="dialog" aria-label="${escapeAttr(t('contacts', 'Контакти'))}">
          <div class="mobile-contact-heading">
            <span class="mobile-contact-icon"><i class="fa-solid fa-headset"></i></span>
            <h2>${escapeHtml(options.contactsTitle || t('contacts', 'Контакти'))}</h2>
            <button type="button" class="mobile-contact-close" data-mobile-contacts-close aria-label="${escapeAttr(t('closeContacts', 'Закрити контакти'))}">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="mobile-contact-label">${options.phoneGroupTitle || 'Наші телефони'}</p>
          ${phoneLinks}
          <div class="mobile-contact-hours">
            <p>${options.scheduleTitle || 'Графік роботи'}</p>
            ${scheduleHtml}
          </div>
          <div class="mobile-contact-messengers">
            <a href="#" aria-label="Telegram"><i class="fa-brands fa-telegram"></i><span>Telegram</span></a>
            <a href="#" aria-label="Viber"><i class="fa-brands fa-viber"></i><span>Viber</span></a>
          </div>
        </section>
      </div>
      <div class="mobile-menu-drawer" data-mobile-menu-backdrop aria-hidden="true">
        <aside class="mobile-menu-panel" aria-label="${escapeAttr(t('siteMenu', 'Меню сайту'))}">
          <button type="button" class="mobile-tap mobile-menu-close" aria-label="${escapeAttr(t('closeMenu', 'Закрити меню'))}">
            <i class="fa-solid fa-xmark"></i>
          </button>
          <nav class="mobile-menu-nav">${navLinks}</nav>
          <div class="mobile-menu-footer">
            <button type="button" data-modal-open="${options.orderButtonModal || 'orderModal'}" class="mobile-menu-order">${options.orderButtonText || 'Замовити воду'}</button>
            ${languageSwitcher}
          </div>
        </aside>
      </div>
    `);
  };

  const initBannerBottleParallax = () => {
    const media = window.matchMedia('(hover: hover) and (pointer: fine)');
    if (!media.matches) return;

    document.querySelectorAll('.banner-bottle-hover').forEach((bottle) => {
      const section = bottle.closest('.constructor-banner-main, #home, .inner-hero');
      if (!section) return;

      let frame = 0;
      let currentX = 0;
      let currentY = 0;
      let targetX = 0;
      let targetY = 0;

      const animatePosition = () => {
        currentX += (targetX - currentX) * 0.055;
        currentY += (targetY - currentY) * 0.055;

        if (Math.abs(targetX - currentX) < 0.04) currentX = targetX;
        if (Math.abs(targetY - currentY) < 0.04) currentY = targetY;

        bottle.style.setProperty('--bottle-parallax-x', `${currentX.toFixed(2)}px`);
        bottle.style.setProperty('--bottle-parallax-y', `${currentY.toFixed(2)}px`);

        if (currentX !== targetX || currentY !== targetY) {
          frame = window.requestAnimationFrame(animatePosition);
          return;
        }

        frame = 0;
      };

      const updatePosition = (event) => {
        const rect = section.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width - 0.5;
        const y = (event.clientY - rect.top) / rect.height - 0.5;

        targetX = x * -84;
        targetY = y * -57;

        bottle.classList.add('is-parallax-active');
        if (!frame) frame = window.requestAnimationFrame(animatePosition);
      };

      const resetPosition = () => {
        targetX = 0;
        targetY = 0;
        bottle.classList.remove('is-parallax-active');
        if (!frame) frame = window.requestAnimationFrame(animatePosition);
      };

      section.addEventListener('pointermove', updatePosition);
      section.addEventListener('pointerleave', resetPosition);
      section.addEventListener('pointercancel', resetPosition);
    });
  };

  const buildOrderModal = () => {
    const options = getOrderPopupOptions();
    const modalId = options.modalId || 'orderModal';
    const modal = getModal(modalId);
    const dialog = modal?.querySelector('.order-modal-dialog');
    if (!dialog || dialog.children.length) return;

    const phones = getOrderPopupPhones();
    const modalPhoneLinks = phones.map((phone) => {
      const label = escapeHtml(phone.phone_label || '');
      if (!label) return '';

      return `
              <a href="${escapeAttr(phone.phone_url || '#')}" class="order-modal-phone-link">
                <i class="fa-solid fa-phone"></i>
                ${label}
              </a>
      `;
    }).join('');
    const messengerLinks = getOrderPopupMessengers().map((messenger) => {
      const label = escapeHtml(messenger.messenger_name || '');
      if (!label) return '';

      return `
              <a href="${escapeAttr(messenger.messenger_url || '#')}" aria-label="${label}">
                <i class="${escapeAttr(getMessengerIconClass(messenger.messenger_icon || messenger.messenger_name))}"></i>
                <span>${label}</span>
              </a>
      `;
    }).join('');

    dialog.innerHTML = `
      <button type="button" data-modal-close="${escapeAttr(modalId)}" class="order-modal-close" aria-label="${escapeAttr(options.closeLabel || 'Закрити форму')}">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="order-modal-layout">
        <section class="order-modal-form-section">
          <p class="order-modal-contact-heading">${escapeHtml(options.contactHeading || "Ви можете зв'язатися з нами телефоном або у месенджерах.")}</p>
          <div class="order-modal-intro">
            <div class="order-modal-contact-actions">
              ${modalPhoneLinks}
              ${messengerLinks}
            </div>
          </div>
          <div class="order-modal-divider"><span>${escapeHtml(t('checkoutDivider', 'Або оформіть замовлення нижче'))}</span></div>
          <h2>${escapeHtml(t('checkoutTitle', 'Оформлення замовлення'))}</h2>
          <form class="order-modal-form">
            <input type="text" name="website" tabindex="-1" autocomplete="off" hidden>
            <div class="order-form-grid">
              <label class="order-field">
                <span>${escapeHtml(t('nameLabel', "Ваше ім'я*"))}</span>
                <input type="text" name="name" required placeholder="${escapeAttr(t('namePlaceholder', 'Як ми можемо до Вас звертатися'))}">
              </label>
              <label class="order-field">
                <span>${escapeHtml(t('addressLabel', 'Адреса доставки*'))}</span>
                <input type="text" name="address" required placeholder="${escapeAttr(t('addressPlaceholder', 'Вулиця, будинок, квартира'))}">
              </label>
              <label class="order-field">
                <span>${escapeHtml(t('phoneLabel', 'Номер телефону*'))}</span>
                <input type="tel" name="phone" required placeholder="+38 (___) ___-__-__">
              </label>
              <label class="order-field">
                <span>${escapeHtml(t('dateLabel', 'Дата доставки*'))}</span>
                <input type="text" name="date" data-short-date required inputmode="numeric" maxlength="5" pattern="(?:0[1-9]|[12][0-9]|3[01])\\.(?:0[1-9]|1[0-2])" placeholder="дд.мм" autocomplete="off">
              </label>
            </div>
            <label class="order-field order-message-field">
              <span>${escapeHtml(t('messageLabel', 'Ваше повідомлення'))}</span>
              <textarea name="message" rows="3" placeholder="${escapeAttr(t('messagePlaceholder', 'Додаткова інформація до замовлення'))}"></textarea>
            </label>
            <p data-order-feedback role="status" aria-live="polite" hidden></p>
            <button type="submit" class="order-submit">${escapeHtml(t('submit', 'Замовити'))}</button>
            <div class="order-success" data-order-success role="status" aria-live="polite" hidden>
              <span class="order-success-icon" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <h3>${escapeHtml(t('successTitle', 'Заявку прийнято!'))}</h3>
              <p data-order-success-message></p>
              <p class="order-success-autoclose">${escapeHtml(t('successAutoClose', 'Вікно автоматично закриється через 8 секунд.'))}</p>
              <span class="order-success-progress" aria-hidden="true"><span></span></span>
            </div>
          </form>
        </section>
        <aside class="order-cart-section" aria-label="${escapeAttr(t('miniCart', 'Мінікошик'))}">
          <div class="order-cart-head">
            <span><i class="fa-solid fa-cart-shopping"></i></span>
            <div>
              <p>${escapeHtml(t('miniCart', 'Мінікошик'))}</p>
              <h3>${escapeHtml(t('yourOrder', 'Ваше замовлення'))}</h3>
            </div>
          </div>
          <div class="order-cart-items" data-cart-items></div>
          <button type="button" class="order-cart-add-water" data-cart-add-default ${defaultWaterId ? '' : 'hidden'}>
            <i class="fa-solid fa-plus"></i>
            ${escapeHtml(t('addWater', 'Додати воду'))}
          </button>
          <div class="order-related-products">
            <h4>${escapeHtml(t('relatedProducts', 'Супутні товари'))}</h4>
            <div class="order-related-list">
              ${extraProducts.map((product) => `<article>
                <div>
                  <strong>${escapeHtml(product.title)}</strong>
                  <span>${escapeHtml(formatCurrency(product.unitPrice, product.currency))}</span>
                </div>
                <button type="button" data-cart-related-add="${escapeAttr(product.id)}">${escapeHtml(t('add', 'Додати'))}</button>
              </article>`).join('')}
            </div>
          </div>
          <div class="order-cart-total">
            <span>${escapeHtml(t('total', 'Разом'))}</span>
            <strong data-cart-total>0 ₴</strong>
          </div>
          <p class="order-delivery-note">${escapeHtml(t('freeDelivery', 'Доставка безкоштовна при замовленні від 2-ох бутлів.'))}</p>
        </aside>
      </div>
    `;
  };

  const buildFloatingActions = () => {
    const modalId = getOrderPopupOptions().modalId || 'orderModal';

    if (!document.querySelector('[data-cart-open]') && getModal(modalId)) {
      document.body.insertAdjacentHTML('beforeend', `
        <button type="button" class="floating-cart" data-cart-open aria-label="${escapeAttr(t('openCart', 'Відкрити кошик'))}">
          <i class="fa-solid fa-cart-shopping"></i>
          <span data-cart-count>0</span>
        </button>
      `);
    }

    if (!document.querySelector('[data-scroll-top]')) {
      document.body.insertAdjacentHTML('beforeend', `
        <button type="button" class="floating-scroll-top" data-scroll-top aria-label="${escapeAttr(t('scrollTop', 'Повернутися нагору'))}">
          <i class="fa-solid fa-arrow-up"></i>
        </button>
      `);
    }
  };

  const updateFloatingActions = () => {
    const scrollTopButton = document.querySelector('[data-scroll-top]');
    if (!scrollTopButton) return;

    scrollTopButton.classList.toggle('is-visible', window.scrollY > 420);
  };

  const formatCurrency = (value, currency = '₴') => `${value.toLocaleString(translations.numberLocale || 'uk-UA')} ${currency}`;

  const loadCart = () => {
    try {
      const saved = JSON.parse(localStorage.getItem(cartStorageKey) || '{}');
      return Object.keys(saved).reduce((acc, oldId) => {
        const id = /^water-\d+$/.test(oldId) ? defaultWaterId : oldId;
        if (cartCatalog[id] && Number.isFinite(saved[oldId]) && saved[oldId] > 0) {
          acc[id] = Math.max(getCartItemMin(cartCatalog[id]), Math.min(99, Math.floor(saved[oldId])));
        }
        return acc;
      }, {});
    } catch (error) {
      return {};
    }
  };

  const saveCart = () => {
    try {
      localStorage.setItem(cartStorageKey, JSON.stringify(cart));
    } catch (error) {
      // Cart persistence is a convenience only.
    }
  };

  const getCartItems = () => Object.entries(cart).map(([id, amount]) => ({
    id,
    amount,
    ...cartCatalog[id]
  })).filter((item) => item.title);

  const getWaterUnitPrice = (amount, product = cartCatalog[defaultWaterId]) => {
    return (product?.tiers?.find((tier) => amount >= tier.quantity) || product?.tiers?.at(-1))?.unitPrice || 0;
  };

  const getCartItemUnitPrice = (item) => (item.type === 'water' ? getWaterUnitPrice(item.amount, item) : item.unitPrice);

  const getCartItemSubtotal = (item) => item.amount * Math.round(getCartItemUnitPrice(item) * 100) / 100;

  const getCartItemMin = (item) => (item?.type === 'water' ? item.tiers.at(-1).quantity : 1);

  const getQuantityLabel = (item) => {
    if (item.type !== 'water') return item.unitLabel;
    const lastTwo = item.amount % 100;
    const last = item.amount % 10;
    if (last === 1 && lastTwo !== 11) return t('bottleOne', 'бутель');
    if (last >= 2 && last <= 4 && (lastTwo < 12 || lastTwo > 14)) return t('bottleFew', 'бутлі');
    return t('bottleMany', 'бутлів');
  };

  const getCartTotal = () => getCartItems().reduce((total, item) => (
    total + Math.round(getCartItemUnitPrice(item) * 100) * item.amount
  ), 0) / 100;

  const getCartCount = () => getCartItems().reduce((total, item) => (
    total + item.amount
  ), 0);

  const resolveProductButton = (button) => button.dataset.cartItem || '';

  const updateCartButtons = () => {
    document.querySelectorAll('[data-modal-open]').forEach((button) => {
      const itemId = resolveProductButton(button);
      if (!itemId) return;

      if (!button.dataset.cartLabel) {
        button.dataset.cartLabel = button.innerHTML.trim();
      }

      const tierQuantity = Number(button.dataset.constructorQuantity);
      const activeTier = cartCatalog[itemId]?.tiers?.find(tier => cart[itemId] >= tier.quantity);
      if (cart[itemId] && (!tierQuantity || activeTier?.quantity === tierQuantity)) {
        button.classList.add('cart-added');
        button.innerHTML = `<i class="fa-solid fa-check"></i> ${escapeHtml(t('added', 'Додано'))}`;
      } else {
        button.classList.remove('cart-added');
        button.innerHTML = button.dataset.cartLabel;
      }
    });

    document.querySelectorAll('[data-cart-related-add]').forEach((button) => {
      const itemId = button.dataset.cartRelatedAdd;
      button.classList.toggle('cart-added', Boolean(cart[itemId]));
      button.innerHTML = cart[itemId] ? `<i class="fa-solid fa-check"></i> ${escapeHtml(t('added', 'Додано'))}` : escapeHtml(t('add', 'Додати'));
    });
  };

  const renderCart = () => {
    const items = getCartItems();
    const itemsContainer = document.querySelector('[data-cart-items]');
    const totalContainer = document.querySelector('[data-cart-total]');
    const floatingCart = document.querySelector('[data-cart-open]');
    const floatingCount = document.querySelector('[data-cart-count]');

    if (itemsContainer) {
      itemsContainer.innerHTML = items.length ? items.map((item) => {
        const unitPrice = getCartItemUnitPrice(item);
        const subtotal = getCartItemSubtotal(item);
        const quantityLabel = getQuantityLabel(item);
        const itemDetail = item.type === 'water' ? `${item.amount} ${quantityLabel} ${t('inOrder', 'у замовленні')}` : item.detail;
        const minQuantity = getCartItemMin(item);

        return `
          <article class="order-cart-item">
            <div class="order-cart-item-main">
              <h4>${escapeHtml(item.title)}</h4>
              <p>${escapeHtml(itemDetail)} • ${escapeHtml(formatCurrency(unitPrice, item.currency))} / ${escapeHtml(item.type === 'water' ? t('bottleOne', 'бутель') : t('itemUnit', 'шт.'))}</p>
              <div class="order-cart-controls">
                <button type="button" data-cart-decrease="${escapeAttr(item.id)}" aria-label="${escapeAttr(t('decrease', 'Зменшити кількість'))}">
                  <i class="fa-solid fa-minus"></i>
                </button>
                <label>
                  <input type="number" inputmode="numeric" min="${minQuantity}" max="99" value="${item.amount}" data-cart-quantity="${escapeAttr(item.id)}" aria-label="${escapeAttr(t('quantity', 'Кількість'))} ${escapeAttr(item.title)}">
                  <span>${escapeHtml(quantityLabel)}</span>
                </label>
                <button type="button" data-cart-increase="${escapeAttr(item.id)}" aria-label="${escapeAttr(t('increase', 'Збільшити кількість'))}">
                  <i class="fa-solid fa-plus"></i>
                </button>
              </div>
            </div>
            <div class="order-cart-item-side">
              <strong>${escapeHtml(formatCurrency(subtotal, item.currency))}</strong>
              <button type="button" data-cart-remove="${escapeAttr(item.id)}" aria-label="${escapeAttr(t('remove', 'Прибрати товар'))}">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </article>
        `;
      }).join('') : `
        <div class="order-cart-empty">
          <i class="fa-solid fa-bottle-water"></i>
          <p>${escapeHtml(t('emptyCart', "Додайте воду або товари, і вони з'являться тут."))}</p>
        </div>
      `;
    }

    if (totalContainer) totalContainer.innerText = formatCurrency(getCartTotal(), items[0]?.currency || '₴');
    if (floatingCart) floatingCart.classList.toggle('is-visible', items.length > 0);
    if (floatingCount) floatingCount.innerText = getCartCount();

    updateCartButtons();
  };

  const addToCart = (itemId, quantity) => {
    if (orderSubmitting) return;
    const item = cartCatalog[itemId];
    if (!item) return;

    if (item.type === 'water') {
      cart[itemId] = Math.max(getCartItemMin(item), Math.min(99, Number(quantity) || cart[itemId] || item.quantity));
    } else {
      cart[itemId] = Math.min(99, (cart[itemId] || 0) + 1);
    }

    saveCart();
    renderCart();
  };

  const changeCartItem = (itemId, delta) => {
    if (orderSubmitting) return;
    const item = cartCatalog[itemId];
    if (!item || !cart[itemId]) return;
    const minQuantity = getCartItemMin(item);
    cart[itemId] = Math.min(99, cart[itemId] + delta);
    if (cart[itemId] < minQuantity) {
      if (item.type === 'water') {
        cart[itemId] = minQuantity;
      } else {
        delete cart[itemId];
      }
    }
    saveCart();
    renderCart();
  };

  const setCartItemQuantity = (itemId, quantity) => {
    if (orderSubmitting) return;
    const item = cartCatalog[itemId];
    if (!item) return;
    const minQuantity = getCartItemMin(item);
    const nextQuantity = Math.max(minQuantity, Math.min(99, Number.parseInt(quantity, 10) || minQuantity));
    cart[itemId] = nextQuantity;
    saveCart();
    renderCart();
  };

  const removeCartItem = (itemId) => {
    if (orderSubmitting) return;
    delete cart[itemId];
    saveCart();
    renderCart();
  };

  const setMobileContactState = (isOpen) => {
    const layer = document.querySelector('.mobile-contact-layer');
    const toggle = document.querySelector('[data-mobile-contacts-toggle]');
    if (!layer || !toggle) return;

    layer.classList.toggle('open', isOpen);
    layer.setAttribute('aria-hidden', String(!isOpen));
    toggle.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('mobile-contact-active', isOpen);
  };

  const setMobileMenuState = (isOpen) => {
    const drawer = document.querySelector('.mobile-menu-drawer');
    const toggle = document.querySelector('.mobile-menu-toggle');
    if (!drawer || !toggle) return;

    if (isOpen) setMobileContactState(false);
    drawer.classList.toggle('open', isOpen);
    drawer.setAttribute('aria-hidden', String(!isOpen));
    toggle.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('mobile-menu-active', isOpen);
  };

  const mobileSwiperSelectors = [
    '.promo-grid-actions',
    '.promo-grid-offers',
    '.feature-slider-section > div > .grid',
    '.office-usecases > div > .grid',
    '.water-quality > div > .grid'
  ];
  const mobileSwiperMedia = window.matchMedia('(max-width: 1023px)');
  let mobileSwiperRecords = [];

  const mountMobileSwipers = () => {
    if (!mobileSwiperMedia.matches || typeof window.Swiper !== 'function' || mobileSwiperRecords.length) return;

    mobileSwiperRecords = mobileSwiperSelectors.flatMap((selector) => [...document.querySelectorAll(selector)]).flatMap((wrapper) => {
      if (!wrapper.children.length) return [];

      const host = document.createElement('div');
      const pagination = document.createElement('div');
      host.className = 'swiper mobile-content-swiper';
      pagination.className = 'swiper-pagination mobile-swiper-pagination';

      wrapper.parentNode.insertBefore(host, wrapper);
      host.appendChild(wrapper);
      host.appendChild(pagination);
      wrapper.classList.add('swiper-wrapper');
      [...wrapper.children].forEach((slide) => slide.classList.add('swiper-slide'));

      const instance = new window.Swiper(host, {
        slidesPerView: 'auto',
        spaceBetween: 16,
        speed: 450,
        grabCursor: true,
        watchOverflow: true,
        observer: true,
        observeParents: true,
        pagination: {
          el: pagination,
          clickable: true
        }
      });

      return [{ host, wrapper, instance }];
    });
  };

  const unmountMobileSwipers = () => {
    mobileSwiperRecords.forEach(({ host, wrapper, instance }) => {
      instance.destroy(true, true);
      wrapper.classList.remove('swiper-wrapper');
      [...wrapper.children].forEach((slide) => slide.classList.remove('swiper-slide'));
      host.parentNode.insertBefore(wrapper, host);
      host.remove();
    });
    mobileSwiperRecords = [];
  };

  const syncMobileSwipers = () => {
    if (mobileSwiperMedia.matches) {
      mountMobileSwipers();
    } else {
      unmountMobileSwipers();
    }
  };

  const resetOrderSuccess = (modal) => {
    if (orderSuccessTimer) {
      window.clearTimeout(orderSuccessTimer);
      orderSuccessTimer = 0;
    }
    const form = modal?.querySelector('.order-modal-form');
    const success = form?.querySelector('[data-order-success]');
    if (!form || !success) return;
    form.classList.remove('is-success');
    success.hidden = true;
    const message = success.querySelector('[data-order-success-message]');
    if (message) message.textContent = '';
  };

  const setModalState = (id, isOpen) => {
    const modal = getModal(id);
    if (!modal) return;

    if (isOpen) {
      resetOrderSuccess(modal);
      setMobileMenuState(false);
      setMobileContactState(false);
      modal.classList.remove('hidden', 'is-closing');
      modal.classList.add('flex');
      requestAnimationFrame(() => modal.classList.add('is-open'));
      document.body.classList.add('modal-open');
      return;
    }

    modal.classList.remove('is-open');
    modal.classList.add('is-closing');
    document.body.classList.remove('modal-open');
    if (orderSuccessTimer) {
      window.clearTimeout(orderSuccessTimer);
      orderSuccessTimer = 0;
    }

    window.setTimeout(() => {
      if (modal.classList.contains('is-open')) return;
      modal.classList.add('hidden');
      modal.classList.remove('flex', 'is-closing');
      resetOrderSuccess(modal);
    }, 280);
  };

  const updatePrice = (changedQuantity) => {
    const quantities = changedQuantity
      ? [changedQuantity]
      : [...document.querySelectorAll('#quantity, [data-quantity]')];

    quantities.forEach((quantity) => {
      const form = quantity.closest('form') || document;
      const priceDisplay = form.querySelector('#priceDisplay, [data-price-display]');
      if (!priceDisplay) return;

      const qty = Number.parseInt(quantity.value, 10);
      const price = getWaterUnitPrice(qty);
      priceDisplay.innerText = formatCurrency(price, cartCatalog[defaultWaterId]?.currency);
      priceDisplay.classList.add('scale-110', 'text-brand-orange');

      window.setTimeout(() => {
        priceDisplay.classList.remove('scale-110', 'text-brand-orange');
      }, 200);
    });
  };

  const updateHeader = () => {
    const header = document.getElementById('main-header');
    if (!header) return;
    header.classList.toggle('scrolled', window.scrollY > 50);
  };

  document.addEventListener('click', (event) => {
    if (event.target.closest('[data-mobile-contacts-toggle]')) {
      const layer = document.querySelector('.mobile-contact-layer');
      setMobileMenuState(false);
      setMobileContactState(!layer?.classList.contains('open'));
      return;
    }

    if (event.target.closest('[data-mobile-contacts-close]')) {
      setMobileContactState(false);
      return;
    }

    const contactBackdrop = event.target.closest('[data-mobile-contact-backdrop]');
    if (contactBackdrop && event.target === contactBackdrop) {
      setMobileContactState(false);
      return;
    }

    if (event.target.closest('.mobile-menu-toggle')) {
      setMobileMenuState(true);
      return;
    }

    if (event.target.closest('.mobile-menu-close')) {
      setMobileMenuState(false);
      return;
    }

    const menuBackdrop = event.target.closest('[data-mobile-menu-backdrop]');
    if (menuBackdrop && event.target === menuBackdrop) {
      setMobileMenuState(false);
      return;
    }

    if (event.target.closest('.mobile-menu-link')) {
      setMobileMenuState(false);
    }

    if (event.target.closest('[data-cart-open]')) {
      setModalState('orderModal', true);
      return;
    }

    if (event.target.closest('[data-scroll-top]')) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    if (event.target.closest('[data-cart-add-default]')) {
      addToCart(defaultWaterId);
      return;
    }

    const relatedAddButton = event.target.closest('[data-cart-related-add]');
    if (relatedAddButton) {
      addToCart(relatedAddButton.dataset.cartRelatedAdd);
      return;
    }

    const increaseButton = event.target.closest('[data-cart-increase]');
    if (increaseButton) {
      changeCartItem(increaseButton.dataset.cartIncrease, 1);
      return;
    }

    const decreaseButton = event.target.closest('[data-cart-decrease]');
    if (decreaseButton) {
      changeCartItem(decreaseButton.dataset.cartDecrease, -1);
      return;
    }

    const removeButton = event.target.closest('[data-cart-remove]');
    if (removeButton) {
      removeCartItem(removeButton.dataset.cartRemove);
      return;
    }

    const openButton = event.target.closest('[data-modal-open]');
    if (openButton) {
      const itemId = resolveProductButton(openButton);
      const originalLabel = (openButton.dataset.cartLabel || openButton.textContent || '').trim();

      if (itemId) {
        addToCart(itemId, openButton.dataset.constructorQuantity);

        if (cartCatalog[itemId]?.type === 'extra' || originalLabel === t('add', 'Додати')) {
          return;
        }
      } else if (!getCartItems().length) {
        addToCart(defaultWaterId);
      }

      setMobileMenuState(false);
      setModalState(openButton.dataset.modalOpen, true);
      return;
    }

    const closeButton = event.target.closest('[data-modal-close]');
    if (closeButton) {
      setModalState(closeButton.dataset.modalClose, false);
      return;
    }

    const backdrop = event.target.closest('[data-modal-backdrop]');
    if (backdrop && event.target === backdrop) {
      setModalState(backdrop.dataset.modalBackdrop, false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    setMobileMenuState(false);
    setMobileContactState(false);
    document.querySelectorAll('[data-modal-backdrop]').forEach((modal) => setModalState(modal.id, false));
  });

  document.addEventListener('change', (event) => {
    if (event.target.matches('#quantity, [data-quantity]')) {
      updatePrice(event.target);
    }

    if (event.target.matches('[data-cart-quantity]')) {
      setCartItemQuantity(event.target.dataset.cartQuantity, event.target.value);
    }
  });

  document.addEventListener('input', (event) => {
    if (!event.target.matches('[data-short-date]')) return;
    const digits = event.target.value.replace(/\D/g, '').slice(0, 4);
    event.target.value = digits.length > 2 ? `${digits.slice(0, 2)}.${digits.slice(2)}` : digits;
  });

  let pendingOrder = null;
  document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!form.matches('.order-modal-form')) return;
    event.preventDefault();
    if (form.dataset.submitting === 'true') return;
    const feedback = form.querySelector('[data-order-feedback]');
    const submit = form.querySelector('[type="submit"]');
    const config = window.svitvodyCheckout;
    feedback.hidden = false;
    feedback.className = 'order-feedback';
    if (!getCartItems().length) { feedback.textContent = t('cartRequired', 'Додайте товари до кошика.'); return; }
    if (!form.reportValidity()) return;
    const data = new FormData(form);
    const payload = {
      name: data.get('name'), phone: data.get('phone'), address: data.get('address'), date: data.get('date'),
      message: data.get('message'), website: data.get('website'), language: config?.language,
      items: getCartItems().map(({ id, amount }) => ({ id, amount })), expected_total_cents: Math.round(getCartTotal() * 100)
    };
    const signature = JSON.stringify(payload);
    if (pendingOrder?.signature !== signature) pendingOrder = { signature, id: crypto.randomUUID() };
    payload.request_id = pendingOrder.id;
    form.dataset.submitting = 'true';
    orderSubmitting = true;
    submit.disabled = true;
    feedback.textContent = t('sending', 'Надсилаємо заявку…');
    feedback.classList.add('is-progress');
    // Freeze the snapshot while submitting; failed requests keep both the form and basket intact.
    const controls = [...form.querySelectorAll('input, textarea'), ...document.querySelectorAll('[data-cart-items] button, [data-cart-items] input, [data-cart-related-add], [data-cart-add-default]')];
    controls.forEach(control => { control.disabled = true; });
    try {
      if (!config?.url || !config?.nonce) throw new Error(t('refresh', 'Оновіть сторінку перед надсиланням заявки.'));
      const response = await fetch(config.url, {
        method: 'POST', credentials: 'same-origin',
        body: new URLSearchParams({ action: 'sv_submit_order', nonce: config.nonce, lang: config.language, payload: JSON.stringify(payload) }),
        signal: AbortSignal.timeout(20000)
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.data?.message || t('sendFailed', 'Не вдалося надіслати заявку. Спробуйте ще раз.'));
      const success = form.querySelector('[data-order-success]');
      const successMessage = success?.querySelector('[data-order-success-message]');
      if (successMessage) {
        successMessage.textContent = formatTranslated(t('success', 'Дякуємо! Заявку №%s збережено. Наш менеджер зв’яжеться з Вами.'), result.data.order_id);
      }
      feedback.hidden = true;
      form.classList.add('is-success');
      if (success) success.hidden = false;
      cart = {};
      saveCart();
      renderCart();
      form.reset();
      pendingOrder = null;
      const modal = form.closest('[data-modal-backdrop]');
      if (modal) modal.scrollTo({ top: 0, behavior: 'smooth' });
      orderSuccessTimer = window.setTimeout(() => {
        orderSuccessTimer = 0;
        if (modal) setModalState(modal.id, false);
      }, 8000);
    } catch (error) {
      feedback.textContent = error.name === 'TimeoutError' || error.name === 'TypeError'
        ? t('uncertain', 'Немає підтвердження від сервера. Дані збережені у формі. Повторіть надсилання.')
        : error.message;
      feedback.className = 'order-feedback is-error';
    } finally {
      form.dataset.submitting = 'false';
      orderSubmitting = false;
      submit.disabled = false;
      controls.forEach(control => { control.disabled = false; });
    }
  });

  window.addEventListener('scroll', () => {
    updateHeader();
    updateFloatingActions();
  });

  window.addEventListener('load', () => {
    updateHeader();
    updateFloatingActions();
    updatePrice();
  });

  cart = loadCart();
  buildOrderModal();
  buildFloatingActions();
  buildMobileNavigation();
  initBannerBottleParallax();
  renderCart();
  updateFloatingActions();
  syncMobileSwipers();
  mobileSwiperMedia.addEventListener('change', syncMobileSwipers);

  window.updatePrice = updatePrice;
})();
