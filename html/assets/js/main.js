(() => {
  const getModal = (id) => document.getElementById(id);
  const cartStorageKey = 'svitvody-cart-v1';
  const cartCatalog = {
    'water-2': {
      type: 'water',
      title: 'Вода питна 18.9 л',
      detail: 'Від 2-ох бутлів',
      quantity: 2,
      unitLabel: 'бутлі',
      unitPrice: 180
    },
    'water-6': {
      type: 'water',
      title: 'Вода питна 18.9 л',
      detail: 'Від 6-ти бутлів',
      quantity: 6,
      unitLabel: 'бутлів',
      unitPrice: 170
    },
    'water-12': {
      type: 'water',
      title: 'Вода питна 18.9 л',
      detail: 'Від 12-ти бутлів',
      quantity: 12,
      unitLabel: 'бутлів',
      unitPrice: 160
    },
    'bottle-deposit': {
      type: 'extra',
      title: 'Застава за бутель',
      detail: 'Повертається після здачі тари',
      quantity: 1,
      unitLabel: 'шт.',
      unitPrice: 280
    },
    pump: {
      type: 'extra',
      title: 'Помпа ECONOM PLUS',
      detail: 'Ручна помпа для бутля',
      quantity: 1,
      unitLabel: 'шт.',
      unitPrice: 200
    }
  };

  let cart = {};

  const pageLinks = [
    ['Головна', 'index.html', 'page-home'],
    ['Про воду', 'about-water.html', 'page-about-water'],
    ['Для дому', 'water-for-home.html', 'page-water-home'],
    ['Для офісу', 'water-for-office.html', 'page-water-office'],
    ['Вартість', document.getElementById('pricing') ? '#pricing' : 'index.html#pricing', '']
  ];

  const buildMobileNavigation = () => {
    const desktopHeader = document.getElementById('main-header');
    if (!desktopHeader || document.querySelector('.mobile-site-header')) return;

    const activePage = [...document.body.classList].find((className) => className.startsWith('page-')) || '';
    const navLinks = pageLinks.map(([label, href, pageClass]) => {
      const activeClass = pageClass === activePage ? ' is-active' : '';
      return `<a href="${href}" class="mobile-menu-link${activeClass}">${label}</a>`;
    }).join('');

    desktopHeader.insertAdjacentHTML('beforebegin', `
      <header class="mobile-site-header">
        <div class="mobile-site-header-inner">
          <a href="index.html" class="mobile-brand" aria-label="SvitVody, головна">
            <img class="site-logo site-logo-mobile" src="assets/img/svit-vody-logo-blue.svg" alt="Світ Води - доставка здоров'я">
          </a>
          <div class="mobile-header-actions">
            <button type="button" class="mobile-header-order" data-modal-open="orderModal">
              <span class="mobile-order-main">Замовити</span><span class="mobile-order-extra">воду</span>
            </button>
            <button type="button" class="mobile-tap mobile-phone" data-mobile-contacts-toggle aria-label="Відкрити контакти" aria-expanded="false">
              <i class="fa-solid fa-phone"></i>
            </button>
            <button type="button" class="mobile-tap mobile-menu-toggle" aria-label="Відкрити меню" aria-expanded="false">
              <i class="fa-solid fa-bars"></i>
            </button>
          </div>
        </div>
      </header>
      <div class="mobile-contact-layer" data-mobile-contact-backdrop aria-hidden="true">
        <section class="mobile-contact-panel" role="dialog" aria-label="Контакти">
          <div class="mobile-contact-heading">
            <span class="mobile-contact-icon"><i class="fa-solid fa-headset"></i></span>
            <h2>Контакти</h2>
            <button type="button" class="mobile-contact-close" data-mobile-contacts-close aria-label="Закрити контакти">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="mobile-contact-label">Наші телефони</p>
          <a href="tel:+380671234567" class="mobile-contact-phone">+380 (67) 123 45 67</a>
          <div class="mobile-contact-hours">
            <p>Графік роботи</p>
            <span>Пн-Пт: 08:00 - 20:00</span>
            <span>Сб-Нд: 09:00 - 18:00</span>
          </div>
          <div class="mobile-contact-messengers">
            <a href="#" aria-label="Telegram"><i class="fa-brands fa-telegram"></i><span>Telegram</span></a>
            <a href="#" aria-label="Viber"><i class="fa-brands fa-viber"></i><span>Viber</span></a>
          </div>
        </section>
      </div>
      <div class="mobile-menu-drawer" data-mobile-menu-backdrop aria-hidden="true">
        <aside class="mobile-menu-panel" aria-label="Меню сайту">
          <button type="button" class="mobile-tap mobile-menu-close" aria-label="Закрити меню">
            <i class="fa-solid fa-xmark"></i>
          </button>
          <nav class="mobile-menu-nav">${navLinks}</nav>
          <div class="mobile-menu-footer">
            <button type="button" data-modal-open="orderModal" class="mobile-menu-order">Замовити воду</button>
            <div class="mobile-language-switcher" aria-label="Мова сайту">
              <button type="button" class="is-active">UA</button>
              <button type="button">RU</button>
            </div>
          </div>
        </aside>
      </div>
    `);
  };

  const initBannerBottleParallax = () => {
    const media = window.matchMedia('(hover: hover) and (pointer: fine)');
    if (!media.matches) return;

    document.querySelectorAll('.banner-bottle-hover').forEach((bottle) => {
      const section = bottle.closest('#home, .inner-hero');
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
    const dialog = document.querySelector('#orderModal .order-modal-dialog');
    if (!dialog || dialog.children.length) return;

    dialog.innerHTML = `
      <button type="button" data-modal-close="orderModal" class="order-modal-close" aria-label="Закрити форму">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="order-modal-layout">
        <section class="order-modal-form-section">
          <p class="order-modal-contact-heading">Ви можете зв'язатися з нами телефоном або у месенджерах.</p>
          <div class="order-modal-intro">
            <div class="order-modal-contact-actions">
              <a href="tel:+380671234567" class="order-modal-phone-link">
                <i class="fa-solid fa-phone"></i>
                +380 (67) 123 45 67
              </a>
              <a href="#" aria-label="Telegram"><i class="fa-brands fa-telegram"></i><span>Telegram</span></a>
              <a href="#" aria-label="Viber"><i class="fa-brands fa-viber"></i><span>Viber</span></a>
            </div>
          </div>
          <div class="order-modal-divider"><span>Або оформіть замовлення нижче</span></div>
          <h2>Оформлення замовлення</h2>
          <form class="order-modal-form">
            <div class="order-form-grid">
              <label class="order-field">
                <span>Ваше ім'я*</span>
                <input type="text" name="name" required placeholder="Як ми можемо до Вас звертатися">
              </label>
              <label class="order-field">
                <span>Адреса доставки*</span>
                <input type="text" name="address" required placeholder="Вулиця, будинок, квартира">
              </label>
              <label class="order-field">
                <span>Номер телефону*</span>
                <input type="tel" name="phone" required placeholder="+38 (___) ___-__-__">
              </label>
              <label class="order-field">
                <span>Дата доставки*</span>
                <input type="date" name="date" required>
              </label>
              <fieldset class="order-time-field">
                <legend>Зручний час доставки*</legend>
                <div>
                  <label><input type="radio" name="modal-time" value="morning" required><span>До обіду</span></label>
                  <label><input type="radio" name="modal-time" value="afternoon" required><span>Після обіду</span></label>
                </div>
              </fieldset>
            </div>
            <label class="order-field order-message-field">
              <span>Ваше повідомлення</span>
              <textarea name="message" rows="3" placeholder="Додаткова інформація до замовлення"></textarea>
            </label>
            <button type="submit" class="order-submit">Замовити</button>
          </form>
        </section>
        <aside class="order-cart-section" aria-label="Мінікошик">
          <div class="order-cart-head">
            <span><i class="fa-solid fa-cart-shopping"></i></span>
            <div>
              <p>Мінікошик</p>
              <h3>Ваше замовлення</h3>
            </div>
          </div>
          <div class="order-cart-items" data-cart-items></div>
          <button type="button" class="order-cart-add-water" data-cart-add-default>
            <i class="fa-solid fa-plus"></i>
            Додати воду
          </button>
          <div class="order-related-products">
            <h4>Супутні товари</h4>
            <div class="order-related-list">
              <article>
                <div>
                  <strong>Помпа ECONOM PLUS</strong>
                  <span>200 ₴</span>
                </div>
                <button type="button" data-cart-related-add="pump">Додати</button>
              </article>
              <article>
                <div>
                  <strong>Застава за бутель</strong>
                  <span>280 ₴</span>
                </div>
                <button type="button" data-cart-related-add="bottle-deposit">Додати</button>
              </article>
            </div>
          </div>
          <div class="order-cart-total">
            <span>Разом</span>
            <strong data-cart-total>0 ₴</strong>
          </div>
          <p class="order-delivery-note">Доставка безкоштовна при замовленні від 2-ох бутлів.</p>
        </aside>
      </div>
    `;
  };

  const buildFloatingActions = () => {
    if (!document.querySelector('[data-cart-open]') && getModal('orderModal')) {
      document.body.insertAdjacentHTML('beforeend', `
        <button type="button" class="floating-cart" data-cart-open aria-label="Відкрити кошик">
          <i class="fa-solid fa-cart-shopping"></i>
          <span data-cart-count>0</span>
        </button>
      `);
    }

    if (!document.querySelector('[data-scroll-top]')) {
      document.body.insertAdjacentHTML('beforeend', `
        <button type="button" class="floating-scroll-top" data-scroll-top aria-label="Повернутися нагору">
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

  const formatCurrency = (value) => `${value.toLocaleString('uk-UA')} ₴`;

  const loadCart = () => {
    try {
      const saved = JSON.parse(localStorage.getItem(cartStorageKey) || '{}');
      return Object.keys(saved).reduce((acc, id) => {
        if (cartCatalog[id] && Number.isFinite(saved[id]) && saved[id] > 0) {
          acc[id] = saved[id];
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

  const getWaterUnitPrice = (amount) => (amount >= 12 ? 160 : amount >= 6 ? 170 : 180);

  const getCartItemUnitPrice = (item) => (item.type === 'water' ? getWaterUnitPrice(item.amount) : item.unitPrice);

  const getCartItemSubtotal = (item) => item.amount * getCartItemUnitPrice(item);

  const getCartItemMin = (item) => (item?.type === 'water' ? 2 : 1);

  const getQuantityLabel = (item) => {
    if (item.type !== 'water') return item.unitLabel;
    return item.amount === 1 ? 'бутель' : item.amount < 5 ? 'бутлі' : 'бутлів';
  };

  const getCartTotal = () => getCartItems().reduce((total, item) => (
    total + getCartItemSubtotal(item)
  ), 0);

  const getCartCount = () => getCartItems().reduce((total, item) => (
    total + item.amount
  ), 0);

  const resolveProductButton = (button) => {
    if (button.dataset.cartItem) return button.dataset.cartItem;
    if (!button.closest('#pricing')) return '';

    let node = button.parentElement;

    while (node && node !== document.body) {
      const heading = node.querySelector('h3, h4');
      const text = `${heading?.innerText || ''} ${node.innerText || ''}`;

      if (/Застава за бутель/i.test(text)) {
        button.dataset.cartItem = 'bottle-deposit';
        return 'bottle-deposit';
      }
      if (/Помпа ECONOM PLUS/i.test(text)) {
        button.dataset.cartItem = 'pump';
        return 'pump';
      }
      if (/Від 12-ти бутлів/i.test(text)) {
        button.dataset.cartItem = 'water-12';
        return 'water-12';
      }
      if (/Від 6-ти бутлів/i.test(text)) {
        button.dataset.cartItem = 'water-6';
        return 'water-6';
      }
      if (/Від 2-ох бутлів/i.test(text)) {
        button.dataset.cartItem = 'water-2';
        return 'water-2';
      }

      node = node.parentElement;
    }

    return '';
  };

  const updateCartButtons = () => {
    document.querySelectorAll('[data-modal-open]').forEach((button) => {
      const itemId = resolveProductButton(button);
      if (!itemId) return;

      if (!button.dataset.cartLabel) {
        button.dataset.cartLabel = button.innerHTML.trim();
      }

      if (cart[itemId]) {
        button.classList.add('cart-added');
        button.innerHTML = '<i class="fa-solid fa-check"></i> Додано';
      } else {
        button.classList.remove('cart-added');
        button.innerHTML = button.dataset.cartLabel;
      }
    });

    document.querySelectorAll('[data-cart-related-add]').forEach((button) => {
      const itemId = button.dataset.cartRelatedAdd;
      button.classList.toggle('cart-added', Boolean(cart[itemId]));
      button.innerHTML = cart[itemId] ? '<i class="fa-solid fa-check"></i> Додано' : 'Додати';
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
        const itemDetail = item.type === 'water' ? `${item.amount} ${quantityLabel} у замовленні` : item.detail;
        const minQuantity = getCartItemMin(item);

        return `
          <article class="order-cart-item">
            <div class="order-cart-item-main">
              <h4>${item.title}</h4>
              <p>${itemDetail} • ${formatCurrency(unitPrice)} / ${item.type === 'water' ? 'бутель' : 'шт.'}</p>
              <div class="order-cart-controls">
                <button type="button" data-cart-decrease="${item.id}" aria-label="Зменшити кількість">
                  <i class="fa-solid fa-minus"></i>
                </button>
                <label>
                  <input type="number" inputmode="numeric" min="${minQuantity}" max="99" value="${item.amount}" data-cart-quantity="${item.id}" aria-label="Кількість ${item.title}">
                  <span>${quantityLabel}</span>
                </label>
                <button type="button" data-cart-increase="${item.id}" aria-label="Збільшити кількість">
                  <i class="fa-solid fa-plus"></i>
                </button>
              </div>
            </div>
            <div class="order-cart-item-side">
              <strong>${formatCurrency(subtotal)}</strong>
              <button type="button" data-cart-remove="${item.id}" aria-label="Прибрати товар">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </article>
        `;
      }).join('') : `
        <div class="order-cart-empty">
          <i class="fa-solid fa-bottle-water"></i>
          <p>Додайте воду або товари, і вони з'являться тут.</p>
        </div>
      `;
    }

    if (totalContainer) totalContainer.innerText = formatCurrency(getCartTotal());
    if (floatingCart) floatingCart.classList.toggle('is-visible', items.length > 0);
    if (floatingCount) floatingCount.innerText = getCartCount();

    updateCartButtons();
  };

  const addToCart = (itemId) => {
    const item = cartCatalog[itemId];
    if (!item) return;

    if (item.type === 'water') {
      Object.keys(cartCatalog).forEach((id) => {
        if (cartCatalog[id].type === 'water') delete cart[id];
      });
      cart[itemId] = item.quantity;
    } else {
      cart[itemId] = (cart[itemId] || 0) + 1;
    }

    saveCart();
    renderCart();
  };

  const changeCartItem = (itemId, delta) => {
    const item = cartCatalog[itemId];
    if (!item || !cart[itemId]) return;
    const minQuantity = getCartItemMin(item);
    cart[itemId] += delta;
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
    const item = cartCatalog[itemId];
    if (!item) return;
    const minQuantity = getCartItemMin(item);
    const nextQuantity = Math.max(minQuantity, Math.min(99, Number.parseInt(quantity, 10) || minQuantity));
    cart[itemId] = nextQuantity;
    saveCart();
    renderCart();
  };

  const removeCartItem = (itemId) => {
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
    '#promo .promo-grid-actions',
    '#promo .promo-grid-offers',
    '.feature-slider-section > div > .grid',
    '.office-usecases > div > .grid',
    '.water-quality > div > .grid'
  ];
  const mobileSwiperMedia = window.matchMedia('(max-width: 1023px)');
  let mobileSwiperRecords = [];

  const mountMobileSwipers = () => {
    if (!mobileSwiperMedia.matches || typeof window.Swiper !== 'function' || mobileSwiperRecords.length) return;

    mobileSwiperRecords = mobileSwiperSelectors.flatMap((selector) => {
      const wrapper = document.querySelector(selector);
      if (!wrapper || !wrapper.children.length) return [];

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

  const setModalState = (id, isOpen) => {
    const modal = getModal(id);
    if (!modal) return;

    if (isOpen) {
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

    window.setTimeout(() => {
      if (modal.classList.contains('is-open')) return;
      modal.classList.add('hidden');
      modal.classList.remove('flex', 'is-closing');
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
      const price = qty >= 12 ? 160 : qty >= 6 ? 170 : 180;
      priceDisplay.innerText = price + ' ₴';
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

  const revealSections = () => {
    document.querySelectorAll('.reveal').forEach((element) => {
      const revealTop = element.getBoundingClientRect().top;
      if (revealTop < window.innerHeight - 150) {
        element.classList.add('active');
      }
    });
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
      addToCart('water-2');
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
        addToCart(itemId);

        if (/^Додати$/i.test(originalLabel)) {
          return;
        }
      } else if (!getCartItems().length) {
        addToCart('water-2');
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

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form.matches('form') || form.getAttribute('action')) return;

    event.preventDefault();
    alert('Дякуємо! Ваша заявка прийнята. Наш менеджер зв\'яжеться з Вами найближчим часом.');
    cart = {};
    saveCart();
    renderCart();
    setModalState('orderModal', false);
  });

  window.addEventListener('scroll', () => {
    updateHeader();
    updateFloatingActions();
    revealSections();
  });

  window.addEventListener('load', () => {
    updateHeader();
    updateFloatingActions();
    revealSections();
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
