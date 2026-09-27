<style>
/* CSS for Scroll-Bound Canvas Video */
.scroll-video-container {
    height: 300vh; /* This determines how long the user has to scroll to finish the animation. 300vh = 3 screens tall */
    position: relative;
    background-color: var(--theme-lightest, #f4f8ee);
}

.scroll-canvas-sticky {
    position: sticky;
    top: 0;
    height: 100vh;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

#hero-lightpass {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures the canvas fills the screen nicely */
    max-width: 100vw;
}

/* Optional Overlay text */
.scroll-video-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: var(--theme-dark, #24362a);
    z-index: 10;
    pointer-events: none; /* So it doesn't block scrolling/clicking */
}

.scroll-video-text h1 {
    font-size: clamp(3rem, 5vw, 5rem);
    font-weight: 800;
    text-shadow: 0 4px 20px rgba(255,255,255,0.7);
    margin: 0;
}
</style>

<div class="scroll-video-container" id="video-section">
    <div class="scroll-canvas-sticky">
        <canvas id="hero-lightpass"></canvas>
        
        <div class="scroll-video-text">
            <h1>Fresh From Farm</h1>
            <p style="font-size: 1.2rem; font-weight: 600; text-shadow: 0 2px 10px rgba(255,255,255,0.8);">Scroll down to see the magic</p>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("hero-lightpass");
    const context = canvas.getContext("2d");

    // SETTINGS: Adjust these according to your extracted frames
    const frameCount = 239; // TOTAL NUMBER OF FRAMES YOU EXTRACTED
    const frameFolder = "<?php echo ML_asset('Uploads/video-frames/ezgif-6b566ceec912a5ba-jpg/'); ?>"; // Exact path with subfolder
    const framePrefix = "ezgif-frame-"; // Changed to match your screenshot
    const frameExtension = ".jpg"; 

    // Function to format numbers to match your image names (e.g. 1 -> 001)
    // NOTE: Ezgif sometimes starts at 001, sometimes 002. We offset by 1 if 001 is missing.
    const currentFrame = index => {
        const actualIndex = index + 1; // Since your first file is ezgif-frame-002.jpg
        return `${frameFolder}${framePrefix}${actualIndex.toString().padStart(3, '0')}${frameExtension}`;
    };

    // Preload images to prevent flashing/lagging
    const images = [];
    const preloadImages = () => {
        for (let i = 1; i <= frameCount; i++) {
            const img = new Image();
            img.src = currentFrame(i);
            images.push(img);
        }
    };

    // Set canvas dimensions (Adjust to your video's original resolution)
    canvas.width = 1920;
    canvas.height = 1080;

    // Draw the first frame once it loads
    const img = new Image();
    img.src = currentFrame(1);
    img.onload = function() {
        context.drawImage(img, 0, 0, canvas.width, canvas.height);
    }

    // Scroll magic
    const container = document.querySelector('.scroll-video-container');

    window.addEventListener('scroll', () => {
        // Calculate how far down the user has scrolled within the container
        const scrollTop = window.scrollY - container.offsetTop;
        const maxScrollTop = container.scrollHeight - window.innerHeight;
        
        if (scrollTop < 0 || scrollTop > maxScrollTop) return; // Only animate when in view

        const scrollFraction = scrollTop / maxScrollTop;
        
        // Calculate the corresponding frame index
        const frameIndex = Math.min(
            frameCount - 1,
            Math.ceil(scrollFraction * frameCount)
        );
        
        // Request animation frame for smooth drawing
        requestAnimationFrame(() => {
            if (images[frameIndex]) {
                context.drawImage(images[frameIndex], 0, 0, canvas.width, canvas.height);
            }
        });
    });

    preloadImages();
});
</script>
