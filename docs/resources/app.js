marked.setOptions({ gfm: true, breaks: false });

const SHOW_SPLIT = typeof PACKAGES !== "undefined";

const CATEGORY_COLORS = {
    "Basics":                 "#e63946",
    "Types & Error Handling":  "#457b9d",
    "Async":                  "#2a9d8f",
    "Collections":            "#e9c46a",
    "Text & Encoding":        "#f4a261",
    "I/O":                    "#264653",
    "Identifiers":            "#3d5a80",
    "Networking":             "#6a4c93",
    "Protocols":              "#0891b2",
    "Terminal":               "#1d3557",
    "Security":               "#d62828",
    "System":                 "#606c38",
    "Other":                  "#666",
};

const FEATURES = [
    {
        title: "Type-Safe from Input to Output",
        description: "Validate and coerce untrusted data with composable type combinators. Shapes, unions, optionals: all with zero reflection overhead.",
        code: `use Psl\\Type;\n\n$userType = Type\\shape([\n    'name' => Type\\non_empty_string(),\n    'age'  => Type\\positive_int(),\n    'tags' => Type\\vec(Type\\string()),\n]);\n\n$user = $userType->coerce($untrustedInput);\n// array{name: non-empty-string,\n//   age: positive-int, tags: list<string>}`,
        links: [["Type", "type"], ["Result", "result"], ["Option", "option"]],
    },
    {
        title: "Async Without the Ceremony",
        description: "Run concurrent operations with a single function call. Structured concurrency built on fibers. No promises, no callbacks.",
        code: `use Psl\\Async;\nuse Psl\\IO;\n\nAsync\\main(static function(): int {\n    [$a, $b, $c] = Async\\concurrently([\n        static fn() => Async\\sleep(0.1),\n        static fn() => Async\\sleep(0.2),\n        static fn() => Async\\sleep(0.1),\n    ]);\n\n    IO\\write_error_line('Done in ~0.2s, not 0.4s');\n\n    return 0;\n});`,
        links: [["Async", "async"], ["Channel", "channel"], ["IO", "io"]],
    },
    {
        title: "Collections That Make Sense",
        description: "Map, filter, sort, and reshape arrays with pure functions. Separate return types for lists and dicts. No more array key confusion.",
        code: `use Psl\\Vec;\nuse Psl\\Dict;\nuse Psl\\Str;\n\n$names = ['alice', 'bob', 'charlie', 'dave'];\n\nVec\\map($names, Str\\uppercase(...));\n// ['ALICE', 'BOB', 'CHARLIE', 'DAVE']\n\nVec\\filter($names, fn($n) => Str\\length($n) > 3);\n// ['alice', 'charlie', 'dave']\n\nDict\\pull($names, Str\\uppercase(...), fn($n) => $n);\n// {alice: 'ALICE', bob: 'BOB', ...}`,
        links: [["Vec", "vec"], ["Dict", "dict"], ["Iter", "iter"]],
    },
    {
        title: "TCP Server in 10 Lines",
        description: "Production-ready networking primitives. TCP, TLS, UDP, Unix sockets: all async, all composable.",
        code: `use Psl\\Async;\nuse Psl\\TCP;\nuse Psl\\IO;\n\nAsync\\main(static function(): int {\n    $server = TCP\\listen('127.0.0.1', 8080);\n    IO\\write_error_line('Listening on :8080');\n\n    while (true) {\n        $conn = $server->accept();\n        Async\\run(static function() use ($conn) {\n            $conn->writeAll("Hello!\\n");\n            $conn->close();\n        })->ignore();\n    }\n});`,
        links: [["TCP", "tcp"], ["Network", "network"], ["Async", "async"]],
    },
];

