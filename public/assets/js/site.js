// Mobile nav toggle
(function () {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('main-nav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
        nav.classList.toggle('open');
    });

    document.querySelectorAll('#main-nav a').forEach(function (link) {
        link.addEventListener('click', function () {
            nav.classList.remove('open');
        });
    });
})();

// Hero video fallback and autoplay handoff
(function () {
    var video = document.getElementById('hero-video');
    if (!video) return;

    var media = video.closest('.hero-media');
    if (!media) return;

    function revealVideo() {
        media.classList.add('is-ready');
    }

    function tryPlay() {
        var playPromise = video.play();
        if (playPromise && typeof playPromise.then === 'function') {
            playPromise.then(revealVideo).catch(function () {
                // Keep fallback image visible if autoplay is blocked or source is unavailable.
            });
        }
    }

    video.addEventListener('loadeddata', tryPlay);
    video.addEventListener('canplay', revealVideo);
    video.addEventListener('playing', revealVideo);

    if (video.readyState >= 2) {
        tryPlay();
    }
})();

// Product carousel controls
(function () {
    var carousel = document.querySelector('.carousel');
    if (!carousel) return;

    var cards = carousel.querySelectorAll('.product-card');
    var prev = document.querySelector('.carousel-btn.prev');
    var next = document.querySelector('.carousel-btn.next');
    var status = document.querySelector('.carousel-status');
    var cardWidth = cards.length ? cards[0].offsetWidth + 20 : 340;

    function updateStatus() {
        if (!status || !cards.length) return;
        var index = Math.round(carousel.scrollLeft / cardWidth) + 1;
        var boundedIndex = Math.max(1, Math.min(cards.length, index));
        status.textContent = boundedIndex + ' of ' + cards.length;
    }

    if (prev) {
        prev.addEventListener('click', function () {
            carousel.scrollBy({ left: -cardWidth, behavior: 'smooth' });
        });
    }

    if (next) {
        next.addEventListener('click', function () {
            carousel.scrollBy({ left: cardWidth, behavior: 'smooth' });
        });
    }

    carousel.addEventListener('scroll', updateStatus, { passive: true });
    window.addEventListener('resize', function () {
        cardWidth = cards.length ? cards[0].offsetWidth + 20 : 340;
        updateStatus();
    });

    updateStatus();
})();

// FAQ tabs
(function () {
    var tabs = document.querySelectorAll('.faq-tab');
    var items = document.querySelectorAll('.faq-item');
    if (!tabs.length || !items.length) return;

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (item) {
                item.classList.remove('active');
            });
            tab.classList.add('active');

            var cat = tab.dataset.cat;
            items.forEach(function (item) {
                item.style.display = cat === 'all' || item.dataset.cat === cat ? '' : 'none';
            });
        });
    });
})();

// FAQ search filter
(function () {
    var searchInput = document.querySelector('.faq-search-wrap input');
    var items = document.querySelectorAll('.faq-item');
    if (!searchInput || !items.length) return;

    searchInput.addEventListener('input', function () {
        var q = searchInput.value.toLowerCase().trim();
        items.forEach(function (item) {
            var text = item.textContent.toLowerCase();
            item.style.display = !q || text.includes(q) ? '' : 'none';
        });
    });
})();

// Prevent placeholder links from jumping
document.querySelectorAll('a[href="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
        e.preventDefault();
    });
});

// How It Works — staggered reveal
(function () {
    var container = document.getElementById('hiw-steps');
    if (!container) return;

    var steps = Array.from(container.querySelectorAll('.hiw-animate'));
    if (!steps.length) return;

    var fired = false;
    var observer = new IntersectionObserver(function (entries) {
        if (fired || !entries[0].isIntersecting) return;
        fired = true;
        observer.disconnect();

        steps.forEach(function (step, i) {
            setTimeout(function () {
                step.classList.add('hiw-visible');
            }, i * 200);
        });
    }, { threshold: 0.2 });

    observer.observe(container);
})();

