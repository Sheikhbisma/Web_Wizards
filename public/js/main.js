document.addEventListener("DOMContentLoaded", () => {
    let UserPassword = document.getElementById('passwordInput');
    let messageBox = document.getElementById('messageBox');
    let passLength = document.getElementById('length');
    let passalpha = document.getElementById('alpha');
    let characters = document.getElementById('characters');

    if (UserPassword) {
        UserPassword.addEventListener('focus', () => {
            messageBox.classList.remove('d-none');
        });
        
      

        UserPassword.addEventListener('input', () => {
            const passwordValue = UserPassword.value;

            passLength.className = passwordValue.length >= 8 ? "text-success mb-1" : "text-danger mb-1";
            passLength.innerHTML = passwordValue.length >= 8 ? "&#10004; Password must be at least 8 characters long." : "&#10008; Password must be at least 8 characters long.";

            let checkAlpha = /[A-Za-z]/.test(passwordValue);
            passalpha.className = checkAlpha ? "text-success mb-1" : "text-danger mb-1";
            passalpha.innerHTML = checkAlpha ? "&#10004; Must contain at least one alphabet (A-Z or a-z)." : "&#10008; Must contain at least one alphabet (A-Z or a-z).";

            let checkchars = /(?=.*\d)(?=.*[!@#$%^&*])/.test(passwordValue);
            characters.className = checkchars ? "text-success mb-1" : "text-danger mb-1";
            characters.innerHTML = checkchars ? "&#10004; Must contain at least one number and one special character (e.g. @, #, $)." : "&#10008; Must contain at least one number and one special character (e.g. @, #, $).";
        });
    }
});


function validateSignUp(e) {
    e.preventDefault();
    let isValid = true;

    let username = document.getElementById("fullName").value;
    let contact = document.getElementById("contactNumber").value; // Sahi ID
    let passwordValue = document.getElementById('passwordInput').value;
    
    const contactPattern = /^03[012347][0-9]{8}$/;

    if (username.length < 3) {
        document.querySelector("#usernameerr").classList.remove("d-none");
        isValid = false;
    } else {
        document.querySelector("#usernameerr").classList.add("d-none");
    }

    if (!contactPattern.test(contact)) {
        document.querySelector("#contacterr").classList.remove("d-none");
        isValid = false;
    } else {
        document.querySelector("#contacterr").classList.add("d-none");
    }

    let isLengthValid = passwordValue.length >= 8;
    let checkAlpha = /[A-Za-z]/.test(passwordValue);
    let checkchars = /(?=.*\d)(?=.*[!@#$%^&*])/.test(passwordValue);

    if (!isLengthValid || !checkAlpha || !checkchars) {
        document.querySelector("#passerr").classList.remove("d-none");
        isValid = false;
    }

    let roleBox = document.getElementById("role");
    if (roleBox && roleBox.value === "farmer") {
        let stallName = document.getElementById("stallName").value.trim();
        let contactPerson = document.getElementById("contactPerson").value.trim();
        let farmerAddress = document.getElementById("farmerAddress").value.trim();
        if (stallName === "" || contactPerson === "" || farmerAddress === "") {
            alert("Farmers must enter stall name, contact person and address");
            isValid = false;
        }
    }

    if (isValid) {
        console.log("success");
        document.getElementById("signupform").submit();
    } else {
        console.log("err");
    }

    return isValid;
}

const sign_in_btn = document.querySelector("#sign-in-btn");
const sign_up_btn = document.querySelector("#sign-up-btn");
const container = document.querySelector(".custom-login-container");

if (sign_up_btn) {
    sign_up_btn.addEventListener("click", () => {
        console.log("Sign up button clicked!"); 
        container.classList.add("sign-up-mode");
    });
}

if (sign_in_btn) {
    sign_in_btn.addEventListener("click", () => {
        console.log("Sign in button clicked!");
        container.classList.remove("sign-up-mode");
    });
}

let roleSelect = document.getElementById("role");
let farmerExtra = document.getElementById("farmerExtra");
if (roleSelect && farmerExtra) {
    roleSelect.addEventListener("change", function () {
        if (this.value === "farmer") {
            farmerExtra.classList.remove("d-none");
        } else {
            farmerExtra.classList.add("d-none");
        }
    });
}

/* ============================================================
     MarketLink Premium - Home hero slider + parallax + search
   ============================================================ */
(function () {
    var stage = document.getElementById("mlpHeroStage");
    var region = document.getElementById("mlpHero3D");
    var dotsBox = document.getElementById("mlpHeroDots");
    var slides = [];
    var timer = null;

    function go(i) {
        if (!slides.length) { return; }
        var n = slides.length;
        var idx = ((i % n) + n) % n;
        for (var s = 0; s < n; s++) {
            slides[s].classList.toggle("active", s === idx);
            var dot = dotsBox ? dotsBox.children[s] : null;
            if (dot) { dot.classList.toggle("active", s === idx); }
        }
        if (stage) { stage.style.transform = "rotateX(5deg) rotateY(" + (8 - idx * 5) + "deg)"; }
        startAuto();
    }

    function next() { go((slides.indexOf(document.querySelector(".mlp-d3-slide.active")) + 1)); }
    function prev() { go((slides.indexOf(document.querySelector(".mlp-d3-slide.active")) - 1)); }

    function startAuto() {
        if (timer) { clearInterval(timer); }
        timer = setInterval(next, 6000);
    }
    function stopAuto() { if (timer) { clearInterval(timer); timer = null; } }

    if (stage && dotsBox) {
        slides = Array.prototype.slice.call(stage.querySelectorAll(".mlp-d3-slide"));
        slides.forEach(function (_, i) {
            var d = document.createElement("span");
            d.addEventListener("click", function () { go(i); });
            dotsBox.appendChild(d);
        });
        go(0);
        stage.querySelectorAll(".mlp-d3-nav.prev")[0].addEventListener("click", prev);
        stage.querySelectorAll(".mlp-d3-nav.next")[0].addEventListener("click", next);
        if (region) {
            region.addEventListener("mouseenter", stopAuto);
            region.addEventListener("mouseleave", startAuto);
        }
    }

    /* gentle mouse tilt + floating item parallax */
    if (region) {
        var floats = Array.prototype.slice.call(region.querySelectorAll(".mlp-d3-fo"));
        region.addEventListener("mousemove", function (e) {
            if (!stage) { return; }
            var r = region.getBoundingClientRect();
            var dx = (e.clientX - r.left) / r.width - 0.5;
            var dy = (e.clientY - r.top) / r.height - 0.5;
            stage.style.transform = "rotateX(" + (-dy * 8) + "deg) rotateY(" + (dx * 12) + "deg)";
            floats.forEach(function (f) {
                var depth = parseFloat(f.getAttribute("data-depth") || "20");
                f.style.marginLeft = (dx * depth) + "px";
                f.style.marginTop = (dy * depth) + "px";
            });
        });
        region.addEventListener("mouseleave", function () {
            if (stage) { stage.style.transform = ""; }
            floats.forEach(function (f) { f.style.marginLeft = ""; f.style.marginTop = ""; });
        });
    }

    /* ============================================================
       Scroll experience
       1. reading-progress bar in the navbar
       2. directional reveals for [data-reveal]
       3. staggered reveals for children of [data-stagger]
       4. count-up numbers for [data-count-to]
       All rAF-throttled and skipped when the user prefers
       reduced motion.
       ============================================================ */
    (function () {
        var reduced = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        /* --- 1. progress bar ------------------------------------- */
        var bar = document.querySelector(".ml-scroll-progress > i");
        var barBox = document.querySelector(".ml-scroll-progress");
        if (bar && barBox && !reduced) {
            var tickingBar = false;
            var paintBar = function () {
                tickingBar = false;
                var doc = document.documentElement;
                var max = doc.scrollHeight - window.innerHeight;
                var pct = max > 0 ? (window.scrollY / max) * 100 : 0;
                if (pct < 0) { pct = 0; } else if (pct > 100) { pct = 100; }
                bar.style.width = pct.toFixed(2) + "%";
            };
            var askBar = function () {
                if (tickingBar) { return; }
                tickingBar = true;
                window.requestAnimationFrame(paintBar);
            };
            window.addEventListener("scroll", askBar, { passive: true });
            window.addEventListener("resize", askBar, { passive: true });
            paintBar();
        }

        /* --- 2 + 3. reveals -------------------------------------- */
        var targets = Array.prototype.slice.call(document.querySelectorAll("[data-reveal]"));

        /* Any container marked data-stagger reveals its direct element
           children one after another instead of all at once. */
        Array.prototype.slice.call(document.querySelectorAll("[data-stagger]")).forEach(function (group) {
            var step = parseInt(group.getAttribute("data-stagger"), 10) || 90;
            var kids = group.children;
            for (var i = 0; i < kids.length; i++) {
                var kid = kids[i];
                if (kid.getAttribute("data-reveal")) { continue; }
                kid.setAttribute("data-reveal", kid.tagName === "IMG" ? "zoom" : "up");
                kid.style.setProperty("--reveal-delay", (i * step) + "ms");
                targets.push(kid);
            }
        });

        /* No will-change here on purpose. Promoting ~40 sections and cards
           at once exhausts Chrome's texture memory, and it then downsamples
           the layers, which renders the whole page blurry. Chrome already
           composites an element for the length of a CSS transition, so the
           hint buys nothing and costs the entire page its sharpness. */
        var show = function (el) {
            el.classList.add("is-revealed");
        };

        if (reduced) {
            targets.forEach(function (el) { el.classList.add("is-revealed"); });
        } else if ("IntersectionObserver" in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        show(entry.target);
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
            targets.forEach(function (el) { io.observe(el); });
        } else {
            targets.forEach(function (el) { el.classList.add("is-revealed"); });
        }

        /* --- 4. count-up numbers --------------------------------- */
        var counters = Array.prototype.slice.call(document.querySelectorAll("[data-count-to]"));
        var runCount = function (el) {
            var target = parseFloat(el.getAttribute("data-count-to"));
            if (isNaN(target)) { return; }
            var suffix = el.getAttribute("data-count-suffix") || "";
            var dur = 1400;
            var start = null;
            var step = function (now) {
                if (start === null) { start = now; }
                var t = Math.min(1, (now - start) / dur);
                /* easeOutCubic so it settles instead of stopping dead */
                var eased = 1 - Math.pow(1 - t, 3);
                el.textContent = Math.round(target * eased) + suffix;
                if (t < 1) { window.requestAnimationFrame(step); }
            };
            el.textContent = "0" + suffix;
            window.requestAnimationFrame(step);
        };

        if (counters.length) {
            if (reduced) {
                counters.forEach(function (el) {
                    el.textContent = el.getAttribute("data-count-to") + (el.getAttribute("data-count-suffix") || "");
                });
            } else if ("IntersectionObserver" in window) {
                var cio = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            runCount(entry.target);
                            cio.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.6 });
                counters.forEach(function (el) { cio.observe(el); });
            } else {
                counters.forEach(function (el) {
                    el.textContent = el.getAttribute("data-count-to") + (el.getAttribute("data-count-suffix") || "");
                });
            }
        }
    })();

    window.MLP = {
        runSearch: function (form) {
            var q = form.querySelector("#mlpSearchQ");
            var loc = form.querySelector("#mlpSearchLoc");
            var day = form.querySelector("#mlpSearchDay");
            var tab = form.querySelector("#mlpSearchTab");
            var term = (q && q.value.trim()) || "";
            var where = (loc && loc.value.trim()) || "";
            var when = (day && day.value) || "";
            if (!term && (where || when)) {
                if (tab) { tab.value = "markets"; }
                if (q) { q.value = [where, when].filter(Boolean).join(" "); }
            } else if (tab) {
                tab.value = "products";
            }
            return true;
        }
    };
})();