function escape_html(s) {
    return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function extract_descriptions() {
    const result = {};
    for (const [slug, md] of Object.entries(DOCS)) {
        for (const line of md.split("\n").slice(1)) {
            const trimmed = line.trim();
            if (trimmed.length <= 10 || /^[#`|*-]/.test(trimmed)) continue;
            const clean = trimmed.replace(/\[([^\]]+)\]\([^)]+\)/g, "$1").replace(/`([^`]+)`/g, "$1");
            result[slug] = clean.length > 100 ? clean.slice(0, 97) + "..." : clean;
            break;
        }
    }
    return result;
}

function get_theme() {
    return document.documentElement.dataset.theme;
}

function set_theme(value) {
    document.documentElement.dataset.theme = value;
    localStorage.setItem("theme", value);
    const btn = document.getElementById("theme-toggle");
    if (btn) btn.textContent = value === "dark" ? "Light" : "Dark";
}

function init_theme() {
    const saved = localStorage.getItem("theme");
    const prefers_dark = matchMedia("(prefers-color-scheme: dark)").matches;
    set_theme(saved || (prefers_dark ? "dark" : "light"));
}

function toggle_theme() {
    set_theme(get_theme() === "dark" ? "light" : "dark");
}

function open_sidebar() {
    document.getElementById("sidebar").classList.add("open");
    document.getElementById("overlay").classList.add("open");
    document.getElementById("hamburger").style.display = "none";
}

function close_sidebar() {
    document.getElementById("sidebar").classList.remove("open");
    document.getElementById("overlay").classList.remove("open");
    document.getElementById("hamburger").style.display = "";
}

function filter_nav(query) {
    const q = query.toLowerCase();
    for (const link of document.querySelectorAll(".nav-link")) {
        if (!link.dataset.slug && link.dataset.slug !== "") continue;
        link.style.display = q === "" || link.textContent.toLowerCase().includes(q) ? "" : "none";
    }
    for (const cat of document.querySelectorAll(".nav-category")) {
        let sib = cat.nextElementSibling;
        let visible = false;
        while (sib && !sib.classList.contains("nav-category")) {
            if (sib.classList.contains("nav-link") && sib.style.display !== "none") visible = true;
            sib = sib.nextElementSibling;
        }
        cat.style.display = visible ? "" : "none";
    }
}

function observe_fade_ins() {
    const observer = new IntersectionObserver(
        (entries) => entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add("visible"); observer.unobserve(e.target); }
        }),
        { threshold: 0.1 },
    );
    for (const el of document.querySelectorAll(".fade-in")) observer.observe(el);
}

function add_copy_buttons(container) {
    for (const pre of container.querySelectorAll("pre")) {
        const code = pre.querySelector("code");
        const btn = document.createElement("button");
        btn.className = "copy-btn";
        btn.textContent = "Copy";
        btn.addEventListener("click", () => {
            navigator.clipboard.writeText(code ? code.textContent : "").then(() => {
                btn.textContent = "Copied!";
                btn.classList.add("copied");
                setTimeout(() => { btn.textContent = "Copy"; btn.classList.remove("copied"); }, 1500);
            });
        });
        pre.appendChild(btn);
    }
}

function highlight_code(container, selector) {
    for (const block of container.querySelectorAll(selector)) {
        hljs.highlightElement(block);
    }
}

function build_nav() {
    let html = '<a class="nav-link" href="#" data-slug="">Home</a>';
    for (const [category, slugs] of Object.entries(CATEGORIES)) {
        html += `<div class="nav-category">${category}</div>`;
        for (const slug of slugs) {
            html += `<a class="nav-link" href="#${slug}" data-slug="${slug}">${TITLES[slug] || slug}</a>`;
        }
    }
    document.getElementById("nav-links").innerHTML = html;
}

function build_features() {
    return '<div id="features">' + FEATURES.map((f) => `
        <div class="feature fade-in">
            <div class="feature-code">
                <pre><code class="language-php">${escape_html(f.code)}</code></pre>
            </div>
            <div class="feature-text">
                <h3>${f.title}</h3>
                <p>${f.description}</p>
                <div class="feature-links">
                    ${f.links.map(([label, slug]) => `<a href="#${slug}">${label}</a>`).join("")}
                </div>
            </div>
        </div>
    `).join("") + '</div>';
}

function build_footer() {
    return `
        <div class="page-footer fade-in">
            <p class="footer-license">MIT License &middot; Made by <a href="https://github.com/azjezz">azjezz</a> and <a href="https://github.com/php-standard-library/php-standard-library/graphs/contributors">contributors</a> &middot; Sponsored by <a href="https://carthage.software">Carthage.Software</a></p>
        </div>
    `;
}

function build_front_page() {
    const descriptions = extract_descriptions();

    let html = `
        <div class="hero fade-in">
            <h1>PSL</h1>
            <p class="tagline">PHP Standard Library</p>
            <p class="hero-description">A standard library for PHP, inspired by <a href="https://github.com/hhvm/hsl">hhvm/hsl</a>. Provides a consistent, centralized, well-typed set of APIs covering async, collections, networking, I/O, cryptography, terminal UI, and more - replacing PHP functions and primitives with safer, async-ready alternatives that error predictably.</p>
            ${SHOW_SPLIT ? `<div class="install-box-wrapper"><a id="rotating-install" class="install-box install-box-typing" href="#type">composer require php-standard-library/type<span class="typing-cursor"></span></a></div>
            <p class="hero-separator">or get everything at once</p>` : ""}
            <div class="install-box-wrapper"><div class="install-box">composer require php-standard-library/php-standard-library</div></div>
            <div class="hero-links">
                <a href="https://github.com/php-standard-library/php-standard-library" class="hero-btn">GitHub</a>
                <a href="https://github.com/sponsors/azjezz" class="hero-btn hero-btn-sponsor">Sponsor</a>
            </div>
        </div>
    `;

    html += build_features();

    for (const [category, slugs] of Object.entries(CATEGORIES)) {
        const color = CATEGORY_COLORS[category] || "#000";
        html += `<div class="category-section fade-in">`;
        html += `<h2 style="border-color:${color}">${category}</h2>`;
        html += `<div class="component-grid">`;
        for (const slug of slugs) {
            const title = escape_html(TITLES[slug] || slug);
            const desc = escape_html(descriptions[slug] || "");
            html += `<div class="component-card" style="--accent:${color}"><a href="#${slug}"><h3>${title}</h3><p>${desc}</p></a></div>`;
        }
        html += `</div></div>`;
    }

    html += build_footer();

    return html;
}

let _rotatingTimeout = null;

function start_rotating_install() {
    if (_rotatingTimeout) clearTimeout(_rotatingTimeout);
    if (typeof PACKAGES === "undefined") return;

    const prefix = "composer require php-standard-library/";
    const slugs = Object.keys(PACKAGES);
    let index = 0;
    let phase = "wait";

    function get_suffix() {
        const pkg = PACKAGES[slugs[index]];
        return pkg.replace("php-standard-library/", "");
    }

    function tick() {
        const el = document.getElementById("rotating-install");
        if (!el) { _rotatingTimeout = null; return; }

        const cursor = el.querySelector(".typing-cursor");
        const current = el.textContent;
        const currentSuffix = current.slice(prefix.length);

        if (phase === "wait") {
            phase = "delete";
            _rotatingTimeout = setTimeout(tick, 4000);
            return;
        }

        if (phase === "delete") {
            if (currentSuffix.length > 0) {
                el.innerHTML = ""; el.append(prefix + currentSuffix.slice(0, -1), cursor);
                _rotatingTimeout = setTimeout(tick, 30);
            } else {
                index = (index + 1) % slugs.length;
                el.href = "#" + slugs[index];
                phase = "type";
                _rotatingTimeout = setTimeout(tick, 100);
            }
            return;
        }

        if (phase === "type") {
            const target = get_suffix();
            const typed = currentSuffix.length;
            if (typed < target.length) {
                el.innerHTML = ""; el.append(prefix + target.slice(0, typed + 1), cursor);
                _rotatingTimeout = setTimeout(tick, 50);
            } else {
                phase = "wait";
                _rotatingTimeout = setTimeout(tick, 0);
            }
        }
    }

    phase = "wait";
    tick();
}

function render() {
    const hash = location.hash.slice(1) || "";
    const el = document.getElementById("rendered");

    if (hash && DOCS[hash]) {
        el.innerHTML = marked.parse(DOCS[hash]);

        const pkg = SHOW_SPLIT ? PACKAGES[hash] : undefined;
        if (pkg) {
            const heading = el.querySelector("h1");
            if (heading) {
                const wrapper = document.createElement("div");
                wrapper.className = "install-box-wrapper";
                const box = document.createElement("div");
                box.className = "install-box";
                box.textContent = "composer require " + pkg;
                wrapper.appendChild(box);
                heading.after(wrapper);
            }
        }

        highlight_code(el, "pre code");
        add_copy_buttons(el);
    } else {
        el.innerHTML = build_front_page();
        highlight_code(el, "#features pre code");
        start_rotating_install();
    }

    for (const link of document.querySelectorAll(".nav-link")) {
        link.classList.toggle("active", link.dataset.slug === hash);
    }

    window.scrollTo(0, 0);
    close_sidebar();
    observe_fade_ins();
}

function init_version_switcher() {
    const select = document.getElementById("version-select");
    const option = document.createElement("option");
    option.value = CURRENT_VERSION;
    option.textContent = CURRENT_VERSION;
    option.selected = true;
    select.appendChild(option);

    fetch("/versions.json")
        .then((r) => r.ok ? r.json() : Promise.reject())
        .then((data) => {
            select.innerHTML = "";
            for (const version of data.versions) {
                const opt = document.createElement("option");
                opt.value = version;
                opt.textContent = version;
                opt.selected = version === CURRENT_VERSION;
                select.appendChild(opt);
            }
        })
        .catch(() => {});

    select.addEventListener("change", () => {
        const hash = location.hash.slice(1);
        location.href = "/" + select.value + "/" + (hash ? "#" + hash : "");
    });
}

init_theme();
init_version_switcher();
build_nav();

document.getElementById("search").addEventListener("input", (e) => filter_nav(e.target.value));
document.getElementById("hamburger").addEventListener("click", open_sidebar);
document.getElementById("overlay").addEventListener("click", close_sidebar);
document.getElementById("theme-toggle").addEventListener("click", toggle_theme);

document.addEventListener("keydown", (e) => {
    const search = document.getElementById("search");
    if (e.key === "/" && document.activeElement !== search) { e.preventDefault(); search.focus(); }
    if (e.key === "Escape") { search.value = ""; filter_nav(""); search.blur(); }
});

window.addEventListener("hashchange", render);
render();