// Count-up animation for stats band
(function () {
    var nums = document.querySelectorAll('.stat-num[data-target]');
    if (!nums.length) return;

    function countUp(el) {
        var target = parseFloat(el.dataset.target);
        var suffix = el.dataset.suffix || '';
        var duration = 1400;
        var start = null;

        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var value = target * eased;
            el.textContent = (Number.isInteger(target) ? Math.floor(value) : value.toFixed(1)) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                countUp(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    nums.forEach(function (el) { observer.observe(el); });
})();

// Quick-quote form placeholder submit
(function () {
    var form = document.getElementById('qq-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('.qq-submit');
        var success = document.getElementById('qq-success');
        if (btn) { btn.textContent = 'Sent'; btn.disabled = true; }
        if (success) success.hidden = false;
    });
})();

// Brochure modal lead capture and download
(function () {
    var openers = document.querySelectorAll('[data-brochure-open]');
    var closers = document.querySelectorAll('[data-brochure-close]');
    var modal = document.querySelector('[data-brochure-modal]');
    var form = document.querySelector('[data-brochure-form]');
    var feedback = document.querySelector('[data-brochure-feedback]');

    if (!modal || !form || !openers.length) return;

    function setFeedback(message, isError) {
        if (!feedback) return;
        feedback.hidden = false;
        feedback.textContent = message;
        feedback.classList.toggle('is-error', !!isError);
        feedback.classList.toggle('is-success', !isError);
    }

    function openModal() {
        modal.hidden = false;
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('modal-open');
        if (feedback) {
            feedback.hidden = true;
            feedback.textContent = '';
            feedback.classList.remove('is-error', 'is-success');
        }
    }

    openers.forEach(function (button) {
        button.addEventListener('click', openModal);
    });

    closers.forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var submit = form.querySelector('button[type="submit"]');
        var payload = new FormData(form);

        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Submitting...';
        }

        fetch('/brochure/request', {
            method: 'POST',
            body: payload,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.data.success) {
                    var message = result.data.errors && result.data.errors.mobile ? result.data.errors.mobile : 'Unable to submit brochure request.';
                    throw new Error(message);
                }

                setFeedback('Lead captured. Download starting...', false);
                window.location.href = result.data.downloadUrl;
                setTimeout(closeModal, 1200);
                form.reset();
            })
            .catch(function (error) {
                setFeedback(error.message, true);
            })
            .finally(function () {
                if (submit) {
                    submit.disabled = false;
                    submit.textContent = 'Submit & Download';
                }
            });
    });
})();

