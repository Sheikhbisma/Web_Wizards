<style>
#hero-scroll-container {
    background-color: var(--theme-lightest, #f4f8ee);
    position: relative;
    font-family: 'Poppins', sans-serif;
    margin-top: 0 !important;
    padding-top: 0 !important;
    /* Kill any gap the Bootstrap/body default margin leaves below sticky navbar */
    display: block;
}

/* Also ensure the immediate parent (whatever home.php wraps this in) has no top padding */
#hero-scroll-container ~ *,
#hero-scroll-container + * { }
body > #hero-scroll-container,
.ml-body > #hero-scroll-container { margin-top: 0 !important; }

#canvas-hero-section {
    position: relative;
    width: 100%;
    height: 100vh;
    min-height: 500px;
    overflow: hidden;
    margin-top: 0;
    padding-top: 0;
    background: radial-gradient(circle at center, #e4eed4 0%, #f4f8ee 100%);
}

#hero-canvas {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100vw;
    height: 100vh;
    object-fit: cover;
    z-index: 1;
    image-rendering: -webkit-optimize-contrast; /* Sharper on Chrome/Safari */
    image-rendering: crisp-edges;               /* Sharper on Firefox */
}

/* Gradient overlay to make text readable */
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(244,248,238,0.85) 0%, rgba(244,248,238,0.1) 50%, rgba(244,248,238,0.85) 100%);
    z-index: 2;
    pointer-events: none;
}

.canvas-hero-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    z-index: 3;
    pointer-events: none;
}

