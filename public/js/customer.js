(function () {
    'use strict';

    var CSRF = (window.ML && window.ML.csrf) || '';
    var BASE = (window.ML && window.ML.base) || '/techwiz7/public/';

    function toast(msg, ok) {
        var root = document.querySelector('.ml-toast-root');
        if (!root) return;
        var el = document.createElement('div');
        el.className = 'ml-toast' + (ok ? '' : ' error');
        el.innerHTML = '<i class="bi ' + (ok ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger') + '"></i>' + msg;
        root.appendChild(el);
        setTimeout(function () { el.remove(); }, 3600);
    }

    function mlPost(url, data) {
        var body = new URLSearchParams(data);
        body.set('csrf_token', CSRF);
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        }).then(function (r) { return r.json(); });
    }

    function setBadge(sel, n) {
        var els = document.querySelectorAll(sel);
        els.forEach(function (b) {
            b.textContent = n;
            b.classList.toggle('show', parseInt(n, 10) > 0);
        });
    }

    /* Fly the product thumbnail from the button that was clicked to the
       cart icon in the navbar. Driven by the Web Animations API so there
       is no CSS class to add and remove, and no layout thrash. */
    function flyToCart(fromEl) {
        var cartBtn = document.querySelector('[data-cart-count]');
        if (!cartBtn) return;
        var target = cartBtn.closest('a, button');
        if (!target) return;

        var card = fromEl.closest('.ml-product, .ml-product-card, [data-fly-img]');
        var src = card && card.querySelector('img');

        var ghost;
        if (src && src.currentSrc) {
            ghost = document.createElement('img');
            ghost.src = src.currentSrc;
            ghost.alt = '';
            ghost.className = 'ml-fly-ghost';
        } else {
            /* No thumbnail on this card, so fly a cart glyph instead of
               dropping the effect entirely. */
            ghost = document.createElement('i');
            ghost.className = 'bi bi-basket-fill ml-fly-ghost ml-fly-ghost--icon';
        }

        var a = fromEl.getBoundingClientRect();
        var b = target.getBoundingClientRect();
        var size = 54;
        ghost.style.width = size + 'px';
        ghost.style.height = size + 'px';
        ghost.style.left = (a.left + a.width / 2 - size / 2) + 'px';
        ghost.style.top = (a.top + a.height / 2 - size / 2) + 'px';
        document.body.appendChild(ghost);

        var dx = (b.left + b.width / 2) - (a.left + a.width / 2);
        var dy = (b.top + b.height / 2) - (a.top + a.height / 2);

        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce) { ghost.remove(); return; }

        var anim = ghost.animate([
            { transform: 'translate(0, 0) scale(1)', opacity: 1 },
            { transform: 'translate(' + dx + 'px, ' + dy + 'px) scale(.28)', opacity: .9, offset: .72 },
            { transform: 'translate(' + dx + 'px, ' + dy + 'px) scale(.08)', opacity: 0 }
        ], {
            duration: 780,
            easing: 'cubic-bezier(.55,.06,.68,.19)',
            fill: 'forwards'
        });

        anim.onfinish = function () {
            ghost.remove();
            target.animate([
                { transform: 'scale(1)' },
                { transform: 'scale(1.28)' },
                { transform: 'scale(1)' }
            ], { duration: 420, easing: 'ease-out' });
        };
    }

    document.addEventListener('click', function (e) {
        var add = e.target.closest('[data-add-cart]');
        if (add) {
            e.preventDefault();
            if (add.disabled) return;
            var pid = add.getAttribute('data-add-cart');
            var qtyInp = add.closest('.cart-form') ? add.querySelector('[data-qty-input]') : add.closest('form') ? add.querySelector('[data-qty-input]') : null;
            var q = 1;
            if (qtyInp) q = parseInt(qtyInp.value, 10) || 1;
            add.disabled = true;
            mlPost('../private/backend-scripting/customer-cart.php', { action: 'add', product_id: pid, qty: q })
                .then(function (res) {
                    add.disabled = false;
                    if (res.ok) {
                        setBadge('[data-cart-count]', res.count);
                        flyToCart(add);
                        toast(res.msg || 'Added to cart');
                        if (res.favChanged !== undefined) setBadge('[data-fav-count]', res.favCount);
                    } else {
                        toast(res.error || 'Something went wrong', false);
                    }
                }).catch(function () { add.disabled = false; toast('Network error', false); });
            return;
        }

        var fav = e.target.closest('[data-fav-btn]');
        if (fav) {
            e.preventDefault();
            var favType = fav.getAttribute('data-fav-type');
            var favId = fav.getAttribute('data-fav-id');
            if (!favId) return;
            mlPost('../private/backend-scripting/customer-fav.php', { fav_type: favType, id: favId })
                .then(function (res) {
                    if (res.ok) {
                        if (res.removed) {
                            toast('Removed from favorites');
                            if (fav.hasAttribute('data-fav-silent')) fav.closest('[data-fav-item]') && fav.closest('[data-fav-item]').remove();
                            var btn = fav.querySelector('.bi');
                            if (btn) { btn.className = 'bi bi-heart'; }
                            fav.classList.remove('is-fav');
                        } else {
                            toast('Added to favorites');
                            var btn = fav.querySelector('.bi');
                            if (btn) { btn.className = 'bi bi-heart-fill'; }
                            fav.classList.add('is-fav');
                        }
                        setBadge('[data-fav-count]', res.count);
                    } else {
                        if (res.login) { window.location.href = BASE + 'login'; return; }
                        toast(res.error || 'Error', false);
                    }
                }).catch(function () { toast('Network error', false); });
            return;
        }

        var notif = e.target.closest('[data-notif-id]');
        if (notif) {
            var nid = notif.getAttribute('data-notif-id');
            mlPost('../private/backend-scripting/customer-notif.php', { action: 'read', id: nid }).catch(function () {});
        }

        var markAll = e.target.closest('[data-mark-all]');
        if (markAll) {
            mlPost('../private/backend-scripting/customer-notif.php', { action: 'readall' }).then(function (res) {
                if (res.ok) {
                    setBadge('[data-notif-count]', 0);
                    document.querySelectorAll('.notif-item.unread').forEach(function (n) { n.classList.remove('unread'); });
                    toast('All notifications marked as read');
                }
            });
        }

        var qtyBtn = e.target.closest('[data-qty-btn]');
        if (qtyBtn) {
            var box = qtyBtn.closest('.ml-qty') || qtyBtn.closest('[data-cart-row]');
            var input = box ? box.querySelector('[data-qty-input]') : null;
            if (!input) return;
            var min = parseInt(input.getAttribute('min') || '1', 10);
            var max = parseInt(input.getAttribute('max') || '999999', 10);
            var cur = parseInt(input.value, 10) || min;
            var next = qtyBtn.getAttribute('data-qty-btn') === 'inc' ? cur + 1 : cur - 1;
            if (next < min) next = min;
            if (next > max) next = max;
            input.value = next;
            var row = qtyBtn.closest('[data-cart-row]');
            if (!row) return;
            var pid = row.getAttribute('data-cart-row');
            mlPost('../private/backend-scripting/customer-cart.php', { action: 'update', product_id: pid, qty: next })
                .then(function (res) {
                    if (res.ok) {
                        setBadge('[data-cart-count]', res.count);
                        if (res.purged) {
                            if (row) row.remove();
                            refreshCartTotals();
                            toast('Removed — item is no longer available', false);
                            if (!document.querySelector('[data-cart-row]')) window.location.reload();
                            return;
                        }
                        updateRowTotals(row, res.price, next);
                        toast('Cart updated');
                    } else toast(res.error || 'Error', false);
                });
            return;
        }

        var rmv = e.target.closest('[data-remove-cart]');
        if (rmv) {
            var row = rmv.closest('[data-cart-row]');
            var pid = row.getAttribute('data-cart-row');
            mlPost('../private/backend-scripting/customer-cart.php', { action: 'remove', product_id: pid })
                .then(function (res) {
                    if (res.ok) {
                        if (row) row.remove();
                        setBadge('[data-cart-count]', res.count);
                        if (res.empty) { window.location.reload(); return; }
                        refreshCartTotals();
                        toast('Removed from cart');
                    } else toast(res.error || 'Error', false);
                });
            return;
        }
    });

    function moneyNum(text) {
        var raw = String(text).replace(/[^0-9.]/g, '');
        return parseFloat(raw) || 0;
    }

    function moneyText(n) {
        return 'Rs ' + Math.round(moneyNum(n)).toLocaleString();
    }

    function updateRowTotals(row, price, qty) {
        var cell = row.querySelector('[data-row-total]');
        if (cell) cell.textContent = moneyText(price * qty);
        refreshCartTotals();
    }

    function refreshCartTotals() {
        var totCell = document.querySelector('[data-cart-total]');
        if (!totCell) return;
        var sum = 0;
        document.querySelectorAll('[data-cart-row]').forEach(function (r) {
            var t = r.querySelector('[data-row-total]');
            if (t) sum += moneyNum(t.textContent);
        });
        totCell.textContent = moneyText(sum);
        var itemCell = document.querySelector('[data-cart-items-count]');
        if (itemCell) itemCell.textContent = document.querySelectorAll('[data-cart-row]').length;
    }

    document.querySelectorAll('.ml-slot').forEach(function (slot) {
        slot.addEventListener('click', function () {
            var group = slot.getAttribute('data-slot-group');
            document.querySelectorAll('.ml-slot[data-slot-group="' + group + '"]').forEach(function (s) {
                s.classList.remove('selected');
            });
            slot.classList.add('selected');
            var hidden = document.querySelector('input[name="' + group + '"]');
            if (hidden) hidden.value = slot.getAttribute('data-slot-value');
        });
    });

    var chatToggle = document.getElementById('mlChatToggle');
    var chatPanel = document.getElementById('mlChatPanel');
    var chatClose = document.getElementById('mlChatClose');
    var chatBody = document.getElementById('mlChatBody');
    var chatForm = document.getElementById('mlChatForm');
    var chatText = document.getElementById('mlChatText');

    function chatIcon(openState) {
        if (!chatToggle) return;
        var i = chatToggle.querySelector('i');
        if (i) {
            i.className = openState ? 'bi bi-x-lg' : 'bi bi-chat-dots-fill';
        }
    }

    if (chatToggle && chatPanel) {
        chatToggle.addEventListener('click', function () {
            chatPanel.classList.toggle('open');
            chatIcon(chatPanel.classList.contains('open'));
        });
        if (chatClose) chatClose.addEventListener('click', function () {
            chatPanel.classList.remove('open');
            chatIcon(false);
        });
    }

    function pushBotMessage() {
        var bot = document.createElement('div');
        bot.className = 'ml-msg ml-msg-bot';
        bot.textContent = '\u2026';
        chatBody.appendChild(bot);
        chatBody.scrollTop = chatBody.scrollHeight;
        return bot;
    }

    if (chatForm && chatText && chatBody) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var q = chatText.value.trim();
            if (!q) return;
            chatText.value = '';
            var userMsg = document.createElement('div');
            userMsg.className = 'ml-msg ml-msg-user';
            userMsg.textContent = q;
            chatBody.appendChild(userMsg);
            chatBody.scrollTop = chatBody.scrollHeight;

            var bot = pushBotMessage();
            var csrf = (window.ML && window.ML.csrf) || '';
            fetch('../private/backend-scripting/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: 'csrf_token=' + encodeURIComponent(csrf) + '&message=' + encodeURIComponent(q)
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    bot.textContent = (res && res.reply) ? res.reply : botReply(q);
                    chatBody.scrollTop = chatBody.scrollHeight;
                })
                .catch(function () {
                    bot.textContent = botReply(q);
                    chatBody.scrollTop = chatBody.scrollHeight;
                });
        });
    }

    function botReply(q) {
        var s = q.toLowerCase();
        if (s.indexOf('market') !== -1) return 'We have fresh markets across Karachi and Hyderabad. Open the Markets page to see locations, days and pickup windows.';
        if (s.indexOf('farmer') !== -1) return 'Our farmers are verified local producers. Browse the Farmers section to see stalls, ratings and their products.';
        if (s.indexOf('product') !== -1 || s.indexOf('vegetable') !== -1 || s.indexOf('fruit') !== -1) return 'You can browse all fresh produce on the Products page and filter by category or market.';
        if (s.indexOf('order') !== -1) return 'Go to My Orders to track every pre-order. You can modify or cancel an order up to 24 hours before pickup.';
        if (s.indexOf('cart') !== -1) return 'Cart is stored in your browser session. Add items, then checkout and pick a market date and slot.';
        if (s.indexOf('pay') !== -1 || s.indexOf('payment') !== -1) return 'Payment is settled in person at the market when you collect your order. No online payment needed.';
        if (s.indexOf('cancel') !== -1) return 'Orders can be cancelled or modified free of charge until the cutoff (24h before pickup).';
        if (s.indexOf('time') !== -1 || s.indexOf('pickup') !== -1 || s.indexOf('slot') !== -1) return 'Pickup runs 7:00 AM to 2:00 PM on market days. Choose a date and time slot at checkout.';
        if (s.indexOf('hi') !== -1 || s.indexOf('hello') !== -1 || s.indexOf('salam') !== -1) return 'Salam! How can I help you today? Try asking about markets, farmers, products or orders.';
        return 'I can help with markets, farmers, products, cart, orders, pickup times and payment. Try asking me a question!';
    }

    document.querySelectorAll('[data-slider]').forEach(function (root) {
        var track = root.querySelector('[data-slider-track]');
        if (!track) return;
        var prev = root.querySelector('[data-slider-prev]');
        var next = root.querySelector('[data-slider-next]');

        var step = function () {
            var slide = track.querySelector('.ml-slide');
            if (!slide) return track.clientWidth * .8;
            var gap = parseFloat(getComputedStyle(track).columnGap) || 16;
            return slide.getBoundingClientRect().width + gap;
        };

        var sync = function () {
            var max = track.scrollWidth - track.clientWidth;
            if (prev) prev.disabled = track.scrollLeft <= 8;
            if (next) next.disabled = max - track.scrollLeft <= 8;
        };

        var glide = function (dir) {
            track.scrollBy({ left: dir * step(), behavior: 'smooth' });
        };

        if (prev) prev.addEventListener('click', function () { glide(-1); });
        if (next) next.addEventListener('click', function () { glide(1); });

        track.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);
        sync();

        var down = false;
        var startX = 0;
        var startLeft = 0;
        var moved = false;

        track.addEventListener('mousedown', function (e) {
            if (e.button !== 0) return;
            down = true;
            moved = false;
            startX = e.clientX;
            startLeft = track.scrollLeft;
        });

        window.addEventListener('mousemove', function (e) {
            if (!down) return;
            var dx = e.clientX - startX;
            if (Math.abs(dx) > 4) {
                moved = true;
                track.classList.add('is-dragging');
            }
            track.scrollLeft = startLeft - dx;
        });

        window.addEventListener('mouseup', function () {
            if (!down) return;
            down = false;
            track.classList.remove('is-dragging');
        });

        track.addEventListener('click', function (e) {
            if (moved) {
                e.preventDefault();
                e.stopPropagation();
                moved = false;
            }
        }, true);
    });

    // ---- Recently viewed (device-local, no DB) ----
    window.mlRecent = {
        KEY: 'ml_recent_v1',
        load: function () {
            try { return JSON.parse(localStorage.getItem(this.KEY) || '[]') || []; }
            catch (e) { return []; }
        },
        add: function (item) {
            if (!item || !item.id) return;
            var list = this.load().filter(function (x) { return String(x.id) !== String(item.id); });
            list.unshift({ id: item.id, name: item.name, price: item.price, unit: item.unit || '', img: item.img || '' });
            try { localStorage.setItem(this.KEY, JSON.stringify(list.slice(0, 10))); } catch (e) {}
        },
        render: function (host) {
            if (!host) return;
            var list = this.load();
            if (!list.length) { host.style.display = 'none'; return; }
            host.style.display = '';
            var base = (window.ML && window.ML.base) || '/techwiz7/public';
            var inner = document.getElementById('mlRecentList');
            if (!inner) return;
            inner.innerHTML = '';
            list.forEach(function (it) {
                var a = document.createElement('a');
                a.className = 'ml-recent-item';
                a.href = base + '/product?id=' + encodeURIComponent(it.id);
                if (it.img) {
                    var imgEl = document.createElement('img');
                    imgEl.src = it.img;
                    imgEl.alt = '';
                    imgEl.loading = 'lazy';
                    a.appendChild(imgEl);
                } else {
                    var ph = document.createElement('span');
                    ph.className = 'ml-recent-ph';
                    ph.innerHTML = '<i class="bi bi-basket-fill"></i>';
                    a.appendChild(ph);
                }
                var meta = document.createElement('div');
                meta.className = 'ml-recent-meta';
                var nm = document.createElement('span');
                nm.className = 'ml-recent-name';
                nm.textContent = it.name || 'Product';
                var pr = document.createElement('span');
                pr.className = 'ml-recent-price';
                pr.textContent = 'Rs ' + (Number(it.price) || 0).toLocaleString();
                meta.appendChild(nm);
                meta.appendChild(pr);
                a.appendChild(meta);
                inner.appendChild(a);
            });
        }
    };

    // ---- Live search suggestions ----
    var LIVE_URL = '../private/backend-scripting/live-search.php';
    var SUG_BASE = (window.ML && window.ML.base) || '/techwiz7/public';

    function renderSug(box, d, input) {
        var items = [];
        (d.products || []).forEach(function (p) {
            items.push({ href: SUG_BASE + '/product?id=' + p.id, icon: 'bi-basket2', label: p.name, sub: (p.stall || '') + (p.price >= 0 ? ' · Rs ' + Number(p.price).toLocaleString() : '') });
        });
        (d.farmers || []).forEach(function (f) {
            items.push({ href: SUG_BASE + '/farmer?id=' + f.id, icon: 'bi-person-badge', label: f.stall, sub: 'Farmer stall' });
        });
        (d.markets || []).forEach(function (m) {
            items.push({ href: SUG_BASE + '/market?id=' + m.id, icon: 'bi-shop', label: m.name, sub: m.address || '' });
        });

        if (!items.length) {
            box.innerHTML = '<a class="ml-suggest-item ml-suggest-empty" href="' + SUG_BASE + '/search?q=' + encodeURIComponent(input.value) + '"><i class="bi bi-search"></i><span>No exact match &mdash; run full search</span></a>';
        } else {
            items.forEach(function (it) {
                var a = document.createElement('a');
                a.className = 'ml-suggest-item';
                a.href = it.href;
                var i = document.createElement('i');
                i.className = 'bi ' + it.icon;
                var tx = document.createElement('span');
                tx.className = 'ml-suggest-txt';
                var lb = document.createElement('span');
                lb.className = 'ml-suggest-label';
                lb.textContent = it.label;
                var sb = document.createElement('span');
                sb.className = 'ml-suggest-sub';
                sb.textContent = it.sub;
                tx.appendChild(lb);
                tx.appendChild(sb);
                a.appendChild(i);
                a.appendChild(tx);
                box.appendChild(a);
            });
            var all = document.createElement('a');
            all.className = 'ml-suggest-item ml-suggest-seeall';
            all.href = SUG_BASE + '/search?q=' + encodeURIComponent(input.value);
            var sp = document.createElement('span');
            sp.innerHTML = 'See all results &rarr;';
            all.appendChild(sp);
            box.appendChild(all);
        }
        box.classList.add('open');
    }

    document.querySelectorAll('[data-live-search]').forEach(function (input) {
        var wrap = input.parentNode;
        if (wrap && getComputedStyle(wrap).position === 'static') wrap.style.position = 'relative';
        var box = document.createElement('div');
        box.className = 'ml-suggest';
        wrap.insertBefore(box, input.nextSibling);
        input.setAttribute('autocomplete', 'off');

        var timer = null;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            var q = input.value.trim();
            if (q.length < 2) { box.classList.remove('open'); return; }
            timer = setTimeout(function () {
                fetch(LIVE_URL + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (input.value.trim() !== q) return;
                        box.innerHTML = '';
                        renderSug(box, d, input);
                    }).catch(function () {});
            }, 150);
        });
        input.addEventListener('blur', function () {
            setTimeout(function () { box.classList.remove('open'); }, 160);
        });
    });

    window.mlToast = toast;
    window.mlBadge = setBadge;
    window.setBadge = setBadge;
    window.toast = toast;
})();
/* ============================================================
   Cursor flashlight beam
   One engine, shared by the navbar and the footer. Writes --mouse-x /
   --mouse-y on the host element; the CSS mask on the reveal layer is what
   actually clips the photo to a soft circle.
   ============================================================ */