// Glassmorphic circular animated equipment showcase
(function () {
    if (typeof gsap === 'undefined') return;

    var imageContainer = document.getElementById('image-container');
    var nameElement = document.getElementById('name');
    var designationElement = document.getElementById('designation');
    var quoteElement = document.getElementById('quote');
    var prevButton = document.getElementById('prev-button');
    var nextButton = document.getElementById('next-button');
    var canvas = document.getElementById('glcanvas');

    if (!imageContainer || !nameElement || !designationElement || !quoteElement || !prevButton || !nextButton) {
        return;
    }

    var testimonials = [
        {
            quote: 'Engineered support for loaders operating in confined underground conditions with high-duty hydraulic and feed-system demands.',
            name: 'Underground LHD Loaders',
            designation: 'Underground mining',
            src: '/assets/images/photo1.webp'
        },
        {
            quote: 'Built around heavy-haul applications where drivetrain, braking, and hydraulic uptime directly affect production continuity.',
            name: 'Dump Trucks',
            designation: 'Mining and construction',
            src: '/assets/images/photo2.webp'
        },
        {
            quote: 'Compatible with exploration and production drilling setups that require precision spares, feed assemblies, and rebuild support.',
            name: 'Drilling Rigs',
            designation: 'Exploration and production',
            src: '/assets/images/photo1.webp'
        },
        {
            quote: 'Configured for continuous-use logistics and port handling equipment where motion systems and service responsiveness are critical.',
            name: 'Port Equipment',
            designation: 'Ports and logistics',
            src: '/assets/images/photo2.webp'
        },
        {
            quote: 'Suited for infrastructure fleets that rely on reliable hydraulic, transmission, and wear-component performance on site.',
            name: 'Construction Machinery',
            designation: 'Infrastructure and earthworks',
            src: '/assets/images/photo1.webp'
        },
        {
            quote: 'Adapted for support vehicles and mobile field assets where rugged serviceability and dependable component life matter most.',
            name: 'Surface Support Equipment',
            designation: 'Service and field operations',
            src: '/assets/images/photo2.webp'
        }
    ];

    var activeIndex = 0;
    var autoplayInterval = null;

    function calculateGap(width) {
        var minWidth = 1024;
        var maxWidth = 1456;
        var minGap = 60;
        var maxGap = 86;

        if (width <= minWidth) return minGap;
        if (width >= maxWidth) return Math.max(minGap, maxGap + 0.06018 * (width - maxWidth));

        return minGap + (maxGap - minGap) * ((width - minWidth) / (maxWidth - minWidth));
    }

    function animateWords() {
        gsap.from('.word', {
            opacity: 0,
            y: 10,
            stagger: 0.02,
            duration: 0.2,
            ease: 'power2.out'
        });
    }

    function updateTestimonial(direction) {
        activeIndex = (activeIndex + direction + testimonials.length) % testimonials.length;

        var containerWidth = imageContainer.offsetWidth;
        var gap = calculateGap(containerWidth);
        var maxStickUp = gap * 0.8;

        testimonials.forEach(function (testimonial, index) {
            var img = imageContainer.querySelector('[data-index="' + index + '"]');
            if (!img) {
                img = document.createElement('img');
                img.src = testimonial.src;
                img.alt = testimonial.name;
                img.classList.add('testimonial-image');
                img.dataset.index = index;
                imageContainer.appendChild(img);
            }

            var offset = (index - activeIndex + testimonials.length) % testimonials.length;
            var zIndex = testimonials.length - Math.abs(offset);
            var scale = index === activeIndex ? 1 : 0.85;
            var opacity = index === activeIndex ? 1 : 0.72;
            var translateX;
            var translateY;
            var rotateY;

            if (offset === 0) {
                translateX = '0%';
                translateY = '0%';
                rotateY = 0;
            } else if (offset === 1 || offset === testimonials.length - 1) {
                translateX = offset === 1 ? '22%' : '-22%';
                translateY = '-' + ((maxStickUp / Math.max(img.offsetHeight || 1, 1)) * 100) + '%';
                rotateY = offset === 1 ? -15 : 15;
            } else {
                translateX = offset < testimonials.length / 2 ? '34%' : '-34%';
                translateY = '-18%';
                rotateY = offset < testimonials.length / 2 ? -24 : 24;
                opacity = 0;
                scale = 0.78;
            }

            gsap.to(img, {
                zIndex: zIndex,
                opacity: opacity,
                scale: scale,
                x: translateX,
                y: translateY,
                rotateY: rotateY,
                duration: 0.8,
                ease: 'power3.out'
            });
        });

        gsap.to([nameElement, designationElement], {
            opacity: 0,
            y: -20,
            duration: 0.25,
            ease: 'power2.in',
            onComplete: function () {
                nameElement.textContent = testimonials[activeIndex].name;
                designationElement.textContent = testimonials[activeIndex].designation;
                gsap.to([nameElement, designationElement], {
                    opacity: 1,
                    y: 0,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });

        gsap.to(quoteElement, {
            opacity: 0,
            y: -20,
            duration: 0.25,
            ease: 'power2.in',
            onComplete: function () {
                quoteElement.innerHTML = testimonials[activeIndex].quote.split(' ').map(function (word) {
                    return '<span class="word">' + word + '</span>';
                }).join(' ');
                gsap.to(quoteElement, {
                    opacity: 1,
                    y: 0,
                    duration: 0.3,
                    ease: 'power2.out'
                });
                animateWords();
            }
        });
    }

    function handleNext() {
        updateTestimonial(1);
    }

    function handlePrev() {
        updateTestimonial(-1);
    }

    prevButton.addEventListener('click', handlePrev);
    nextButton.addEventListener('click', handleNext);

    updateTestimonial(0);
    autoplayInterval = setInterval(handleNext, 5000);

    [prevButton, nextButton].forEach(function (button) {
        button.addEventListener('click', function () {
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
                autoplayInterval = null;
            }
        });
    });

    window.addEventListener('resize', function () {
        updateTestimonial(0);
    });

    if (!canvas) return;

    var gl = canvas.getContext('webgl');
    if (!gl) return;

    var vertexSource = '\n  attribute vec2 position;\n  varying vec2 vUv;\n  void main() {\n    vUv = position * 0.5 + 0.5;\n    gl_Position = vec4(position, 0.0, 1.0);\n  }\n';

    var fragmentSource = '\n  precision mediump float;\n  uniform float iTime;\n  uniform vec2 iResolution;\n  varying vec2 vUv;\n\n  float random(vec2 uv) {\n    return fract(sin(dot(uv.xy, vec2(12.9898, 78.233))) * 43758.5453123);\n  }\n\n  float noise(vec2 uv) {\n    vec2 i = floor(uv);\n    vec2 f = fract(uv);\n    float a = random(i);\n    float b = random(i + vec2(1.0, 0.0));\n    float c = random(i + vec2(0.0, 1.0));\n    float d = random(i + vec2(1.0, 1.0));\n    vec2 u = f * f * (3.0 - 2.0 * f);\n    return mix(a, b, u.x) + (c - a) * u.y * (1.0 - u.x) + (d - b) * u.x * u.y;\n  }\n\n  float fbm(vec2 uv) {\n    float value = 0.0;\n    float amplitude = 0.5;\n    for (int i = 0; i < 5; i++) {\n      value += amplitude * noise(uv);\n      uv *= 2.0;\n      amplitude *= 0.5;\n    }\n    return value;\n  }\n\n  void main() {\n    vec2 uv = vUv * 2.0 - 1.0;\n    uv.x *= iResolution.x / iResolution.y;\n    float t = iTime * 0.14;\n    float n = fbm(uv * 2.0 + t);\n    vec3 base = mix(vec3(0.06, 0.07, 0.11), vec3(0.95, 0.60, 0.16), smoothstep(0.2, 0.9, n));\n    vec3 glow = mix(base, vec3(0.20, 0.36, 0.82), smoothstep(0.55, 1.0, sin(uv.x * 3.0 + t * 4.0) * 0.5 + 0.5));\n    gl_FragColor = vec4(glow, 1.0);\n  }\n';

    function createShader(type, source) {
        var shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);
        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) return null;
        return shader;
    }

    function createProgram(vsrc, fsrc) {
        var vs = createShader(gl.VERTEX_SHADER, vsrc);
        var fs = createShader(gl.FRAGMENT_SHADER, fsrc);
        if (!vs || !fs) return null;

        var prog = gl.createProgram();
        gl.attachShader(prog, vs);
        gl.attachShader(prog, fs);
        gl.linkProgram(prog);
        if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return null;
        return prog;
    }

    function resizeCanvas() {
        var rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        gl.viewport(0, 0, canvas.width, canvas.height);
    }

    var prog = createProgram(vertexSource, fragmentSource);
    if (!prog) return;

    var posBuf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, posBuf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([
        -1, -1, 1, -1, -1, 1,
        -1, 1, 1, -1, 1, 1
    ]), gl.STATIC_DRAW);

    var posLoc = gl.getAttribLocation(prog, 'position');
    var iTimeLoc = gl.getUniformLocation(prog, 'iTime');
    var iResLoc = gl.getUniformLocation(prog, 'iResolution');

    function render(now) {
        resizeCanvas();
        gl.useProgram(prog);
        gl.bindBuffer(gl.ARRAY_BUFFER, posBuf);
        gl.enableVertexAttribArray(posLoc);
        gl.vertexAttribPointer(posLoc, 2, gl.FLOAT, false, 0, 0);
        gl.uniform1f(iTimeLoc, now * 0.001);
        gl.uniform2f(iResLoc, canvas.width, canvas.height);
        gl.drawArrays(gl.TRIANGLES, 0, 6);
        requestAnimationFrame(render);
    }

    requestAnimationFrame(render);
})();