/* Base text styling */
.hero-text-step {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0; 
    width: 40%;
    color: var(--theme-dark, #24362a);
}

/* Left side elements */
.left-side {
    left: 5%;
}

/* Right side elements */
.right-side {
    right: 5%;
    text-align: right;
}

/* Center Intro Element */
.center-intro {
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    width: 80%;
    opacity: 1; /* Visible at start */
}

.hero-text-step h5 {
    font-size: 1rem;
    letter-spacing: 3px;
    color: var(--theme-accent, #a0bc79);
    margin-bottom: 15px;
    font-weight: 700;
    text-transform: uppercase;
}

.hero-text-step h1 {
    font-size: clamp(2.5rem, 4vw, 4.5rem);
    font-family: 'Fraunces', Georgia, serif;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 20px;
    color: var(--theme-primary, #4a5f31);
    text-shadow: 0 4px 15px rgba(255,255,255,0.9);
}

/* Infinite left-to-right marquee for the step-0 headline.
   The h1 itself is only the clipping window; all movement happens on
   .hero-marquee-track inside it. That keeps .center-intro's
   translate(-50%, -50%) centring -- and the scroll timeline's y:-50 on
   .step-0 -- completely untouched, so the two systems never fight.
   nowrap + two identical copies is what makes the loop seamless: the
   track slides exactly one copy-width, so copy two lands where copy one
   started and the reset is never visible. */
.hero-marquee {
    overflow: hidden;
    width: 100%;
    white-space: nowrap;
}

.hero-marquee-track {
    display: inline-flex;
    align-items: center;
    will-change: transform;
}

.hero-marquee-set {
    flex: none;          /* copies must never compress, or they misalign */
    padding-right: 0.55em;
}

.hero-text-step p {
    font-size: clamp(1.1rem, 1.5vw, 1.3rem);
    font-weight: 500;
    line-height: 1.6;
    color: #4a5f31;
    text-shadow: 0 2px 10px rgba(255,255,255,0.9);
}
</style>

<!-- Load GSAP & ScrollTrigger -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

<div id="hero-scroll-container">
    <section id="canvas-hero-section">
        <canvas id="hero-canvas"></canvas>
        <div class="hero-overlay"></div>
        <div class="canvas-hero-content">
            
            <!-- Step 0: The Intro that appears instantly before scrolling -->
            <div class="hero-text-step center-intro step-0">
                <h5>Welcome to MarketLink</h5>
                <h1 class="hero-marquee" data-marquee>
                    <span class="hero-marquee-track">
                        <span class="hero-marquee-set">THE ULTIMATE PLATFORM FOR LOCAL FARMERS</span>
                        <span class="hero-marquee-set" aria-hidden="true">THE ULTIMATE PLATFORM FOR LOCAL FARMERS</span>
                    </span>
                </h1>
                <p>Scroll down to explore how we connect communities directly with farm-fresh produce.</p>
            </div>

            <!-- Step 1: Left Side -->
            <div class="hero-text-step left-side step-1">
                <h5>Direct Connections</h5>
                <h1>BROWSE & LOCATE NEARBY MARKETS</h1>
                <p>Use interactive maps to find local farmers. Browse weekly stocks, filter available products, and see accurate pickup points instantly.</p>
            </div>
            
            <!-- Step 2: Right Side -->
            <div class="hero-text-step right-side step-2">
                <h5>Seamless Experience</h5>
                <h1>PRE-ORDER & TRACK EFFORTLESSLY</h1>
                <p>Place your pre-orders online, track your history, save your favorite farmers, and leave reviews to support the local farming community.</p>
            </div>
            
            <!-- Step 3: Left Side -->
            <div class="hero-text-step left-side step-3">
                <h5>Smart AI Assistance</h5>
                <h1>INTELLIGENT FARMER MATCHING</h1>
                <p>Get answers to common questions about market timings, product details, and farmer availability directly through our smart AI feature.</p>
            </div>
            
        </div>
    </section>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    gsap.registerPlugin(ScrollTrigger);

    // ---- Fix any gap by making the section exactly fill the visible viewport ----
    function fixHeroHeight() {
        var nav = document.getElementById('mlHeaderNav');
        var topbar = document.querySelector('.ml-topbar');
        var section = document.getElementById('canvas-hero-section');
        if (!section) return;
        var navH = nav ? nav.offsetHeight : 0;
        var topbarH = (topbar && getComputedStyle(topbar).display !== 'none') ? topbar.offsetHeight : 0;
        // If navbar is sticky, the topbar is in normal flow above it.
        // The total offset already consumed = topbarH (topbar) + 0 (navbar is sticky, doesn't push content)
        // So hero just needs to be 100vh minus nothing, but we set min-height:
        section.style.height = 'calc(100vh - 0px)';
    }
    fixHeroHeight();
    window.addEventListener('resize', fixHeroHeight);

    const canvas = document.getElementById("hero-canvas");
    const context = canvas.getContext("2d");

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    window.addEventListener("resize", function () {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        render();
    });

    const frameCount = 239;  // frames 002 to 240 = 239 frames
    const frameFolder = "<?php echo ML_asset('Uploads/video-frames/ezgif-6b566ceec912a5ba-jpg/'); ?>";
    const framePrefix = "ezgif-frame-";
    const frameExtension = ".jpg"; 

    function files(index) {
        // Files start at 002, so index 0 = frame 002
        const actualIndex = index + 2; 
        return `${frameFolder}${framePrefix}${actualIndex.toString().padStart(3, '0')}${frameExtension}`;
    }

    const images = [];
    const imageSeq = { frame: 0 };

    for (let i = 0; i < frameCount; i++) {
        const img = new Image();
        img.src = files(i);
        images.push(img);
    }

    images[0].onload = render; // This will now fire correctly because 002 exists!

    function render() {
        if(!images[imageSeq.frame]) return;
        scaleImage(images[imageSeq.frame], context);
    }

    function scaleImage(img, ctx) {
        var canvas = ctx.canvas;
        var hRatio = canvas.width / img.width;
        var vRatio = canvas.height / img.height;
        var ratio = Math.max(hRatio, vRatio);
        var centerShift_x = (canvas.width - img.width * ratio) / 2;
        var centerShift_y = (canvas.height - img.height * ratio) / 2;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(
            img, 0, 0, img.width, img.height,
            centerShift_x, centerShift_y, img.width * ratio, img.height * ratio
        );
    }

    // GSAP ScrollTrigger Timeline
    let tl = gsap.timeline({
        scrollTrigger: {
            trigger: "#canvas-hero-section",
            pin: true,
            start: "top top",
            end: "+=600%", 
            scrub: 0.5
        }
    });

    // Scrub video frames
    tl.to(imageSeq, {
        frame: frameCount - 1,
        snap: "frame",
        ease: "none",
        onUpdate: render,
        duration: 1 
    }, 0);

    // Fade out the initial center intro text as we start scrolling
    tl.to(".step-0", { opacity: 0, duration: 0.05, y: -50 }, 0.02);

    // Step 1: Slides in from LEFT (-100px) and fades out upwards
    tl.to(".step-1", { opacity: 1, duration: 0.1, x: 0, startAt: {x: -100} }, 0.1)
      .to(".step-1", { opacity: 0, duration: 0.1, y: -50 }, 0.35);
      
    // Step 2: Slides in from RIGHT (100px) and fades out upwards
    tl.to(".step-2", { opacity: 1, duration: 0.1, x: 0, startAt: {x: 100} }, 0.45)
      .to(".step-2", { opacity: 0, duration: 0.1, y: -50 }, 0.65);
      
    // Step 3: Slides in from LEFT (-100px) and fades out upwards
    tl.to(".step-3", { opacity: 1, duration: 0.1, x: 0, startAt: {x: -100} }, 0.75)
      .to(".step-3", { opacity: 0, duration: 0.1, y: -50 }, 0.95);

    /* ---------- Step-0 headline: infinite left-to-right loop ----------
       Separate from the timeline above and on its own element, so the
       scroll-scrubbed fade of .step-0 still works normally. */
    const marqueeTrack = document.querySelector("[data-marquee] .hero-marquee-track");
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (marqueeTrack && !reduceMotion) {
        // Set the start offset BEFORE the tween is created, otherwise the
        // headline paints once at the wrong offset and jumps on frame one.
        // Start flush at 0 with copy 1 on screen, then slide leftwards so
        // copy 2 is the one that arrives. The track is exactly two copies
        // wide, so -50% == one copy-width and the reset is invisible.
        gsap.set(marqueeTrack, { xPercent: 0 });

        const marqueeLoop = gsap.to(marqueeTrack, {
            xPercent: -50,     // 0 -> -50 == one copy-width, leftwards
            duration: 13,
            ease: "none",      // linear, or the loop visibly pulses
            repeat: -1
        });

        // The intro fades out at timeline position 0.02. Keep the loop
        // running while it's on screen and stop it afterwards so we
        // aren't burning a rAF forever on invisible text.
        ScrollTrigger.create({
            trigger: ".step-0",
            start: "top bottom",
            end: "bottom top",
            onToggle: (self) => (self.isActive ? marqueeLoop.play() : marqueeLoop.pause())
        });
    }
});
</script>