function attachFlashlight(host, layerSelector, targetSelector, litRadius) {
    if (!host || !host.querySelector(layerSelector)) return;

    let mouse = { x: -500, y: -500 };
    let currentPos = { x: -500, y: -500 };
    const speed = 0.12; // Fluid lag factor (lower = smoother water wave feel)
    const targets = Array.from(host.querySelectorAll(targetSelector));

    // Cache the geometry instead of measuring every element on every mousemove -
    // the repeated getBoundingClientRect calls forced a layout on each event and
    // the beam visibly stuttered.
    let rect = host.getBoundingClientRect();
    let centres = [];
    const remeasure = () => {
        rect = host.getBoundingClientRect();
        centres = targets.map(el => {
            const r = el.getBoundingClientRect();
            return { el, x: r.left + r.width / 2 - rect.left, y: r.top + r.height / 2 - rect.top };
        });
    };
    remeasure();
    window.addEventListener("resize", remeasure);
    window.addEventListener("scroll", remeasure, { passive: true });

    // The beam parks at -500,-500. Lerping from there to the first cursor
    // position drags a visible streak across the whole host on entry (and
    // again on exit), so snap on the first move of each hover and only lerp
    // for the movement after that.
    let primed = false;

    host.addEventListener("mousemove", (e) => {
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
        if (!primed) {
            currentPos.x = mouse.x;
            currentPos.y = mouse.y;
            primed = true;
        }
        host.classList.add("is-illuminated");

        // Anything the beam is touching flips to dark text so it stays legible
        // against the bright revealed photo.
        for (const t of centres) {
            t.el.classList.toggle("illuminated-text", Math.hypot(mouse.x - t.x, mouse.y - t.y) < litRadius);
        }
    });

    host.addEventListener("mouseleave", () => {
        mouse.x = -500;
        mouse.y = -500;
        primed = false;
        host.classList.remove("is-illuminated");
        targets.forEach(el => el.classList.remove("illuminated-text"));
    });

    // Idle the rAF loop once the beam has settled, instead of burning 60fps
    // for the whole session.
    let running = false;
    function frame() {
        const dx = mouse.x - currentPos.x;
        const dy = mouse.y - currentPos.y;
        if (Math.abs(dx) < 0.1 && Math.abs(dy) < 0.1) {
            currentPos.x = mouse.x;
            currentPos.y = mouse.y;
            running = false;
        } else {
            currentPos.x += dx * speed;
            currentPos.y += dy * speed;
            requestAnimationFrame(frame);
        }
        host.style.setProperty("--mouse-x", `${currentPos.x}px`);
        host.style.setProperty("--mouse-y", `${currentPos.y}px`);
    }
    function wake() {
        if (running) return;
        running = true;
        requestAnimationFrame(frame);
    }

    host.addEventListener("mousemove", wake);
    host.addEventListener("mouseenter", wake);
    host.style.setProperty("--mouse-x", "-500px");
    host.style.setProperty("--mouse-y", "-500px");
}

document.addEventListener("DOMContentLoaded", () => {
    // Navbar. The brand children carry their own colours, so tagging
    // .navbar-brand alone left "MarketLink" cream on a bright photo.
    attachFlashlight(
        document.getElementById("mlHeaderNav"),
        ".ml-navbar-reveal-layer",
        ".nav-link, .navbar-brand, .ml-icon-btn, .ml-brand-name, .ml-brand-sub, .ml-brand-icon, .ml-badge, .ml-btn-ghost",
        90
    );

    // Footer. The footer text is spread over many elements and several carry
    // Bootstrap's !important text utilities (.text-white, .text-white-50,
    // .text-leaf), so it is tagged broadly and the colour is overridden with
    // an equally !important rule in the stylesheet. The two tinted panels are
    // tagged too, because their own dark backgrounds would otherwise swallow
    // the beam and leave dark text sitting on a dark strip.
    attachFlashlight(
        document.querySelector(".ml-footer-redesign"),
        ".ml-footer-reveal-layer",
        "a, h5, h6, p, span, i, strong, small, li, div.ml-footer-heading, div.ml-hours-note, div.ml-fbadge, div.ml-footer-text, div.ml-footer-hours-box, div.ml-footer-bottom",
        74
    );
});
